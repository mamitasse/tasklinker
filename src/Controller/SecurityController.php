<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si déjà connecté → on l’envoie vers les projets
        if ($this->getUser()) {
            return $this->redirectToRoute('app_project_index');
        }

        // Dernière erreur de login (si échec)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier username saisi (email)
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepte automatiquement cette route
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
