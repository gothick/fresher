<?php

namespace App\Entity;

use App\Repository\GoalReminderJobRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\GoalReminder;

/**
 * @ORM\Entity(repositoryClass=GoalReminderJobRepository::class)
 */
class GoalReminderJob extends ReminderJob
{
    /**
     * @ORM\ManyToOne(targetEntity=GoalReminder::class, inversedBy="reminderJobs")
     * @var GoalReminder|null
     */
    private $goalReminder;

    public function getGoalReminder(): ?GoalReminder
    {
        return $this->goalReminder;
    }
    public function setGoalReminder(?GoalReminder $goalReminder): void
    {
        $this->goalReminder = $goalReminder;
    }
}
