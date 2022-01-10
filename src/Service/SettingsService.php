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
        $settings = $settingsRepository->getTheSingleRow();
        if ($settings === null) {
            // Minuscule chance of a race condition, especially as settings
            // are set up during site installation, just after the only user
            // so far has been created. Even in that worst-case scenario
            // we'll always bring back the first row from the database when we
            // getTheSingleRow() so all that will happen is that an extra row
            // will languish in the database forever.
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
