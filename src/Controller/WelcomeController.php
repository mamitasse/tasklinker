<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    #[Route('/welcome', name: 'app_welcome', methods: ['GET'])]
    public function index(): Response
    {
        // Si l'utilisateur est déjà connecté, inutile d'afficher welcome
        if ($this->getUser()) {
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('security/welcome.html.twig');
    }
}
