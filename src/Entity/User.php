<?php

namespace App\Entity;

// Repository associé à cette entité (sert à faire des requêtes sur User)
use App\Repository\UserRepository;

// Collections Doctrine pour gérer les relations (ManyToMany ici)
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

// Annotations modernes Doctrine (PHP 8 attributes)
use Doctrine\ORM\Mapping as ORM;

/**
 * ============================
 * ENTITÉ USER
 * ============================
 * Représente un utilisateur de l’application TaskLinker
 */
#[ORM\Entity(repositoryClass: UserRepository::class)] // Cette classe est une entité Doctrine
#[ORM\Table(name: '`user`')] // Nom de la table en base (user est un mot réservé → backticks)
class User
{
    /**
     * ============================
     * IDENTIFIANT
     * ============================
     */

    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Auto-incrémentée
    #[ORM\Column] // Colonne en base
    private ?int $id = null;

    /**
     * ============================
     * INFORMATIONS PERSONNELLES
     * ============================
     */

    #[ORM\Column(length: 100)]
    private ?string $firstName = null; // Prénom de l’utilisateur

    #[ORM\Column(length: 100)]
    private ?string $lastName = null; // Nom de famille

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null; // Email unique (login logique)

    /**
     * ============================
     * INFORMATIONS PROFESSIONNELLES
     * ============================
     */

    // Statut du contrat (CDI, CDD, Freelance, etc.)
    // → exigé par les specs du projet TaskLinker
    #[ORM\Column(length: 50)]
    private ?string $contractStatus = null;

    // Date d’entrée dans l’entreprise
    // → DateTimeImmutable = date non modifiable (bonne pratique)
    #[ORM\Column]
    private ?\DateTimeImmutable $hiredAt = null;

    /**
     * ============================
     * RELATION AVEC PROJECT
     * ============================
     */

    // Un utilisateur peut appartenir à plusieurs projets
    // Un projet peut avoir plusieurs utilisateurs
    #[ORM\ManyToMany(
        targetEntity: Project::class,
        mappedBy: 'users' // correspond à la propriété $users dans Project
    )]
    private Collection $projects;

    /**
     * ============================
     * CONSTRUCTEUR
     * ============================
     */
    public function __construct()
    {
        // Initialisation obligatoire des collections Doctrine
        $this->projects = new ArrayCollection();
    }

    /**
     * ============================
     * GETTERS / SETTERS
     * ============================
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    // -------- First name --------
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this; // permet le chaînage ->setFirstName()->setLastName()
    }

    // -------- Last name --------
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    // -------- Email --------
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // -------- Contract status --------
    public function getContractStatus(): ?string
    {
        return $this->contractStatus;
    }

    public function setContractStatus(string $contractStatus): self
    {
        $this->contractStatus = $contractStatus;
        return $this;
    }

    // -------- Hired at --------
    public function getHiredAt(): ?\DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function setHiredAt(\DateTimeImmutable $hiredAt): self
    {
        $this->hiredAt = $hiredAt;
        return $this;
    }

    /**
     * ============================
     * RELATION PROJECTS
     * ============================
     */

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    // Ajoute un projet à l’utilisateur
    public function addProject(Project $project): self
    {
        // On évite les doublons
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    // Supprime un projet de l’utilisateur
    public function removeProject(Project $project): self
    {
        $this->projects->removeElement($project);
        return $this;
    }

    /**
     * ============================
     * MÉTHODES UTILITAIRES
     * ============================
     */

    // Prénom + nom (utile pour l’affichage)
    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    // Conversion automatique en string
    // → utilisée dans les formulaires Symfony (EntityType)
    public function __toString(): string
    {
        return $this->getFullName();
    }
}
