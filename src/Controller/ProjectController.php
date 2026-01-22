<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route(name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        // Si tu veux afficher seulement les projets du user connecté :
        // $projects = $projectRepository->findBy(['owner' => $this->getUser()]);
        // Sinon, laisse findAll()
        $projects = $projectRepository->findAll();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();

        // ✅ Règles métier : owner + createdAt automatiques
        $project->setOwner($this->getUser());
        $project->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ProjectType::class, $project, [
            // on peut aussi enlever les champs côté FormType,
            // mais même si le champ existe, on impose la valeur ici
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        // ✅ Protection : seul le owner peut modifier
        if ($project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce projet.');
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // owner/createdAt restent cohérents (on ne laisse pas l’utilisateur les trafiquer)
            $project->setOwner($this->getUser());

            $entityManager->flush();
            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        // ✅ Protection : seul le owner peut supprimer
        if ($project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce projet.');
        }

        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }

    // ✅ La page clé de l’énoncé : afficher les tâches d’un projet
    #[Route('/{id}/tasks', name: 'app_project_tasks', methods: ['GET'])]
    public function tasks(Project $project, TaskRepository $taskRepository): Response
    {
        // (optionnel) n’afficher que si tu es owner
        // si l’énoncé exige que seuls les owners voient :
        // if ($project->getOwner() !== $this->getUser()) {
        //     throw $this->createAccessDeniedException();
        // }

        $tasks = $taskRepository->findBy(
            ['project' => $project],
            ['createdAt' => 'ASC']
        );

        return $this->render('task/index.html.twig', [
            'project' => $project,
            'tasks' => $tasks,
        ]);
    }
}
