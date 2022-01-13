<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends BaseController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {

        $verifiedUserCount = $userRepository->getVerifiedUserCount();
        if ($verifiedUserCount > 3) {
            $this->addFlash('danger', "Sorry, there are already {$verifiedUserCount} users.");
            return $this->redirectToRoute('app_too_many_users');
        }

        $user = new User();
        // TODO: Configurable default? Change it client-side if we can detect?
        $user->setTimezone('Europe/London');
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'show_agree_terms' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('fresher@gothick.org.uk', 'Fresher Mailbot'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            return $this->redirectToRoute('app_register_awaiting_email');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/awaiting", name="app_register_awaiting_email", methods={"GET"})
     */
    public function awaitingEmail()
    {
        return $this->render('registration/register_awaiting_email.html.twig');
    }

    /**
     * @Route("/sorry", name="app_too_many_users")
     */
    public function tooManyUsers()
    {
        return $this->render('registration/too_many_users.html.twig');
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            // Likely ExpiredSignatureException or InvalidSignatureException. We could
            // be cleverer about these later.
            $this->addFlash('danger', $exception->getReason());
            return $this->redirectToRoute('app_verify_resend_email');
        }

        $this->addFlash('success', 'Your email address has been verified.');
        return $this->redirectToRoute('login');
    }
    /**
     * @Route("/verify/resend_email", name="app_verify_resend_email", methods={"GET", "POST"})
     */
    public function resendVerifyEmail(
        Request $request,
        UserRepository $userRepository
    ): Response {

        $form = $this->createFormBuilder()
            ->add('email', TextType::class)
            ->add('submit', SubmitType::class, ['label' => 'Re-send Verification Email'])
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!is_array($data)) {
                throw new Exception("Expected data array from form");
            }
            if (!empty($data['email'])) {
                $user = $userRepository->findOneBy(['email' => $data['email']]);
                if ($user !== null) {
                    if (!$user->isVerified()) {
                        $this->emailVerifier->sendEmailConfirmation(
                            'app_verify_email',
                            $user,
                            (new TemplatedEmail())
                                ->from(new Address('fresher@gothick.org.uk', 'Fresher Mailbot'))
                                ->to($user->getEmail())
                                ->subject('Please Confirm your Email')
                                ->htmlTemplate('registration/confirmation_email.html.twig')
                        );
                        return $this->redirectToRoute('app_register_awaiting_email');
                    } else {
                        // Exactly the same message as below so we don't leak information
                        // on whether someone is a registered user or not. This is a public
                        // page and someone could discover it and throw things at it to see
                        // if people are registered if we do something different if they
                        // are/aren't.
                        $this->addFlash('danger', 'Sorry. Something went wrong.');
                    }
                } else {
                    $this->addFlash('danger', 'Sorry. Something went wrong.');
                }
            }
        }

        return $this->renderForm('registration/register_resend_email.html.twig', [
            'form' => $form
        ]);
    }
}
