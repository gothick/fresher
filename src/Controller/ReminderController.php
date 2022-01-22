<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Theme;
use App\Entity\ThemeReminder;
use App\Entity\User;
use App\Form\ThemeReminderType;
use App\Service\ReminderService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        /** @var User $user */
        $user = $this->getUser();
        $validTypes = $reminderService->getAvailableReminderTypesForUser($user);
        $choices = array_flip(array_map(function($t) {
            return $t['name'];
        }, $validTypes));

        $form = $this->createFormBuilder()
            ->add('type', ChoiceType::class, [
                'choices' => $choices,
                'label' => 'What reminder type would you like to add?'
                ])
            ->add('next', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $type */
            $type = $form->getData()['type'];
            if (array_key_exists($type, $validTypes)) {
                // User has successfully chosen a valid type of new reminder
                // to add.
                return $this->redirectToRoute('theme_reminder_type_new', [
                    'theme' => $theme->getId(),
                    'type' => $type
                ]);
            } else {
                $this->addFlash('danger', 'Reminder type not valid for this user.');
                return $this->redirectToRoute('theme_reminder_new', [
                    'theme' => $theme->getId()
                ]);
            }
        }
        return $this->renderForm('reminder/theme_reminder_new.html.twig', ['form' => $form]);
    }
    /**
     * @Route(
     *  "/theme/{theme}/reminder/{type}/new",
     *  name="theme_reminder_type_new",
     *  methods={"GET", "POST"}
     * )
     * @IsGranted("access", subject="theme")
     */
    public function newThemeReminderOfType(
        Theme $theme,
        string $type,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
    ): Response {
        // Quick double-check; we could have got here from anywhere.
        /** @var User $user */
        $user = $this->getUser();
        $validTypes = $reminderService->getAvailableReminderTypesForUser($user);
        if (!array_key_exists($type, $validTypes)) {
            throw new Exception('Reminder type not valid for this user');
        }
        $typeName = $validTypes[$type]['name'];

        /** @var ThemeReminder $reminder */
        $reminder = new $validTypes[$type]['class']();
        $reminder->setTheme($theme);
        $form = $this->createForm(ThemeReminderType::class, $reminder);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ThemeReminder $reminder */
            $reminder = $form->getData();
            $entityManager->persist($reminder);
            $entityManager->flush();
            $reminderService->createReminderJobsForTheme($theme);
            $this->addFlash('success', 'New Theme Reminder created.');
            return $this->redirectToRoute('theme_show', [
                'id' => $theme->getId()
            ]);
        }
        return $this->renderForm(
            'reminder/theme_reminder_type_new.html.twig',
            [
                'form' => $form,
                'type' => $typeName
            ]
        );
    }
    // ): Response {
    //     /** @var User $user */
    //     $user = $this->getUser();

    //     $themeReminder->setTheme($theme);
    //     $form = $this->createForm(ThemeReminderType::class, $themeReminder, [
    //         'allow_phone' => $user->canReceiveSMS()
    //     ]);

    //     $form->handleRequest($request);
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         /** @var ThemeReminder $themeReminder */
    //         $themeReminder = $form->getData();
    //         $entityManager->persist($themeReminder);
    //         $entityManager->flush();
    //         $reminderService->createReminderJobsForTheme($theme);
    //         $this->addFlash('success', "New Theme Reminder added.");
    //         return $this->redirectToRoute('theme_show', [
    //             'id' => $theme->getId()
    //         ]);
    //     }

    //     return $this->renderForm('reminder/new.html.twig', [
    //         'form' => $form,
    //     ]);
    // }

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
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ThemeReminderType::class, $themeReminder, [
            'submit_label' => 'Save',
            'allow_phone' => $user->canReceiveSMS()
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
