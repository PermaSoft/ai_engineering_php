# Plan 10: Form & Utility Tests

**Priority:** 🟡 MEDIUM
**Estimated Time:** 5-6 hours
**Dependencies:** Plan 09 (CLI Tests)
**Status:** Ready to execute

---

## Context & Objective

Add comprehensive unit test coverage for form transformers and utility classes (currently 100% missing):
1. Test TagArrayToStringTransformer (6 tests for transformation logic)
2. Test Validator utility (11 tests for validation methods)
3. Cover edge cases and error handling
4. Verify performance optimizations work

This completes the missing unit test coverage identified in the comparison report.

---

## Reference Materials

### Memory Files
- `.claude/memory/testing-strategy.md` - Test patterns
- `.claude/memory/forms-validation.md` - Form transformers
- `BRANCH_COMPARISON_REPORT.md` (Section 8: Testing Strategy)

### Walkthrought Files
```bash
git show walkthrought:tests/Form/DataTransformer/TagArrayToStringTransformerTest.php
git show walkthrought:tests/Utils/ValidatorTest.php
```

---

## Prerequisites

- Testing infrastructure (Plan 08) completed
- Form transformers implemented (Plan 07)
- Understanding of PHPUnit and mocking

---

## Deliverables Checklist

### Code Changes
- [ ] `tests/Form/DataTransformer/TagArrayToStringTransformerTest.php` - 6+ tests
- [ ] `tests/Utils/ValidatorTest.php` - 11+ tests

### Testing
- [ ] All transformer tests pass
- [ ] All validator tests pass
- [ ] Edge cases covered
- [ ] Mock objects used appropriately

### Documentation
- [ ] Update memory file with unit testing patterns

---

## Implementation Steps

### Step 1: Test TagArrayToStringTransformer

**File:** `tests/Form/DataTransformer/TagArrayToStringTransformerTest.php`

```php
<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Entity\Tag;
use App\Form\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TagArrayToStringTransformer.
 *
 * Coverage:
 * - Transform: Tag collection to string
 * - Reverse transform: String to tag collection
 * - Edge cases: empty values, whitespace, duplicates
 * - Performance: batch query optimization
 */
final class TagArrayToStringTransformerTest extends TestCase
{
    private TagRepository $tagRepository;
    private TagArrayToStringTransformer $transformer;

    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->transformer = new TagArrayToStringTransformer($this->tagRepository);
    }

    public function testTransformEmptyCollection(): void
    {
        $tags = new ArrayCollection();

        $result = $this->transformer->transform($tags);

        $this->assertSame('', $result);
    }

    public function testTransformNull(): void
    {
        $result = $this->transformer->transform(null);

        $this->assertSame('', $result);
    }

    public function testTransformWithTags(): void
    {
        $tags = new ArrayCollection([
            new Tag('php'),
            new Tag('symfony'),
            new Tag('doctrine'),
        ]);

        $result = $this->transformer->transform($tags);

        $this->assertSame('php, symfony, doctrine', $result);
    }

    public function testReverseTransformEmptyString(): void
    {
        $result = $this->transformer->reverseTransform('');

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testReverseTransformNull(): void
    {
        $result = $this->transformer->reverseTransform(null);

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testReverseTransformWithExistingTags(): void
    {
        $existingTag1 = new Tag('php');
        $existingTag2 = new Tag('symfony');

        $this->tagRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['name' => ['php', 'symfony']])
            ->willReturn([$existingTag1, $existingTag2]);

        $result = $this->transformer->reverseTransform('php, symfony');

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($existingTag1, $result->first());
    }

    public function testReverseTransformWithNewTags(): void
    {
        $existingTag = new Tag('php');

        $this->tagRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['name' => ['php', 'newtag']])
            ->willReturn([$existingTag]);

        $result = $this->transformer->reverseTransform('php, newtag');

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);

        // First should be existing tag
        $tags = $result->toArray();
        $this->assertSame($existingTag, $tags[0]);

        // Second should be new tag
        $this->assertInstanceOf(Tag::class, $tags[1]);
        $this->assertSame('newtag', $tags[1]->getName());
    }

    public function testReverseTransformHandlesWhitespace(): void
    {
        $this->tagRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['name' => ['php', 'symfony']])
            ->willReturn([]);

        $result = $this->transformer->reverseTransform('  php  ,   symfony  ');

        $this->assertCount(2, $result);
        $tags = $result->toArray();
        $this->assertSame('php', $tags[0]->getName());
        $this->assertSame('symfony', $tags[1]->getName());
    }

    public function testReverseTransformIgnoresEmptyValues(): void
    {
        $this->tagRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['name' => ['php']])
            ->willReturn([]);

        $result = $this->transformer->reverseTransform('php, , ,');

        $this->assertCount(1, $result);
        $this->assertSame('php', $result->first()->getName());
    }

    public function testReverseTransformUsesBatchQuery(): void
    {
        // IMPORTANT: Verify only ONE query is made, not N queries
        $this->tagRepository
            ->expects($this->once()) // Only called once
            ->method('findBy')
            ->with($this->callback(function ($criteria) {
                // Verify it's using IN query with array of names
                return isset($criteria['name']) && is_array($criteria['name']);
            }))
            ->willReturn([]);

        $this->transformer->reverseTransform('php, symfony, doctrine, mysql, redis');

        // If N+1 query problem existed, findBy would be called 5 times
        // This test verifies it's only called once with all names
    }
}
```

---

### Step 2: Create Validator Utility (if not exists)

**File:** `src/Utils/Validator.php`

```php
<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\String\UnicodeString;

/**
 * Utility class for common validation tasks.
 *
 * Provides validation methods for:
 * - Usernames
 * - Emails
 * - Passwords
 * - Content sanitization
 */
final class Validator
{
    /**
     * Validates username format.
     *
     * Username must:
     * - Be 3-20 characters
     * - Contain only alphanumeric characters and underscores
     */
    public static function validateUsername(string $username): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }

    /**
     * Validates email format using filter_var.
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates password strength.
     *
     * Password must be at least $minLength characters.
     */
    public static function validatePassword(string $password, int $minLength = 6): bool
    {
        return (new UnicodeString($password))->length() >= $minLength;
    }

    /**
     * Sanitizes string for safe output.
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Checks if string is valid slug format.
     */
    public static function validateSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }
}
```

---

### Step 3: Test Validator Utility

**File:** `tests/Utils/ValidatorTest.php`

```php
<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Validator utility class.
 *
 * Coverage:
 * - Username validation (format, length)
 * - Email validation
 * - Password validation (strength, length)
 * - Sanitization
 * - Slug validation
 * - Edge cases
 */
final class ValidatorTest extends TestCase
{
    /**
     * @dataProvider validUsernameProvider
     */
    public function testValidUsername(string $username): void
    {
        $this->assertTrue(Validator::validateUsername($username));
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validUsernameProvider(): array
    {
        return [
            'lowercase' => ['testuser'],
            'uppercase' => ['TESTUSER'],
            'mixed case' => ['TestUser'],
            'with numbers' => ['test123'],
            'with underscore' => ['test_user'],
            'minimum length' => ['abc'],
            'maximum length' => ['12345678901234567890'],
        ];
    }

    /**
     * @dataProvider invalidUsernameProvider
     */
    public function testInvalidUsername(string $username): void
    {
        $this->assertFalse(Validator::validateUsername($username));
    }

    /**
     * @return array<string, array<string>>
     */
    public static function invalidUsernameProvider(): array
    {
        return [
            'too short' => ['ab'],
            'too long' => ['123456789012345678901'],
            'with spaces' => ['test user'],
            'with dash' => ['test-user'],
            'with dot' => ['test.user'],
            'with special chars' => ['test@user'],
            'empty' => [''],
        ];
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidEmail(string $email): void
    {
        $this->assertTrue(Validator::validateEmail($email));
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validEmailProvider(): array
    {
        return [
            'simple' => ['test@example.com'],
            'with plus' => ['test+tag@example.com'],
            'with dot' => ['test.user@example.com'],
            'subdomain' => ['test@mail.example.com'],
            'numbers' => ['test123@example.com'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testInvalidEmail(string $email): void
    {
        $this->assertFalse(Validator::validateEmail($email));
    }

    /**
     * @return array<string, array<string>>
     */
    public static function invalidEmailProvider(): array
    {
        return [
            'no at sign' => ['testexample.com'],
            'no domain' => ['test@'],
            'no username' => ['@example.com'],
            'spaces' => ['test @example.com'],
            'double at' => ['test@@example.com'],
            'empty' => [''],
        ];
    }

    /**
     * @dataProvider validPasswordProvider
     */
    public function testValidPassword(string $password, int $minLength = 6): void
    {
        $this->assertTrue(Validator::validatePassword($password, $minLength));
    }

    /**
     * @return array<string, array<string|int>>
     */
    public static function validPasswordProvider(): array
    {
        return [
            'minimum length' => ['123456', 6],
            'long password' => ['thisIsAVeryLongPassword123', 6],
            'with special chars' => ['p@ssw0rd!', 6],
            'unicode characters' => ['пароль123', 6],
            'custom min length' => ['12345678', 8],
        ];
    }

    public function testInvalidPasswordTooShort(): void
    {
        $this->assertFalse(Validator::validatePassword('12345', 6));
        $this->assertFalse(Validator::validatePassword('', 6));
    }

    public function testSanitizeRemovesHtmlTags(): void
    {
        $input = '<script>alert("XSS")</script>';
        $result = Validator::sanitize($input);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testSanitizeEscapesQuotes(): void
    {
        $input = 'He said "Hello"';
        $result = Validator::sanitize($input);

        $this->assertStringContainsString('&quot;', $result);
    }

    /**
     * @dataProvider validSlugProvider
     */
    public function testValidSlug(string $slug): void
    {
        $this->assertTrue(Validator::validateSlug($slug));
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validSlugProvider(): array
    {
        return [
            'simple' => ['hello-world'],
            'with numbers' => ['post-123'],
            'single word' => ['test'],
            'long slug' => ['this-is-a-very-long-slug-with-many-words'],
        ];
    }

    /**
     * @dataProvider invalidSlugProvider
     */
    public function testInvalidSlug(string $slug): void
    {
        $this->assertFalse(Validator::validateSlug($slug));
    }

    /**
     * @return array<string, array<string>>
     */
    public static function invalidSlugProvider(): array
    {
        return [
            'uppercase' => ['Hello-World'],
            'spaces' => ['hello world'],
            'underscores' => ['hello_world'],
            'special chars' => ['hello@world'],
            'double dash' => ['hello--world'],
            'trailing dash' => ['hello-world-'],
            'leading dash' => ['-hello-world'],
            'empty' => [''],
        ];
    }
}
```

---

## Verification Criteria

### Run Form Tests

```bash
# Run transformer tests
php bin/phpunit tests/Form/DataTransformer/

# Should see:
# - TagArrayToStringTransformerTest: 9+ tests passing
# - All assertions passing
# - Mock verification working
```

### Run Utility Tests

```bash
# Run validator tests
php bin/phpunit tests/Utils/

# Should see:
# - ValidatorTest: 11+ tests passing
# - All data providers working
# - Edge cases covered
```

### Coverage Check

```bash
# Check coverage for specific classes
php bin/phpunit --coverage-text --filter=TagArrayToStringTransformer
php bin/phpunit --coverage-text --filter=Validator

# Should show high coverage (>90%)
```

---

## Memory File Updates

**File:** `.claude/memory/testing-strategy.md`

Add this section:

```markdown
## Unit Testing Patterns

### Testing Data Transformers

Data transformer tests use mocks for repositories:

```php
final class MyTransformerTest extends TestCase
{
    private MyRepository $repository;
    private MyTransformer $transformer;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MyRepository::class);
        $this->transformer = new MyTransformer($this->repository);
    }

    public function testTransform(): void
    {
        $input = /* ... */;
        $result = $this->transformer->transform($input);
        $this->assertSame($expected, $result);
    }
}
```

### Using Data Providers

Data providers enable testing multiple scenarios:

```php
/**
 * @dataProvider validInputProvider
 */
public function testValidInput(string $input): void
{
    $this->assertTrue($validator->validate($input));
}

public static function validInputProvider(): array
{
    return [
        'case 1' => ['value1'],
        'case 2' => ['value2'],
    ];
}
```

### Mock Expectations

Verify method calls on mocks:

```php
$this->repository
    ->expects($this->once())
    ->method('findBy')
    ->with(['name' => ['tag1', 'tag2']])
    ->willReturn($mockData);
```

### Testing Edge Cases

Always test:
- Empty values (null, empty string, empty array)
- Whitespace handling
- Special characters
- Boundary conditions (min/max length)
- Invalid input
- Error conditions

### Assertion Best Practices

Use specific assertions:
```php
$this->assertSame()      // Strict equality (===)
$this->assertEquals()     // Loose equality (==)
$this->assertCount()      // Array/collection count
$this->assertInstanceOf() // Type checking
$this->assertStringContainsString() // Substring
```

### Test Coverage Goals

- Unit tests: 90%+ coverage
- Focus on business logic
- Test edge cases and error paths
- Mock external dependencies
```

---

## Context Reset Information

If resuming after context reset:

**Files Created:**
1. `tests/Form/DataTransformer/TagArrayToStringTransformerTest.php` - 9 tests
2. `tests/Utils/ValidatorTest.php` - 11+ tests
3. `src/Utils/Validator.php` - Utility class (if not exists)

**Verification:**
- Run `php bin/phpunit tests/Form/` - all pass
- Run `php bin/phpunit tests/Utils/` - all pass
- Check coverage reports
- Verify mocks working correctly
- Memory file updated

**Next Plan:** `11-internationalization.md`

---

## Completion Checklist

- [ ] TagArrayToStringTransformerTest created (9 tests)
- [ ] ValidatorTest created (11+ tests)
- [ ] Validator utility implemented
- [ ] All tests pass
- [ ] Data providers working
- [ ] Mock objects verified
- [ ] Edge cases covered
- [ ] Coverage report shows improvement
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  test: add comprehensive unit tests for forms and utilities

  - Add TagArrayToStringTransformerTest (9 tests)
    - Test transformation in both directions
    - Test batch query optimization
    - Test edge cases and whitespace handling
  - Add ValidatorTest (11+ tests)
    - Test username validation
    - Test email validation
    - Test password validation
    - Test sanitization
    - Test slug validation
  - Add Validator utility class
  - Use data providers for multiple scenarios
  - Verify mock expectations

  Completes missing unit test coverage.
  Total test methods increased by 20+.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 11