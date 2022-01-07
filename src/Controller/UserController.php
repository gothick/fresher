<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserChangePasswordType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user", name="user_")
 */
class UserController extends BaseController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('user/index.html.twig', [
            'user' => $user,
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
}
