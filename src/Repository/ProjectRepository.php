<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ProjectRepository
 *
 * 👉 Ce repository est responsable de TOUTES les requêtes
 * liées à l'entité Project.
 *
 * Symfony / Doctrine l'utilisent automatiquement.
 */
class ProjectRepository extends ServiceEntityRepository
{
    /**
     * Le constructeur est généré par Symfony.
     * Il permet à Doctrine de savoir :
     *  - quelle entité ce repository gère (Project::class)
     *  - quel EntityManager utiliser
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * ================================
     * MÉTHODE PERSONNALISÉE (énoncé)
     * ================================
     *
     * Objectif :
     * - Chef de projet (ROLE_ADMIN) → voit TOUS les projets non archivés
     * - Collaborateur (ROLE_USER)   → voit SEULEMENT ses projets
     *
     * @param User $user L'utilisateur connecté
     * @return Project[] Liste des projets visibles
     */
    public function findAccessibleNotArchivedProjectsFor(User $user): array
    {
        /**
         * QueryBuilder = outil Doctrine pour construire une requête SQL
         * sans écrire du SQL à la main.
         *
         * p = alias de Project
         */
        $qb = $this->createQueryBuilder('p')
            // On exclut les projets archivés
            ->andWhere('p.archivedAt IS NULL')
            ->orderBy('p.id', 'ASC');

        /**
         * CAS 1 : CHEF DE PROJET
         * ---------------------
         * ROLE_ADMIN → accès à tous les projets
         */
        if (in_array('ROLE_MANAGER', $user->getRoles(), true)) {
            return $qb->getQuery()->getResult();
        }

        /**
         * CAS 2 : COLLABORATEUR
         * --------------------
         * On joint la relation ManyToMany Project <-> User
         * et on filtre sur l'utilisateur connecté
         */
        return $qb
            ->innerJoin('p.users', 'u')
            ->andWhere('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
