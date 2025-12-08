<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\DataTransformer\CollectionToArrayTransformer;
use App\Form\Type\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom field for comma-separated tags input with autocomplete.
 *
 * Input: "lorem, ipsum, dolor"
 * Output: Collection<Tag>
 *
 * Provides:
 * - Tag autocomplete suggestions
 * - Comma-separated tag input
 * - Automatic tag creation
 * - Data transformation between collection and string
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Load all existing tags for autocomplete suggestions
        $tags = $this->tags->findAll();

        // Pass tag names to the view
        $view->vars['tags'] = array_map(
            static fn($tag) => $tag->getName(),
            $tags
        );

        // Add attributes for JavaScript enhancement
        $view->vars['attr'] = array_merge($view->vars['attr'] ?? [], [
            'class' => trim(($view->vars['attr']['class'] ?? '') . ' form-control'),
            'data-tags' => json_encode($view->vars['tags']),
            'placeholder' => 'Enter tags separated by commas',
        ]);
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
