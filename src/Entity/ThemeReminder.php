<?php

namespace App\Entity;

use App\Repository\ThemeReminderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThemeReminderRepository::class)
 */
class ThemeReminder extends Reminder
{
    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="reminders")
     */
    private $theme;

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }
}
