<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Fixtures to load test users, tags, and blog posts into the database.
 *
 * Usage:
 *   php bin/console doctrine:fixtures:load
 */
final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin users
        $janeAdmin = new User();
        $janeAdmin->setUsername('jane_admin');
        $janeAdmin->setEmail('jane_admin@symfony.com');
        $janeAdmin->setFullName('Jane Doe');
        $janeAdmin->setPassword($this->passwordHasher->hashPassword($janeAdmin, 'kitten'));
        $janeAdmin->setRoles(['ROLE_ADMIN']);
        $manager->persist($janeAdmin);

        $tomAdmin = new User();
        $tomAdmin->setUsername('tom_admin');
        $tomAdmin->setEmail('tom_admin@symfony.com');
        $tomAdmin->setFullName('Tom Doe');
        $tomAdmin->setPassword($this->passwordHasher->hashPassword($tomAdmin, 'kitten'));
        $tomAdmin->setRoles(['ROLE_ADMIN']);
        $manager->persist($tomAdmin);

        // Create regular user
        $johnUser = new User();
        $johnUser->setUsername('john_user');
        $johnUser->setEmail('john_user@symfony.com');
        $johnUser->setFullName('John Doe');
        $johnUser->setPassword($this->passwordHasher->hashPassword($johnUser, 'kitten'));
        $johnUser->setRoles(['ROLE_USER']);
        $manager->persist($johnUser);

        $manager->flush();

        // Create tags
        $tagNames = ['lorem', 'ipsum', 'consectetur', 'adipiscing', 'incididunt', 'labore', 'voluptate', 'dolore', 'pariatur'];
        $tags = [];
        foreach ($tagNames as $tagName) {
            $tag = new Tag($tagName);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        $manager->flush();

        // Create blog posts
        $authors = [$janeAdmin, $tomAdmin];
        $postTitles = [
            'Lorem Ipsum Dolor Sit Amet',
            'Consectetur Adipiscing Elit',
            'Sed Do Eiusmod Tempor',
            'Incididunt Ut Labore',
            'Et Dolore Magna Aliqua',
            'Ut Enim Ad Minim Veniam',
            'Quis Nostrud Exercitation',
            'Ullamco Laboris Nisi',
            'Ut Aliquip Ex Ea Commodo',
            'Duis Aute Irure Dolor',
            'Reprehenderit In Voluptate',
            'Velit Esse Cillum Dolore',
            'Eu Fugiat Nulla Pariatur',
            'Excepteur Sint Occaecat',
            'Cupidatat Non Proident',
            'Sunt In Culpa Qui Officia',
            'Deserunt Mollit Anim',
            'Id Est Laborum Sed',
            'Perspiciatis Unde Omnis',
            'Iste Natus Error Sit',
            'Voluptatem Accusantium Doloremque',
            'Laudantium Totam Rem',
            'Aperiam Eaque Ipsa',
            'Quae Ab Illo Inventore',
            'Veritatis Et Quasi Architecto',
            'Beatae Vitae Dicta Sunt',
            'Explicabo Nemo Enim Ipsam',
            'Voluptatem Quia Voluptas',
            'Sit Aspernatur Aut Odit',
            'Aut Fugit Sed Quia',
        ];

        $posts = [];
        foreach ($postTitles as $index => $title) {
            $post = new Post();
            $post->setTitle($title);
            $post->setSlug($this->slugger->slug($title)->lower()->toString());
            $post->setSummary('Summary for ' . $title . '. This is a brief overview of the post content.');
            $post->setContent("This is the full content for the post titled '{$title}'.\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
            $post->setAuthor($authors[$index % 2]); // Alternate between authors
            $post->setPublishedAt(new \DateTimeImmutable(sprintf('-%d days', 30 - $index)));

            // Add 2-4 random tags to each post
            $numTags = random_int(2, 4);
            /** @var array<int> $randomTags */
            $randomTags = array_rand($tags, $numTags);
            foreach ($randomTags as $tagIndex) {
                $post->addTag($tags[$tagIndex]);
            }

            $manager->persist($post);
            $posts[] = $post;
        }

        $manager->flush();

        // Create comments for each post
        $commentContents = [
            'Great post! This was very informative and helpful.',
            'I completely agree with your points here.',
            'Thanks for sharing this valuable information.',
            'This is exactly what I was looking for.',
            'Interesting perspective on this topic.',
            'Well written and easy to understand.',
            'Could you elaborate more on this subject?',
            'I learned something new today, thank you!',
            'Very insightful article, keep up the good work.',
            'This helped me solve my problem, much appreciated.',
        ];

        foreach ($posts as $post) {
            // Add 5 comments to each post
            for ($i = 0; $i < 5; $i++) {
                $comment = new Comment();
                $comment->setAuthor($johnUser);
                $comment->setContent($commentContents[array_rand($commentContents)]);

                // Random date within last 30 days, but after post publication
                $postDate = $post->getPublishedAt();
                $daysOffset = random_int(0, 29);
                $commentDate = $postDate->modify(sprintf('+%d days', $daysOffset));
                $comment->setPublishedAt($commentDate);

                $post->addComment($comment);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
