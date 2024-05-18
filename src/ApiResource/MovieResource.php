<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'movie:item']),
        new GetCollection(normalizationContext: ['groups' => 'movie:list'])
    ],
    order: ['releaseYear' => 'DESC'],
    paginationEnabled: false,
)]
class MovieResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['movie:list', 'movie:item'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[Groups(['movie:list', 'movie:item'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ApiProperty]
    #[Groups(['movie:list', 'movie:item'])]
    #[ORM\Column]
    private ?int $releaseYear = null;

    #[ApiProperty]
    #[Groups(['movie:list', 'movie:item'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ApiProperty]
    #[Groups(['movie:list', 'movie:item'])]
    #[ORM\Column(length: 255)]
    private ?string $imagePath = null;

//    public function __construct(Movie $movie)
//    {
//        $this->id = $movie->getId();
//        $this->title = $movie->getTitle();
//        $this->releaseYear = $movie->getReleaseYear();
//        $this->description = $movie->getDescription();
//        $this->imagePath = $movie->getImagePath();
//        $this->createdAt = $movie->getCreatedAt();
//        $this->updatedAt = $movie->getUpdatedAt();
//    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }
}
