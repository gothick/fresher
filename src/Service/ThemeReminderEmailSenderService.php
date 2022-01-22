<?php

namespace App\Service;

use App\Entity\Theme;
use Exception;
use Psr\Log\LoggerInterface;
use App\Entity\User;
use App\Repository\MotivationalQuoteRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ThemeReminderEmailSenderService extends ThemeReminderSenderService {
    /** @var LoggerInterface */
    private $logger;

    /** @var MotivationalQuoteRepository */
    private $quoteRepository;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $adminEmailAddress;

    /** @var string */
    private $adminEmailName;

    public function __construct(
        Theme $theme,
        LoggerInterface $logger,
        MotivationalQuoteRepository $quoteRepository,
        MailerInterface $mailer,
        string $adminEmailAddress,
        string $adminEmailName
        )
    {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->mailer = $mailer;
        $this->adminEmailAddress = $adminEmailAddress;
        $this->adminEmailName = $adminEmailName;

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

        $themeName = $this->theme->getName();
        $name = is_null($user->getDisplayName()) ? '' : $user->getDisplayName();

        $this->logger->info("Sending notification to {$email} about theme {$this->theme->getId()}");
        $quote = $this->quoteRepository->getRandomQuote();

        $email = (new TemplatedEmail())
            ->from(new Address($this->adminEmailAddress, $this->adminEmailName))
            ->to(new Address($email, $name))
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject("Theme Reminder: {$themeName}")
            ->htmlTemplate('email/theme_reminder.html.twig')
            ->context([
                'theme' => $this->theme,
                'quote' => $quote
            ]);
        $this->mailer->send($email);
    }
}
