<?php

namespace App\Entity;

use App\Repository\GoalReminderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GoalReminderRepository::class)
 */
class GoalReminder extends Reminder
{
    /**
     * @ORM\ManyToOne(targetEntity=Goal::class, inversedBy="reminders")
     */
    private $goal;

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): self
    {
        $this->goal = $goal;

        return $this;
    }
}
