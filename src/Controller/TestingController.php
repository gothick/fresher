<?php

namespace App\Controller;

use App\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TestingController extends AbstractController
{
    /**
     * @Route("/testing", name="testing")
     */
    public function index(): Response
    {
        return $this->render('testing/index.html.twig', [
            'controller_name' => 'TestingController',
        ]);
    }

    /**
     * @Route("/testing/theme/{theme}/reminder", name="testing_theme_reminder")
     * @IsGranted("access", subject="theme")
     */
    public function themeReminder(
        Theme $theme
    ): Response {
        // TODO: Security
        return $this->render('testing/theme_reminder.html.twig', [
            'theme' => $theme
        ]);
    }
}
