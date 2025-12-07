<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form for admin user creation and editing.
 *
 * Allows admins to create new users with all properties including password and roles.
 */
final class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', null, [
                'label' => 'label.fullname',
            ])
            ->add('username', null, [
                'label' => 'label.username',
                'help' => 'help.username',
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'label.password',
                'required' => !$options['is_edit'],
                'mapped' => false,
                'constraints' => $options['is_edit'] ? [] : [
                    new NotBlank(),
                    new Length(['min' => 6]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'label.roles',
                'choices' => [
                    'role.user' => User::ROLE_USER,
                    'role.admin' => User::ROLE_ADMIN,
                ],
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
