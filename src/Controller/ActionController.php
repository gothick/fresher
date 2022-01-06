<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Goal;
use App\Entity\Theme;
use App\Form\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActionController extends AbstractController
{
    /**
     * @Route("/theme/{theme}/goal/{goal}/action", name="action", methods="GET")
     */
    public function index(
        Theme $theme,
        Goal $goal
    ): Response {
        // TODO: Security :D
        $actions = $goal->getActions();

        return $this->render('action/index.html.twig', [
            'actions' => $actions,
            'goal' => $goal,
            'theme' => $theme
        ]);
    }

    /**
     * @Route(
     *  "/theme/{theme}/goal/{goal}/action/{action}",
     *  name="action_show",
     *  methods={"GET"},
     *  requirements={"action"="\d+"}
     * )
     */
    public function show(
        Theme $theme,
        Goal $goal,
        Action $action
    ): Response {
        // TODO: Security
        return $this->render('action/show.html.twig', [
            'theme' => $theme,
            'goal' => $goal,
            'action' => $action
        ]);
    }

    /**
     * @Route("/theme/{theme}/goal/{goal}/action/new", name="action_new", methods={"GET", "POST"})
     */
    public function new(
        Theme $theme,
        Goal $goal,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // TODO: Security

        $action = new Action();
        $action->setGoal($goal);

        $form = $this->createForm(ActionType::class, $action);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Action $action */
            $action = $form->getData();
            $entityManager->persist($action);
            $entityManager->flush();
            $this->addFlash('success', "New Action added.");
            return $this->redirectToRoute('action_show', [
                'theme' => $theme->getId(),
                'goal' => $goal->getId(),
                'action' => $action->getId()
            ]);
        }

        return $this->renderForm('action/new.html.twig', [
            'theme' => $theme,
            'goal' => $goal,
            'form' => $form
        ]);
    }

    /**
     * @Route("/theme/{theme}/goal/{goal}/action/{action}/edit", name="action_edit", methods={"GET", "POST"})
     */
    public function edit(
        Theme $theme,
        Goal $goal,
        Action $action,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        // TODO: Security

        $form = $this->createForm(ActionType::class, $action, [
            'submit_label' => 'Save'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Action $action */
            $action = $form->getData();
            $entityManager->flush();
            $this->addFlash('success', "Action updated.");
            return $this->redirectToRoute('action_show', [
                'theme' => $theme->getId(),
                'goal' => $goal->getId(),
                'action' => $action->getId()
            ]);
        }

        return $this->renderForm('action/edit.html.twig', [
            'theme' => $theme,
            'goal' => $goal,
            'form' => $form
        ]);
    }

    /**
     * @Route("/theme/{theme}/goal/{goal}/action/{action}/delete", name="action_delete", methods={"DELETE"})
     */
    public function delete(
        Theme $theme,
        Goal $goal,
        Action $action,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // TODO: Security

        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('action_delete', $submittedToken)) {
            $entityManager->remove($action);
            $entityManager->flush();
            $this->addFlash('success', "Action deleted.");
            return $this->redirectToRoute('action', [
                'theme' => $theme->getId(),
                'goal' => $goal->getId()
            ]);
        }
        $this->addFlash('danger', "Goal could not be deleted.");
        return $this->redirectToRoute('action_show', [
            'theme' => $theme->getId(),
            'goal' => $goal->getId(),
            'action' => $action->getId()
        ]);
    }
}
