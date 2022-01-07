<?php

namespace App\Entity;

use Carbon\CarbonImmutable;

trait StartEndDateProgressTrait
{
    /**
     * Returns progress of task based on start and end dates as a float
     * between 0 and 1, or null if there isn't enough information to work
     * it out.
     *
     * @return float|null
     *
     */
    public function getProgress()
    {
        if ($this->getStartDate() === null || $this->getEndDate() === null) {
            return null;
        }
        $start = new CarbonImmutable($this->getStartDate());
        $end = new CarbonImmutable($this->getEndDate());
        $now = CarbonImmutable::now();

        if ($now <= $start) {
            return 0.0;
        }
        if ($now >= $end) {
            return 1.0;
        }
        $totalHours = $start->diffInHours($end);
        $soFar = $start->diffInHours($now);
        return $soFar / $totalHours;
    }
}
