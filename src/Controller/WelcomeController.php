<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends BaseController
{
    /**
     * @Route("/", name="welcome")
     */
    public function index(): Response
    {
        if ($this->isUserLoggedIn()) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('welcome/index.html.twig', [
        ]);
    }
}
