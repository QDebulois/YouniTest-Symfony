<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Category::class, orphanRemoval: true)]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPostModifie::class, orphanRemoval: true)]
    private Collection $userPostModifie;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCategoryModifie::class, orphanRemoval: true)]
    private Collection $userCategoryModifie;

    public function __construct()
    {
        $this->userPostModifie = new ArrayCollection();
        $this->userCategoryModifie = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserPostModifie>
     */
    public function getUserPostModifie(): Collection
    {
        return $this->userPostModifie;
    }

    public function addUserPostModifie(UserPostModifie $userPostModifie): static
    {
        if (!$this->userPostModifie->contains($userPostModifie)) {
            $this->userPostModifie->add($userPostModifie);
            $userPostModifie->setUser($this);
        }

        return $this;
    }

    public function removeUserPostModifie(UserPostModifie $userPostModifie): static
    {
        if ($this->userPostModifie->removeElement($userPostModifie)) {
            // set the owning side to null (unless already changed)
            if ($userPostModifie->getUser() === $this) {
                $userPostModifie->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserCategoryModifie>
     */
    public function getUserCategoryModifie(): Collection
    {
        return $this->userCategoryModifie;
    }

    public function addUserCategoryModifie(UserCategoryModifie $userCategoryModifie): static
    {
        if (!$this->userCategoryModifie->contains($userCategoryModifie)) {
            $this->userCategoryModifie->add($userCategoryModifie);
            $userCategoryModifie->setUser($this);
        }

        return $this;
    }

    public function removeUserCategoryModifie(UserCategoryModifie $userCategoryModifie): static
    {
        if ($this->userCategoryModifie->removeElement($userCategoryModifie)) {
            // set the owning side to null (unless already changed)
            if ($userCategoryModifie->getUser() === $this) {
                $userCategoryModifie->setUser(null);
            }
        }

        return $this;
    }
}
