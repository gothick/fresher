<?php

namespace App\Controller;

use App\Form\SettingsType;
use App\Service\SettingsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/settings", name="settings_")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(
        SettingsService $settingsService,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $settings = $settingsService->getSettings();
        $form = $this->createForm(SettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "Settings updated.");
        }

        return $this->renderForm('settings/index.html.twig', [
            'form' => $form
        ]);
    }
}
