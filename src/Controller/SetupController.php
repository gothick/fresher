<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SetupController extends AbstractController
{
    /**
     * @Route("/setup", name="setup")
     */
    public function index(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($userRepository->getUserCount() > 0) {
            // Can't do the setup if there's already a user.
            throw(new AccessDeniedException('Access denied. Site already set up'));
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, ['show_agree_terms' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setIsVerified(true); // The first one's on us.
            $user->setRoles(['ROLE_ADMIN']);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Administrator set up successfully. Please login');
            return $this->redirectToRoute('settings_index');
        }

        return $this->renderForm('setup/index.html.twig', [
            'registrationForm' => $form
        ]);
    }
}
