<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        // Afficher uniquement les projets non archivés
        $projects = $projectRepository->findBy(
            ['archivedAt' => null],
            ['id' => 'ASC']
        );

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $project = new Project();
        $project->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(
        Project $project,
        TaskRepository $taskRepository,
        StatusRepository $statusRepository
    ): Response {
        // Si archivé -> 404
        if ($project->isArchived()) {
            throw $this->createNotFoundException();
        }

        // Statuts "To Do / Doing / Done"
        $statusTodo  = $statusRepository->findOneBy(['label' => 'To Do']);
        $statusDoing = $statusRepository->findOneBy(['label' => 'Doing']);
        $statusDone  = $statusRepository->findOneBy(['label' => 'Done']);

        if (!$statusTodo || !$statusDoing || !$statusDone) {
            throw $this->createNotFoundException(
                'Statuts manquants. Vérifie tes fixtures Status: To Do / Doing / Done.'
            );
        }

        // Tâches par statut
        $todo = $taskRepository->findBy(
            ['project' => $project, 'status' => $statusTodo],
            ['id' => 'ASC']
        );

        $doing = $taskRepository->findBy(
            ['project' => $project, 'status' => $statusDoing],
            ['id' => 'ASC']
        );

        $done = $taskRepository->findBy(
            ['project' => $project, 'status' => $statusDone],
            ['id' => 'ASC']
        );

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'todo' => $todo,
            'doing' => $doing,
            'done' => $done,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $em): Response
    {
        if ($project->isArchived()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/archive', name: 'app_project_archive', methods: ['POST'])]
    public function archive(Request $request, Project $project, EntityManagerInterface $em): Response
    {
        if ($project->isArchived()) {
            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('archive' . $project->getId(), (string) $request->request->get('_token'))) {
            $project->setArchivedAt(new \DateTimeImmutable());
            $em->flush();
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }

    // Optionnel (si tu l'avais dans l'énoncé / maquette)
    #[Route('/{id}/tasks', name: 'app_project_tasks', methods: ['GET'])]
    public function tasks(Project $project): Response
    {
        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
    }
}
