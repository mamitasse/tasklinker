<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        // -------------------------
        // USERS (passwords hashed)
        // -------------------------
        $user1 = (new User())
            ->setEmail('alice@example.com')
            ->setFirstName('Alice')
            ->setLastName('Martin');

        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password'));

        $user2 = (new User())
            ->setEmail('bob@example.com')
            ->setFirstName('Bob')
            ->setLastName('Durand');

        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password'));

        $user3 = (new User())
            ->setEmail('charlie@example.com')
            ->setFirstName('Charlie')
            ->setLastName('Bernard');

        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'password'));

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);

        // -------------------------
        // STATUS
        // -------------------------
        $todo = (new Status())->setLabel('À faire');
        $doing = (new Status())->setLabel('En cours');
        $done = (new Status())->setLabel('Terminé');

        $manager->persist($todo);
        $manager->persist($doing);
        $manager->persist($done);

        // -------------------------
        // PROJECTS
        // -------------------------
        $project1 = (new Project())
            ->setName('Projet Site Vitrine')
            ->setDescription('Création du site vitrine de l’entreprise')
            ->setCreatedAt($now)
            ->setOwner($user1);

        $project2 = (new Project())
            ->setName('Projet Application Mobile')
            ->setDescription('Développement d’une app mobile interne')
            ->setCreatedAt($now)
            ->setOwner($user2);

        $manager->persist($project1);
        $manager->persist($project2);

        // -------------------------
        // TASKS
        // -------------------------
        $task1 = (new Task())
            ->setTitle('Rédiger le cahier des charges')
            ->setDescription('Lister les besoins et contraintes')
            ->setCreatedAt($now)
            ->setProject($project1)
            ->setAssignee($user1)
            ->setStatus($todo);

        $task2 = (new Task())
            ->setTitle('Créer la maquette Figma')
            ->setDescription('Maquette desktop + mobile')
            ->setCreatedAt($now)
            ->setProject($project1)
            ->setAssignee($user3)
            ->setStatus($doing);

        $task3 = (new Task())
            ->setTitle('Intégrer la page d’accueil')
            ->setDescription(null)
            ->setCreatedAt($now)
            ->setProject($project1)
            ->setAssignee(null)
            ->setStatus($todo);

        $task4 = (new Task())
            ->setTitle('Définir l’architecture technique')
            ->setDescription('API + base de données')
            ->setCreatedAt($now)
            ->setProject($project2)
            ->setAssignee($user2)
            ->setStatus($doing);

        $task5 = (new Task())
            ->setTitle('Mettre en place CI/CD')
            ->setDescription('Pipeline GitHub Actions')
            ->setCreatedAt($now)
            ->setProject($project2)
            ->setAssignee($user1)
            ->setStatus($todo);

        $task6 = (new Task())
            ->setTitle('Livrer la V1')
            ->setDescription('Démo + doc')
            ->setCreatedAt($now)
            ->setProject($project2)
            ->setAssignee($user2)
            ->setStatus($done);

        $manager->persist($task1);
        $manager->persist($task2);
        $manager->persist($task3);
        $manager->persist($task4);
        $manager->persist($task5);
        $manager->persist($task6);

        $manager->flush();
    }
}
