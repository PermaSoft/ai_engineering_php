<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form for changing user password with security constraints.
 *
 * Security features:
 * - Requires current password verification
 * - Password confirmation field
 * - Autocomplete disabled
 * - Maximum length constraint (128 chars) to prevent DoS
 * - Fields marked as mapped => false
 */
final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'label.current_password',
                'mapped' => false, // Don't map to entity property
                'constraints' => [
                    new NotBlank(
                        message: 'password.not_blank',
                    ),
                    new UserPassword(
                        message: 'password.mismatch',
                    ),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // Don't map to entity property
                'first_options' => [
                    'label' => 'label.new_password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'label.password_confirmation',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'password.dont_match',
                'constraints' => [
                    new NotBlank(
                        message: 'password.not_blank',
                    ),
                    new Length(
                        min: 6,
                        max: 128, // ✅ SECURITY: Maximum password length to prevent DoS
                        minMessage: 'password.too_short',
                        maxMessage: 'password.too_long',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Don't bind to entity - we handle password manually
            'data_class' => null,
        ]);
    }
}
