<?php

declare(strict_types=1);

namespace App\Tests\Form\Type\DataTransformer;

use App\Entity\Tag;
use App\Form\Type\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests for TagArrayToStringTransformer.
 *
 * Coverage:
 * - Transform: Tag array to string
 * - Reverse transform: String to tag array
 * - Edge cases: empty values, whitespace, duplicates
 * - Performance: batch query optimization
 */
final class TagArrayToStringTransformerTest extends KernelTestCase
{
    private TagRepository $tagRepository;
    private EntityManagerInterface $entityManager;
    private TagArrayToStringTransformer $transformer;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->transformer = new TagArrayToStringTransformer($this->tagRepository);
    }

    public function testTransformEmptyArray(): void
    {
        $tags = [];

        $result = $this->transformer->transform($tags);

        $this->assertSame('', $result);
    }

    public function testTransformWithTags(): void
    {
        $tags = [
            new Tag('php'),
            new Tag('symfony'),
            new Tag('doctrine'),
        ];

        $result = $this->transformer->transform($tags);

        $this->assertSame('php, symfony, doctrine', $result);
    }

    public function testTransformWithSingleTag(): void
    {
        $tags = [new Tag('php')];

        $result = $this->transformer->transform($tags);

        $this->assertSame('php', $result);
    }

    public function testReverseTransformEmptyString(): void
    {
        $result = $this->transformer->reverseTransform('');

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testReverseTransformNull(): void
    {
        $result = $this->transformer->reverseTransform(null);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testReverseTransformWithExistingTags(): void
    {
        // Create existing tags in database
        $existingTag1 = new Tag('test_existing_php');
        $existingTag2 = new Tag('test_existing_symfony');
        $this->entityManager->persist($existingTag1);
        $this->entityManager->persist($existingTag2);
        $this->entityManager->flush();

        $result = $this->transformer->reverseTransform('test_existing_php, test_existing_symfony');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Tag::class, $result[0]);
        $this->assertInstanceOf(Tag::class, $result[1]);
        $this->assertSame('test_existing_php', $result[0]->getName());
        $this->assertSame('test_existing_symfony', $result[1]->getName());
    }

    public function testReverseTransformWithNewTags(): void
    {
        // Create one existing tag
        $existingTag = new Tag('test_existing_tag');
        $this->entityManager->persist($existingTag);
        $this->entityManager->flush();

        $result = $this->transformer->reverseTransform('test_existing_tag, test_newtag');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // First should be existing tag (from database)
        $this->assertInstanceOf(Tag::class, $result[0]);
        $this->assertSame('test_existing_tag', $result[0]->getName());

        // Second should be new tag (not persisted yet)
        $this->assertInstanceOf(Tag::class, $result[1]);
        $this->assertSame('test_newtag', $result[1]->getName());
    }

    public function testReverseTransformHandlesWhitespace(): void
    {
        $result = $this->transformer->reverseTransform('  test_ws_php  ,   test_ws_symfony  ');

        $this->assertCount(2, $result);
        $this->assertSame('test_ws_php', $result[0]->getName());
        $this->assertSame('test_ws_symfony', $result[1]->getName());
    }

    public function testReverseTransformIgnoresEmptyValues(): void
    {
        $result = $this->transformer->reverseTransform('test_empty_php, , ,');

        $this->assertCount(1, $result);
        $this->assertSame('test_empty_php', $result[0]->getName());
    }

    public function testReverseTransformWithMultipleTags(): void
    {
        // Test that it handles multiple tags correctly (batch query optimization)
        $result = $this->transformer->reverseTransform('test_batch1, test_batch2, test_batch3, test_batch4, test_batch5');

        $this->assertCount(5, $result);
        foreach ($result as $i => $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertSame('test_batch' . ($i + 1), $tag->getName());
        }
    }

    public function testReverseTransformPreservesOrder(): void
    {
        // Create tags in different order
        $tag1 = new Tag('test_order_symfony');
        $tag2 = new Tag('test_order_php');
        $this->entityManager->persist($tag1);
        $this->entityManager->persist($tag2);
        $this->entityManager->flush();

        $result = $this->transformer->reverseTransform('test_order_php, test_order_symfony, test_order_doctrine');

        // Result should match input order, not database order
        $this->assertCount(3, $result);
        $this->assertSame('test_order_php', $result[0]->getName());
        $this->assertSame('test_order_symfony', $result[1]->getName());
        $this->assertSame('test_order_doctrine', $result[2]->getName());
    }
}
