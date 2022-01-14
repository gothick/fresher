<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ReminderExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('friendly_day_schedule', [ReminderRuntime::class, 'friendlyDaySchedule'])
        ];
    }
}
