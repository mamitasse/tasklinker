<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class TaskController extends AbstractController
{
    // ✅ Liste des tâches d’un projet
    #[Route('/{id}/tasks', name: 'app_project_tasks', methods: ['GET'])]
    public function index(Project $project, TaskRepository $taskRepository): Response
    {
        return $this->render('task/index.html.twig', [
            'project' => $project,
            'tasks' => $taskRepository->findBy(['project' => $project]),
        ]);
    }

    // ✅ Créer une tâche dans un projet
    #[Route('/{id}/tasks/new', name: 'app_project_tasks_new', methods: ['GET', 'POST'])]
    public function new(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();

        // ✅ important : on fixe le projet automatiquement
        $task->setProject($project);

        $form = $this->createForm(TaskType::class, $task, [
            // optionnel : tu peux désactiver le champ project côté formulaire ensuite
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_tasks', ['id' => $project->getId()]);
        }

        return $this->render('task/new.html.twig', [
            'project' => $project,
            'task' => $task,
            'form' => $form,
        ]);
    }

    // ✅ Voir une tâche (dans le contexte d’un projet)
    #[Route('/{projectId}/tasks/{id}', name: 'app_project_tasks_show', methods: ['GET'])]
    public function show(int $projectId, Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'projectId' => $projectId,
            'task' => $task,
        ]);
    }

    // ✅ Éditer une tâche
    #[Route('/{projectId}/tasks/{id}/edit', name: 'app_project_tasks_edit', methods: ['GET', 'POST'])]
    public function edit(int $projectId, Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_project_tasks', ['id' => $projectId]);
        }

        return $this->render('task/edit.html.twig', [
            'projectId' => $projectId,
            'task' => $task,
            'form' => $form,
        ]);
    }

    // ✅ Supprimer une tâche
    #[Route('/{projectId}/tasks/{id}', name: 'app_project_tasks_delete', methods: ['POST'])]
    public function delete(int $projectId, Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_tasks', ['id' => $projectId]);
    }
}
