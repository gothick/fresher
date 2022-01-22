<?php

namespace App\Service;

use App\Entity\Theme;
use App\Entity\ThemeSmsReminder;
use App\Entity\ThemeEmailReminder;
use Exception;


class ThemeReminderSenderFactory {
    static function getReminderSender(Theme $theme, string $reminderType): ThemeReminderSenderService {
        // Keep it simple for now. We can clever it up later.
        if ($reminderType === ThemeEmailReminder::class) {
            return new ThemeReminderEmailSenderService($theme);
        } elseif ($reminderType === ThemeSmsReminder::class) {
            return new ThemeReminderSmsSenderService($theme);
        }
        throw new Exception('Unknown reminder type');
    }
}
