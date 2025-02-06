<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

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
            $authors = $authorRepository->findAll();
            $this->addFlash('danger', 'La date n\'est pas valide');
        }

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

    #[Route('/new', name: 'app_author_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('app_author_new');
        }

        return $this->render('admin/author/new.html.twig', [
            'form' => $form,
        ]);

    }
}
