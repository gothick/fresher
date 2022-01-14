<?php

namespace App\Entity;

use App\Repository\ThemeReminderRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThemeReminderRepository::class)
 */
class ThemeReminder extends Reminder
{
    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="reminders")
     * @var Theme|null
     */
    private $theme;

    /**
     * @ORM\OneToMany(targetEntity=ThemeReminderJob::class, mappedBy="themeReminder", orphanRemoval=true)
     * @var Collection|ThemeReminderJob[]
     */
    private $reminderJobs;

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return Collection|ThemeReminderJob[]
     */
    public function getReminderJobs(): Collection
    {
        return $this->reminderJobs;
    }

    public function addReminderJob(ThemeReminderJob $reminderJob): self
    {
        if (!$this->reminderJobs->contains($reminderJob)) {
            $this->reminderJobs[] = $reminderJob;
            $reminderJob->setThemeReminder($this);
        }

        return $this;
    }

    public function removeReminderJob(ThemeReminderJob $reminderJob): self
    {
        if ($this->reminderJobs->removeElement($reminderJob)) {
            // set the owning side to null (unless already changed)
            if ($reminderJob->getThemeReminder() === $this) {
                $reminderJob->setThemeReminder(null);
            }
        }
        return $this;
    }
}
