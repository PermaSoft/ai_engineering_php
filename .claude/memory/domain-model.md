# Domain Model - Entity Specifications

Complete specifications for all Doctrine entities in the Symfony Demo Application.

## Entity Relationships Overview

```
User
├── authored Posts (OneToMany)
└── authored Comments (OneToMany)

Post
├── author (ManyToOne → User)
├── comments (OneToMany → Comment, orphanRemoval, cascade persist)
└── tags (ManyToMany → Tag, cascade persist)

Comment
├── post (ManyToOne → Post)
└── author (ManyToOne → User)

Tag
└── posts (ManyToMany → Post)
```

## User Entity

**Location**: `src/Entity/User.php`
**Table**: `symfony_demo_user`
**Repository**: `App\Repository\UserRepository`

### Interfaces
- `Symfony\Component\Security\Core\User\UserInterface`
- `Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface`

### Properties

| Property | Type | Database Type | Nullable | Unique | Default | Validation |
|----------|------|--------------|----------|--------|---------|------------|
| `id` | `?int` | INTEGER | Auto-generated | Yes | `null` | None |
| `fullName` | `?string` | STRING | No | No | `null` | NotBlank |
| `username` | `?string` | STRING | No | Yes | `null` | NotBlank, Length(min:2, max:50) |
| `email` | `?string` | STRING | No | Yes | `null` | Email |
| `password` | `?string` | STRING | No | No | `null` | None (hashed) |
| `roles` | `array` | JSON | No | No | `[]` | None |

### Constants

```php
final public const ROLE_USER = 'ROLE_USER';
final public const ROLE_ADMIN = 'ROLE_ADMIN';
```

### Key Methods

**`getUserIdentifier(): string`**
- Returns username as user identifier for authentication

**`getRoles(): array`**
- Returns user roles
- Always guarantees at least `ROLE_USER` is present
- Returns unique array of roles

**`eraseCredentials(): void`**
- Removes sensitive data (not implemented as no plainPassword property)

**Serialization**:
- `__serialize()`: Returns `[$this->id, $this->username, $this->password]`
- `__unserialize(array $data)`: Restores id, username, password

### Notes
- Password is hashed using Symfony's auto password hasher
- Username is used as the user identifier (not email)
- Roles stored as JSON array in database

---

## Post Entity

**Location**: `src/Entity/Post.php`
**Table**: `symfony_demo_post`
**Repository**: `App\Repository\PostRepository`

### Properties

| Property | Type | Database Type | Nullable | Validation |
|----------|------|--------------|----------|------------|
| `id` | `?int` | INTEGER | Auto-generated | None |
| `title` | `?string` | STRING | No | NotBlank |
| `slug` | `?string` | STRING | No | UniqueEntity |
| `summary` | `?string` | STRING | No | NotBlank(message: 'post.blank_summary'), Length(max:255) |
| `content` | `?string` | TEXT | No | NotBlank(message: 'post.blank_content'), Length(min:10, minMessage: 'post.too_short_content') |
| `publishedAt` | `\DateTimeImmutable` | DATETIME | No | None |
| `author` | `?User` | ManyToOne | No | None |
| `comments` | `Collection<Comment>` | OneToMany | No | None |
| `tags` | `Collection<Tag>` | ManyToMany | No | Count(max:4, maxMessage: 'post.too_many_tags') |

### Relationships

**Author** (ManyToOne):
```php
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(nullable: false)]
private ?User $author = null;
```

**Comments** (OneToMany):
```php
#[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true, cascade: ['persist'])]
#[ORM\OrderBy(['publishedAt' => 'DESC'])]
private Collection $comments;
```
- Orphan removal enabled (deleting post deletes all comments)
- Cascade persist (persisting post persists new comments)
- Default sort: newest first

**Tags** (ManyToMany):
```php
#[ORM\ManyToMany(targetEntity: Tag::class, cascade: ['persist'])]
#[ORM\JoinTable(name: 'symfony_demo_post_tag')]
#[ORM\OrderBy(['name' => 'ASC'])]
private Collection $tags;
```
- Join table: `symfony_demo_post_tag`
- Cascade persist (persisting post persists new tags)
- Default sort: alphabetical

### Class-Level Validation

```php
#[UniqueEntity(fields: ['slug'], errorPath: 'title', message: 'post.slug_unique')]
```
- Ensures slug uniqueness
- Error message displayed on title field

### Constructor

```php
public function __construct()
{
    $this->publishedAt = new \DateTimeImmutable();
    $this->comments = new ArrayCollection();
    $this->tags = new ArrayCollection();
}
```
- Auto-sets publishedAt to current time
- Initializes empty collections

### Collection Management Methods

**Comments**:
- `getComments(): Collection`
- `addComment(Comment $comment): void` - Sets bidirectional relationship, prevents duplicates
- `removeComment(Comment $comment): void`

**Tags**:
- `getTags(): Collection`
- `addTag(Tag ...$tags): void` - Variadic method, prevents duplicates
- `removeTag(Tag $tag): void`

### Notes
- Slug is auto-generated in PostType form using FormEvents
- Published date defaults to "now" but can be scheduled for future
- Maximum 4 tags per post

---

## Comment Entity

**Location**: `src/Entity/Comment.php`
**Table**: `symfony_demo_comment`
**Repository**: Default Doctrine repository

### Properties

| Property | Type | Database Type | Nullable | Validation |
|----------|------|--------------|----------|------------|
| `id` | `?int` | INTEGER | Auto-generated | None |
| `post` | `?Post` | ManyToOne | No | None |
| `content` | `?string` | TEXT | No | NotBlank(message: 'comment.blank'), Length(min:5, minMessage: 'comment.too_short', max:10000, maxMessage: 'comment.too_long') |
| `publishedAt` | `\DateTimeImmutable` | DATETIME | No | None |
| `author` | `?User` | ManyToOne | No | None |

### Relationships

**Post** (ManyToOne):
```php
#[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
#[ORM\JoinColumn(nullable: false)]
private ?Post $post = null;
```
- Inverse side of Post's comments relationship

**Author** (ManyToOne):
```php
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(nullable: false)]
private ?User $author = null;
```

### Constructor

```php
public function __construct()
{
    $this->publishedAt = new \DateTimeImmutable();
}
```
- Auto-sets publishedAt to current time

### Spam Detection

```php
#[Assert\IsTrue(message: 'comment.is_spam')]
public function isLegitComment(): bool
{
    $containsInvalidCharacters = null !== u($this->content)->indexOf('@');
    return !$containsInvalidCharacters;
}
```
- Custom validation method
- Rejects comments containing '@' character
- Uses Symfony String component

### Notes
- Simple spam detection demonstrates custom validation
- Comment publication time is automatic
- Content limited to 10,000 characters

---

## Tag Entity

**Location**: `src/Entity/Tag.php`
**Table**: `symfony_demo_tag`
**Repository**: `App\Repository\TagRepository`

### Properties

| Property | Type | Database Type | Nullable | Unique | Readonly |
|----------|------|--------------|----------|--------|----------|
| `id` | `?int` | INTEGER | Auto-generated | Yes | No |
| `name` | `string` | STRING | No | Yes | Yes |

### Interfaces
- `\JsonSerializable` - Custom JSON encoding

### Constructor

```php
public function __construct(string $name)
{
    $this->name = $name;
}
```
- Name is required at construction
- Name is readonly (cannot be changed after creation)

### Methods

**`jsonSerialize(): string`**
```php
public function jsonSerialize(): string
{
    return $this->name;
}
```
- Returns just the name when JSON-encoded
- Used in form field rendering

**`__toString(): string`**
```php
public function __toString(): string
{
    return $this->name;
}
```
- String representation is the tag name

### Notes
- Tag is a value object (immutable after creation)
- Readonly property prevents accidental modification
- No setters provided for name
- Used in TagsInputType form field with custom data transformers

---

## Database Schema Notes

### Table Names
All tables prefixed with `symfony_demo_` to avoid conflicts:
- `symfony_demo_user`
- `symfony_demo_post`
- `symfony_demo_comment`
- `symfony_demo_tag`
- `symfony_demo_post_tag` (join table)

### Cascade Operations

**Post → Comments**:
- `orphanRemoval: true` - Deleting post deletes all comments
- `cascade: ['persist']` - Persisting post persists new comments

**Post → Tags**:
- `cascade: ['persist']` - Persisting post persists new tags
- No orphan removal (tags can exist without posts)

### Indexes
Doctrine automatically creates indexes for:
- Primary keys (id columns)
- Foreign keys (author_id, post_id)
- Unique constraints (username, email, slug, tag name)

## Fixture Data Summary

**Users** (3):
```
jane_admin (ROLE_ADMIN) - jane_admin@symfony.com
tom_admin (ROLE_ADMIN) - tom_admin@symfony.com
john_user (ROLE_USER) - john_user@symfony.com
Password: "kitten" (all users)
```

**Tags** (9):
```
lorem, ipsum, consectetur, adipiscing, incididunt,
labore, voluptate, dolore, pariatur
```

**Posts** (30):
- 30 blog posts with Lorem Ipsum titles
- Each post: 2-4 random tags, 5 comments
- Authors: jane_admin or tom_admin
- Published dates: last 30 days

**Comments** (150):
- 5 comments per post
- All authored by john_user
- Random Lorem Ipsum content

## Migration Strategy

When recreating this application:

1. Create User entity first (no dependencies)
2. Create Tag entity (no dependencies)
3. Create Post entity (depends on User, Tag)
4. Create Comment entity (depends on User, Post)
5. Generate migration: `php bin/console make:migration`
6. Run migration: `php bin/console doctrine:migrations:migrate`
7. Load fixtures: `php bin/console doctrine:fixtures:load`
