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
            'form' => $form,
        ]);
    }

    #[Route('/{projectId}/tasks/{id}', name: 'app_project_tasks_show', methods: ['GET'])]
    public function show(int $projectId, Task $task): Response
    {
        if ($task->getProject()->getId() !== $projectId) {
            throw $this->createNotFoundException();
        }

        if ($task->getProject()->isArchived()) {
            throw $this->createNotFoundException();
        }

        return $this->render('task/show.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
        ]);
    }

    #[Route('/{projectId}/tasks/{id}/edit', name: 'app_project_tasks_edit', methods: ['GET', 'POST'])]
    public function edit(int $projectId, Request $request, Task $task, EntityManagerInterface $em): Response
    {
        if ($task->getProject()->getId() !== $projectId) {
            throw $this->createNotFoundException();
        }

        if ($task->getProject()->isArchived()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TaskType::class, $task, [
            'project' => $task->getProject(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_project_show', ['id' => $projectId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/tasks/{id}/status', name: 'app_task_update_status', methods: ['POST'])]
public function updateStatus(
    Task $task,
    Request $request,
    StatusRepository $statusRepository,
    EntityManagerInterface $em
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    if (!is_array($data) || empty($data['status'])) {
        return $this->json(['error' => 'Status manquant'], 400);
    }

    // mapping FRONT → status_id en base
    $map = [
        'todo' => 1,
        'doing' => 2,
        'done' => 3,
    ];

    $wanted = $data['status'];

    if (!isset($map[$wanted])) {
        return $this->json(['error' => 'Status invalide'], 400);
    }

    $statusEntity = $statusRepository->find($map[$wanted]);

    if (!$statusEntity) {
        return $this->json(['error' => 'Status introuvable'], 500);
    }

    $task->setStatus($statusEntity);
    $em->flush();

    return $this->json(['success' => true]);
}

}
