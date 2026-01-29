<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // =========================
        // 1) USERS (pour "Inviter des membres")
        // =========================
        $natalie = new User();
        $natalie->setFirstName('Natalie');
        $natalie->setLastName('Dillon');
        $natalie->setEmail('natalie@test.com');
        $manager->persist($natalie);

        $demi = new User();
        $demi->setFirstName('Demi');
        $demi->setLastName('Baker');
        $demi->setEmail('demi@test.com');
        $manager->persist($demi);

        $marie = new User();
        $marie->setFirstName('Marie');
        $marie->setLastName('Dupont');
        $marie->setEmail('marie@test.com');
        $manager->persist($marie);

        // =========================
        // 2) STATUTS (obligatoires)
        // =========================
        $statusTodo = new Status();
        $statusTodo->setLabel('To Do');
        $manager->persist($statusTodo);

        $statusDoing = new Status();
        $statusDoing->setLabel('Doing');
        $manager->persist($statusDoing);

        $statusDone = new Status();
        $statusDone->setLabel('Done');
        $manager->persist($statusDone);

        // =========================
        // 3) PROJETS
        // =========================
        $project1 = new Project();
        $project1->setName('Projet Site Vitrine');
        $manager->persist($project1);

        $project2 = new Project();
        $project2->setName('Projet Application Mobile');
        $manager->persist($project2);

        // ✅ si tu as bien mis la relation ManyToMany Project<->User :
        // (sinon, commente ces lignes)
        if (method_exists($project1, 'addUser')) {
            $project1->addUser($natalie)->addUser($demi);
            $project2->addUser($demi)->addUser($marie);
        }

        // =========================
        // 4) TACHES - Projet 1
        // =========================
        $t1 = new Task();
        $t1->setTitle("Gestion des droits d'accès");
        $t1->setDescription("Un employé ne peut accéder qu'à ses projets");
        $t1->setProject($project1);
        $t1->setStatus($statusTodo);
        $t1->setDeadline(new \DateTimeImmutable('2026-02-05'));
        $manager->persist($t1);

        $t2 = new Task();
        $t2->setTitle("Développement de la page employé");
        $t2->setDescription("Page employé avec liste des employés + édition/modification/suppression");
        $t2->setProject($project1);
        $t2->setStatus($statusDoing);
        $t2->setDeadline(new \DateTimeImmutable('2026-02-10'));
        $t2->setAssignee($demi);
        $manager->persist($t2);

        $t3 = new Task();
        $t3->setTitle("Développement de la structure globale");
        $t3->setDescription("Intégrer les maquettes");
        $t3->setProject($project1);
        $t3->setStatus($statusDone);
        $t3->setDeadline(new \DateTimeImmutable('2026-01-25'));
        $t3->setAssignee($demi);
        $manager->persist($t3);

        $t4 = new Task();
        $t4->setTitle("Développement de la page projet");
        $t4->setDescription("Page projet avec colonnes To Do / Doing / Done");
        $t4->setProject($project1);
        $t4->setStatus($statusDone);
        $t4->setAssignee($natalie);
        $manager->persist($t4);

        // =========================
        // 5) TACHES - Projet 2
        // =========================
        $t5 = new Task();
        $t5->setTitle("Créer la page d'accueil projets");
        $t5->setDescription("Afficher la liste des projets via Doctrine");
        $t5->setProject($project2);
        $t5->setStatus($statusTodo);
        $manager->persist($t5);

        $t6 = new Task();
        $t6->setTitle("Créer le CRUD tâches");
        $t6->setDescription("Créer / modifier / supprimer une tâche via FormTypes");
        $t6->setProject($project2);
        $t6->setStatus($statusDoing);
        $t6->setAssignee($marie);
        $manager->persist($t6);

        $manager->flush();
    }
}
