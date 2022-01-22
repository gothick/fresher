<?php

namespace App\Service;

use App\Repository\SettingsRepository;
use App\Entity\Settings;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class SettingsService
{
    /** @var Settings */
    private $settings;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        SettingsRepository $settingsRepository,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        // This is set up by a single user during the setup flow, so there's
        // no actual chance of a race condition.
        $settings = $settingsRepository->getTheSingleRow();
        if ($settings === null) {
            $settings = new Settings();
            $entityManager->persist($settings);
            $entityManager->flush();
        }
        $this->settings = $settings;
        $this->userRepository = $userRepository;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    // Helpers for Twig, etc.
    /**
     * @return int|null
     */
    public function getMaxUsers()
    {
        return $this->settings->getMaxUsers();
    }

    public function areNewUsersAllowed(): bool
    {
        $maxUsers = $this->settings->getMaxUsers();
        if ($maxUsers !== null) {
            if ($this->userRepository->getUserCount() >= $maxUsers) {
                return false;
            }
        }
        return true;
    }
}
