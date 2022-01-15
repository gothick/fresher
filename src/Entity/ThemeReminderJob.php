<?php

namespace App\Entity;

use App\Repository\ThemeReminderJobRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThemeReminderJobRepository::class)
 *
 */
class ThemeReminderJob
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ThemeReminder::class, inversedBy="reminderJobs")
     * @var ThemeReminder|null
     */
    private $themeReminder;

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

    public function getThemeReminder(): ?ThemeReminder
    {
        return $this->themeReminder;
    }

    public function setThemeReminder(?ThemeReminder $themeReminder): void
    {
        $this->themeReminder = $themeReminder;
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
