<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/author')]
final class AuthorController extends AbstractController
{
    #[Route('', name: 'app_author_index')]
    public function index(Request $request, AuthorRepository $authorRepository): Response
    {
        $authors = [];
        $dates = [];

        if($request->query->has('start')){
            $dates['start'] = $request->query->get('start');
        }

        if($request->query->has('end')){
            $dates['end'] = $request->query->get('end');
        }
        try{
            $authors = $authorRepository->findByDateOfBirth($dates);
        }catch (\Exception $e){
            $this->addFlash('danger', 'La date n\'est pas valide');
        }

        $authors = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($authors),
            currentPage: $request->query->get('page', 1),
            maxPerPage: 5
        );
        return $this->render('admin/author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/{id}', name: 'app_author_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Author $author): Response
    {
        return $this->render('admin/author/show.html.twig', [
            'author' => $author,
        ]);
    }

    #[IsGranted('ROLE_AJOUT_DE_LIVRE')]
    #[Route('/new', name: 'app_admin_author_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_admin_author_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(?Author $author, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($author && !$this->isGranted('ROLE_MODIFICATION_LIVRE', $author)){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier cet auteur');
            return $this->redirectToRoute('app_author_index');

        }

        $author ??= new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('app_author_show', ['id' => $author->getId()]);
        }

        return $this->render('admin/author/new.html.twig', [
            'form' => $form,
        ]);

    }
}
