<?php

namespace App\Entity;

use App\Repository\ReminderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ReminderRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
abstract class Reminder
{
    public function __construct()
    {
        $this->enabled = true;
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="time")
     */
    private $timeOfDay;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices = {"weekdays", "everyday", "weekends"},
     *     message = "Choose a valid day schedule."
     * )
     */
    private $daySchedule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getTimeOfDay(): ?\DateTimeInterface
    {
        return $this->timeOfDay;
    }

    public function setTimeOfDay(\DateTimeInterface $timeOfDay): self
    {
        $this->timeOfDay = $timeOfDay;

        return $this;
    }

    public function getDaySchedule(): ?string
    {
        return $this->daySchedule;
    }

    public function setDaySchedule(string $daySchedule): self
    {
        $this->daySchedule = $daySchedule;

        return $this;
    }
}
