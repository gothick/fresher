<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserChangePasswordType;
use App\Form\UserType;
use App\Service\ReminderService;
use App\Service\SmsService;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/user", name="user_")
 */
class UserController extends BaseController
{
    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderService $reminderService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $originalPhoneNumber = $user->getPhoneNumber();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            if ($originalPhoneNumber !== $user->getPhoneNumber()) {
                // If the user changed their number, we can't send them any new
                // SMSes until they're verified.
                $this->addFlash('warning', "You've updated your phone number and must now re-verify before any new messages can be sent.");
                $user->setPhoneNumberVerified(false);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Settings saved.');
            // Any user update could've forced the need for regenerating
            // remidner jobs -- timezone, phone number, etc.
            $reminderService->createThemeReminderJobsForUser($user);
            return $this->redirectToRoute('user_index');
        }

        return $this->renderForm('user/index.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * @Route("/change_password", name="change_password", methods={"GET", "POST"})
     */
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new Exception("Couldn't retrieve user even though I seem to be logged in.");
        }
        $userInfo = ['plainPassword' => null];

        $form = $this->createForm(UserChangePasswordType::class, $userInfo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $info = $form->getData();
            $plainPassword = $info['plainPassword'];
            // TODO: Password strength validation?
            $password = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($password);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Password changed!'
            );
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('user/change_password.html.twig', [
            'user' => $user,
            'form' =>$form->createView()
        ]);
    }

    /**
     * @Route("/send_sms_code", name="send_sms_code", methods={"GET", "POST"})
     */
    public function sendSmsCode(
        Request $request,
        SmsService $smsService
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new Exception("Couldn't retrieve user details.");
        }

        if ($user->getPhoneNumberVerified()) {
            $this->addFlash('warning', 'Your phone number is already verified');
            return $this->redirectToRoute('user_index');
        }

        if (empty($user->getPhoneNumber())) {
            // The link to get here should only appear if there's a phone
            // number, but there's no guarantee that someone won't come
            // here directly.
            $this->addFlash('warning', 'Please add a phone number to verify');
            return $this->redirectToRoute('user_index');
        }

        $form = $this->createFormBuilder()
            ->add('submit', SubmitType::class, ['label' => 'Send Code'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $smsService->sendVerificationCode($user, $this->generateUrl('user_validate_sms_code', [], UrlGeneratorInterface::ABSOLUTE_URL));
            $this->addFlash('success', 'Sent code to ' . $user->getPhoneNumber());
            return $this->redirectToRoute('user_validate_sms_code');
        }

        return $this->renderForm('user/send_sms_code.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
    /**
     * @Route("/validate_sms_code", name="validate_sms_code", methods={"GET", "POST"})
     */
    public function validateSmsCode(
        Request $request,
        SmsService $smsService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getPhoneNumberVerified()) {
            $this->addFlash('warning', 'Phone number already verified');
            return $this->redirectToRoute('user_index');
        }

        if (!$user->hasUnexpiredVerificationCode()) {
            $this->addFlash('danger', 'Sorry, your verification code expired. Please generate another.');
            return $this->redirectToRoute('user_send_sms_code');
        }

        $form = $this->createFormBuilder()
            ->add('verificationCode', IntegerType::class, [
                'label' => 'Please enter your six-digit code',
                'required' => true
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array $data */
            $data = $form->getData();
            $code = strval($data['verificationCode']);
            if ($smsService->validateVerificationCode($user, $code)) {
                $this->addFlash('success', 'Successfully verified SMS number. You can now send SMS messages.');
                return $this->redirectToRoute('user_index');
            } else {
                $this->addFlash('danger', 'Sorry, that validation code was invalid. Please try again.');
                return $this->redirectToRoute('user_validate_sms_code');
            }
        }

        return $this->renderForm('user/validate_sms_code.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
