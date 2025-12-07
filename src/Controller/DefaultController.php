<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for the homepage.
 */
final class DefaultController extends AbstractController
{
    /**
     * Homepage - redirects to blog index.
     */
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->redirectToRoute('blog_index');
    }
}
