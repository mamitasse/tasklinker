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
    /**
     * ============================
     * PAGE LISTE DES PROJETS
     * ============================
     * Avant : on faisais findBy(['archivedAt' => null]) => donc TOUS les projets non archivés
     * Problème : après ajout de l'auth, un collaborateur voit tous les projets -> pas conforme à l'énoncé.
     *
     * Maintenant : on récupère UNIQUEMENT les projets accessibles à l'utilisateur connecté :
     *  - ROLE_ADMIN (chef de projet) => tous les projets non archivés
     *  - ROLE_USER (collaborateur)   => seulement ceux où il est membre (relation ManyToMany)
     */
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // ✅ On délègue la logique d'accès au repository (plus propre, plus testable)
        $projects = $projectRepository->findAccessibleNotArchivedProjectsFor($user);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * ============================
     * CRÉATION PROJET
     * ============================
     * (l'énoncé dira ensuite : seul ROLE_ADMIN peut créer/modifier)
     * => On mettra la restriction après, mais ici on garde la logique.
     */
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

    /**
     * ============================
     * AFFICHAGE D'UN PROJET + KANBAN
     * ============================
     * On garde le fonctionnement (statuts To Do/Doing/Done).
     *
     * ⚠️ ÉTAPE suivante de l'énoncé :
     * il faudra empêcher un collaborateur d'ouvrir un projet qui ne lui appartient pas.
     * (ça, je le fera plus tard.
     */
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

    /**
     * ============================
     * ÉDITION PROJET
     * ============================
     * Pour l’instant on garde.
     * (Plus tard : seul ROLE_ADMIN pourra éditer.)
     */
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

    /**
     * ============================
     * ARCHIVAGE PROJET (soft delete)
     * ============================
     * je ne supprimes pas le projet,
     * je remplis archivedAt => il disparaît des listes.
     */
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

    // Optionnel
    #[Route('/{id}/tasks', name: 'app_project_tasks', methods: ['GET'])]
    public function tasks(Project $project): Response
    {
        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
    }
}
