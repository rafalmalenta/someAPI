<?php

namespace App\Entity;

use App\Repository\OpinionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=OpinionRepository::class)
 */
class Opinion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"details"})
     * @Assert\Range(
     *     min = 1,
     *     max = 10,
     *     notInRangeMessage = "rating must be between {{ min }} and {{ max }}"
     * )
     */
    private $rating;

    /**
     * @ORM\Column(type="text")
     * @Groups({"details"})
     * @Assert\Length(
     *      min = 2,
     *      max = 500,
     *      minMessage = "description must be at least {{ limit }} characters long",
     *      maxMessage = "description name cannot be longer than {{ limit }} characters"
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"details"})
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "description must be at least {{ limit }} characters long",
     *      maxMessage = "description name cannot be longer than {{ limit }} characters"
     * )
     */
    private $author;

    /**
     * @ORM\Column(type="date")
     * @Groups({"details"})
     */
    private $created;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"details"})
     * @Assert\Regex(
     *     pattern="/^\S+@\S+$/",
     *     match=true,
     *     message="This doesnt looks like real email"
     * )
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="Book", inversedBy="opinions" )
     * @ORM\JoinColumn(name="book_isbn", referencedColumnName="isbn")
     */
    private $book;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreated(): string
    {
        return $this->created->format('Y-m-d');
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     * @param mixed $book
     */
    public function setBook($book): self
    {
        $this->book = $book;
        return $this;
    }

}
