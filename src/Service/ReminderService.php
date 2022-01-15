<?php

namespace App\Service;

use App\Entity\Reminder;
use App\Entity\Theme;
use App\Entity\ThemeReminder;
use App\Entity\ThemeReminderJob;
use App\Entity\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ReminderService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    const DAY_SCHEDULE = [
        'everyday' => 'Every Day',
        'weekdays' => 'Every Weekday',
        'weekends' => 'Weekend Days'
    ];

    /**
     * @return array<string, string>
     */
    public function getDayScheduleChoices()
    {
        // For ChoiceType we want the decoded version as the key
        return array_flip(self::DAY_SCHEDULE);
    }

    public function getFriendlyDaySchedule(string $schedule): string
    {
        return self::DAY_SCHEDULE[$schedule];
    }

    public function createThemeReminderJobsForUser(User $user): void
    {
        foreach ($user->getThemes() as $theme) {
            foreach ($theme->getReminders() as $reminder) {
                $this->createThemeReminderJobs($user, $reminder);
            }
        }
    }
    private function createThemeReminderJobs(User $user, ThemeReminder $reminder): void
    {
        $userTimezone = $user->getTimezone();
        if ($userTimezone === null || $userTimezone === '') {
            $this->logger->warning("User {$user->getId()} has no timezone set. Defaulting jobs to UTC");
            $userTimezone = 'UTC';
        }

        // Remove every existing job. They'll just be created again if necessary.
        // The removal of the existing jobs and the addition of the new ones will
        // happen in a transaction on flush() so we should be quite safe.
        $reminder->getReminderJobs()->forAll(function ($key, $entity) {
            $this->entityManager->remove($entity);
            return true;
        });

        // We go through all the next few days of the user's own timezone,
        // creating appropriate ReminderJobs in the UTC timezone.
        $now = CarbonImmutable::now('UTC');
        $start = CarbonImmutable::now(new CarbonTimeZone($user->getTimezone()))->setTime(0, 0);
        $end = new CarbonImmutable($start->addDays(7));
        for ($day = new Carbon($start); $day < $end; $day->addDays(1)) {
            // Create all appropriate reminder jobs for the day in question.
            if (
                $reminder->getDaySchedule() === 'everyday' ||
                ($reminder->getDaySchedule() === 'weekdays' && $day->isWeekday()) ||
                ($reminder->getDaySchedule() === 'weekends' && $day->isWeekend())
            ) {
                $reminderTimeOfDay = new Carbon($reminder->getTimeOfDay());
                $jobTime = (new Carbon($day))->setTime($reminderTimeOfDay->hour, $reminderTimeOfDay->minute);
                $jobTimeUtc = new CarbonImmutable($jobTime, 'UTC');
                if ($jobTimeUtc > $now) {  // Don't add jobs in the past.
                    $reminderJob = new ThemeReminderJob();
                    $reminderJob->setScheduledAt($jobTimeUtc);
                    $reminder->addReminderJob($reminderJob);
                    $this->entityManager->persist($reminderJob);
                    $this->logger->info("Set up job for " . $jobTimeUtc->toCookieString());
                }
            }
        }
        $this->entityManager->flush();
    }
    public function sendThemeRemindersForUser(User $user): void
    {
        $now = CarbonImmutable::now('UTC');
        foreach ($user->getThemes() as $theme) {
            foreach ($theme->getReminders() as $reminder) {
                foreach ($reminder->getReminderJobs() as $reminderJob) {
                    if (
                        !$reminderJob->hasBeenRun() &&
                        $reminderJob->getScheduledAt() < $now
                    ) {
                        // Email's not that transactional and I'd rather default to
                        // sending no reminder than sending multiple reminders if
                        // something goes wrong.
                        $reminderJob->setWasRunAt($now);
                        $this->entityManager->flush();
                        // TODO: This should use Messenger, not hang around waiting.
                        $this->sendReminderForTheme($theme);
                    }
                }
            }
        }
    }
    private function sendReminderForTheme(Theme $theme): void
    {
        $user = $theme->getOwner();
        if ($user === null) {
            throw new Exception('No user found.');
        }
        if ($user->getEmail() === null) {
            throw new Exception('Expected every user to have an email address.');
        }
        $themeName = $theme->getName();
        $name = is_null($user->getDisplayName()) ? '' : $user->getDisplayName();

        $this->logger->info("Sending email to {$user->getEmail()} about theme {$theme->getId()}");
        $email = (new TemplatedEmail())
            // Our From address is globally configured in the mailer config.
            // ->from('noreply@fresher.gothick.org.uk')
            ->to(new Address($user->getEmail(), $name))
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->htmlTemplate('email/theme_reminder.html.twig')
            ->context(['theme' => $theme]);
        $this->mailer->send($email);
    }
}
