<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for user profile management.
 *
 * Handles profile editing and password changes.
 */
#[Route('/profile')]
#[IsGranted('IS_AUTHENTICATED')]
final class UserController extends AbstractController
{
    /**
     * Edit user profile (username, email, full name).
     */
    #[Route('/edit', name: 'user_edit')]
    public function edit(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'profile.updated_successfully');

            return $this->redirectToRoute('user_edit');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Change user password.
     */
    #[Route('/change-password', name: 'user_change_password')]
    public function changePassword(
        #[CurrentUser] User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
            );

            $entityManager->flush();

            $this->addFlash('success', 'password.changed_successfully');

            return $this->redirectToRoute('user_edit');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
