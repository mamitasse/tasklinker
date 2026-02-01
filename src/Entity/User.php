<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ============================
 * ENTITÉ USER (Employé)
 * ============================
 * Représente un employé de BeWize dans TaskLinker.
 * En V2, cette entité devient aussi l'utilisateur de sécurité Symfony :
 * - email = identifiant de connexion
 * - password = mot de passe hashé
 * - roles = permissions (collaborateur / chef de projet)
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Cet e-mail est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * ============================
     * IDENTIFIANT
     * ============================
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * ============================
     * INFORMATIONS PERSONNELLES
     * ============================
     */
    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * ============================
     * SÉCURITÉ (V2)
     * ============================
     */

    /**
     * Rôles Symfony.
     * - ROLE_USER : collaborateur (par défaut)
     * - ROLE_MANAGER (ou ROLE_PROJECT_MANAGER) : chef de projet
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Mot de passe hashé.
     * (Ne jamais stocker le mot de passe en clair)
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * ============================
     * INFORMATIONS PROFESSIONNELLES
     * ============================
     */
    #[ORM\Column(length: 50)]
    private ?string $contractStatus = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $hiredAt = null;

    /**
     * ============================
     * RELATIONS
     * ============================
     */

    /**
     * Un user peut être associé à plusieurs projets (ManyToMany)
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'users')]
    private Collection $projects;

    /**
     * ✅ IMPORTANT (fix Doctrine) :
     * Un user peut être assigné à plusieurs tâches (OneToMany)
     * => correspond à Task::$assignee (ManyToOne)
     *
     * Sans cette propriété, Doctrine dit :
     * "Task#assignee refers to inverse side User#tasks which does not exist"
     */
    #[ORM\OneToMany(mappedBy: 'assignee', targetEntity: Task::class)]
    private Collection $tasks;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->tasks = new ArrayCollection(); // ✅ ajout indispensable
    }

    // ============================
    // GETTERS / SETTERS
    // ============================

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
        return $this;
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
    /** @return Collection<int, Project> */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
        }
        return $this;
    }

    public function removeProject(Project $project): self
    {
        $this->projects->removeElement($project);
        return $this;
    }

    /**
     * ============================
     * RELATION TASKS (nouveau)
     * ============================
     */

    /** @return Collection<int, Task> */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * ============================
     * MÉTHODES UTILITAIRES
     * ============================
     */
    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    /**
     * ============================
     * MÉTHODES SECURITY (Symfony)
     * ============================
     */

    public function getUserIdentifier(): string
    {
        // Symfony utilise ce champ comme identifiant (login)
        return (string) $this->email;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        // Toujours au minimum ROLE_USER
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Si plus tard tu stockes un "plainPassword" temporaire, tu le nettoieras ici.
    }
}
