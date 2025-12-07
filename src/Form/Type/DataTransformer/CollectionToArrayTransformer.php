<?php

declare(strict_types=1);

namespace App\Form\Type\DataTransformer;

use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms between Doctrine Collection and PHP array.
 *
 * Transform: Collection<Tag> → array<Tag>
 * Reverse: array<Tag> → ArrayCollection<Tag>
 *
 * @implements DataTransformerInterface<Collection<int, Tag>, array<int, Tag>>
 */
final class CollectionToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms a Doctrine Collection to a PHP array.
     *
     * @param Collection<int, Tag>|null $value
     * @return array<int, Tag>
     */
    public function transform(mixed $value): array
    {
        if (null === $value) {
            return [];
        }

        return $value->toArray();
    }

    /**
     * Transforms a PHP array to a Doctrine ArrayCollection.
     *
     * @param array<int, Tag>|null $value
     * @return Collection<int, Tag>
     */
    public function reverseTransform(mixed $value): Collection
    {
        if (!is_array($value)) {
            return new ArrayCollection([]);
        }

        return new ArrayCollection($value);
    }
}
