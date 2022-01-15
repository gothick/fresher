<?php

namespace App\Entity;

use App\Repository\MotivationalQuoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=MotivationalQuoteRepository::class)
 */
class MotivationalQuote
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $quote;

    /**
     * @ORM\Column(type="text")
     */
    private $attribution;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function getAttribution(): ?string
    {
        return $this->attribution;
    }

    public function setAttribution(string $attribution): self
    {
        $this->attribution = $attribution;

        return $this;
    }
}
