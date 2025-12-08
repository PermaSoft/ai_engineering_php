<?php

declare(strict_types=1);

namespace App\Tests\Example;

use App\Entity\User;
use App\Tests\Utils\TestUtilities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Example test demonstrating test infrastructure usage.
 */
final class InfrastructureTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
    }

    public function testCanCreateTestUser(): void
    {
        $user = TestUtilities::createUser(
            $this->entityManager,
            $this->passwordHasher,
            ['username' => 'testuser']
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('testuser', $user->getUsername());
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, 'password'));
    }

    public function testCanCreateTestAdmin(): void
    {
        $admin = TestUtilities::createAdmin(
            $this->entityManager,
            $this->passwordHasher
        );

        $this->assertContains('ROLE_ADMIN', $admin->getRoles());
    }

    public function testDatabaseTransactionRollback(): void
    {
        // Create user
        $user = TestUtilities::createUser(
            $this->entityManager,
            $this->passwordHasher,
            ['username' => 'rollback_test']
        );

        $userId = $user->getId();
        $this->assertNotNull($userId);

        // User exists in current transaction
        $found = $this->entityManager->find(User::class, $userId);
        $this->assertNotNull($found);

        // After test, transaction will rollback automatically (DAMA)
        // Next test will not see this user
    }
}
