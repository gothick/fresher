<?php

namespace App\Service;

use App\Entity\Theme;
use App\Entity\ThemeEmailReminder;
use App\Entity\ThemeReminder;
use App\Entity\ThemeSmsReminder;
use App\Entity\User;
use App\Repository\MotivationalQuoteRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;
use Twig\Environment;

class ReminderSenderService
{
    /** @var MotivationalQuoteRepository */
    private $quoteRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var MailerInterface */
    private $mailer;

    /** @var SmsService */
    private $smsService;

    /** @var Environment */
    private $twig;

    /** @var string */
    private $adminEmailAddress;

    /** @var string */
    private $adminEmailName;

    public function __construct(
        MotivationalQuoteRepository $quoteRepository,
        LoggerInterface $logger,
        MailerInterface $mailer,
        SmsService $smsService,
        Environment $twig,
        string $adminEmailAddress,
        string $adminEmailName
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->smsService = $smsService;
        $this->twig = $twig;
        $this->adminEmailAddress = $adminEmailAddress;
        $this->adminEmailName = $adminEmailName;
    }

    public function sendReminder(ThemeReminder $reminder): bool
    {
        // We can get cleverer later if we want to add more types, but for now this is simple enough
        if ($reminder instanceof ThemeEmailReminder) {
            return $this->sendEmailReminder($reminder);
        } elseif ($reminder instanceof ThemeSmsReminder) {
            return $this->sendSmsReminder($reminder);
        } else {
            throw new Exception('Unknown reminder type');
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getAvailableReminderTypesForUser(User $user): array
    {
        // All users have an email address
        $available = [
            'email' => [
                'description' => 'E-mail Reminder'
            ]
        ];
        if ($user->getPhoneNumberVerified()) {
            $available['sms'] = [
                'description' => 'SMS Reminder'
            ];
        }
        return $available;
    }

    protected function sendEmailReminder(ThemeEmailReminder $reminder): bool
    {
        try {
            /** @var Theme $theme */
            $theme = $reminder->getTheme();

            /** @var User $user */
            $user = $theme->getOwner();

            $emailAddress = $user->getEmail();

            if ($emailAddress === null) {
                throw new Exception('Expected every user to have an email address.');
            }
            $themeName = $theme->getName();
            $name = is_null($user->getDisplayName()) ? '' : $user->getDisplayName();

            $quote = $this->quoteRepository->getRandomQuote();

            $this->logger->info("Sending email to {$user->getEmail()} about theme {$theme->getId()}");
            $email = (new TemplatedEmail())
                ->from(new Address($this->adminEmailAddress, $this->adminEmailName))
                ->to(new Address($emailAddress, $name))
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
            $email->getHeaders()->addTextHeader('X-Fresher-Reminder-Id', strval($reminder->getId()));
            $this->mailer->send($email);
        } catch (Exception $e) {
            $this->logger->error("Exception thrown when sending Email reminder: {$e->getMessage()}");
            return false;
        }
        return true;
    }

    protected function sendSmsReminder(ThemeSmsReminder $reminder): bool
    {
        try {

            /** @var Theme $theme */
            $theme = $reminder->getTheme();

            /** @var User $user */
            $user = $theme->getOwner();
            $content = $this->twig->render('sms/sms_general_theme_reminder.txt.twig', [
                'theme' => $theme,
                'random_action' => $theme->getRandomGoalAction()
            ]);
            $this->logger->info("Sending notification to User {$user->getId()} about theme {$theme->getId()}");
            $this->smsService->sendMessageToUser($user, $content);
        } catch (Exception $e) {
            // The SMS service may throw; we want to be resilient
            $this->logger->error("Exception thrown when sending SMS reminder: {$e->getMessage()}");
            return false;
        }
        return true;
    }
}
