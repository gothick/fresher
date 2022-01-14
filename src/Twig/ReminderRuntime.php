<?php

namespace App\Twig;

use App\Service\ReminderService;
use Twig\Extension\RuntimeExtensionInterface;

class ReminderRuntime implements RuntimeExtensionInterface
{
    /** @var ReminderService */
    private $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }
    public function friendlyDaySchedule(string $schedule): string
    {
        return $this->reminderService->getFriendlyDaySchedule($schedule);
    }
}
