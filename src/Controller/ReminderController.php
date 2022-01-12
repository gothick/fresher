<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\GoalReminder;
use App\Entity\Theme;
use App\Form\GoalReminderType;
use ContainerN9NmnxR\getGoalReminderTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReminderController extends AbstractController
{
    /**
     * @Route("/goal/{goal}/reminder", name="goal_reminder")
     */
    public function index(
        Goal $goal
    ): Response {
        if ($goal->getTheme() === null) {
            throw new Exception('Expected every goal to have a theme');
        }
        return $this->render('reminder/goal_reminder_index.html.twig', [
            'goal' => $goal,
            'theme' => $goal->getTheme()
        ]);
    }

    /**
     * @Route("/goal/{goal}/reminder/new", name="goal_reminder_new", methods={"GET", "POST"})
     */
    public function new(
        Goal $goal,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($goal->getTheme() === null) {
            throw new Exception('Expected every goal to have a theme');
        }

        $goalReminder = new GoalReminder();
        $goalReminder->setGoal($goal);
        $form = $this->createForm(GoalReminderType::class, $goalReminder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var GoalReminder $goalReminder */
            $goalReminder = $form->getData();
            $entityManager->persist($goalReminder);
            $entityManager->flush();
            $this->addFlash('success', "New Goal Reminder added.");
            return $this->redirectToRoute('goal_show', [
                'theme' => $goal->getTheme()->getId(),
                'goal' => $goal->getId()
            ]);
        }

        return $this->renderForm('reminder/new.html.twig', [
            'form' => $form,
            //'goal' => $goal
        ]);
    }
    /**
     * @Route("/goal/{goal}/reminder/{reminder}/edit]", name="goal_reminder_edit", methods={"GET", "POST"})
     */
    public function edit(
        Goal $goal,
        GoalReminder $goalReminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($goal->getTheme() === null) {
            throw new Exception('Expected every goal to have a theme');
        }
        $form = $this->createForm(GoalReminderType::class, $goalReminder, [
            'submit_label' => 'Save'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var GoalReminder $goalReminder */
            $goalReminder = $form->getData();
            $entityManager->flush();
            $this->addFlash('success', "Goal Reminder edited.");
            return $this->redirectToRoute('goal_show', [
                'theme' => $goal->getTheme()->getId(),
                'goal' => $goal->getId()
            ]);
        }

        return $this->renderForm('reminder/edit.html.twig', [
            'form' => $form,
            'goal' => $goal,
            'reminder' => $goalReminder
        ]);
    }
    /**
     * @Route("/goal/{goal}/reminder/{reminder}/delete", name="goal_reminder_delete", methods={"DELETE"})
     */
    public function delete(
        Goal $goal,
        GoalReminder $goalReminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // TODO: security
        if ($goal->getTheme() === null) {
            throw new Exception('Expected every goal to have a theme');
        }

        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('goal_reminder_delete', $submittedToken)) {
            $entityManager->remove($goalReminder);
            $entityManager->flush();
            $this->addFlash('success', "Goal Reminder deleted successfully.");
            return $this->redirectToRoute('goal_show', [
                'theme' => $goal->getTheme()->getId(),
                'goal' => $goal->getId()
            ]);
        }
        $this->addFlash('danger', "Goal Reminder could not be deleted.");
        return $this->redirectToRoute('goal_show', [
            'theme' => $goal->getTheme()->getId(),
            'goal' => $goal->getId()
        ]);
    }
}
