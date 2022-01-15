<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use App\Form\ThemeType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/theme", name="theme_")
 */
class ThemeController extends BaseController
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
     * @IsGranted("access", subject="theme")
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
     * @IsGranted("access", subject="theme")
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

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"})
     * @IsGranted("access", subject="theme")
     */
    public function delete(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('theme_delete', $submittedToken)) {
            // TODO: The actual deletion
            $entityManager->remove($theme);
            $entityManager->flush();
            $this->addFlash('success', "Theme deleted successfully.");
            return $this->redirectToRoute('dashboard');
        }
        $this->addFlash('danger', "Theme could not be deleted.");
        return $this->redirectToRoute('theme_show', [ 'id' => $theme->getId()]);
    }
}
