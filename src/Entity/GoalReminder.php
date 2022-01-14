<?php

namespace App\Entity;

use App\Repository\GoalReminderRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\GoalReminderJob;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=GoalReminderRepository::class)
 */
class GoalReminder extends Reminder
{
    /**
     * @ORM\ManyToOne(targetEntity=Goal::class, inversedBy="reminders")
     */
    private $goal;

    /**
     * @ORM\OneToMany(targetEntity=GoalReminderJob::class, mappedBy="goalReminder", orphanRemoval=true)
     * @var Collection|GoalReminderJob[]
     */
    private $reminderJobs;

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): self
    {
        $this->goal = $goal;

        return $this;
    }

    /**
     * @return Collection|GoalReminderJob[]
     */
    public function getReminderJobs(): Collection
    {
        return $this->reminderJobs;
    }

    public function addReminderJob(GoalReminderJob $reminderJob): self
    {
        if (!$this->reminderJobs->contains($reminderJob)) {
            $this->reminderJobs[] = $reminderJob;
            $reminderJob->setGoalReminder($this);
        }

        return $this;
    }

    public function removeReminderJob(GoalReminderJob $reminderJob): self
    {
        if ($this->reminderJobs->removeElement($reminderJob)) {
            // set the owning side to null (unless already changed)
            if ($reminderJob->getGoalReminder() === $this) {
                $reminderJob->setGoalReminder(null);
            }
        }
        return $this;
    }
}
