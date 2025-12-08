<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Test utilities for creating test data.
 *
 * Provides factory methods for creating entities with sensible defaults.
 */
final class TestUtilities
{
    /**
     * Creates a test user with default values.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createUser(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        array $overrides = []
    ): User {
        $defaults = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'fullName' => 'Test User',
            'password' => 'password',
            'roles' => ['ROLE_USER'],
        ];

        $data = array_merge($defaults, $overrides);

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setFullName($data['fullName']);
        $user->setRoles($data['roles']);

        // Hash password
        $hashedPassword = $hasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Creates a test admin user.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createAdmin(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        array $overrides = []
    ): User {
        return self::createUser($em, $hasher, array_merge([
            'username' => 'admin_' . uniqid(),
            'roles' => ['ROLE_ADMIN'],
        ], $overrides));
    }

    /**
     * Creates a test post.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createPost(
        EntityManagerInterface $em,
        User $author,
        array $overrides = []
    ): Post {
        $defaults = [
            'title' => 'Test Post ' . uniqid(),
            'slug' => 'test-post-' . uniqid(),
            'summary' => 'This is a test post summary.',
            'content' => 'This is the test post content with **markdown**.',
        ];

        $data = array_merge($defaults, $overrides);

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setSlug($data['slug']);
        $post->setSummary($data['summary']);
        $post->setContent($data['content']);
        $post->setAuthor($author);

        $em->persist($post);
        $em->flush();

        return $post;
    }

    /**
     * Creates a test tag.
     */
    public static function createTag(EntityManagerInterface $em, string $name): Tag
    {
        $tag = new Tag($name);
        $em->persist($tag);
        $em->flush();

        return $tag;
    }

    /**
     * Creates a test comment.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createComment(
        EntityManagerInterface $em,
        Post $post,
        User $author,
        array $overrides = []
    ): Comment {
        $defaults = [
            'content' => 'This is a test comment.',
        ];

        $data = array_merge($defaults, $overrides);

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setAuthor($author);
        $comment->setPost($post);

        $em->persist($comment);
        $em->flush();

        return $comment;
    }

    /**
     * Clears all test data from database.
     */
    public static function clearDatabase(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();

        // Disable foreign key checks
        $connection->executeStatement('PRAGMA foreign_keys = OFF');

        // Truncate all tables
        $connection->executeStatement('DELETE FROM comment');
        $connection->executeStatement('DELETE FROM post_tag');
        $connection->executeStatement('DELETE FROM post');
        $connection->executeStatement('DELETE FROM tag');
        $connection->executeStatement('DELETE FROM symfony_demo_user');

        // Re-enable foreign key checks
        $connection->executeStatement('PRAGMA foreign_keys = ON');

        $em->clear();
    }
}
