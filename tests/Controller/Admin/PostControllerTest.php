<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for Admin PostController.
 */
final class PostControllerTest extends WebTestCase
{
    public function testAdminIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/admin/post/');

        $this->assertResponseRedirects();
    }

    public function testAdminIndexRequiresAdminRole(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);

        $client->loginUser($user);
        $client->request('GET', '/en/admin/post/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminIndexAllowsAdmin(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $client->loginUser($admin);
        $client->request('GET', '/en/admin/post/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Post Management');
    }

    public function testAdminCanCreatePost(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $client->loginUser($admin);

        // Visit new post form
        $crawler = $client->request('GET', '/en/admin/post/new');
        $this->assertResponseIsSuccessful();

        // Submit form
        $form = $crawler->selectButton('Save')->form([
            'post[title]' => 'Test Post Title',
            'post[summary]' => 'This is a test summary',
            'post[content]' => 'This is the test content for the post.',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/admin/post/');

        // Verify post created
        $post = $entityManager->getRepository(Post::class)->findOneBy(['title' => 'Test Post Title']);
        $this->assertNotNull($post);
        $this->assertSame('This is a test summary', $post->getSummary());
    }

    public function testAdminCanEditOwnPost(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);
        $post = $entityManager->getRepository(Post::class)->findOneBy(['author' => $admin]);

        $this->assertNotNull($post, 'Admin should have at least one post');

        $client->loginUser($admin);

        // Visit edit form
        $crawler = $client->request('GET', '/en/admin/post/' . $post->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        // Submit form with updated data
        $form = $crawler->selectButton('Save')->form([
            'post[title]' => 'Updated Title',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();

        // Verify update
        $entityManager->refresh($post);
        $this->assertSame('Updated Title', $post->getTitle());
    }

    public function testAdminCanDeleteOwnPost(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);
        $post = $entityManager->getRepository(Post::class)->findOneBy(['author' => $admin]);

        $this->assertNotNull($post);
        $postId = $post->getId();

        $client->loginUser($admin);

        // Submit delete form with CSRF token
        $client->request('POST', '/en/admin/post/' . $postId . '/delete', [
            'token' => $this->getContainer()->get('security.csrf.token_manager')->getToken('delete')->getValue(),
        ]);

        $this->assertResponseRedirects('/en/admin/post/');

        // Verify deletion
        $deletedPost = $entityManager->getRepository(Post::class)->find($postId);
        $this->assertNull($deletedPost);
    }
}
