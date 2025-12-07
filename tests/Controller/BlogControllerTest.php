<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for BlogController.
 */
final class BlogControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/blog/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Blog');
    }

    public function testIndexPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/blog/page/2');

        $this->assertResponseIsSuccessful();
    }

    public function testPostShow(): void
    {
        $client = static::createClient();

        // Get first post from database
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $post = $entityManager->getRepository(Post::class)->findOneBy([]);

        $this->assertNotNull($post, 'At least one post should exist in test database');

        $client->request('GET', '/en/blog/posts/' . $post->getSlug());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $post->getTitle());
    }

    public function testTagFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/blog/?tag=lorem');

        $this->assertResponseIsSuccessful();
    }

    public function testSearch(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/blog/search?q=lorem');

        $this->assertResponseIsSuccessful();
    }

    public function testRssFeed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/blog/rss.xml');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    public function testAnonymousCannotComment(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $post = $entityManager->getRepository(Post::class)->findOneBy([]);

        $client->request('POST', '/en/blog/comment/' . $post->getSlug() . '/new');

        $this->assertResponseRedirects(); // Should redirect to login
    }
}
