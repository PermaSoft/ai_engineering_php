<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for comment functionality.
 */
final class CommentControllerTest extends WebTestCase
{
    public function testAuthenticatedUserCanComment(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);
        $post = $entityManager->getRepository(Post::class)->findOneBy([]);

        $this->assertNotNull($user, 'User john_user must exist for test');
        $this->assertNotNull($post, 'At least one post must exist for test');

        $client->loginUser($user);

        // Visit post page first to get form
        $crawler = $client->request('GET', '/en/blog/posts/' . $post->getSlug());
        $this->assertResponseIsSuccessful();

        // Submit comment
        $form = $crawler->selectButton('Publish comment')->form([
            'comment[content]' => 'This is a test comment without spam.',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
        $client->followRedirect();

        // Verify comment was created
        $comment = $entityManager->getRepository(Comment::class)->findOneBy([
            'content' => 'This is a test comment without spam.',
        ]);
        $this->assertNotNull($comment);
        $this->assertSame($user->getId(), $comment->getAuthor()->getId());
    }

    public function testSpamCommentRejected(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);
        $post = $entityManager->getRepository(Post::class)->findOneBy([]);

        $this->assertNotNull($user, 'User john_user must exist for test');
        $this->assertNotNull($post, 'At least one post must exist for test');

        $client->loginUser($user);

        // Visit post page
        $crawler = $client->request('GET', '/en/blog/posts/' . $post->getSlug());

        // Submit spam comment (contains @)
        $form = $crawler->selectButton('Publish comment')->form([
            'comment[content]' => 'This is spam with @ symbol.',
        ]);

        $client->submit($form);

        // Should show error, not redirect
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testAnonymousUserCannotSeeCommentForm(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $post = $entityManager->getRepository(Post::class)->findOneBy([]);

        $this->assertNotNull($post, 'At least one post must exist for test');

        $crawler = $client->request('GET', '/en/blog/posts/' . $post->getSlug());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('button:contains("Publish comment")');
        $this->assertSelectorExists('a:contains("Sign in")');
    }
}
