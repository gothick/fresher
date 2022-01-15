<?php

namespace App\Controller;

use App\Entity\MotivationalQuote;
use App\Form\MotivationalQuoteType;
use App\Repository\MotivationalQuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuoteController extends AbstractController
{
    /**
     * @Route("/admin/quote", name="admin_quote")
     */
    public function index(
        MotivationalQuoteRepository $quoteRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $query = $quoteRepository
            ->createQueryBuilder('q')
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            3 /* Quotes per page */
        );

        return $this->render('quote/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
    /**
     * @Route("/admin/quote/new", name="admin_quote_new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $quote = new MotivationalQuote();
        $form = $this->createForm(MotivationalQuoteType::class, $quote);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MotivationalQuote $quote */
            $quote = $form->getData();
            $entityManager->persist($quote);
            $entityManager->flush();
            $this->addFlash('success', 'New Quote added');
            return $this->redirectToRoute('admin_quote');
        }
        return $this->renderForm('quote/new.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @Route("/admin/quote/{quote}/edit", name="admin_quote_edit", methods={"GET", "POST"})
     */
    public function edit(
        MotivationalQuote $quote,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(MotivationalQuoteType::class, $quote, [
            'submit_label' => 'Save'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Quote saved');
            return $this->redirectToRoute('admin_quote');
        }
        return $this->renderForm('quote/edit.html.twig', [
            'form' => $form,
            'quote' => $quote
        ]);
    }

    /**
     * @Route("/admin/quote/{quote}/delete", name="admin_quote_delete", methods={"DELETE"})
     */
    public function delete(
        MotivationalQuote $quote,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $submittedToken = (string) $request->request->get('token');
        if ($this->isCsrfTokenValid('quote_delete', $submittedToken)) {
            // TODO: The actual deletion
            $entityManager->remove($quote);
            $entityManager->flush();
            $this->addFlash('success', "Quote deleted successfully.");
            return $this->redirectToRoute('admin_quote');
        }
        $this->addFlash('danger', "Quote could not be deleted.");
        return $this->redirectToRoute('admin_quote');
    }
}
