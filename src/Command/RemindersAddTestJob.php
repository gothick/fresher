<?php

namespace App\Command;

use App\Entity\Theme;
use App\Entity\ThemeReminder;
use App\Entity\ThemeReminderJob;
use App\Repository\UserRepository;
use App\Service\ReminderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\User;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class RemindersAddTestJob extends Command
{
    protected static $defaultName = 'app:reminders:addtestjob';

    /** @var UserRepository */
    private $userRepository;

    /** @var ReminderService */
    private $reminderService;

    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        UserRepository $userRepository,
        ReminderService $reminderService,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->reminderService = $reminderService;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userRepository->findOneBy(['email' => 'gothick@gothick.org.uk']);
        if ($user === null) {
            throw new Exception("Couldn't find myself!");
        }
        $this->logger->info('Got user: ' . $user->getEmail());
        $theme = $user->getThemes()->first();
        if ($theme !== false) {
            $reminder = $theme->getReminders()->first();
            if ($reminder !== false) {
                $reminderJob = new ThemeReminderJob();
                $scheduleTime = CarbonImmutable::now()->addDays(-1);
                $reminderJob->setScheduledAt($scheduleTime);
                $reminder->addReminderJob($reminderJob);
                $this->entityManager->persist($reminderJob);
                $this->entityManager->flush();
                $output->writeln("Scheduled test job for user {$user->getEmail()}, theme {$theme->getName()} for {$scheduleTime->toCookieString()}");
                return Command::SUCCESS;
            }
        }
        $output->writeln('Failed to find either Matt, a theme or a reminder.');
        return Command::FAILURE;
    }
}
