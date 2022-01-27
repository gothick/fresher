<?php

namespace App\Service;

use App\Entity\User;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class SmsService
{
    /** @var NotifierInterface */
    private $notifier;

    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        NotifierInterface $notifier,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->notifier = $notifier;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function sendMessageToUser(User $user, string $content, bool $overrideVerification = false): void
    {
        if ($overrideVerification === false && $user->getPhoneNumberVerified() !== true) {
            throw new Exception("Can't send SMS message to unverified phone number");
        }

        /** @var string $email */
        $email = $user->getEmail();

        $phoneNumber = $user->getPhoneNumber();
        $anonymisedPhoneNumber = $user->getAnonymisedPhoneNumber();

        if ($phoneNumber === null || empty($phoneNumber)) {
            throw new Exception("Phone number null or empty.");
        }

        $notification = new Notification($content, ['sms']);
        $this->logger->info("Sending SMS message user {$user->getId()} at {$anonymisedPhoneNumber}");
        $recipient = new Recipient($email, $phoneNumber);
        $this->notifier->send($notification, $recipient);
    }

    public function sendVerificationCode(User $user, string $validateUrl): void
    {
        $code = strval(random_int(100000, 999999));
        $user->setPhoneNumberVerified(false);
        $user->setVerificationCode($code);
        $user->setVerificationCodeExpiresAt(CarbonImmutable::now()->addDays(1));
        $user->setVerificationCodeTries(0);
        $this->entityManager->flush();
        $this->sendMessageToUser(
            $user,
            "Your verification code is {$code}. Please visit {$validateUrl} to finish verification",
            true
        );
    }

    public function validateVerificationCode(User $user, string $code): bool
    {
        if (!$user->hasUnexpiredVerificationCode()) {
            throw new Exception('Verification code expired.');
        }
        if ($user->getVerificationCode() === $code) {
            $user->setPhoneNumberVerified(true);
            $user->setVerificationCodeTries(null);
            $user->setVerificationCode(null);
            $this->entityManager->flush();
            return true;
        } else {
            $tries = $user->getVerificationCodeTries() ? $user->getVerificationCodeTries() : 0;
            if ($tries >= 5) {
                throw new Exception('Number of validation attempts exceeded. Please generate a new validation code');
            } else {
                $user->setVerificationCodeTries($tries + 1);
                $this->entityManager->flush();
            }
        }
        return false;
    }
}
