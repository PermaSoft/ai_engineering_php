# Plan 08: Testing Infrastructure

**Priority:** 🟡 MEDIUM
**Estimated Time:** 3-4 hours
**Dependencies:** Plan 07 (Form Enhancements)
**Status:** Ready to execute

---

## Context & Objective

Enhance the testing foundation to support missing test coverage (36% reduction from walkthrought):
1. Update PHPUnit configuration for better coverage reporting
2. Create AbstractCommandTestCase base class for CLI tests
3. Add test utilities and helper methods
4. Create test data factories/fixtures
5. Configure test environment properly

This provides infrastructure for Plans 09-10 which will add the missing tests.

---

## Reference Materials

### Memory Files
- `.claude/memory/testing-strategy.md` - Test patterns and organization
- `BRANCH_COMPARISON_REPORT.md` (Section 8: Testing Strategy)

### Walkthrought Files
```bash
git show walkthrought:phpunit.xml.dist
git show walkthrought:tests/Command/AbstractCommandTestCase.php
git show walkthrought:tests/Utils/TestHelpers.php
```

---

## Prerequisites

- PHPUnit installed
- DAMA Doctrine Test Bundle configured
- Understanding of Symfony testing patterns

---

## Deliverables Checklist

### Code Changes
- [ ] `phpunit.xml.dist` - Enhanced configuration
- [ ] `tests/AbstractCommandTestCase.php` - Base class for command tests
- [ ] `tests/Utils/TestUtilities.php` - Helper methods for tests
- [ ] `.env.test` - Test environment configuration
- [ ] Update existing tests to use new utilities

### Testing
- [ ] Run full test suite
- [ ] Verify test environment works
- [ ] Check coverage reporting
- [ ] Verify database transaction rollback

### Documentation
- [ ] Update memory file with testing patterns

---

## Implementation Steps

### Step 1: Enhance PHPUnit Configuration

**File:** `phpunit.xml.dist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.3/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         executionOrder="depends,defects"
         requireCoverageMetadata="false"
         beStrictAboutCoverageMetadata="true"
         beStrictAboutOutputDuringTests="true"
         failOnRisky="true"
         failOnWarning="true">

    <!-- Test Suites -->
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <!-- Source Files for Coverage -->
    <source restrictDeprecations="true" restrictNotices="true" restrictWarnings="true">
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/DataFixtures</directory>
            <file>src/Kernel.php</file>
        </exclude>
    </source>

    <!-- PHP Configuration -->
    <php>
        <ini name="display_errors" value="1"/>
        <ini name="error_reporting" value="-1"/>
        <server name="APP_ENV" value="test" force="true"/>
        <server name="SHELL_VERBOSITY" value="-1"/>
        <server name="SYMFONY_PHPUNIT_REMOVE" value=""/>
        <server name="SYMFONY_PHPUNIT_VERSION" value="11.3"/>
        <server name="KERNEL_CLASS" value="App\Kernel"/>

        <!-- Disable deprecation notices during tests -->
        <env name="SYMFONY_DEPRECATIONS_HELPER" value="disabled"/>

        <!-- Database for testing -->
        <env name="DATABASE_URL" value="sqlite:///%kernel.project_dir%/var/data_test.db"/>
    </php>

    <!-- Extensions -->
    <extensions>
        <!-- DAMA Doctrine Test Bundle: Automatic transaction rollback -->
        <bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
    </extensions>

    <!-- Coverage Report Configuration -->
    <coverage>
        <report>
            <html outputDirectory="var/coverage/html"/>
            <text outputFile="php://stdout" showUncoveredFiles="false"/>
        </report>
    </coverage>

    <!-- Logging -->
    <logging>
        <testdoxText outputFile="var/coverage/testdox.txt"/>
    </logging>
</phpunit>
```

**Key Features:**
- Strict error reporting for better quality
- DAMA extension for automatic transaction rollback
- Coverage reporting to `var/coverage/`
- Separate test database
- Test execution order optimization

---

### Step 2: Create Abstract Command Test Case

**File:** `tests/AbstractCommandTestCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Base class for testing Symfony Console commands.
 *
 * Provides helper methods for:
 * - Executing commands
 * - Checking output
 * - Testing interactive and non-interactive modes
 */
abstract class AbstractCommandTestCase extends KernelTestCase
{
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    /**
     * Executes a command and returns its tester.
     *
     * @param array<string, mixed> $input Command input arguments and options
     * @param array<string, mixed> $options CommandTester options (interactive, decorated, etc.)
     */
    protected function executeCommand(
        Command $command,
        array $input = [],
        array $options = []
    ): CommandTester {
        $this->application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    /**
     * Executes a command by name and returns its tester.
     *
     * @param array<string, mixed> $input Command input arguments and options
     * @param array<string, mixed> $options CommandTester options
     */
    protected function executeCommandByName(
        string $commandName,
        array $input = [],
        array $options = []
    ): CommandTester {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    /**
     * Asserts that command output contains a specific string.
     */
    protected function assertOutputContains(CommandTester $tester, string $expected): void
    {
        $output = $tester->getDisplay();
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * Asserts that command output does not contain a specific string.
     */
    protected function assertOutputNotContains(CommandTester $tester, string $expected): void
    {
        $output = $tester->getDisplay();
        $this->assertStringNotContainsString($expected, $output);
    }

    /**
     * Asserts that command exited successfully.
     */
    protected function assertCommandIsSuccessful(CommandTester $tester): void
    {
        $this->assertSame(
            Command::SUCCESS,
            $tester->getStatusCode(),
            sprintf('Command failed with output: %s', $tester->getDisplay())
        );
    }

    /**
     * Asserts that command failed.
     */
    protected function assertCommandFailed(CommandTester $tester): void
    {
        $this->assertNotSame(
            Command::SUCCESS,
            $tester->getStatusCode(),
            'Command should have failed but succeeded'
        );
    }
}
```

**Benefits:**
- Reduces boilerplate in command tests
- Provides clear, reusable assertions
- Handles command setup automatically
- Makes tests more readable

---

### Step 3: Create Test Utilities

**File:** `tests/Utils/TestUtilities.php`

```php
<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Test utilities for creating test data.
 *
 * Provides factory methods for creating entities with sensible defaults.
 */
final class TestUtilities
{
    /**
     * Creates a test user with default values.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createUser(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        array $overrides = []
    ): User {
        $defaults = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'fullName' => 'Test User',
            'password' => 'password',
            'roles' => ['ROLE_USER'],
        ];

        $data = array_merge($defaults, $overrides);

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setFullName($data['fullName']);
        $user->setRoles($data['roles']);

        // Hash password
        $hashedPassword = $hasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Creates a test admin user.
     */
    public static function createAdmin(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        array $overrides = []
    ): User {
        return self::createUser($em, $hasher, array_merge([
            'username' => 'admin_' . uniqid(),
            'roles' => ['ROLE_ADMIN'],
        ], $overrides));
    }

    /**
     * Creates a test post.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createPost(
        EntityManagerInterface $em,
        User $author,
        array $overrides = []
    ): Post {
        $defaults = [
            'title' => 'Test Post ' . uniqid(),
            'slug' => 'test-post-' . uniqid(),
            'summary' => 'This is a test post summary.',
            'content' => 'This is the test post content with **markdown**.',
        ];

        $data = array_merge($defaults, $overrides);

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setSlug($data['slug']);
        $post->setSummary($data['summary']);
        $post->setContent($data['content']);
        $post->setAuthor($author);

        $em->persist($post);
        $em->flush();

        return $post;
    }

    /**
     * Creates a test tag.
     */
    public static function createTag(EntityManagerInterface $em, string $name): Tag
    {
        $tag = new Tag($name);
        $em->persist($tag);
        $em->flush();

        return $tag;
    }

    /**
     * Creates a test comment.
     *
     * @param array<string, mixed> $overrides Properties to override
     */
    public static function createComment(
        EntityManagerInterface $em,
        Post $post,
        User $author,
        array $overrides = []
    ): Comment {
        $defaults = [
            'content' => 'This is a test comment.',
        ];

        $data = array_merge($defaults, $overrides);

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setAuthor($author);
        $comment->setPost($post);

        $em->persist($comment);
        $em->flush();

        return $comment;
    }

    /**
     * Clears all test data from database.
     */
    public static function clearDatabase(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();

        // Disable foreign key checks
        $connection->executeStatement('PRAGMA foreign_keys = OFF');

        // Truncate all tables
        $connection->executeStatement('DELETE FROM comment');
        $connection->executeStatement('DELETE FROM post_tag');
        $connection->executeStatement('DELETE FROM post');
        $connection->executeStatement('DELETE FROM tag');
        $connection->executeStatement('DELETE FROM symfony_demo_user');

        // Re-enable foreign key checks
        $connection->executeStatement('PRAGMA foreign_keys = ON');

        $em->clear();
    }
}
```

**Benefits:**
- Consistent test data creation
- Reduces duplication across tests
- Provides sensible defaults
- Easy to override specific values
- Database cleanup utility

---

### Step 4: Update Test Environment Configuration

**File:** `.env.test`

```env
# Test environment configuration

# Use test kernel
APP_ENV=test
APP_DEBUG=true
APP_SECRET=test_secret_change_this

# Test database (SQLite in memory for speed)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_test.db"

# Disable mailer in tests (use null transport)
MAILER_DSN=null://null

# Disable external services in tests
# Add any other test-specific overrides here
```

**Key Points:**
- Separate test database
- Null mailer transport (no emails sent)
- Debug mode enabled for better error messages

---

### Step 5: Update bootstrap.php for Tests

**File:** `tests/bootstrap.php`

```php
<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Ensure test database exists
$testDbPath = dirname(__DIR__).'/var/data_test.db';
if (file_exists($testDbPath)) {
    @unlink($testDbPath);
}

// Run database migrations for test environment
passthru(sprintf(
    'php "%s/bin/console" doctrine:schema:create --env=test --no-interaction --quiet',
    dirname(__DIR__)
));

// Load fixtures for tests
passthru(sprintf(
    'php "%s/bin/console" doctrine:fixtures:load --env=test --no-interaction --quiet',
    dirname(__DIR__)
));
```

**Features:**
- Auto-creates test database
- Runs migrations
- Loads fixtures
- Fresh database for each test run

---

### Step 6: Create Example Test Using Infrastructure

**File:** `tests/Example/InfrastructureTest.php`

```php
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
```

---

## Verification Criteria

### Test PHPUnit Configuration

```bash
# Run tests
php bin/phpunit

# Should see:
# - All tests pass
# - Coverage report generated in var/coverage/
# - Test database created and used
# - Transaction rollback working
```

### Test AbstractCommandTestCase

```bash
# Once command tests are written (Plan 09), verify:
php bin/phpunit tests/Command/

# Should pass with clean output
```

### Test Utilities

```bash
# Run example infrastructure test
php bin/phpunit tests/Example/InfrastructureTest.php

# Should create users successfully
# Should demonstrate transaction rollback
```

### Coverage Report

```bash
# Generate HTML coverage report
php bin/phpunit --coverage-html var/coverage/html

# Open in browser
open var/coverage/html/index.html

# Should show:
# - Covered and uncovered lines
# - Coverage percentage per file
```

---

## Memory File Updates

**File:** `.claude/memory/testing-strategy.md`

Add this section:

```markdown
## Testing Infrastructure

### PHPUnit Configuration

Enhanced `phpunit.xml.dist` provides:
- DAMA Doctrine Test Bundle integration (automatic transaction rollback)
- Separate test database (SQLite)
- Coverage reporting to `var/coverage/`
- Strict error reporting
- Test execution order optimization

### Base Test Classes

#### AbstractCommandTestCase

Base class for command tests:

```php
class MyCommandTest extends AbstractCommandTestCase
{
    public function testCommand(): void
    {
        $tester = $this->executeCommandByName('app:my-command', [
            'argument' => 'value',
            '--option' => true,
        ]);

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, 'Expected output');
    }
}
```

Provides:
- `executeCommand()` - Execute command object
- `executeCommandByName()` - Execute by name
- `assertOutputContains()` - Check output
- `assertCommandIsSuccessful()` - Verify success

### Test Utilities

#### TestUtilities Factory Methods

```php
// Create test user
$user = TestUtilities::createUser($em, $hasher, [
    'username' => 'testuser',
    'email' => 'test@example.com',
]);

// Create admin
$admin = TestUtilities::createAdmin($em, $hasher);

// Create post
$post = TestUtilities::createPost($em, $author, [
    'title' => 'Test Post',
]);

// Create tag
$tag = TestUtilities::createTag($em, 'php');

// Create comment
$comment = TestUtilities::createComment($em, $post, $author);

// Clear database
TestUtilities::clearDatabase($em);
```

### Test Database

- SQLite database: `var/data_test.db`
- Recreated on each test run
- Migrations run automatically
- Fixtures loaded automatically
- Transactions rolled back after each test (DAMA)

### Running Tests

```bash
# All tests
php bin/phpunit

# Specific test file
php bin/phpunit tests/Controller/BlogControllerTest.php

# With coverage
php bin/phpunit --coverage-html var/coverage/html

# Specific group
php bin/phpunit --group slow
```

### Test Organization

```
tests/
├── AbstractCommandTestCase.php    # Base for command tests
├── bootstrap.php                  # Test bootstrap
├── Utils/
│   └── TestUtilities.php         # Factory methods
├── Controller/                    # Functional tests
├── Command/                       # Command tests
├── Form/                          # Form tests
├── Utils/                         # Unit tests
└── Example/                       # Example tests
```
```

---

## Context Reset Information

If resuming after context reset:

**Files Created:**
1. `tests/AbstractCommandTestCase.php` - Base class for command tests
2. `tests/Utils/TestUtilities.php` - Factory methods for test data
3. `tests/Example/InfrastructureTest.php` - Example test
4. `.env.test` - Test environment configuration

**Files Modified:**
1. `phpunit.xml.dist` - Enhanced configuration
2. `tests/bootstrap.php` - Improved test bootstrap

**Verification:**
- Run `php bin/phpunit` - all tests pass
- Check coverage report generated
- Test utilities work
- Transaction rollback working
- Memory file updated

**Next Plan:** `09-cli-tests.md`

---

## Completion Checklist

- [ ] PHPUnit configuration enhanced
- [ ] AbstractCommandTestCase created
- [ ] TestUtilities created
- [ ] Test environment configured
- [ ] Test bootstrap updated
- [ ] Example test passes
- [ ] Full test suite runs successfully
- [ ] Coverage report generates
- [ ] Transaction rollback verified
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  feat: enhance testing infrastructure with utilities and base classes

  - Update PHPUnit configuration with DAMA extension
  - Create AbstractCommandTestCase for command testing
  - Create TestUtilities factory methods for test data
  - Configure test environment and database
  - Improve test bootstrap with auto-migrations
  - Add example infrastructure test

  Provides foundation for adding missing tests (Plans 09-10).
  Enables consistent test data creation and cleanup.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 09