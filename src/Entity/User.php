<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *  max = 255,
     *  maxMessage = "Display name cannot be longer than {{ limit }} characters"
     * )
     */
    private $displayName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Theme::class, mappedBy="owner", orphanRemoval=true)
     */
    private $themes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Timezone
     * @Assert\NotBlank
     */
    private $timezone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $verificationCode;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $verificationCodeTries;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $verificationCodeExpiresAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $phoneNumberVerified;

    /**
     * @ORM\OneToMany(targetEntity=Helper::class, mappedBy="owner", orphanRemoval=true)
     */
    private $helpers;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
        $this->helpers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|Theme[]
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): self
    {
        if (!$this->themes->contains($theme)) {
            $this->themes[] = $theme;
            $theme->setOwner($this);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): self
    {
        if ($this->themes->removeElement($theme)) {
            // set the owning side to null (unless already changed)
            if ($theme->getOwner() === $this) {
                $theme->setOwner(null);
            }
        }

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getAnonymisedPhoneNumber(): ?string
    {
        if (
            $this->phoneNumber === null ||
            empty($this->phoneNumber) ||
            strlen($this->phoneNumber) <= 5
        ) {
            return "*****";
        }
        return substr($this->phoneNumber, 0, 3) . '***' . substr($this->phoneNumber, -3);
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        if ($phoneNumber !== null) {
            $phoneNumber = preg_replace('/\s+/', '', $phoneNumber);
        }
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $verificationCode): self
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function getVerificationCodeTries(): ?int
    {
        return $this->verificationCodeTries;
    }

    public function setVerificationCodeTries(?int $verificationCodeTries): self
    {
        $this->verificationCodeTries = $verificationCodeTries;

        return $this;
    }

    public function getVerificationCodeExpiresAt(): ?\DateTimeImmutable
    {
        return $this->verificationCodeExpiresAt;
    }

    public function setVerificationCodeExpiresAt(?\DateTimeImmutable $verificationCodeExpiresAt): self
    {
        $this->verificationCodeExpiresAt = $verificationCodeExpiresAt;

        return $this;
    }

    public function hasUnexpiredVerificationCode(): bool
    {
        return $this->verificationCode !== null &&
            $this->verificationCodeExpiresAt !== null &&
            $this->verificationCodeExpiresAt > Carbon::now();
    }

    public function getPhoneNumberVerified(): ?bool
    {
        return $this->phoneNumberVerified;
    }

    public function setPhoneNumberVerified(?bool $phoneNumberVerified): self
    {
        $this->phoneNumberVerified = $phoneNumberVerified;

        return $this;
    }

    /**
     * @return Collection|Helper[]
     */
    public function getHelpers(): Collection
    {
        return $this->helpers;
    }

    public function addHelper(Helper $helper): self
    {
        if (!$this->helpers->contains($helper)) {
            $this->helpers[] = $helper;
            $helper->setOwner($this);
        }

        return $this;
    }

    public function removeHelper(Helper $helper): self
    {
        if ($this->helpers->removeElement($helper)) {
            // set the owning side to null (unless already changed)
            if ($helper->getOwner() === $this) {
                $helper->setOwner(null);
            }
        }

        return $this;
    }
}
