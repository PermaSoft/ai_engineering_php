# Testing Strategy

Complete testing approach, patterns, and test coverage.

## Test Organization

```
tests/
├── AbstractCommandTestCase.php         # Base class for command tests
├── bootstrap.php                       # Test bootstrap
├── Controller/
│   ├── ApplicationAvailabilityTest.php # Smoke tests for all URLs
│   ├── BlogControllerTest.php          # Blog functionality
│   ├── CommentControllerTest.php       # Comment functionality
│   ├── SecurityControllerTest.php      # Login/logout
│   ├── UserControllerTest.php          # User profile
│   └── Admin/
│       └── PostControllerTest.php      # Admin CRUD
├── Command/
│   ├── AddUserCommandTest.php          # User creation command (7 tests)
│   └── ListUsersCommandTest.php        # List users command (5 tests)
├── Form/
│   └── Type/
│       └── DataTransformer/
│           └── TagArrayToStringTransformerTest.php  # Form transformer tests (11 tests)
├── Utils/
│   └── TestUtilities.php               # Factory methods for test data
└── Example/
    └── InfrastructureTest.php          # Example test demonstrating infrastructure
```

---

## Test Configuration

### PHPUnit Configuration

**Location**: `phpunit.xml.dist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.3/phpunit.xsd"
         colors="true"
         bootstrap="tests/bootstrap.php">

    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <php>
        <ini name="display_errors" value="1"/>
        <ini name="error_reporting" value="-1"/>
        <server name="APP_ENV" value="test" force="true"/>
        <server name="SHELL_VERBOSITY" value="-1"/>
        <env name="SYMFONY_DEPRECATIONS_HELPER" value="disabled"/>
    </php>

    <source>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </source>

    <extensions>
        <bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
    </extensions>
</phpunit>
```

**Key Settings**:
- **Bootstrap**: `tests/bootstrap.php` for test initialization
- **Environment**: `APP_ENV=test`
- **DAMA Extension**: Automatic database transaction rollback per test

### Test Environment Configuration

**Database**: `config/packages/test/doctrine.yaml`

```yaml
doctrine:
    dbal:
        # SQLite in-memory database for fast tests
        url: 'sqlite:///:memory:'
```

**Features**:
- In-memory database (no file I/O)
- Fresh database per test run
- Fast test execution

### DAMA Doctrine Test Bundle

Wraps each test in a database transaction and rolls back after test completes.

**Benefits**:
- Test isolation
- No database cleanup needed
- Fast execution (no truncate/recreate)

**Configuration**: Automatic via PHPUnit extension

---

## Testing Infrastructure

### AbstractCommandTestCase

Base class for command tests located at `tests/AbstractCommandTestCase.php`.

**Usage Example**:

```php
use App\Tests\AbstractCommandTestCase;

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

**Provided Methods**:
- `executeCommand(Command $command, array $input, array $options)` - Execute command object
- `executeCommandByName(string $name, array $input, array $options)` - Execute by command name
- `assertOutputContains(CommandTester $tester, string $expected)` - Assert output contains string
- `assertOutputNotContains(CommandTester $tester, string $expected)` - Assert output doesn't contain string
- `assertCommandIsSuccessful(CommandTester $tester)` - Assert command succeeded
- `assertCommandFailed(CommandTester $tester)` - Assert command failed

### TestUtilities Factory Methods

Located at `tests/Utils/TestUtilities.php`, provides factory methods for creating test data with sensible defaults.

**Create Test User**:
```php
use App\Tests\Utils\TestUtilities;

$user = TestUtilities::createUser($em, $hasher, [
    'username' => 'testuser',
    'email' => 'test@example.com',
    'fullName' => 'Test User',
    'password' => 'password',
    'roles' => ['ROLE_USER'],
]);
```

**Create Admin User**:
```php
$admin = TestUtilities::createAdmin($em, $hasher, [
    'username' => 'admin',
]);
```

**Create Test Post**:
```php
$post = TestUtilities::createPost($em, $author, [
    'title' => 'Test Post',
    'slug' => 'test-post',
    'summary' => 'Summary',
    'content' => 'Content with **markdown**',
]);
```

**Create Test Tag**:
```php
$tag = TestUtilities::createTag($em, 'php');
```

**Create Test Comment**:
```php
$comment = TestUtilities::createComment($em, $post, $author, [
    'content' => 'Test comment',
]);
```

**Clear Database** (for tests outside DAMA transaction):
```php
TestUtilities::clearDatabase($em);
```

### Test Database

- **Location**: `var/data_test.db` (SQLite)
- **Bootstrap**: Automatically recreated on each test run
- **Migrations**: Run automatically via `tests/bootstrap.php`
- **Fixtures**: Loaded automatically via `tests/bootstrap.php`
- **Isolation**: DAMA extension rolls back transactions after each test

### Running Tests

```bash
# All tests
php bin/phpunit

# Specific test file
php bin/phpunit tests/Controller/BlogControllerTest.php

# With coverage HTML report
php bin/phpunit --coverage-html var/coverage/html

# With testdox output
php bin/phpunit --testdox

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
    └── InfrastructureTest.php    # Infrastructure demo
```

---

## Command Testing

### Using AbstractCommandTestCase

Command tests extend `AbstractCommandTestCase` for simplified command testing:

```php
use App\Tests\AbstractCommandTestCase;

final class AddUserCommandTest extends AbstractCommandTestCase
{
    public function testExecuteCreatesUser(): void
    {
        $tester = $this->executeCommandByName('app:add-user', [
            'username' => 'testuser',
            'password' => 'password123',
            'email' => 'test@example.com',
        ]);

        $this->assertCommandIsSuccessful($tester);
        $this->assertOutputContains($tester, 'successfully created');
    }
}
```

### Testing Command Options

Test commands with flags and options:

```php
public function testExecuteWithAdminRole(): void
{
    $tester = $this->executeCommandByName('app:add-user', [
        'username' => 'admin',
        'password' => 'password123',
        'email' => 'admin@example.com',
        '--admin' => true,  // Test option
    ]);

    $this->assertCommandIsSuccessful($tester);
}
```

### Testing Error Cases

Verify error handling:

```php
public function testExecuteWithDuplicateUsername(): void
{
    // Create first user
    $this->executeCommandByName('app:add-user', [
        'username' => 'duplicate',
        'password' => 'password123',
        'email' => 'first@example.com',
    ]);

    // Try duplicate
    $tester = $this->executeCommandByName('app:add-user', [
        'username' => 'duplicate',
        'password' => 'password123',
        'email' => 'second@example.com',
    ]);

    $this->assertCommandFailed($tester);
    $this->assertOutputContains($tester, 'already exists');
}
```

### Testing with TestUtilities

Use TestUtilities for data setup:

```php
use App\Tests\Utils\TestUtilities;

public function testListUsers(): void
{
    TestUtilities::createUser($this->entityManager, $this->passwordHasher, [
        'username' => 'user1',
        'fullName' => 'User One',
    ]);

    $tester = $this->executeCommandByName('app:list-users');

    $this->assertCommandIsSuccessful($tester);
    $this->assertOutputContains($tester, 'user1');
}
```

### Command Test Coverage

**AddUserCommandTest** (7 tests):
- Execute creates user
- Execute with admin role
- Execute with duplicate username
- Execute with invalid email
- Execute with short password (documents current behavior)
- Execute with empty username
- Execute displays correct role message

**ListUsersCommandTest** (5 tests):
- Execute displays user list
- Execute shows total user count
- Execute shows user list title
- Execute shows all user roles
- Execute shows user details

---

## Functional Tests

### WebTestCase

Base class for controller tests.

**Features**:
- HTTP client for requests
- Database access
- Service container access
- User authentication helpers

**Example**:

```php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/blog/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Blog');
    }
}
```

---

## Controller Tests

### DefaultControllerTest - Smoke Tests

**Location**: `tests/Controller/DefaultControllerTest.php`

Purpose: Verify all public and secure URLs load successfully.

```php
final class DefaultControllerTest extends WebTestCase
{
    /**
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function getPublicUrls(): iterable
    {
        yield ['/'];
        yield ['/en/blog/'];
        yield ['/en/blog/posts/lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit'];
        yield ['/en/blog/search'];
        yield ['/en/login'];
    }

    /**
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls(string $url): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('jane_admin');
        $client->loginUser($user);

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function getSecureUrls(): iterable
    {
        yield ['/en/admin/post/'];
        yield ['/en/admin/post/new'];
        yield ['/en/profile/edit'];
        yield ['/en/profile/change-password'];
    }
}
```

**Features**:
- Data providers for parameterized tests
- Hardcoded URLs (not generated) to catch broken public URLs
- Separate tests for public and authenticated routes
- `loginUser()` for authentication

**Best Practice**: Hardcode URLs instead of using `$router->generate()` to ensure public URLs remain stable.

### BlogControllerTest

**Location**: `tests/Controller/BlogControllerTest.php`

```php
final class BlogControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/blog/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Blog');
        $this->assertCount(10, $crawler->filter('article.post'));
    }

    public function testRssFeed(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/blog/rss.xml');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/rss+xml; charset=UTF-8');

        $this->assertGreaterThan(0, $crawler->filter('item')->count());
    }

    public function testNewComment(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john_user');
        $client->loginUser($user);

        $client->request('GET', '/en/blog/posts/lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit');

        $client->submitForm('Submit', [
            'comment[content]' => 'This is a test comment.',
        ]);

        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertSelectorExists('html:contains("This is a test comment.")');
    }

    public function testAjaxSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/blog/search?q=lorem');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('article.post')->count());
    }
}
```

**Features**:
- Crawler for DOM traversal
- `assertSelectorTextContains()` for content verification
- `assertResponseHeaderSame()` for header checks
- `submitForm()` for form submission
- `followRedirect()` to follow 303 redirects
- `assertSelectorExists()` for element presence

### Admin\BlogControllerTest

**Location**: `tests/Controller/Admin/BlogControllerTest.php`

```php
final class BlogControllerTest extends WebTestCase
{
    public function testRegularUsersCannotAccessAdmin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john_user');
        $client->loginUser($user);

        $client->request('GET', '/en/admin/post/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanCreatePost(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $admin = $userRepository->findOneByUsername('jane_admin');
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/en/admin/post/new');

        $form = $crawler->selectButton('Save')->form([
            'post[title]' => 'Test Post Title',
            'post[summary]' => 'This is a test summary.',
            'post[content]' => 'This is the test post content. It must be long enough.',
            'post[tags]' => 'test,demo',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/admin/post/');
    }

    public function testAdminCanEditPost(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $admin = $userRepository->findOneByUsername('jane_admin');
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/en/admin/post/1/edit');

        $form = $crawler->selectButton('Save')->form([
            'post[title]' => 'Updated Title',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Updated Title');
    }

    public function testAdminCanDeletePost(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $admin = $userRepository->findOneByUsername('jane_admin');
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/en/admin/post/1');

        $client->submit($crawler->selectButton('Delete')->form());

        $this->assertResponseRedirects('/en/admin/post/');
    }
}
```

**Features**:
- Access control tests (403 forbidden)
- Full CRUD operation tests
- Form selection and submission
- Redirect verification
- Content verification after redirect

### UserControllerTest

**Location**: `tests/Controller/UserControllerTest.php`

```php
final class UserControllerTest extends WebTestCase
{
    public function testEditUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john_user');
        $client->loginUser($user);

        $crawler = $client->request('GET', '/en/profile/edit');

        $form = $crawler->selectButton('Save')->form([
            'user[fullName]' => 'Updated Name',
            'user[email]' => 'updated@example.com',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/profile/edit');

        $user = $userRepository->findOneByUsername('john_user');
        $this->assertSame('Updated Name', $user->getFullName());
        $this->assertSame('updated@example.com', $user->getEmail());
    }

    public function testChangePassword(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john_user');
        $client->loginUser($user);

        $crawler = $client->request('GET', '/en/profile/change-password');

        $form = $crawler->selectButton('Save')->form([
            'change_password[currentPassword]' => 'kitten',
            'change_password[newPassword][first]' => 'new_password',
            'change_password[newPassword][second]' => 'new_password',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/login');
    }
}
```

**Features**:
- Profile update verification
- Password change with logout
- Database assertion after form submission

---

## Command Tests

### AbstractCommandTestCase

**Location**: `tests/Command/AbstractCommandTestCase.php`

Base class for console command tests.

```php
abstract class AbstractCommandTestCase extends KernelTestCase
{
    protected CommandTester $commandTester;

    protected function executeCommand(Command $command, array $inputs = []): int
    {
        $this->commandTester = new CommandTester($command);
        $this->commandTester->setInputs($inputs);

        return $this->commandTester->execute([]);
    }
}
```

### AddUserCommandTest

**Location**: `tests/Command/AddUserCommandTest.php`

```php
final class AddUserCommandTest extends AbstractCommandTestCase
{
    public function testCreateUserInteractive(): void
    {
        $command = static::getContainer()->get(AddUserCommand::class);

        $this->executeCommand($command, [
            'test_user',        // username
            'password123',      // password
            'password123',      // password repeat
            'test@example.com', // email
            'Test User',        // full name
        ]);

        $this->assertCommandIsSuccessful($this->commandTester);
        $this->assertStringContainsString('User successfully created', $this->commandTester->getDisplay());

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByUsername('test_user');

        $this->assertNotNull($user);
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testCreateUserNonInteractive(): void
    {
        $command = static::getContainer()->get(AddUserCommand::class);

        $returnCode = $this->commandTester->execute([
            'username' => 'test_user',
            'password' => 'password123',
            'email' => 'test@example.com',
            '--full-name' => 'Test User',
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $returnCode);
    }

    public function testCreateAdminUser(): void
    {
        $command = static::getContainer()->get(AddUserCommand::class);

        $returnCode = $this->commandTester->execute([
            'username' => 'admin_user',
            'password' => 'password123',
            'email' => 'admin@example.com',
            '--full-name' => 'Admin User',
            '--admin' => true,
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $returnCode);

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneByUsername('admin_user');

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }
}
```

**Features**:
- Interactive mode testing with `setInputs()`
- Non-interactive mode with command arguments
- Database verification
- Output assertion

---

## Form Tests

### TagArrayToStringTransformerTest

**Location**: `tests/Form/Type/DataTransformer/TagArrayToStringTransformerTest.php`

```php
final class TagArrayToStringTransformerTest extends KernelTestCase
{
    private TagArrayToStringTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $tagRepository = static::getContainer()->get(TagRepository::class);
        $this->transformer = new TagArrayToStringTransformer($tagRepository);
    }

    public function testTransform(): void
    {
        $tag1 = new Tag('tag1');
        $tag2 = new Tag('tag2');

        $result = $this->transformer->transform([$tag1, $tag2]);

        $this->assertSame('tag1,tag2', $result);
    }

    public function testReverseTransform(): void
    {
        $tagRepository = static::getContainer()->get(TagRepository::class);

        // Create test tags in database
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $tag1 = new Tag('existing');
        $entityManager->persist($tag1);
        $entityManager->flush();

        $result = $this->transformer->reverseTransform('existing, new');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Tag::class, $result[0]);
        $this->assertInstanceOf(Tag::class, $result[1]);
    }

    public function testReverseTransformEmptyString(): void
    {
        $result = $this->transformer->reverseTransform('');

        $this->assertSame([], $result);
    }

    public function testReverseTransformRemovesDuplicates(): void
    {
        $result = $this->transformer->reverseTransform('tag1, tag1, tag2');

        $this->assertCount(2, $result);
    }
}
```

**Features**:
- Data transformer testing
- Database interaction in tests
- Edge case testing (empty, duplicates)

---

## Utility Tests

### ValidatorTest

**Location**: `tests/Utils/ValidatorTest.php`

```php
final class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testValidateUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The username can only contain lowercase letters and underscores.');

        $this->validator->validateUsername('INVALID');
    }

    /**
     * @dataProvider validUsernamesProvider
     */
    public function testValidUsernamesPass(string $username): void
    {
        $this->validator->validateUsername($username);

        $this->expectNotToPerformAssertions();
    }

    public static function validUsernamesProvider(): iterable
    {
        yield ['john'];
        yield ['john_doe'];
        yield ['jane_admin'];
    }

    /**
     * @dataProvider invalidUsernamesProvider
     */
    public function testInvalidUsernamesFail(string $username): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->validator->validateUsername($username);
    }

    public static function invalidUsernamesProvider(): iterable
    {
        yield ['John'];
        yield ['john-doe'];
        yield ['john@doe'];
        yield [''];
    }

    public function testValidatePasswordTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The password must be at least 6 characters long.');

        $this->validator->validatePassword('12345');
    }

    public function testValidateInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The email should look like a real email.');

        $this->validator->validateEmail('invalid-email');
    }
}
```

**Features**:
- Unit testing (no Symfony kernel)
- Exception testing
- Data providers for multiple scenarios
- Edge case coverage

---

## Test Database Setup

### Fixtures Loading

Fixtures loaded automatically for functional tests.

**Location**: `src/DataFixtures/AppFixtures.php`

Creates:
- 3 users (2 admins, 1 regular)
- 9 tags
- 30 posts with comments

### Manual Database Setup

For manual test database creation:

```bash
# Create test database schema
php bin/console doctrine:database:create --env=test

# Run migrations
php bin/console doctrine:migrations:migrate --env=test

# Load fixtures
php bin/console doctrine:fixtures:load --env=test
```

**Note**: Not needed with in-memory SQLite + DAMA bundle.

---

## Test Helpers & Assertions

### Symfony Assertions

**Response Status**:
```php
$this->assertResponseIsSuccessful();
$this->assertResponseStatusCodeSame(403);
$this->assertResponseRedirects('/path');
```

**Response Headers**:
```php
$this->assertResponseHeaderSame('Content-Type', 'application/json');
$this->assertResponseHasHeader('Location');
```

**Selectors**:
```php
$this->assertSelectorExists('h1');
$this->assertSelectorTextContains('h1', 'Blog');
$this->assertSelectorNotExists('.error');
```

**HTML**:
```php
$this->assertStringContainsString('Expected text', $client->getResponse()->getContent());
```

### Crawler

```php
$crawler = $client->request('GET', '/path');

// Count elements
$count = $crawler->filter('article.post')->count();

// Select form
$form = $crawler->selectButton('Submit')->form();

// Fill form
$form['post[title]'] = 'New Title';

// Submit
$client->submit($form);

// Follow links
$link = $crawler->selectLink('Read more')->link();
$client->click($link);
```

### Authentication

```php
$userRepository = static::getContainer()->get(UserRepository::class);
$user = $userRepository->findOneByUsername('jane_admin');

$client->loginUser($user);
```

### Service Container

```php
$service = static::getContainer()->get(MyService::class);
```

---

## Running Tests

### All Tests

```bash
php bin/phpunit
```

### Specific Test File

```bash
php bin/phpunit tests/Controller/BlogControllerTest.php
```

### Specific Test Method

```bash
php bin/phpunit --filter testIndex
```

### With Coverage

```bash
XDEBUG_MODE=coverage php bin/phpunit --coverage-html coverage/
```

---

## Best Practices Applied

### ✅ WebTestCase for Functional Tests
Controller tests extend `WebTestCase`

### ✅ Hardcoded URLs in Smoke Tests
Public URLs hardcoded to catch broken routes

### ✅ loginUser() Method
Authenticate users without form submission

### ✅ Data Providers
Parameterized tests for multiple scenarios

### ✅ Database Transactions
Automatic rollback via DAMA bundle

### ✅ In-Memory Database
Fast tests with SQLite :memory:

### ✅ Fixtures for Test Data
Consistent, deterministic test data

### ✅ Assertions for Common Checks
Symfony assertions for responses, selectors, headers

### ✅ followRedirects()
Automatically follow redirects in tests

### ✅ Service Container Access
Get services via `static::getContainer()`

---

## Test Coverage Summary

| Area | Test Class | Coverage |
|------|------------|----------|
| Public URLs | DefaultControllerTest | Homepage, blog, login |
| Secure URLs | DefaultControllerTest | Admin, profile |
| Blog | BlogControllerTest | Index, RSS, comments, search |
| Admin CRUD | Admin\BlogControllerTest | Create, read, update, delete posts |
| User Profile | UserControllerTest | Edit profile, change password |
| Commands | AddUserCommandTest, ListUsersCommandTest | User creation, listing |
| Forms | TagArrayToStringTransformerTest | Data transformers |
| Utils | ValidatorTest | Input validation |

**Total Test Files**: 9
**Test Methods**: 30+
**Coverage Areas**: Controllers, Commands, Forms, Utilities