<?php

namespace App\Controller;

use App\Service\SettingsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends BaseController
{
    /**
     * @Route("/", name="welcome")
     */
    public function index(
        SettingsService $settings
    ): Response
    {
        if ($this->isUserLoggedIn()) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('welcome/index.html.twig', [
            'settings' => $settings
        ]);
    }
}
