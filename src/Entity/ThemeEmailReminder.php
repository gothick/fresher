<?php

namespace App\Entity;

use App\Repository\ThemeEmailReminderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThemeEmailReminderRepository::class)
 */
class ThemeEmailReminder extends ThemeReminder
{
    public function getDescription(): string
    {
        return "Email Reminder";
    }
}
