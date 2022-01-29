<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use App\Service\ReminderRendererService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

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
        ReminderRendererService $reminderRenderer,
        Request $request
    ): Response {
        $response = new Response($reminderRenderer->renderThemeReminderString($theme));
        $response->prepare($request);
        return $response;
    }
}
