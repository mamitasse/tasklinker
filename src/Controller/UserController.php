<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team')]
final class UserController extends AbstractController
{
    #[Route('', name: 'app_team_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_team_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_team_delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        TaskRepository $taskRepository
    ): Response {
        if (!$this->isCsrfTokenValid('delete_user_'.$user->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_team_index');
        }

        // ✅ Specs : si l'employé est associé à des tâches/projets → on le retire seulement
        // 1) enlever l'assignee sur les tâches
        $tasks = $taskRepository->findBy(['assignee' => $user]);
        foreach ($tasks as $task) {
            $task->setAssignee(null);
        }

        // 2) enlever des projets (ManyToMany)
        foreach ($user->getProjects() as $project) {
            $project->removeUser($user); // nécessite Project::removeUser()
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_team_index', status: Response::HTTP_SEE_OTHER);
    }
}
