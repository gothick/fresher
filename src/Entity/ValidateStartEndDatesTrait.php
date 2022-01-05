<?php

namespace App\Entity;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait ValidateStartEndDatesTrait
{
    /**
     * Validates that the Theme end date, if entered, isn't before the start date.
     *
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     * @param mixed|null $payload This option can be used to attach
     *                            arbitrary domain-specific data to
     *                            a constraint.
     * @return void
     *
     */
    public function validateStartEndDates(ExecutionContextInterface $context, $payload): void
    {
        if (
            $this->startDate !== null &&
            $this->endDate !== null &&
            $this->startDate > $this->endDate
        ) {
            $context
                ->buildViolation('End date should not be before Start date')
                ->atPath('endDate')
                ->addViolation();
        }
    }
}
