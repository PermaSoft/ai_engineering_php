<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom datetime picker field using HTML5 datetime-local input.
 *
 * Provides a single input field instead of separate dropdowns.
 */
final class DateTimePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => true,
        ]);
    }

    public function getParent(): string
    {
        return DateTimeType::class;
    }
}
