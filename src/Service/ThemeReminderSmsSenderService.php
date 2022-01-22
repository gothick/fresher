<?php

namespace App\Service;

use App\Entity\User;
use Exception;
use App\Entity\Theme;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class ThemeReminderSmsSenderService extends ThemeReminderSenderService {

    /** @var LoggerInterface */
    private $logger;

    /** @var NotifierInterface */
    private $notifier;

    public function __construct(
        Theme $theme,
        LoggerInterface $logger,
        NotifierInterface $notifier
        )
    {
        $this->logger = $logger;
        $this->notifier = $notifier;

        parent::__construct($theme);
    }

    public function sendReminder(): void
    {
        /** @var User|null $user */
        $user = $this->theme->getOwner();
        if ($user === null) {
            throw new Exception('No user found.');
        }
        /** @var string $email */
        $email = $user->getEmail();

        $phoneNumberSMS = $user->getPhoneNumberSMS();
        if ($phoneNumberSMS === null) {
            throw new Exception('SMS reminder must be for a user with a phone number.');
        }
        $themeName = $this->theme->getName();

        $notification = (new Notification("Theme Reminder for {$themeName}", ['sms']))
            ->content("This is a test reminder. There's no content yet. There may be in the future.");

        $this->logger->info("Sending notification to {$phoneNumberSMS} about theme {$this->theme->getId()}");

        $recipient = new Recipient($email, $phoneNumberSMS);
        $this->notifier->send($notification, $recipient);
    }
}
