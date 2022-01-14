<?php

namespace App\Entity;

use App\Repository\ReminderJobRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReminderJobRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 */
abstract class ReminderJob
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $scheduledAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $wasRunAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeImmutable $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function hasBeenRun(): bool
    {
        return $this->getWasRunAt() !== null;
    }

    public function getWasRunAt(): ?\DateTimeImmutable
    {
        return $this->wasRunAt;
    }

    public function setWasRunAt(?\DateTimeImmutable $wasRunAt): self
    {
        $this->wasRunAt = $wasRunAt;

        return $this;
    }
}
