<?php

namespace App\Service;

use App\Entity\Theme;
use App\Entity\User;
use App\Repository\MotivationalQuoteRepository;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ReminderRendererService
{

    /** @var MotivationalQuoteRepository */
    private $quoteRepository;

    /** @var Environment */
    private $twig;

    public function __construct(
        MotivationalQuoteRepository $quoteRepository,
        Environment $twig
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->twig = $twig;
    }

    public function renderThemeEmailReminder(
        Theme $theme,
        string $adminEmailAddress,
        string $adminEmailName
    ): TemplatedEmail {
        /** @var User $user */
        $user = $theme->getOwner();
        $emailAddress = $user->getEmail();
        if ($emailAddress === null) {
            throw new Exception('Expected every user to have an email address.');
        }
        $themeName = $theme->getName();
        $name = is_null($user->getDisplayName()) ? '' : $user->getDisplayName();

        $email = (new TemplatedEmail())
            ->from(new Address($adminEmailAddress, $adminEmailName))
            ->to(new Address($emailAddress, $name))
            ->subject("Theme Reminder: {$themeName}")
            ->htmlTemplate('email/theme_reminder.html.twig')
            ->context($this->getContext($theme));
        return $email;
    }

    public function renderThemeReminderString(
        Theme $theme
    ): string {
        return $this->twig->render(
            'testing/theme_reminder.html.twig',
            $this->getContext($theme)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(Theme $theme): array
    {
        $quote = $this->quoteRepository->getRandomQuote();
        /** @var User $user  */
        $user = $theme->getOwner();
        $randomHelpers = $user->getHelpers()->toArray();
        $count = count($randomHelpers);
        if ($count > 1) {
            // Return at most three random helpers
            shuffle($randomHelpers);
            $randomHelpers = array_slice($randomHelpers, 0, min(3, $count));
        }

        return [
            'theme' => $theme,
            'quote' => $quote,
            'helpers' => $randomHelpers
        ];
    }

    public function renderSmsReminderString(Theme $theme): string
    {
        return $this->twig->render('sms/sms_general_theme_reminder.txt.twig', [
            'theme' => $theme,
            'random_action' => $theme->getRandomGoalAction()
        ]);
    }
}
