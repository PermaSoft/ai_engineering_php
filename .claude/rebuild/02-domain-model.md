# Phase 2: Domain Model

## Overview

**Goal**: Implement all Doctrine entities (User, Post, Comment, Tag) with relationships, validation, and repositories
**Complexity**: Medium
**Dependencies**: Phase 1 (Project Setup)
**Estimated Files**: 8 files (4 entities + 4 repositories)

## Memory Files Required

- **Primary**: [Domain Model](../memory/domain-model.md)
- **Reference**: [Symfony Demo App Index](../memory/symfony-demo-app-index.md)

## Packmind Standards Applied

- Symfony Entities & Doctrine Best Practices (`.packmind/standards/symfony-entities-doctrine-best-practices.md`)

## Entity Creation Order

Entities must be created in dependency order:
1. User (no dependencies)
2. Tag (no dependencies)
3. Post (depends on User, Tag)
4. Comment (depends on User, Post)

## Implementation Checklist

### 1. User Entity

- [ ] Create `src/Entity/User.php`
  - [ ] Implement `UserInterface`
  - [ ] Implement `PasswordAuthenticatedUserInterface`
  - [ ] Table name: `symfony_demo_user`
  - [ ] Repository: `App\Repository\UserRepository`

- [ ] Define properties
  - [ ] `id`: `?int` (auto-generated primary key)
  - [ ] `fullName`: `?string` (STRING, not null) with `#[Assert\NotBlank]`
  - [ ] `username`: `?string` (STRING, unique, not null) with `#[Assert\NotBlank]`, `#[Assert\Length(min:2, max:50)]`
  - [ ] `email`: `?string` (STRING, unique, not null) with `#[Assert\Email]`
  - [ ] `password`: `?string` (STRING, not null, hashed)
  - [ ] `roles`: `array` (JSON, not null, default: `[]`)

- [ ] Define constants
  - [ ] `ROLE_USER = 'ROLE_USER'`
  - [ ] `ROLE_ADMIN = 'ROLE_ADMIN'`

- [ ] Implement required methods
  - [ ] `getUserIdentifier(): string` - returns username
  - [ ] `getRoles(): array` - returns roles, guarantees ROLE_USER minimum
  - [ ] `eraseCredentials(): void` - removes sensitive data
  - [ ] `__serialize(): array` - returns `[$this->id, $this->username, $this->password]`
  - [ ] `__unserialize(array $data): void` - restores id, username, password

- [ ] Create `src/Repository/UserRepository.php`
  - [ ] Extend `ServiceEntityRepository`
  - [ ] Implement `PasswordUpgraderInterface` for password rehashing

### 2. Tag Entity

- [ ] Create `src/Entity/Tag.php`
  - [ ] Implement `\JsonSerializable` interface
  - [ ] Table name: `symfony_demo_tag`
  - [ ] Repository: `App\Repository\TagRepository`

- [ ] Define properties
  - [ ] `id`: `?int` (auto-generated primary key)
  - [ ] `name`: `readonly string` (STRING, unique, not null)

- [ ] Implement constructor
  - [ ] `__construct(string $name)` - name is required, readonly

- [ ] Implement methods
  - [ ] `jsonSerialize(): string` - returns name
  - [ ] `__toString(): string` - returns name
  - [ ] `getId(): ?int`
  - [ ] `getName(): string`

- [ ] Create `src/Repository/TagRepository.php`
  - [ ] Extend `ServiceEntityRepository`

### 3. Post Entity

- [ ] Create `src/Entity/Post.php`
  - [ ] Table name: `symfony_demo_post`
  - [ ] Repository: `App\Repository\PostRepository`

- [ ] Define properties
  - [ ] `id`: `?int` (auto-generated primary key)
  - [ ] `title`: `?string` (STRING, not null) with `#[Assert\NotBlank]`
  - [ ] `slug`: `?string` (STRING, not null)
  - [ ] `summary`: `?string` (STRING, not null) with:
    - `#[Assert\NotBlank(message: 'post.blank_summary')]`
    - `#[Assert\Length(max:255)]`
  - [ ] `content`: `?string` (TEXT, not null) with:
    - `#[Assert\NotBlank(message: 'post.blank_content')]`
    - `#[Assert\Length(min:10, minMessage: 'post.too_short_content')]`
  - [ ] `publishedAt`: `\DateTimeImmutable` (DATETIME, not null)

- [ ] Define relationships
  - [ ] `author`: `?User` (ManyToOne, not null, no cascade)
  - [ ] `comments`: `Collection<Comment>` (OneToMany, orphanRemoval, cascade persist, ordered by publishedAt DESC)
  - [ ] `tags`: `Collection<Tag>` (ManyToMany, cascade persist, ordered by name ASC, join table: symfony_demo_post_tag)

- [ ] Add class-level validation
  - [ ] `#[UniqueEntity(fields: ['slug'], errorPath: 'title', message: 'post.slug_unique')]`

- [ ] Implement constructor
  - [ ] Initialize `publishedAt` to `new \DateTimeImmutable()`
  - [ ] Initialize `comments` to `new ArrayCollection()`
  - [ ] Initialize `tags` to `new ArrayCollection()`

- [ ] Implement collection management methods
  - [ ] `addComment(Comment $comment): void` - sets bidirectional relationship
  - [ ] `removeComment(Comment $comment): void`
  - [ ] `addTag(Tag ...$tags): void` - variadic, prevents duplicates
  - [ ] `removeTag(Tag $tag): void`

- [ ] Create `src/Repository/PostRepository.php`
  - [ ] Extend `ServiceEntityRepository`

### 4. Comment Entity

- [ ] Create `src/Entity/Comment.php`
  - [ ] Table name: `symfony_demo_comment`

- [ ] Define properties
  - [ ] `id`: `?int` (auto-generated primary key)
  - [ ] `content`: `?string` (TEXT, not null) with length validation (min:5, max:10000)
  - [ ] `publishedAt`: `\DateTimeImmutable` (DATETIME, not null)

- [ ] Define relationships
  - [ ] `post`: `?Post` (ManyToOne, inversedBy: 'comments', not null)
  - [ ] `author`: `?User` (ManyToOne, not null)

- [ ] Implement constructor
  - [ ] Initialize `publishedAt` to `new \DateTimeImmutable()`

- [ ] Implement spam detection method
  - [ ] `isLegitComment(): bool` - custom validation, rejects content with '@'

### 5. Database Migration

- [ ] Generate migration: `php bin/console make:migration`
- [ ] Run migration: `php bin/console doctrine:migrations:migrate`
- [ ] Verify schema: `php bin/console doctrine:schema:validate`

### 6. Verification

- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/Entity`
- [ ] Verify repositories are auto-registered
- [ ] Check database tables exist

## Success Criteria

✅ All 4 entities created with correct properties and relationships
✅ All repositories created and auto-registered
✅ Database migration executed successfully
✅ PHPStan passes with level 8
✅ All relationships work correctly

## Next Phase

[Phase 3: Repositories & Services](./03-repositories-services.md)
