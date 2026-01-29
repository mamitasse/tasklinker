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
        // 1) USERS
        // =========================
        $natalie = (new User())
            ->setFirstName('Natalie')
            ->setLastName('Dillon')
            ->setEmail('natalie@driblet.com')
            ->setContractStatus('CDI')
            ->setHiredAt(new \DateTimeImmutable('2019-06-14'));
        $manager->persist($natalie);

        $demi = (new User())
            ->setFirstName('Demi')
            ->setLastName('Baker')
            ->setEmail('demi@driblet.com')
            ->setContractStatus('CDD')
            ->setHiredAt(new \DateTimeImmutable('2021-02-01'));
        $manager->persist($demi);

        $marie = (new User())
            ->setFirstName('Marie')
            ->setLastName('Dupont')
            ->setEmail('marie@driblet.com')
            ->setContractStatus('Freelance')
            ->setHiredAt(new \DateTimeImmutable('2020-09-10'));
        $manager->persist($marie);

        // =========================
        // 2) STATUTS
        // =========================
        $statusTodo = (new Status())->setLabel('To Do');
        $statusDoing = (new Status())->setLabel('Doing');
        $statusDone = (new Status())->setLabel('Done');

        $manager->persist($statusTodo);
        $manager->persist($statusDoing);
        $manager->persist($statusDone);

        // =========================
        // 3) PROJETS
        // =========================
        $project1 = (new Project())->setName('Projet Site Vitrine');
        $project2 = (new Project())->setName('Projet Application Mobile');

        $manager->persist($project1);
        $manager->persist($project2);

        $project1->addUser($natalie)->addUser($demi);
        $project2->addUser($demi)->addUser($marie);

        // =========================
        // 4) TACHES - Projet 1
        // =========================
        $t1 = (new Task())
            ->setTitle("Gestion des droits d'accès")
            ->setDescription("Un employé ne peut accéder qu'à ses projets")
            ->setProject($project1)
            ->setStatus($statusTodo)
            ->setDeadline(new \DateTimeImmutable('2026-02-05'));
        $manager->persist($t1);

        $t2 = (new Task())
            ->setTitle("Développement de la page employé")
            ->setDescription("Page employé avec liste des employés + édition/modification/suppression")
            ->setProject($project1)
            ->setStatus($statusDoing)
            ->setDeadline(new \DateTimeImmutable('2026-02-10'))
            ->setAssignee($demi);
        $manager->persist($t2);

        $t3 = (new Task())
            ->setTitle("Développement de la structure globale")
            ->setDescription("Intégrer les maquettes")
            ->setProject($project1)
            ->setStatus($statusDone)
            ->setDeadline(new \DateTimeImmutable('2026-01-25'))
            ->setAssignee($demi);
        $manager->persist($t3);

        $t4 = (new Task())
            ->setTitle("Développement de la page projet")
            ->setDescription("Page projet avec colonnes To Do / Doing / Done")
            ->setProject($project1)
            ->setStatus($statusDone)
            ->setAssignee($natalie);
        $manager->persist($t4);

        // =========================
        // 5) TACHES - Projet 2
        // =========================
        $t5 = (new Task())
            ->setTitle("Créer la page d'accueil projets")
            ->setDescription("Afficher la liste des projets via Doctrine")
            ->setProject($project2)
            ->setStatus($statusTodo);
        $manager->persist($t5);

        $t6 = (new Task())
            ->setTitle("Créer le CRUD tâches")
            ->setDescription("Créer / modifier / supprimer une tâche via FormTypes")
            ->setProject($project2)
            ->setStatus($statusDoing)
            ->setAssignee($marie);
        $manager->persist($t6);

        $manager->flush();
    }
}
