<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=13, unique=true)
     * @Groups({"mybooks","bookList","details"})
     * @Assert\Length(
     *      min = 4,
     *      max = 13,
     *      minMessage = "ISBN must be at least {{ limit }} characters long",
     *      maxMessage = "ISBN name cannot be longer than {{ limit }} characters"
     * )
     */
    private $isbn;

    /**
     * @ORM\Column(type="string", length=200)
     * @Groups({"mybooks","bookList","details"})
     * @Assert\Length(
     *      min = 1,
     *      max = 200,
     *      minMessage = "title must be at least {{ limit }} characters long",
     *      maxMessage = "title name cannot be longer than {{ limit }} characters"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"mybooks","bookList","details"})
     * @Assert\Length(
     *      min = 1,
     *      minMessage = "description must be at least {{ limit }} characters long",
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     * @Groups({"mybooks","bookList","details"})
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Author", inversedBy="books")
     * @ORM\JoinColumn(name="author_email", referencedColumnName="email")
     * @Groups({"bookList","details"})
     */
    private $author;
    /**
     * @ORM\OneToMany(targetEntity="Opinion", mappedBy="book")
     * @Groups({"mybooks","details"})
     */
    private $opinions;

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreated(): ?string
    {
        return $this->created->format("m/d/Y");
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return Collection|Opinion[]
     */
    public function getOpinions(): Collection
    {
        return $this->opinions;
    }

    public function addOpinion(Opinion $opinion): self
    {
        if (!$this->opinions->contains($opinion)) {
            $this->opinions[] = $opinion;
            $opinion->setBook($this);
        }

        return $this;
    }

    public function removeOpinion(Opinion $opinion): self
    {
        if ($this->opinions->removeElement($opinion)) {
            // set the owning side to null (unless already changed)
            if ($opinion->getBook() === $this) {
                $opinion->setBook(null);
            }
        }

        return $this;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
