<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Theme;
use App\Entity\ThemeEmailReminder;
use App\Entity\ThemeReminder;
use App\Entity\ThemeSmsReminder;
use App\Entity\User;
use App\Form\ThemeReminderType;
use App\Service\ReminderSenderService;
use App\Service\ReminderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class ReminderController extends AbstractController
{
    /**
     * @Route("/theme/{theme}/reminder", name="theme_reminder")
     * @IsGranted("access", subject="theme")
     */
    public function themeReminderIndex(
        Theme $theme,
        ReminderSenderService $reminderSenderService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $reminderTypes = $reminderSenderService->getAvailableReminderTypesForUser($user);
        return $this->render('reminder/theme_reminder_index.html.twig', [
            'theme' => $theme,
            'user' => $user,
            'reminder_types' => $reminderTypes
        ]);
    }

    /**
     * @Route("/theme/{theme}/reminder/email/new", name="theme_reminder_email_new", methods={"GET", "POST"})
     * @IsGranted("access", subject="theme")
     */
    public function newThemeEmailReminder(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
    ): Response {
        $themeReminder = new ThemeEmailReminder();
        $themeReminder->setTheme($theme);
        $form = $this->createForm(ThemeReminderType::class, $themeReminder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeEmailReminder $themeReminder */
            $themeReminder = $form->getData();
            $entityManager->persist($themeReminder);
            $entityManager->flush();
            $reminderService->createReminderJobsForTheme($theme);
            $this->addFlash('success', "New Theme Email Reminder added.");
            return $this->redirectToRoute('theme_show', [
                'id' => $theme->getId()
            ]);
        }

        return $this->renderForm('reminder/new_theme_email_reminder.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/theme/{theme}/reminder/sms/new", name="theme_reminder_sms_new", methods={"GET", "POST"})
     * @IsGranted("access", subject="theme")
     */
    public function newThemeSmsReminder(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getPhoneNumberVerified()) {
            $this->addFlash('danger', 'You must have a verified SMS phone number to add an SMS reminder');
            return $this->redirectToRoute('theme_reminder', [
                'theme' => $theme->getId()
            ]);
        }

        $themeReminder = new ThemeSmsReminder();
        $themeReminder->setTheme($theme);
        $form = $this->createForm(ThemeReminderType::class, $themeReminder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeReminder $themeReminder */
            $themeReminder = $form->getData();
            $entityManager->persist($themeReminder);
            $entityManager->flush();
            $reminderService->createReminderJobsForTheme($theme);
            $this->addFlash('success', "New Theme SMS Reminder added.");
            return $this->redirectToRoute('theme_show', [
                'id' => $theme->getId()
            ]);
        }

        return $this->renderForm('reminder/new_theme_sms_reminder.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/theme/{theme}/reminder/{reminder}/edit", name="theme_reminder_edit", methods={"GET", "POST"})
     * @IsGranted("access", subject="reminder")
     */
    public function editThemeReminder(
        Theme $theme,
        ThemeReminder $reminder,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
    ): Response {
        $form = $this->createForm(ThemeReminderType::class, $reminder, [
            'submit_label' => 'Save'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeReminder $reminder */
            $reminder = $form->getData();
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
            'reminder' => $reminder
        ]);
    }

    /**
     * @Route("/theme/{theme}/reminder/{reminder}/delete", name="theme_reminder_delete", methods={"DELETE"})
     * @IsGranted("access", subject="reminder")
     */
    public function deleteThemeReminder(
        Theme $theme,
        ThemeReminder $reminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // TODO: security
        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('theme_reminder_delete', $submittedToken)) {
            $entityManager->remove($reminder);
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
