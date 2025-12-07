<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to list all users.
 *
 * Usage:
 *   php bin/console app:list-users
 */
#[AsCommand(
    name: 'app:list-users',
    description: 'Lists all users in the database',
)]
final class ListUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();

        if (count($users) === 0) {
            $io->warning('No users found in the database.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user->getId(),
                $user->getUsername(),
                $user->getEmail(),
                $user->getFullName(),
                implode(', ', $user->getRoles()),
            ];
        }

        $io->title('User List');
        $io->table(
            ['ID', 'Username', 'Email', 'Full Name', 'Roles'],
            $rows
        );

        $io->success(sprintf('Total users: %d', count($users)));

        return Command::SUCCESS;
    }
}
