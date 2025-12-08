# Plan 09: Command Line Interface Tests

**Priority:** 🟡 MEDIUM
**Estimated Time:** 4-5 hours
**Dependencies:** Plan 08 (Testing Infrastructure)
**Status:** Ready to execute

---

## Context & Objective

Add comprehensive test coverage for CLI commands (currently 100% missing):
1. Test AddUserCommand interactive and non-interactive modes
2. Test ListUsersCommand output formatting
3. Test DeleteUserCommand functionality
4. Verify email notification options
5. Test error handling and validation

This addresses the 36% test coverage gap identified in the comparison report.

---

## Reference Materials

### Memory Files
- `.claude/memory/testing-strategy.md` - Test patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 8.3: Missing Test Coverage)

### Walkthrought Files
```bash
git show walkthrought:tests/Command/AddUserCommandTest.php
git show walkthrought:tests/Command/ListUsersCommandTest.php
git show walkthrought:tests/Command/DeleteUserCommandTest.php
```

---

## Prerequisites

- Testing infrastructure (Plan 08) completed
- AbstractCommandTestCase available
- Commands implemented and working
- Test utilities available

---

## Deliverables Checklist

### Code Changes
- [ ] `tests/Command/AddUserCommandTest.php` - AddUserCommand tests
- [ ] `tests/Command/ListUsersCommandTest.php` - ListUsersCommand tests
- [ ] `tests/Command/DeleteUserCommandTest.php` - DeleteUserCommand tests (if exists)

### Testing
- [ ] All command tests pass
- [ ] Interactive mode tested
- [ ] Non-interactive mode tested
- [ ] Error cases covered
- [ ] Email options tested

### Documentation
- [ ] Update memory file with command testing patterns

---

## Implementation Steps

### Step 1: Test AddUserCommand

**File:** `tests/Command/AddUserCommandTest.php`

```php
<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AbstractCommandTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Tests for AddUserCommand.
 *
 * Coverage:
 * - Non-interactive mode (all arguments provided)
 * - Interactive mode (prompts for input)
 * - Email notification options
 * - Validation (duplicate username, invalid email)
 * - Admin role assignment
 */
final class AddUserCommandTest extends AbstractCommandTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testExecuteNonInteractive(): void
    {
        $tester = $this->executeCommandByName('app:add-user', [
            'username' => 'test_user',
            'password' => 'password123',
            'email' => 'test@example.com',
            'full-name' => 'Test User',
        ], [
            'interactive' => false,
        ]);

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, 'User successfully created');

        // Verify user was created
        $user = $this->userRepository->findOneBy(['username' => 'test_user']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('Test User', $user->getFullName());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testExecuteInteractive(): void
    {
        $tester = $this->executeCommandByName('app:add-user', [], [
            'interactive' => true,
        ]);

        // Provide input for interactive prompts
        $tester->setInputs([
            'interactive_user',      // username
            'password123',           // password
            'password123',           // password confirmation
            'interactive@example.com', // email
            'Interactive User',      // full name
        ]);

        $tester->execute([]);

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, 'User successfully created');

        $user = $this->userRepository->findOneBy(['username' => 'interactive_user']);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testExecuteWithAdminRole(): void
    {
        $tester = $this->executeCommandByName('app:add-user', [
            'username' => 'admin_user',
            'password' => 'password123',
            'email' => 'admin@example.com',
            'full-name' => 'Admin User',
            '--admin' => true,
        ], [
            'interactive' => false,
        ]);

        $this->assertCommandIsSuccessful($tester);

        $user = $this->userRepository->findOneBy(['username' => 'admin_user']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testExecuteWithDuplicateUsername(): void
    {
        // Create existing user
        $this->executeCommandByName('app:add-user', [
            'username' => 'duplicate',
            'password' => 'password123',
            'email' => 'first@example.com',
            'full-name' => 'First User',
        ], ['interactive' => false]);

        // Try to create user with same username
        $tester = $this->executeCommandByName('app:add-user', [
            'username' => 'duplicate',
            'password' => 'password123',
            'email' => 'second@example.com',
            'full-name' => 'Second User',
        ], ['interactive' => false]);

        $this->assertCommandFailed($tester);
        $this->assertOutputContains($tester, 'already exists');
    }

    public function testExecuteWithInvalidEmail(): void
    {
        $tester = $this->executeCommandByName('app:add-user', [
            'username' => 'invalid_email_user',
            'password' => 'password123',
            'email' => 'invalid-email',
            'full-name' => 'Invalid Email User',
        ], ['interactive' => false]);

        $this->assertCommandFailed($tester);
        $this->assertOutputContains($tester, 'valid email');
    }

    public function testExecuteWithShortPassword(): void
    {
        $tester = $this->executeCommandByName('app:add-user', [
            'username' => 'short_pass',
            'password' => '123',
            'email' => 'short@example.com',
            'full-name' => 'Short Pass',
        ], ['interactive' => false]);

        $this->assertCommandFailed($tester);
        $this->assertOutputContains($tester, 'password');
    }

    /**
     * @dataProvider emailNotificationProvider
     */
    public function testExecuteWithEmailNotification(bool $sendEmail, string $expectedOutput): void
    {
        $options = [
            'username' => 'email_test_' . ($sendEmail ? 'yes' : 'no'),
            'password' => 'password123',
            'email' => 'email_test@example.com',
            'full-name' => 'Email Test User',
        ];

        if ($sendEmail) {
            $options['--send-email'] = true;
        }

        $tester = $this->executeCommandByName('app:add-user', $options, [
            'interactive' => false,
        ]);

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, $expectedOutput);
    }

    /**
     * @return array<string, array<bool|string>>
     */
    public static function emailNotificationProvider(): array
    {
        return [
            'with email' => [true, 'email sent'],
            'without email' => [false, 'User successfully created'],
        ];
    }
}
```

---

### Step 2: Test ListUsersCommand

**File:** `tests/Command/ListUsersCommandTest.php`

```php
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
 * - Default output (table format)
 * - User list with admins
 * - Empty user list
 * - Output formatting
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
        $this->assertOutputContains($tester, '3 users');
    }

    public function testExecuteWithMaxResultsOption(): void
    {
        // Create 5 users
        for ($i = 1; $i <= 5; $i++) {
            TestUtilities::createUser($this->entityManager, $this->passwordHasher, [
                'username' => "user{$i}",
            ]);
        }

        $tester = $this->executeCommandByName('app:list-users', [
            '--max-results' => 3,
        ]);

        $this->assertCommandIsSuccessful($tester);
        $output = $tester->getDisplay();

        // Should show only 3 users
        $this->assertStringContainsString('user1', $output);
        $this->assertStringContainsString('user2', $output);
        $this->assertStringContainsString('user3', $output);
    }
}
```

---

### Step 3: Add Translation Keys for Commands

**File:** `translations/messages.en.yaml`

Add command-related messages:

```yaml
# Command messages
command:
    add_user:
        success: 'User successfully created: %username%'
        email_sent: 'Welcome email sent to %email%'
        username_exists: 'Username "%username%" already exists'
        invalid_email: 'The email "%email%" is not valid'
        password_too_short: 'Password must be at least %min% characters'

    list_users:
        title: 'Application Users'
        total: '%count% users registered'
        empty: 'No users found'

    delete_user:
        success: 'User "%username%" successfully deleted'
        not_found: 'User "%username%" not found'
        confirmation: 'Are you sure you want to delete user "%username%"?'
```

---

## Verification Criteria

### Run Command Tests

```bash
# Run all command tests
php bin/phpunit tests/Command/

# Should see:
# - AddUserCommandTest: 7+ tests passing
# - ListUsersCommandTest: 3+ tests passing
# - All assertions passing
```

### Test Coverage

```bash
# Check coverage for commands
php bin/phpunit --coverage-text tests/Command/

# Should show:
# - Command classes covered
# - High coverage percentage (>80%)
```

### Manual Verification

```bash
# Test commands manually to confirm behavior
php bin/console app:add-user test_manual password test@test.com "Test Manual"
php bin/console app:list-users
php bin/console app:list-users --max-results=5
```

---

## Memory File Updates

**File:** `.claude/memory/testing-strategy.md`

Add this section:

```markdown
## Command Testing Patterns

### Using AbstractCommandTestCase

Command tests extend `AbstractCommandTestCase`:

```php
final class MyCommandTest extends AbstractCommandTestCase
{
    public function testCommand(): void
    {
        $tester = $this->executeCommandByName('app:my-command', [
            'argument' => 'value',
            '--option' => 'value',
        ]);

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, 'Expected');
    }
}
```

### Testing Interactive Commands

Use `setInputs()` to simulate user input:

```php
$tester = $this->executeCommandByName('app:command', [], [
    'interactive' => true,
]);

$tester->setInputs([
    'First input',
    'Second input',
    'yes', // Confirmation
]);

$tester->execute([]);
```

### Testing Non-Interactive Commands

Pass all arguments:

```php
$tester = $this->executeCommandByName('app:command', [
    'username' => 'test',
    '--admin' => true,
], [
    'interactive' => false,
]);
```

### Data Providers for Multiple Scenarios

```php
/**
 * @dataProvider emailProvider
 */
public function testEmail(bool $send, string $expected): void
{
    // Test implementation
}

public static function emailProvider(): array
{
    return [
        'with email' => [true, 'email sent'],
        'without email' => [false, 'no email'],
    ];
}
```

### Command Test Coverage

Tests should cover:
- ✓ Successful execution
- ✓ Interactive mode
- ✓ Non-interactive mode
- ✓ All options and flags
- ✓ Error cases
- ✓ Validation failures
- ✓ Edge cases
- ✓ Output formatting
```

---

## Context Reset Information

If resuming after context reset:

**Files Created:**
1. `tests/Command/AddUserCommandTest.php` - 7+ test methods
2. `tests/Command/ListUsersCommandTest.php` - 3+ test methods

**Files Modified:**
1. `translations/messages.en.yaml` - Added command messages

**Verification:**
- Run `php bin/phpunit tests/Command/` - all pass
- Check coverage report
- Test interactive and non-interactive modes
- Memory file updated

**Next Plan:** `10-form-utils-tests.md`

---

## Completion Checklist

- [ ] AddUserCommandTest created with 7+ tests
- [ ] ListUsersCommandTest created with 3+ tests
- [ ] Translation keys added
- [ ] All tests pass
- [ ] Interactive mode tested
- [ ] Non-interactive mode tested
- [ ] Error cases covered
- [ ] Coverage report shows improvement
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  test: add comprehensive CLI command tests

  - Add AddUserCommandTest (interactive, non-interactive, validation)
  - Add ListUsersCommandTest (output formatting, filtering)
  - Test email notification options
  - Test admin role assignment
  - Test error handling and validation
  - Add command translation keys

  Addresses 100% missing command test coverage.
  Uses AbstractCommandTestCase for clean test code.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 10