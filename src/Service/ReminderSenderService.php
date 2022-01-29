<?php

namespace App\Service;

use App\Entity\Theme;
use App\Entity\ThemeEmailReminder;
use App\Entity\ThemeReminder;
use App\Entity\ThemeSmsReminder;
use App\Entity\User;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

class ReminderSenderService
{
    /** @var LoggerInterface */
    private $logger;

    /** @var MailerInterface */
    private $mailer;

    /** @var SmsService */
    private $smsService;

    /** @var string */
    private $adminEmailAddress;

    /** @var string */
    private $adminEmailName;

    /** @var ReminderRendererService */
    private $reminderRenderer;

    public function __construct(
        LoggerInterface $logger,
        MailerInterface $mailer,
        SmsService $smsService,
        ReminderRendererService $reminderRenderer,
        string $adminEmailAddress,
        string $adminEmailName
    ) {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->smsService = $smsService;
        $this->reminderRenderer = $reminderRenderer;
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

            $this->logger->info("Sending email to {$user->getEmail()} about theme {$theme->getId()}");

            $email = $this->reminderRenderer->renderThemeEmailReminder(
                $theme,
                $this->adminEmailAddress,
                $this->adminEmailName
            );
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
            $content = $this->reminderRenderer->renderSmsReminderString($theme);
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
