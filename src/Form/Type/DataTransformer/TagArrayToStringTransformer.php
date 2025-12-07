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
        return implode(',', $tags);
    }

    /**
     * Transforms a comma-separated string to an array of Tag entities.
     *
     * Creates new tags if they don't exist.
     *
     * @return array<int, Tag>
     */
    public function reverseTransform(mixed $string): array
    {
        if ('' === $string || null === $string) {
            return [];
        }

        // Split by comma, trim whitespace, remove empty values, remove duplicates
        $names = array_filter(array_unique(array_map('trim', explode(',', (string) $string))));

        // Find existing tags or create new ones
        return array_map(function (string $name): Tag {
            return $this->tags->findOneBy(['name' => $name]) ?? new Tag($name);
        }, $names);
    }
}
