<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for public blog functionality.
 *
 * Handles post listing, viewing, search, and RSS feed.
 */
#[Route('/blog')]
final class BlogController extends AbstractController
{
    /**
     * Blog index - paginated list of posts.
     *
     * Can be filtered by tag using ?tag=tagname query parameter.
     */
    #[Route('/', name: 'blog_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
    #[Route('/page/{page}', name: 'blog_index_paginated', defaults: ['_format' => 'html'], requirements: ['page' => Requirement::POSITIVE_INT], methods: ['GET'])]
    #[Route('/rss.xml', name: 'blog_rss', defaults: ['page' => '1', '_format' => 'xml'], methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function index(Request $request, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
    {
        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
        }

        $latestPosts = $posts->findLatest($page, $tag);

        $response = $this->render('blog/index.'.$_format.'.twig', [
            'paginator' => $latestPosts,
            'tagName' => $tag?->getName(),
        ]);

        if ('xml' === $_format) {
            $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');
        }

        return $response;
    }

    /**
     * Show individual blog post.
     */
    #[Route('/posts/{slug}', name: 'blog_post', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function postShow(
        #[MapEntity(mapping: ['slug' => 'slug'])] Post $post
    ): Response {
        return $this->render('blog/post_show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * Create a new comment on a blog post.
     */
    #[Route('/comment/{postSlug}/new', name: 'comment_new', requirements: ['postSlug' => Requirement::ASCII_SLUG], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function commentNew(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['postSlug' => 'slug'])] Post $post,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
    ): Response {
        $comment = new Comment();
        $comment->setAuthor($user);
        $post->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            $this->addFlash('success', 'comment.created_successfully');

            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()], Response::HTTP_SEE_OTHER);
        }

        $response = $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * Render comment form for embedding in post page.
     */
    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * Search blog posts.
     */
    #[Route('/search', name: 'blog_search', methods: ['GET'])]
    public function search(Request $request, PostRepository $posts): Response
    {
        $query = (string) $request->query->get('q', '');
        $results = [];

        if ($query !== '') {
            $results = $posts->findBySearchQuery($query);
        }

        return $this->render('blog/search.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
