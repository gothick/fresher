<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use App\Form\ThemeType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/theme", name="theme_")
 */
class ThemeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new Exception("Expected to find a logged-in user.");
        }
        $themes = $user->getThemes();

        return $this->render('theme/index.html.twig', [
            'themes' => $themes,
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new Exception("Expected to find a logged-in user.");
        }
        $theme = new Theme();
        $theme->setOwner($user);

        $form = $this->createForm(ThemeType::class, $theme);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Theme $theme  */
            $theme = $form->getData();
            $entityManager->persist($theme);
            $entityManager->flush();
            $this->addFlash('success', "New Theme created.");
            return $this->redirectToRoute('theme_show', ['id' => $theme->getId()]);
        }

        return $this->renderForm('theme/new.html.twig', [
            'form' => $form
        ]);
    }
    /**
     * @Route("/{id}/show", name="show", methods={"GET"})
     */
    public function show(
        Theme $theme
    ): Response {
        return $this->render('theme/show.html.twig', [
            'theme' => $theme
        ]);
    }
    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ThemeType::class, $theme, [
            'submit_label' => 'Save'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "Theme updated.");
            return $this->redirectToRoute('theme_show', [ 'id' => $theme->getId()]);
        }

        return $this->renderForm('theme/edit.html.twig', [
            'form' => $form
        ]);
    }

}
