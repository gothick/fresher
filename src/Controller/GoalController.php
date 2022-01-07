<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Theme;
use App\Form\GoalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalController extends BaseController
{
    /**
     * @Route("/theme/{theme}/goal", name="goal", methods={"GET"})
     */
    public function index(Theme $theme): Response
    {
        // TODO: Security
        $goals = $theme->getGoals();

        return $this->render('goal/index.html.twig', [
            'theme' => $theme,
            'goals' => $goals
        ]);
    }

    /**
     * @Route(
     *  "/theme/{theme}/goal/{goal}",
     *  name="goal_show",
     *  methods={"GET"},
     *  requirements={"goal"="\d+"}
     * )
     */
    public function show(Theme $theme, Goal $goal): Response
    {
        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
            'theme' => $theme
        ]);
    }

    /**
     * @Route("/theme/{theme}/goal/{goal}/edit", name="goal_edit", methods={"GET", "POST"})
     */
    public function edit(
        Theme $theme,
        Goal $goal,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $form = $this->createForm(GoalType::class, $goal, [
            'submit_label' => 'Save'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $goal = $form->getData();
            $entityManager->flush();
            $this->addFlash('success', "Goal updated.");
            return $this->redirectToRoute('goal_show', ['theme' => $theme->getId(), 'goal' => $goal->getId() ]);
        }

        return $this->renderForm('goal/edit.html.twig', [
            'theme' => $theme,
            'goal' => $goal,
            'form' => $form
        ]);
    }

    /**
     * @Route("/theme/{theme}/goal/{goal}/delete", name="goal_delete", methods={"DELETE"})
     */
    public function delete(
        Theme $theme,
        Goal $goal,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // TODO: security

        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('goal_delete', $submittedToken)) {
            $entityManager->remove($goal);
            $entityManager->flush();
            $this->addFlash('success', "Goal deleted successfully.");
            return $this->redirectToRoute('theme_show', [ 'id' => $theme->getId() ]);
        }
        $this->addFlash('danger', "Goal could not be deleted.");
        return $this->redirectToRoute('theme_show', [ 'id' => $theme->getId()]);
    }

    /**
     * @Route("/theme/{theme}/goal/new", name="goal_new", methods={"GET", "POST"})
     */
    public function new(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $goal = new Goal();
        $goal->setTheme($theme);
        $form = $this->createForm(GoalType::class, $goal);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $goal = $form->getData();
            $entityManager->persist($goal);
            $entityManager->flush();
            $this->addFlash('success', "New Goal added.");
            return $this->redirectToRoute('theme_show', ['id' => $theme->getId()]);
        }

        return $this->renderForm('goal/new.html.twig', [
            'theme' => $theme,
            'form' => $form
        ]);
    }
}
