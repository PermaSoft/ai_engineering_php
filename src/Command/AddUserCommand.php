<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Console command to create new users.
 *
 * Usage:
 *   php bin/console app:add-user username password email@example.com
 *   php bin/console app:add-user admin_user password admin@example.com --admin
 */
#[AsCommand(
    name: 'app:add-user',
    description: 'Creates a new user in the database',
)]
final class AddUserCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'The plain password')
            ->addArgument('email', InputArgument::REQUIRED, 'The email address')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant ROLE_ADMIN to the user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $email = $input->getArgument('email');
        $isAdmin = $input->getOption('admin');

        // Check if user already exists
        $existingUser = $this->userRepository->findOneBy(['username' => $username]);
        if ($existingUser !== null) {
            $io->error(sprintf('User "%s" already exists.', $username));
            return Command::FAILURE;
        }

        // Create new user
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFullName($username); // Default to username

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Set role
        if ($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        // Validate
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $io->error('Validation errors:');
            foreach ($errors as $error) {
                $io->writeln('  - ' . $error->getMessage());
            }
            return Command::FAILURE;
        }

        // Persist
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'User "%s" was successfully created with %s role.',
            $username,
            $isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'
        ));

        return Command::SUCCESS;
    }
}
