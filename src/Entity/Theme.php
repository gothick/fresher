<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\ThemeReminder;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=ThemeRepository::class)
 */
class Theme
{
    use ValidateStartEndDatesTrait;
    use StartEndDateProgressTrait;
    use IsCurrentTrait;

    public function __construct()
    {
        $this->createdOn = new DateTime();
        $this->goals = new ArrayCollection();
        $this->reminders = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="themes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\OneToMany(targetEntity=Goal::class, mappedBy="theme", orphanRemoval=true)
     * @ORM\OrderBy({"startDate" = "ASC"})
     *
     */
    private $goals;

    /**
     * @ORM\OneToMany(targetEntity=ThemeReminder::class, mappedBy="theme", orphanRemoval=true)
     */
    private $reminders;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeInterface $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return Collection|Goal[]
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    /**
     * @return Collection<int, Goal>
     */
    public function getCurrentGoals(): Collection
    {
        return $this->goals->filter(fn ($g) => $g->isCurrent());
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals[] = $goal;
            $goal->setTheme($this);
        }

        return $this;
    }

    /**
     * @return Action|null
     */
    public function getRandomGoalAction(): ?Action
    {
        $actions = [];
        foreach ($this->getCurrentGoals() as $goal) {
            $actions = array_merge($actions, $goal->getActions()->toArray());
        }
        if (count($actions) == 0) {
            return null;
        }
        return $actions[array_rand($actions)];
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getTheme() === $this) {
                $goal->setTheme(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ThemeReminder[]
     */
    public function getReminders(): Collection
    {
        return $this->reminders;
    }

    public function addReminder(ThemeReminder $reminder): self
    {
        if (!$this->reminders->contains($reminder)) {
            $this->reminders[] = $reminder;
            $reminder->setTheme($this);
        }

        return $this;
    }

    public function removeReminder(ThemeReminder $reminder): self
    {
        if ($this->reminders->removeElement($reminder)) {
            // set the owning side to null (unless already changed)
            if ($reminder->getTheme() === $this) {
                $reminder->setTheme(null);
            }
        }

        return $this;
    }
}
