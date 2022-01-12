<?php

namespace App\Entity;

use Carbon\CarbonImmutable;

trait IsCurrentTrait
{
    /**
     * @return bool|null
     */
    public function isCurrent()
    {
        if ($this->getStartDate() === null || $this->getEndDate() === null) {
            return null;
        }
        $now = CarbonImmutable::now();
        if ($now >= $this->getStartDate() &&
            $now <= $this->getEndDate()) {
            return true;
        }
        return false;
    }
    /**
     * @return bool|null
     */
    public function isInPast()
    {
        if ($this->getStartDate() === null || $this->getEndDate() === null) {
            return null;
        }
        $now = CarbonImmutable::now();
        if ($now > $this->getEndDate()) {
            return true;
        }
        return false;
    }
    /**
     * @return bool|null
     */
    public function isInFuture()
    {
        if ($this->getStartDate() === null || $this->getEndDate() === null) {
            return null;
        }
        $now = CarbonImmutable::now();
        if ($now < $this->getStartDate()) {
            return true;
        }
        return false;
    }

}
