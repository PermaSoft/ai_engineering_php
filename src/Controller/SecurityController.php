<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller for handling authentication (login/logout).
 */
final class SecurityController extends AbstractController
{
    /**
     * Display the login form and handle authentication errors.
     */
    #[Route('/login', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect already authenticated users to the blog index
        if ($this->getUser()) {
            return $this->redirectToRoute('blog_index');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    /**
     * Logout route - handled automatically by Symfony security system.
     */
    #[Route('/logout', name: 'security_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method should never be reached. Logout is handled by Symfony security system.');
    }
}
