<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class TaskController extends AbstractController
{
    /**
     * Création d'une tâche (prototype: page "Créer une tâche")
     */
    #[Route('/{id}/tasks/new', name: 'app_project_tasks_new', methods: ['GET', 'POST'])]
    public function new(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        if ($project->isArchived()) {
            throw $this->createNotFoundException();
        }

        $task = new Task();
        $task->setProject($project);

        $form = $this->createForm(TaskType::class, $task, [
            'project' => $project,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'project' => $project,
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * IMPORTANT (prototype):
     * Afficher une tâche = afficher le formulaire d'édition (et sauvegarder sur POST).
     * URL: /project/{projectId}/tasks/{id}
     */
    #[Route('/{projectId}/tasks/{id}', name: 'app_project_tasks_show', methods: ['GET', 'POST'])]
    public function show(int $projectId, Task $task, Request $request, EntityManagerInterface $em): Response
    {
        // Sécurité : vérifier que la tâche appartient bien au projet de l'URL
        if ($task->getProject()->getId() !== $projectId) {
            throw $this->createNotFoundException();
        }

        if ($task->getProject()->isArchived()) {
            throw $this->createNotFoundException();
        }

        // Formulaire d'édition directement sur la page "show" (comme le prototype)
        $form = $this->createForm(TaskType::class, $task, [
            'project' => $task->getProject(),
        ]);
        $form->handleRequest($request);

        // Quand on clique "Modifier" (submit), on enregistre
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_project_show', ['id' => $projectId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/show.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Optionnel : si tu gardes une URL /edit, tu peux simplement rediriger vers "show"
     */
    #[Route('/{projectId}/tasks/{id}/edit', name: 'app_project_tasks_edit', methods: ['GET'])]
    public function edit(int $projectId, Task $task): Response
    {
        return $this->redirectToRoute('app_project_tasks_show', [
            'projectId' => $projectId,
            'id' => $task->getId(),
        ]);
    }

    /**
     * Suppression d'une tâche (appelée depuis la page task/show.html.twig)
     */
    #[Route('/{projectId}/tasks/{id}/delete', name: 'app_project_tasks_delete', methods: ['POST'])]
    public function delete(int $projectId, Request $request, Task $task, EntityManagerInterface $em): Response
    {
        if ($task->getProject()->getId() !== $projectId) {
            throw $this->createNotFoundException();
        }

        if ($task->getProject()->isArchived()) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete_task_'.$task->getId(), (string) $request->request->get('_token'))) {
            $em->remove($task);
            $em->flush();
        }

        return $this->redirectToRoute('app_project_show', ['id' => $projectId], Response::HTTP_SEE_OTHER);
    }

    /**
     * Drag & Drop : mise à jour du statut via AJAX
     */
    #[Route('/tasks/{id}/status', name: 'app_task_update_status', methods: ['POST'])]
    public function updateStatus(
        Task $task,
        Request $request,
        StatusRepository $statusRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($task->getProject()->isArchived()) {
            return $this->json(['error' => 'Projet archivé'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || empty($data['status'])) {
            return $this->json(['error' => 'Status manquant'], 400);
        }

        // Le front envoie: todo|doing|done ; en base: "To Do"|"Doing"|"Done"
        $map = [
            'todo' => 'To Do',
            'doing' => 'Doing',
            'done' => 'Done',
        ];

        $wanted = (string) $data['status'];
        if (!isset($map[$wanted])) {
            return $this->json(['error' => 'Status invalide'], 400);
        }

        $statusEntity = $statusRepository->findOneBy(['label' => $map[$wanted]]);
        if (!$statusEntity) {
            return $this->json(['error' => 'Status introuvable en base'], 500);
        }

        $task->setStatus($statusEntity);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
