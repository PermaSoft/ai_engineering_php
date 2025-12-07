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
     * Homepage - shows navigation to blog and admin sections.
     */
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('default/homepage.html.twig');
    }
}
