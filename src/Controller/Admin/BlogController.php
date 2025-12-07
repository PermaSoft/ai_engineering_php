<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Security\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/post')]
#[IsGranted('ROLE_ADMIN')]
final class BlogController extends AbstractController
{
    #[Route('/', name: 'admin_post_index')]
    public function index(#[CurrentUser] User $author, PostRepository $posts): Response
    {
        $authorPosts = $posts->findBy(['author' => $author], ['publishedAt' => 'DESC']);

        return $this->render('admin/blog/index.html.twig', ['posts' => $authorPosts]);
    }

    #[Route('/new', name: 'admin_post_new')]
    public function new(#[CurrentUser] User $author, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $post->setAuthor($author);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'post.created_successfully');

            $saveAndCreateNew = $form->get('saveAndCreateNew');
            if ($saveAndCreateNew instanceof SubmitButton && $saveAndCreateNew->isClicked()) {
                return $this->redirectToRoute('admin_post_new');
            }

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id:post}', name: 'admin_post_show', requirements: ['id' => Requirement::POSITIVE_INT])]
    #[IsGranted(PostVoter::SHOW, subject: 'post')]
    public function show(Post $post): Response
    {
        return $this->render('admin/blog/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id:post}/edit', name: 'admin_post_edit', requirements: ['id' => Requirement::POSITIVE_INT])]
    #[IsGranted(PostVoter::EDIT, subject: 'post')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('admin_post_edit', ['id' => $post->getId()]);
        }

        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_post_delete', requirements: ['id' => Requirement::POSITIVE_INT], methods: ['POST'])]
    #[IsGranted(PostVoter::DELETE, subject: 'post')]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete', (string) $request->request->get('token'))) {
            return $this->redirectToRoute('admin_post_index');
        }

        $post->getTags()->clear();
        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('admin_post_index');
    }
}
