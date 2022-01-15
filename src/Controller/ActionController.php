<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Goal;
use App\Entity\Theme;
use App\Form\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ActionController extends BaseController
{
    /**
     * @Route("/theme/{theme}/goal/{goal}/action", name="action", methods="GET")
     * @IsGranted("access", subject="goal")
     */
    public function index(
        Theme $theme,
        Goal $goal
    ): Response {
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
     * @IsGranted("access", subject="action")
     */
    public function show(
        Theme $theme,
        Goal $goal,
        Action $action
    ): Response {
        return $this->render('action/show.html.twig', [
            'theme' => $theme,
            'goal' => $goal,
            'action' => $action
        ]);
    }

    /**
     * @Route("/theme/{theme}/goal/{goal}/action/new", name="action_new", methods={"GET", "POST"})
     * @IsGranted("access", subject="goal")
     */
    public function new(
        Theme $theme,
        Goal $goal,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
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
            return $this->redirectToRoute('goal_show', [
                'theme' => $theme->getId(),
                'goal' => $goal->getId()
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
     * @IsGranted("access", subject="action")
     */
    public function edit(
        Theme $theme,
        Goal $goal,
        Action $action,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ActionType::class, $action, [
            'submit_label' => 'Save'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Action $action */
            $action = $form->getData();
            $entityManager->flush();
            $this->addFlash('success', "Action updated.");
            return $this->redirectToRoute('goal_show', [
                'theme' => $theme->getId(),
                'goal' => $goal->getId()
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
     * @IsGranted("access", subject="action")
     */
    public function delete(
        Theme $theme,
        Goal $goal,
        Action $action,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
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
