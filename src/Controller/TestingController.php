<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use App\Repository\MotivationalQuoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TestingController extends AbstractController
{
    /**
     * @Route("/admin/testing", name="admin_testing")
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $themes = $user->getThemes();

        return $this->render('testing/index.html.twig', [
            'themes' => $themes
        ]);
    }

    /**
     * @Route("/admin/testing/theme/{theme}/reminder", name="admin_testing_theme_reminder")
     * @IsGranted("access", subject="theme")
     */
    public function themeReminder(
        Theme $theme,
        MotivationalQuoteRepository $quoteRepository
    ): Response {

        $quote = $quoteRepository->getRandomQuote();

        return $this->render('testing/theme_reminder.html.twig', [
            'theme' => $theme,
            'quote' => $quote
        ]);
    }
}
