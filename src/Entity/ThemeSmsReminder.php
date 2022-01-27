<?php

namespace App\Entity;

use App\Repository\ThemeSmsReminderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThemeSmsReminderRepository::class)
 */
class ThemeSmsReminder extends ThemeReminder
{
    public function getDescription(): string
    {
        return "SMS Reminder";
    }
}
