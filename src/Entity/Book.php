<?php

namespace App\Entity;

use App\enum\BookStatus;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(min: 2)]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(min: 10, max: 13)]
    #[ORM\Column(length: 255)]
    private ?string $isbn = null;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Url]
    #[ORM\Column(length: 255)]
    private ?string $cover = null;

    #[Assert\NotBlank]
    #[Assert\Type(type: 'DateTimeImmutable')]
    #[Assert\LessThanOrEqual('today')]
    #[ORM\Column]
    private ?\DateTimeImmutable $editedAt = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $plot = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type(type: 'integer')]
    #[ORM\Column]
    private ?int $pageNumber = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [BookStatus::class, 'getAllValue'])]
    #[ORM\Column(length: 255)]
    private ?BookStatus $status = null;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Editor $editor = null;

    /**
     * @var Collection<int, Author>
     */
    #[Assert\AtLeastOneOf([
        new Assert\Count(min: 1),
    ])]
    #[ORM\ManyToMany(targetEntity: Author::class, mappedBy: 'books')]
    private Collection $authors;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'book')]
    private Collection $comments;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStatus(): ?BookStatus
    {
        return $this->status;
    }

    public function setStatus(?BookStatus $status): void
    {
        $this->status = $status;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeImmutable
    {
        return $this->editedAt;
    }

    public function setEditedAt(\DateTimeImmutable $editedAt): static
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    public function getPlot(): ?string
    {
        return $this->plot;
    }

    public function setPlot(string $plot): static
    {
        $this->plot = $plot;

        return $this;
    }

    public function getPageNumber(): ?int
    {
        return $this->pageNumber;
    }

    public function setPageNumber(int $pageNumber): static
    {
        $this->pageNumber = $pageNumber;

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->editor;
    }

    public function setEditor(?Editor $editor): static
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
            $author->addBook($this);
        }

        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        if ($this->authors->removeElement($author)) {
            $author->removeBook($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBook($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getBook() === $this) {
                $comment->setBook(null);
            }
        }

        return $this;
    }
}
