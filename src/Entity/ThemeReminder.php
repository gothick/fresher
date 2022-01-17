<?php

namespace App\Entity;

use App\Repository\ThemeReminderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ThemeReminderRepository::class)
 */
class ThemeReminder
{
    public function __construct()
    {
        $this->enabled = true;
        $this->reminderType = 'email';
        $this->reminderJobs = new ArrayCollection();
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="reminders")
     * @var Theme|null
     */
    private $theme;

    /**
     * @ORM\OneToMany(targetEntity=ThemeReminderJob::class, mappedBy="themeReminder", orphanRemoval=true)
     * @var Collection|ThemeReminderJob[]
     */
    private $reminderJobs;

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

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices = {"email", "notification"},
     *     message = "Choose a valid day schedule."
     * )
     */
    private $reminderType;


    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return Collection|ThemeReminderJob[]
     */
    public function getReminderJobs(): Collection
    {
        return $this->reminderJobs;
    }

    public function addReminderJob(ThemeReminderJob $reminderJob): self
    {
        if (!$this->reminderJobs->contains($reminderJob)) {
            $this->reminderJobs[] = $reminderJob;
            $reminderJob->setThemeReminder($this);
        }

        return $this;
    }

    public function removeReminderJob(ThemeReminderJob $reminderJob): self
    {
        if ($this->reminderJobs->removeElement($reminderJob)) {
            // set the owning side to null (unless already changed)
            if ($reminderJob->getThemeReminder() === $this) {
                $reminderJob->setThemeReminder(null);
            }
        }
        return $this;
    }

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

    public function getReminderType(): ?string
    {
        return $this->reminderType;
    }

    public function setReminderType(?string $reminderType): self
    {
        $this->reminderType = $reminderType;

        return $this;
    }
}
