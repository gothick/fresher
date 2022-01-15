<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Theme;
use App\Entity\ThemeReminder;
use App\Form\ThemeReminderType;
use App\Service\ReminderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ReminderController extends AbstractController
{
    /**
     * @Route("/theme/{theme}/reminder", name="theme_reminder")
     * @IsGranted("access", subject="theme")
     */
    public function themeReminderIndex(
        Theme $theme
    ): Response {
        return $this->render('reminder/theme_reminder_index.html.twig', [
            'theme' => $theme
        ]);
    }

    /**
     * @Route("/theme/{theme}/reminder/new", name="theme_reminder_new", methods={"GET", "POST"})
     * @IsGranted("access", subject="theme")
     */
    public function newThemeReminder(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
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
            $reminderService->createReminderJobsForTheme($theme);
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
     * @Route("/theme/{theme}/reminder/{reminder}/edit", name="theme_reminder_edit", methods={"GET", "POST"})
     * @IsGranted("access", subject="themeReminder")
     */
    public function editThemeReminder(
        Theme $theme,
        ThemeReminder $themeReminder,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
    ): Response {
        $form = $this->createForm(ThemeReminderType::class, $themeReminder, [
            'submit_label' => 'Save'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeReminder $themeReminder */
            $themeReminder = $form->getData();
            $entityManager->flush();
            // Make sure we regenerate the jobs now we've changed the reminder.
            $reminderService->createReminderJobsForTheme($theme);
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
     * @Route("/theme/{theme}/reminder/{reminder}/delete", name="theme_reminder_delete", methods={"DELETE"})
     * @IsGranted("access", subject="themeReminder")
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
