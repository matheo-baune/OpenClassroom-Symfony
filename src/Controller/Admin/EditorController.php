<?php

namespace App\Controller\Admin;

use App\Entity\Editor;
use App\Form\EditorType;
use App\Repository\EditorRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/editor')]
final class EditorController extends AbstractController
{
    #[Route('', name: 'app_editor_index')]
    public function index(Request $request, EditorRepository $editorRepository): Response
    {
        $editors = $editorRepository->findAllQueryBuilder();
        $editors = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($editors),
            currentPage: $request->query->get('page', 1),
            maxPerPage: 5
        );
        return $this->render('admin/editor/index.html.twig', [
            'editors' => $editors,
        ]);
    }

    #[Route('/{id}', name: 'app_editor_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Editor $editor): Response
    {
        return $this->render('admin/editor/show.html.twig', [
            'editor' => $editor,
        ]);
    }

    #[IsGranted('ROLE_AJOUT_DE_LIVRE')]
    #[Route('/new', name: 'app_admin_editor_new', methods: ['GET','POST'])]
    #[Route('/{id}/edit', name: 'app_admin_editor_edit', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function new(?Editor $editor, Request $request, EntityManagerInterface $entityManager) : Response
    {
        if($editor && !$this->isGranted('ROLE_MODIFICATION_LIVRE')){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier cet éditeur');
            return $this->redirectToRoute('app_editor_index');
        }

        $editor ??= new Editor();
        $form = $this->createForm(EditorType::class, $editor);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($editor);
            $entityManager->flush();

            return $this->redirectToRoute('app_editor_show', ['id' => $editor->getId()]);
        }

        return $this->render('admin/editor/new.html.twig', [
            'form' => $form,
        ]);
    }
}
