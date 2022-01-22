<?php

namespace App\Entity;

use App\Repository\ThemeReminderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class ThemeSmsReminder extends ThemeReminder
{
    /**
     * @param array<string> $availableMethods
     * @return bool
     */
    public static function isAvailableFor(array $availableMethods)
    {
        if (in_array('sms', $availableMethods)) {
            return true;
        }
        return false;
    }
    public function getReminderType(): string
    {
        return 'SMS Reminder';
    }
}
