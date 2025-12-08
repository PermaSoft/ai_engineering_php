<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom DateTimePicker using Flatpickr JavaScript library.
 *
 * Provides a sophisticated date/time picker with:
 * - Internationalization support
 * - User-friendly calendar interface
 * - Time selection when enabled
 * - Consistent UX across browsers
 */
final class DateTimePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'html5' => false, // Disable HTML5 widget, use Flatpickr
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Add Stimulus controller and Flatpickr configuration
        $view->vars['attr'] = array_merge($view->vars['attr'] ?? [], [
            'data-controller' => 'flatpickr',
            'data-flatpickr-enable-time-value' => 'true',
            'data-flatpickr-date-format-value' => 'Y-m-d H:i:S',
            'data-flatpickr-alt-format-value' => 'F j, Y at H:i',
            'data-flatpickr-time-24hr-value' => 'true',
            'class' => trim(($view->vars['attr']['class'] ?? '') . ' form-control'),
            'autocomplete' => 'off',
        ]);
    }

    public function getParent(): string
    {
        return DateTimeType::class;
    }
}
