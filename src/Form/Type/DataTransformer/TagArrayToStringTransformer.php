<?php

declare(strict_types=1);

namespace App\Form\Type\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms between array of Tag entities and comma-separated string.
 *
 * Transform: [Tag("lorem"), Tag("ipsum")] → "lorem,ipsum"
 * Reverse: "lorem, ipsum, dolor" → [Tag("lorem"), Tag("ipsum"), Tag("dolor")]
 *
 * Performance optimization:
 * - Uses batch query instead of N+1 queries
 * - Creates only missing tags
 * - Handles whitespace and empty values
 *
 * @implements DataTransformerInterface<array<int, Tag>, string>
 */
final class TagArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {
    }

    /**
     * Transforms an array of Tags to a comma-separated string.
     *
     * @param array<int, Tag> $tags
     */
    public function transform(mixed $tags): string
    {
        return implode(', ', array_map(
            static fn(Tag $tag): string => $tag->getName(),
            $tags
        ));
    }

    /**
     * Transforms a comma-separated string to an array of Tag entities.
     *
     * Creates new tags if they don't exist.
     * OPTIMIZED: Uses single batch query instead of N+1 queries.
     *
     * @return array<int, Tag>
     */
    public function reverseTransform(mixed $string): array
    {
        if ('' === $string || null === $string) {
            return [];
        }

        // Split by comma, trim whitespace, remove empty values, remove duplicates
        $names = array_filter(
            array_map('trim', explode(',', (string) $string)),
            static fn(string $name): bool => $name !== ''
        );

        if (empty($names)) {
            return [];
        }

        // OPTIMIZATION: Batch query for existing tags instead of N+1 queries
        $existingTags = $this->tags->findBy(['name' => $names]);

        // Map existing tags by name for quick lookup
        $existingTagsByName = [];
        foreach ($existingTags as $tag) {
            $existingTagsByName[$tag->getName()] = $tag;
        }

        // Build result array: use existing tags or create new ones
        $result = [];
        foreach ($names as $name) {
            $result[] = $existingTagsByName[$name] ?? new Tag($name);
        }

        return $result;
    }
}
