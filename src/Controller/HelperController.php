<?php

namespace App\Controller;

use App\Entity\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\HelperType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/helper", name="helper_")
 */
class HelperController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $helpers = $user->getHelpers();

        return $this->render('helper/index.html.twig', [
            'helpers' => $helpers
        ]);
    }
    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $helper = new Helper();
        $helper->setOwner($user);

        $form = $this->createForm(HelperType::class, $helper);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Helper */
            $helper = $form->getData();
            $entityManager->persist($helper);
            $entityManager->flush();
            $this->addFlash('success', 'Added Helper');
            return $this->redirectToRoute('helper_index');
        }

        return $this->renderForm('helper/new.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     * @IsGranted("access", subject="helper")
     */
    public function edit(
        Helper $helper,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(HelperType::class, $helper, [
            'submit_label' => 'Save'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Helper */
            $helper = $form->getData();
            $entityManager->flush();
            $this->addFlash('success', 'Updated Helper');
            return $this->redirectToRoute('helper_index');
        }

        return $this->renderForm('helper/edit.html.twig', [
            'form' => $form,
            'helper' => $helper
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"})
     * @IsGranted("access", subject="helper")
     */
    public function delete(
        Helper $helper,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('helper_delete', $submittedToken)) {
            $entityManager->remove($helper);
            $entityManager->flush();
            $this->addFlash('success', "Helper deleted.");
            return $this->redirectToRoute('helper_index');
        }
        $this->addFlash('danger', "Helper could not be deleted.");
        return $this->redirectToRoute('helper_index');
    }
}
