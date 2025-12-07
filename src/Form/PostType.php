<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Post;
use App\Form\Type\DateTimePickerType;
use App\Form\Type\TagsInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Form for creating and editing blog posts.
 *
 * Features:
 * - Auto-generates slug from title on form submission
 * - Custom datetime picker for publish date
 * - Custom tags input for comma-separated tags
 */
final class PostType extends AbstractType
{
    public function __construct(
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title',
            ])
            ->add('summary', TextareaType::class, [
                'help' => 'help.post_summary',
                'label' => 'label.summary',
            ])
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'help' => 'help.post_content',
                'label' => 'label.content',
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'help' => 'help.post_publication',
            ])
            ->add('tags', TagsInputType::class, [
                'label' => 'label.tags',
                'required' => false,
            ])
            ->add('saveAndCreateNew', SubmitType::class, [
                'label' => 'action.save_and_create_new',
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                /** @var Post|null $post */
                $post = $event->getData();
                if (null !== $post && null === $post->getSlug() && null !== $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($post->getTitle())->lower()->toString());
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
