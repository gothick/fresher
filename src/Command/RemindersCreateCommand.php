<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\ReminderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemindersCreateCommand extends Command
{
    protected static $defaultName = 'app:reminders:create';

    /** @var UserRepository */
    private $userRepository;

    /** @var ReminderService */
    private $reminderService;

    public function __construct(
        UserRepository $userRepository,
        ReminderService $reminderService
    ) {
        $this->userRepository = $userRepository;
        $this->reminderService = $reminderService;

        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $this->reminderService->createThemeReminderJobsForUser($user);
        }
        return Command::SUCCESS;
    }
}
