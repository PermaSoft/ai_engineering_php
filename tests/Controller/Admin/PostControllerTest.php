<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);

        $this->assertNotNull($user, 'User john_user must exist for test');

        $client->loginUser($user);
        $client->request('GET', '/en/admin/post/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminIndexAllowsAdmin(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $this->assertNotNull($admin, 'Admin user jane_admin must exist for test');

        $client->loginUser($admin);
        $client->request('GET', '/en/admin/post/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Post Management');
    }

    public function testAdminCanCreatePost(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $this->assertNotNull($admin, 'Admin user jane_admin must exist for test');

        $client->loginUser($admin);

        // Visit new post form
        $crawler = $client->request('GET', '/en/admin/post/new');
        $this->assertResponseIsSuccessful();

        // Submit form
        $uniqueTitle = 'Test Post Title ' . time();
        $form = $crawler->selectButton('Save')->form([
            'post[title]' => $uniqueTitle,
            'post[summary]' => 'This is a test summary',
            'post[content]' => 'This is the test content for the post.',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/admin/post/');

        // Verify post created
        $post = $entityManager->getRepository(Post::class)->findOneBy(['title' => $uniqueTitle]);
        $this->assertNotNull($post);
        $this->assertSame('This is a test summary', $post->getSummary());
    }

    public function testAdminCanEditOwnPost(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $this->assertNotNull($admin, 'Admin user jane_admin must exist for test');

        $post = $entityManager->getRepository(Post::class)->findOneBy(['author' => $admin]);

        $this->assertNotNull($post, 'Admin should have at least one post');

        $client->loginUser($admin);

        // Visit edit form
        $crawler = $client->request('GET', '/en/admin/post/' . $post->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        // Submit form with updated data
        $uniqueTitle = 'Updated Title ' . time();
        $form = $crawler->selectButton('Save')->form([
            'post[title]' => $uniqueTitle,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();

        // Verify update - refetch the post from database
        $entityManager->clear();
        $updatedPost = $entityManager->getRepository(Post::class)->find($post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame($uniqueTitle, $updatedPost->getTitle());
    }

    public function testAdminCanDeleteOwnPost(): void
    {
        $client = static::createClient();
        $client->enableProfiler();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $this->assertNotNull($admin, 'Admin user jane_admin must exist for test');

        $post = $entityManager->getRepository(Post::class)->findOneBy(['author' => $admin]);

        $this->assertNotNull($post, 'At least one post by admin must exist for test');
        $postId = $post->getId();

        $client->loginUser($admin);

        // Visit the edit page to get the delete form with CSRF token
        $crawler = $client->request('GET', '/en/admin/post/' . $postId . '/edit');
        $this->assertResponseIsSuccessful();

        // Find the delete form for this specific post by matching the action URL
        $deleteForm = $crawler->filter('form[action="/en/admin/post/' . $postId . '/delete"]')->form();

        // Submit the delete form (which includes the CSRF token)
        $client->submit($deleteForm);

        $this->assertResponseRedirects('/en/admin/post/');

        // Verify deletion - clear entity manager to get fresh data
        $entityManager->clear();
        $deletedPost = $entityManager->getRepository(Post::class)->find($postId);
        $this->assertNull($deletedPost);
    }
}
