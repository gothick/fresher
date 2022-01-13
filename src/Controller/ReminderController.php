<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\GoalReminder;
use App\Entity\Theme;
use App\Entity\ThemeReminder;
use App\Form\GoalReminderType;
use App\Form\ThemeReminderType;
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
    public function goalReminderIndex(
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
     * @Route("/theme/{theme}/reminder", name="theme_reminder")
     */
    public function themeReminderIndex(
        Theme $theme
    ): Response {
        return $this->render('reminder/theme_reminder_index.html.twig', [
            'theme' => $theme
        ]);
    }

    /**
     * @Route("/goal/{goal}/reminder/new", name="goal_reminder_new", methods={"GET", "POST"})
     */
    public function newGoalReminder(
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
     * @Route("/theme/{theme}/reminder/new", name="theme_reminder_new", methods={"GET", "POST"})
     */
    public function newThemeReminder(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $themeReminder = new ThemeReminder();
        $themeReminder->setTheme($theme);
        $form = $this->createForm(ThemeReminderType::class, $themeReminder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeReminder $themeReminder */
            $themeReminder = $form->getData();
            $entityManager->persist($themeReminder);
            $entityManager->flush();
            $this->addFlash('success', "New Theme Reminder added.");
            return $this->redirectToRoute('theme_show', [
                'id' => $theme->getId()
            ]);
        }

        return $this->renderForm('reminder/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/goal/{goal}/reminder/{reminder}/edit]", name="goal_reminder_edit", methods={"GET", "POST"})
     */
    public function editGoalReminder(
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

        return $this->renderForm('reminder/edit_goal_reminder.html.twig', [
            'form' => $form,
            'goal' => $goal,
            'reminder' => $goalReminder
        ]);
    }
    /**
     * @Route("/theme/{theme}/reminder/{reminder}/edit]", name="theme_reminder_edit", methods={"GET", "POST"})
     */
    public function editThemeReminder(
        Theme $theme,
        ThemeReminder $themeReminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ThemeReminderType::class, $themeReminder, [
            'submit_label' => 'Save'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeReminder $themeReminder */
            $themeReminder = $form->getData();
            $entityManager->flush();
            $this->addFlash('success', "Theme Reminder edited.");
            return $this->redirectToRoute('theme_show', [
                'id' => $theme->getId()
            ]);
        }

        return $this->renderForm('reminder/edit_theme_reminder.html.twig', [
            'form' => $form,
            'theme' => $theme,
            'reminder' => $themeReminder
        ]);
    }

    /**
     * @Route("/goal/{goal}/reminder/{reminder}/delete", name="goal_reminder_delete", methods={"DELETE"})
     */
    public function deleteGoalReminder(
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
    /**
     * @Route("/theme/{theme}/reminder/{reminder}/delete", name="theme_reminder_delete", methods={"DELETE"})
     */
    public function deleteThemeReminder(
        Theme $theme,
        ThemeReminder $themeReminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // TODO: security
        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('theme_reminder_delete', $submittedToken)) {
            $entityManager->remove($themeReminder);
            $entityManager->flush();
            $this->addFlash('success', "Theme Reminder deleted successfully.");
            return $this->redirectToRoute('theme_show', [
                'id' => $theme->getId()
            ]);
        }
        $this->addFlash('danger', "Theme Reminder could not be deleted.");
        return $this->redirectToRoute('theme_show', [
            'id' => $theme->getId()
        ]);
    }

}
