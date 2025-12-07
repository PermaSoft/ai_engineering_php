<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\DataTransformer\CollectionToArrayTransformer;
use App\Form\Type\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom field for comma-separated tags input.
 *
 * Input: "lorem, ipsum, dolor"
 * Output: Collection<Tag>
 *
 * Applies two transformations:
 * 1. Collection ↔ Array
 * 2. Array ↔ String
 */
final class TagsInputType extends AbstractType
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new TagArrayToStringTransformer($this->tags), true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'attr' => [
                'placeholder' => 'tag.placeholder',
                'class' => 'form-control',
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
