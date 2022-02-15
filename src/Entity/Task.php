<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Nauni\Bundle\NauniTestSuiteBundle\Attribute\Suite;

#[Suite(['entity', 'task'])]

#[Entity(readOnly: false)]
class Task
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $title;

    #[Column(type: 'text', nullable: true)]
    private ?string $description;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deadline;

    #[Column(type: 'boolean', options: ["default" => false])]
    private bool $completed = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDeadline(): ?DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(?DateTimeImmutable $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'deadline' => $this->getDeadline()?->format('Y-m-d H:i'),
            'completed' => $this->getCompleted(),
        ];
    }
}
