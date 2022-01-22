<?php

namespace App\Service;

use App\Entity\Theme;

abstract class ThemeReminderSenderService {
    /** @var Theme */
    protected $theme;
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
    }

    abstract public function sendReminder(): void;
}
