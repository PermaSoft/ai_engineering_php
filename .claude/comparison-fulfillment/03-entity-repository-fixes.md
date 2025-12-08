# Plan 03: Entity & Repository Fixes

**Priority:** 🔴 CRITICAL
**Estimated Time:** 2 hours
**Dependencies:** None
**Status:** Ready to execute

---

## Context & Objective

Fix technical debt in entity implementations identified in the comparison report:
1. Replace string literal column types with `Types` constants
2. Add null-safety cast to `User::getUserIdentifier()`
3. Improve code quality and maintainability
4. Ensure Packmind standards compliance

These are not critical bugs but represent code quality issues that should be fixed early to maintain standards.

---

## Reference Materials

### Memory Files
- `.claude/memory/domain-model.md` - Entity specifications
- `BRANCH_COMPARISON_REPORT.md` (Section 2: Domain Model & Entities)

### Comparison Report Key Findings
- String literals used instead of `Doctrine\DBAL\Types\Types` constants
- Missing null-safety cast in `User::getUserIdentifier()`
- Repository code is mostly correct but needs strict types verification

---

## Prerequisites

- Basic understanding of Doctrine ORM
- Read access to memory files

---

## Deliverables Checklist

### Code Changes
- [ ] `src/Entity/Post.php` - Replace string literals with Types constants
- [ ] `src/Entity/Comment.php` - Replace string literals with Types constants
- [ ] `src/Entity/User.php` - Replace string literals, add null-safety
- [ ] `src/Entity/Tag.php` - Replace string literals with Types constants
- [ ] `src/Repository/PostRepository.php` - Verify types
- [ ] `src/Repository/UserRepository.php` - Verify types
- [ ] `src/Repository/TagRepository.php` - Verify types

### Testing
- [ ] Run existing tests to verify no regression
- [ ] Validate database schema still works

### Documentation
- [ ] Update memory file with best practices

---

## Implementation Steps

### Step 1: Add Types Import to All Entities

Add this import at the top of each entity file after the namespace declaration:

```php
use Doctrine\DBAL\Types\Types;
```

**Files to modify:**
- `src/Entity/Post.php`
- `src/Entity/Comment.php`
- `src/Entity/User.php`
- `src/Entity/Tag.php`

---

### Step 2: Fix Post Entity

**File:** `src/Entity/Post.php`

**Find and replace:**

```php
// BEFORE
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: 'integer')]
private ?int $id = null;

#[ORM\Column(type: 'string')]
private ?string $title = null;

#[ORM\Column(type: 'string')]
private ?string $slug = null;

#[ORM\Column(type: 'string')]
private ?string $summary = null;

#[ORM\Column(type: 'text')]
private ?string $content = null;

#[ORM\Column(type: 'datetime_immutable')]
private ?\DateTimeImmutable $publishedAt = null;

// AFTER
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: Types::INTEGER)]
private ?int $id = null;

#[ORM\Column(type: Types::STRING)]
private ?string $title = null;

#[ORM\Column(type: Types::STRING)]
private ?string $slug = null;

#[ORM\Column(type: Types::STRING)]
private ?string $summary = null;

#[ORM\Column(type: Types::TEXT)]
private ?string $content = null;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
private ?\DateTimeImmutable $publishedAt = null;
```

**Rationale:** Using constants prevents typos and provides better IDE support.

---

### Step 3: Fix Comment Entity

**File:** `src/Entity/Comment.php`

**Find and replace:**

```php
// BEFORE
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: 'integer')]
private ?int $id = null;

#[ORM\Column(type: 'text')]
private ?string $content = null;

#[ORM\Column(type: 'datetime_immutable')]
private ?\DateTimeImmutable $publishedAt = null;

// AFTER
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: Types::INTEGER)]
private ?int $id = null;

#[ORM\Column(type: Types::TEXT)]
private ?string $content = null;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
private ?\DateTimeImmutable $publishedAt = null;
```

---

### Step 4: Fix User Entity (Including Null-Safety)

**File:** `src/Entity/User.php`

**Find and replace column types:**

```php
// BEFORE
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: 'integer')]
private ?int $id = null;

#[ORM\Column(type: 'string', unique: true)]
private ?string $username = null;

#[ORM\Column(type: 'string')]
private ?string $fullName = null;

#[ORM\Column(type: 'string', unique: true)]
private ?string $email = null;

#[ORM\Column(type: 'string')]
private ?string $password = null;

// AFTER
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: Types::INTEGER)]
private ?int $id = null;

#[ORM\Column(type: Types::STRING, unique: true)]
private ?string $username = null;

#[ORM\Column(type: Types::STRING)]
private ?string $fullName = null;

#[ORM\Column(type: Types::STRING, unique: true)]
private ?string $email = null;

#[ORM\Column(type: Types::STRING)]
private ?string $password = null;
```

**Fix getUserIdentifier() null-safety:**

```php
// BEFORE
public function getUserIdentifier(): string
{
    return $this->username;  // ✗ Will crash if null
}

// AFTER
public function getUserIdentifier(): string
{
    return (string) $this->username;  // ✓ Safe cast
}
```

**Rationale:** The cast ensures we never return null, which would violate the return type and cause runtime errors.

---

### Step 5: Fix Tag Entity

**File:** `src/Entity/Tag.php`

**Find and replace:**

```php
// BEFORE
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: 'integer')]
private ?int $id = null;

#[ORM\Column(type: 'string', unique: true)]
private ?string $name = null;

// AFTER
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: Types::INTEGER)]
private ?int $id = null;

#[ORM\Column(type: Types::STRING, unique: true)]
private ?string $name = null;
```

---

### Step 6: Verify Repositories

**Files to check:**
- `src/Repository/PostRepository.php`
- `src/Repository/UserRepository.php`
- `src/Repository/TagRepository.php`

**Verify these already have:**
- `declare(strict_types=1)` at the top ✓
- `final class` declaration ✓
- Proper type hints ✓

No changes needed for repositories - they're already correct in rebuilding.

---

### Step 7: Validate Database Schema

After making changes, ensure the database schema is still valid:

```bash
php bin/console doctrine:schema:validate
```

Expected output:
```
[OK] The mapping files are correct.
[OK] The database schema is in sync with the mapping files.
```

---

## Verification Criteria

### Automated Tests

```bash
# Run full test suite
php bin/phpunit

# All tests should pass
```

### Manual Verification

1. **Check imports in all entity files:**
   ```bash
   grep -n "use Doctrine\\DBAL\\Types\\Types;" src/Entity/*.php
   ```
   Should show 4 files with the import.

2. **Verify no string literals remain:**
   ```bash
   grep -n "type: '[a-z_]*'" src/Entity/*.php
   ```
   Should return no results.

3. **Confirm getUserIdentifier cast:**
   ```bash
   grep -A 2 "getUserIdentifier" src/Entity/User.php
   ```
   Should show `(string) $this->username`.

4. **Test application:**
   ```bash
   symfony server:start
   # Navigate to site and test:
   # - User registration/login
   # - Creating posts
   # - Adding comments
   # - Adding tags
   ```

---

## Code Quality Checks

### Use grep to find all column definitions

```bash
# This should show all columns now use Types constants
grep -n "Column(type:" src/Entity/*.php
```

### Verify strict types declarations

```bash
# Should show 4 entity files
grep -l "declare(strict_types=1)" src/Entity/*.php
```

---

## Memory File Updates

**File:** `.claude/memory/domain-model.md`

Update the Doctrine mapping section:

```markdown
## Doctrine Mapping Best Practices

### Column Type Definitions

Always use `Doctrine\DBAL\Types\Types` constants for column types:

```php
use Doctrine\DBAL\Types\Types;

#[ORM\Column(type: Types::INTEGER)]  // ✓ Correct
private ?int $id;

#[ORM\Column(type: 'integer')]  // ✗ Avoid string literals
private ?int $id;
```

Available type constants:
- `Types::INTEGER` - Integer values
- `Types::STRING` - Variable-length strings (VARCHAR)
- `Types::TEXT` - Long text content
- `Types::DATETIME_IMMUTABLE` - Immutable datetime
- `Types::BOOLEAN` - Boolean values
- `Types::ARRAY` - Serialized arrays
- `Types::JSON` - JSON data

### Null Safety in UserInterface

When implementing Symfony's `UserInterface`, ensure `getUserIdentifier()` never returns null:

```php
public function getUserIdentifier(): string
{
    // ✓ Safe - casts null to empty string
    return (string) $this->username;

    // ✗ Unsafe - can return null and violate return type
    return $this->username;
}
```

This prevents `TypeError` exceptions when the username property is null.
```

---

## Context Reset Information

If resuming after context reset:

**Files Modified:**
1. `src/Entity/Post.php` - Added Types import, replaced string literals
2. `src/Entity/Comment.php` - Added Types import, replaced string literals
3. `src/Entity/User.php` - Added Types import, replaced string literals, fixed getUserIdentifier()
4. `src/Entity/Tag.php` - Added Types import, replaced string literals

**Changes Made:**
- All `type: 'string'` → `type: Types::STRING`
- All `type: 'integer'` → `type: Types::INTEGER`
- All `type: 'text'` → `type: Types::TEXT`
- All `type: 'datetime_immutable'` → `type: Types::DATETIME_IMMUTABLE`
- Fixed `User::getUserIdentifier()` with null-safe cast

**Verification:**
- `php bin/phpunit` - All tests pass
- `php bin/console doctrine:schema:validate` - Schema valid
- No string literals in column definitions

**Next Plan:** `04-template-architecture.md`

---

## Quick Reference: Type Constants

```php
use Doctrine\DBAL\Types\Types;

Types::INTEGER              // int
Types::SMALLINT             // small int
Types::BIGINT               // big int
Types::STRING               // VARCHAR
Types::TEXT                 // TEXT/CLOB
Types::BOOLEAN              // boolean
Types::DECIMAL              // decimal
Types::FLOAT                // float
Types::DATETIME_MUTABLE     // DateTime
Types::DATETIME_IMMUTABLE   // DateTimeImmutable
Types::DATE_MUTABLE         // Date
Types::DATE_IMMUTABLE       // Date (immutable)
Types::TIME_MUTABLE         // Time
Types::TIME_IMMUTABLE       // Time (immutable)
Types::ARRAY                // Serialized array
Types::SIMPLE_ARRAY         // Comma-separated list
Types::JSON                 // JSON
Types::BINARY               // Binary data
Types::BLOB                 // Binary large object
Types::GUID                 // GUID/UUID
```

---

## Completion Checklist

- [ ] All 4 entity files updated with Types constants
- [ ] User::getUserIdentifier() has null-safe cast
- [ ] All entity files have Types import
- [ ] No string literals remain in column definitions
- [ ] Doctrine schema validation passes
- [ ] All PHPUnit tests pass
- [ ] Application manual testing completed
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  refactor: use Doctrine Types constants in entities

  - Replace string literal column types with Types constants
  - Add null-safe cast to User::getUserIdentifier()
  - Improve code maintainability and IDE support
  - Follow Symfony/Doctrine best practices

  This improves type safety and prevents typos in column definitions.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 04
