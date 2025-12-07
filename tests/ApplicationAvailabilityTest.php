<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Smoke tests - verify all application URLs are accessible.
 */
final class ApplicationAvailabilityTest extends WebTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('publicUrlProvider')]
    public function testPublicUrls(string $url): void
    {
        $client = static::createClient();

        // Load fixtures for posts
        if (str_contains($url, '{slug}')) {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $post = $entityManager->getRepository(Post::class)->findOneBy([]);
            if ($post) {
                $url = str_replace('{slug}', $post->getSlug(), $url);
            } else {
                $this->markTestSkipped('No posts available for testing');
            }
        }

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful(
            sprintf('The URL "%s" should be accessible', $url)
        );
    }

    public static function publicUrlProvider(): \Generator
    {
        yield ['/en/blog/'];
        yield ['/en/blog/posts/{slug}'];
        yield ['/en/blog/search'];
        yield ['/en/blog/rss.xml'];
        yield ['/en/login'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('authenticatedUrlProvider')]
    public function testAuthenticatedUrls(string $url): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);

        $client->loginUser($user);
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful(
            sprintf('The URL "%s" should be accessible to authenticated users', $url)
        );
    }

    public static function authenticatedUrlProvider(): \Generator
    {
        yield ['/en/profile/edit'];
        yield ['/en/profile/change-password'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('adminUrlProvider')]
    public function testAdminUrls(string $url): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'jane_admin']);

        $client->loginUser($admin);
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful(
            sprintf('The URL "%s" should be accessible to admins', $url)
        );
    }

    public static function adminUrlProvider(): \Generator
    {
        yield ['/en/admin/post/'];
        yield ['/en/admin/post/new'];
    }
}
