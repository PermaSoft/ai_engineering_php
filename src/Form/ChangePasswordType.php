<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form for changing user password.
 *
 * Features:
 * - Validates current password
 * - Requires password confirmation
 * - Enforces minimum password length
 */
final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'label.current_password',
                'constraints' => [
                    new UserPassword(),
                ],
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'current-password',  // Proper autocomplete hint for password managers
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'label.new_password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'label.new_password_confirm',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: 6,
                        max: 128,  // Prevent potential performance issues
                        minMessage: 'password.too_short',
                        maxMessage: 'password.too_long'
                    ),
                ],
            ])
        ;
    }
}
