<?php

namespace App\Controller;

use App\Entity\User;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new Exception('Expected the logged-in user to be available.');
        }

        $themes = $user->getThemes();

        return $this->render('dashboard/index.html.twig', [
            'themes' => $themes
        ]);
    }
}
