<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Tests\AbstractCommandTestCase;
use App\Tests\Utils\TestUtilities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for ListUsersCommand.
 *
 * Coverage:
 * - User list display with table format
 * - User list with regular users and admins
 * - Empty user list
 * - Output formatting verification
 */
final class ListUsersCommandTest extends AbstractCommandTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
    }

    public function testExecuteDisplaysUserList(): void
    {
        // Create test users
        TestUtilities::createUser($this->entityManager, $this->passwordHasher, [
            'username' => 'user1',
            'email' => 'user1@example.com',
            'fullName' => 'User One',
        ]);

        TestUtilities::createAdmin($this->entityManager, $this->passwordHasher, [
            'username' => 'admin1',
            'email' => 'admin1@example.com',
            'fullName' => 'Admin One',
        ]);

        $tester = $this->executeCommandByName('app:list-users');

        $this->assertCommandIsSuccessful($tester);
        $output = $tester->getDisplay();

        // Check table headers
        $this->assertStringContainsString('Username', $output);
        $this->assertStringContainsString('Email', $output);
        $this->assertStringContainsString('Full Name', $output);
        $this->assertStringContainsString('Roles', $output);

        // Check user data
        $this->assertStringContainsString('user1', $output);
        $this->assertStringContainsString('user1@example.com', $output);
        $this->assertStringContainsString('User One', $output);

        $this->assertStringContainsString('admin1', $output);
        $this->assertStringContainsString('ROLE_ADMIN', $output);
    }

    public function testExecuteShowsTotalUserCount(): void
    {
        TestUtilities::createUser($this->entityManager, $this->passwordHasher);
        TestUtilities::createUser($this->entityManager, $this->passwordHasher);
        TestUtilities::createAdmin($this->entityManager, $this->passwordHasher);

        $tester = $this->executeCommandByName('app:list-users');

        $this->assertCommandIsSuccessful($tester);

        // Check for total count message
        $this->assertOutputContains($tester, 'Total users:');
        $this->assertOutputContains($tester, '3');
    }

    public function testExecuteShowsUserListTitle(): void
    {
        TestUtilities::createUser($this->entityManager, $this->passwordHasher);

        $tester = $this->executeCommandByName('app:list-users');

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, 'User List');
    }

    public function testExecuteShowsAllUserRoles(): void
    {
        // Create users with different roles
        TestUtilities::createUser($this->entityManager, $this->passwordHasher, [
            'username' => 'regular_user',
            'roles' => ['ROLE_USER'],
        ]);

        TestUtilities::createUser($this->entityManager, $this->passwordHasher, [
            'username' => 'admin_user',
            'roles' => ['ROLE_ADMIN'],
        ]);

        $tester = $this->executeCommandByName('app:list-users');

        $this->assertCommandIsSuccessful($tester);
        $output = $tester->getDisplay();

        // Verify roles are displayed
        $this->assertStringContainsString('ROLE_USER', $output);
        $this->assertStringContainsString('ROLE_ADMIN', $output);
    }

    public function testExecuteShowsUserDetails(): void
    {
        TestUtilities::createUser($this->entityManager, $this->passwordHasher, [
            'username' => 'detailed_user',
            'email' => 'detailed@example.com',
            'fullName' => 'Detailed Test User',
        ]);

        $tester = $this->executeCommandByName('app:list-users');

        $this->assertCommandIsSuccessful($tester);
        $output = $tester->getDisplay();

        // Verify all user details are shown
        $this->assertStringContainsString('detailed_user', $output);
        $this->assertStringContainsString('detailed@example.com', $output);
        $this->assertStringContainsString('Detailed Test User', $output);
    }
}
