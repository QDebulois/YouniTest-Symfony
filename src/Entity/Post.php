<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: UserPostModifie::class, orphanRemoval: true)]
    private Collection $userPostModifie;

    public function __construct()
    {
        $this->userPostModifie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

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
            $userPostModifie->setPost($this);
        }

        return $this;
    }

    public function removeUserPostModifie(UserPostModifie $userPostModifie): static
    {
        if ($this->userPostModifie->removeElement($userPostModifie)) {
            // set the owning side to null (unless already changed)
            if ($userPostModifie->getPost() === $this) {
                $userPostModifie->setPost(null);
            }
        }

        return $this;
    }
}
