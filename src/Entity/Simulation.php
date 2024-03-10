<?php

namespace App\Entity;

use App\Repository\SimulationRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SimulationRepository::class)]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['simulation:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['simulation:create', 'simulation:get'])]
    #[Assert\NotNull(
        message: 'Name should not be null',
        groups: ['simulation:create']
    )]
    #[Assert\NotBlank(
        message: 'Name should not be blank',
        groups: ['simulation:create']
    )]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Name should be at least {{ limit }} characters long',
        maxMessage: 'Name should be at most {{ limit }} characters long',
        groups: ['simulation:create']
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['simulation:create', 'simulation:get'])]
    #[Type('array')]
    private array $content = [];

    #[ORM\ManyToOne(inversedBy: 'simulations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['simulation:get'])]
    private ?\DateTimeImmutable $sharedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getSharedAt(): ?\DateTimeImmutable
    {
        return $this->sharedAt;
    }

    public function setSharedAt(?\DateTimeImmutable $sharedAt): static
    {
        $this->sharedAt = $sharedAt;

        return $this;
    }
}
