<?php

namespace App\Entity;

use App\Repository\ThemeReminderJobRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\ThemeReminder;

/**
 * @ORM\Entity(repositoryClass=ThemeReminderJobRepository::class)
 */
class ThemeReminderJob extends ReminderJob
{
    /**
     * @ORM\ManyToOne(targetEntity=ThemeReminder::class, inversedBy="reminderJobs")
     * @var ThemeReminder|null
     */
    private $themeReminder;

    public function getThemeReminder(): ?ThemeReminder
    {
        return $this->themeReminder;
    }
    public function setThemeReminder(?ThemeReminder $themeReminder): void
    {
        $this->themeReminder = $themeReminder;
    }
}
