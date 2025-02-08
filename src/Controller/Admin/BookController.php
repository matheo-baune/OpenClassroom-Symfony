<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/book')]
final class BookController extends AbstractController
{
    #[Route('', name: 'app_book_index')]
    public function index(Request $request, BookRepository $bookRepository): Response
    {
        $query = $bookRepository->findAllQueryBuilder();

        $query = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($query),
            currentPage: $request->query->get('page', 1),
            maxPerPage: 4
        );

        return $this->render('admin/book/index.html.twig', [
            'books' => $query,
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('admin/book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[IsGranted('ROLE_AJOUT_DE_LIVRE')]
    #[Route('/new', name: 'app_admin_book_new', methods: ['GET','POST'])]
    #[Route('/{id}/edit', name: 'app_admin_book_edit', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function new(?Book $book, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($book && !$this->isGranted('ROLE_MODIFICATION_LIVRE')){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier ce livre');
            return $this->redirectToRoute('app_book_index');
        }
        $book ??= new Book();
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('admin/book/new.html.twig', [
            'form' => $form,
        ]);
    }
}
