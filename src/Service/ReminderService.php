<?php

namespace App\Service;

use App\Entity\Reminder;
use App\Entity\Theme;
use App\Entity\ThemeEmailReminder;
use App\Entity\ThemeReminder;
use App\Entity\ThemeReminderJob;
use App\Entity\ThemeSmsReminder;
use App\Entity\User;
use App\Form\ThemeReminderType;
use App\Repository\MotivationalQuoteRepository;
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
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

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

    /**
     * @var MotivationalQuoteRepository
     */
    private $quoteRepository;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /** @var string */
    private $adminEmailAddress;

    /** @var string */
    private $adminEmailName;

    // TODO: Take this back out once we have configurable SMS numbers
    /** @var string */
    private $testSmsRecipientNo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        string $adminEmailAddress,
        string $adminEmailName,
        MotivationalQuoteRepository $quoteRepository,
        NotifierInterface $notifier,
        $testSmsRecipientNo
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->adminEmailAddress = $adminEmailAddress;
        $this->adminEmailName = $adminEmailName;
        $this->quoteRepository = $quoteRepository;
        $this->notifier = $notifier;
        $this->testSmsRecipientNo = $testSmsRecipientNo;
    }

    const DAY_SCHEDULE = [
        'everyday' => 'Every Day',
        'weekdays' => 'Every Weekday',
        'weekends' => 'Weekend Days'
    ];

    // TODO: Does this need to be public?
    public const REMINDER_TYPES = [
        'email' => [
            'name' => 'Email Reminder',
            'class' => ThemeEmailReminder::class
        ],
        'sms' => [
            'name' => 'Phone SMS Reminder',
            'class' => ThemeSmsReminder::class
        ]
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
            $this->createReminderJobsForTheme($theme);
        }
    }

    public function createReminderJobsForTheme(Theme $theme): void
    {
        foreach ($theme->getReminders() as $reminder) {
            $this->createThemeReminderJobs($theme, $reminder);
        }
    }

    public function getAvailableReminderTypesForUser(User $user)
    {
        $availableMethods = [];
        if ($user->getPhoneNumberSMS() !== null) {
            $availableMethods[] = 'sms';
        }
        if ($user->getEmail() !== null) {
            $availableMethods[] = 'email';
        }
        return array_filter(self::REMINDER_TYPES, fn ($type) => ($type['class'])::isAvailableFor($availableMethods));
    }

    private function createThemeReminderJobs(Theme $theme, ThemeReminder $reminder): void
    {
        $user = $theme->getOwner();
        if ($user === null) {
            throw new Exception('Expected every theme to have a User');
        }
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

        if ($reminder->getEnabled()) {
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
                        // something goes wrong. We mark *jobs* as complete even if
                        // the *reminder* is disabled, so we don't create a backlog
                        // of un-run jobs that might all send at once if someone
                        // re-enables the reminder.
                        $reminderJob->setWasRunAt($now);
                        $this->entityManager->flush();
                        // TODO: This should use Messenger, not hang around waiting.
                        if ($reminder->getEnabled()) {
                            // TODO: Polymorphism, separation of concerns, etc.
                            switch ($reminder->getReminderType()) {
                                case 'email':
                                    $this->sendEmailReminderForTheme($theme);
                                    break;
                                case 'notification':
                                    $this->sendNotificationReminderForTheme($theme);
                                    break;
                                default:
                                    throw new Exception('Unexpected reminder type: ' . $reminder->getReminderType());
                            }
                        }
                    }
                }
            }
        }
    }
    private function sendEmailReminderForTheme(Theme $theme): void
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

        $quote = $this->quoteRepository->getRandomQuote();

        $this->logger->info("Sending email to {$user->getEmail()} about theme {$theme->getId()}");
        $email = (new TemplatedEmail())
            ->from(new Address($this->adminEmailAddress, $this->adminEmailName))
            ->to(new Address($user->getEmail(), $name))
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject("Theme Reminder: {$themeName}")
            ->htmlTemplate('email/theme_reminder.html.twig')
            ->context([
                'theme' => $theme,
                'quote' => $quote
            ]);
        $this->mailer->send($email);
    }

    private function sendNotificationReminderForTheme(Theme $theme): void
    {
        $user = $theme->getOwner();
        if ($user === null) {
            throw new Exception('No user found.');
        }
        if ($user->getEmail() === null) {
            throw new Exception('Expected every user to have an email address.');
        }
        $themeName = $theme->getName();

        $notification = (new Notification("Theme Reminder for {$theme->getName()}", ['sms']))
            ->content("This is a test reminder. There's no content yet. There may be in the future.");

        $this->logger->info("Sending notification to {$user->getEmail()} about theme {$theme->getId()}");

        $recipient = new Recipient($user->getEmail(), $this->testSmsRecipientNo);
        $this->notifier->send($notification, $recipient);
    }
}
