# Services & Repositories

Business logic, data access patterns, and service organization.

## Repository Organization

```
src/Repository/
├── PostRepository.php      # Blog post queries
├── TagRepository.php       # Tag queries
└── UserRepository.php      # User queries (extends default)
```

## Service Organization

```
src/
├── Pagination/
│   └── Paginator.php              # Custom pagination utility
├── Event/
│   └── CommentCreatedEvent.php    # Domain event
├── EventSubscriber/
│   └── CommentNotificationSubscriber.php  # Event handler
├── Twig/
│   ├── AppExtension.php           # Twig functions
│   └── Components/
│       └── BlogSearchComponent.php # Live search component
└── Utils/
    ├── Markdown.php               # Markdown parser wrapper
    ├── MentionParser.php          # Extract @mentions
    └── Validator.php              # Utility validators
```

---

## Repositories

### PostRepository

**Location**: `src/Repository/PostRepository.php`
**Entity**: `Post`

```php
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findLatest(int $page = 1, ?Tag $tag = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('a', 't')
            ->innerJoin('p.author', 'a')
            ->leftJoin('p.tags', 't')
            ->where('p.publishedAt <= :now')
            ->orderBy('p.publishedAt', 'DESC')
            ->setParameter('now', new \DateTimeImmutable())
        ;

        if (null !== $tag) {
            $qb->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        return (new Paginator($qb))->paginate($page);
    }

    /**
     * @return Post[]
     */
    public function findBySearchQuery(string $query, int $limit = Paginator::PAGE_SIZE): array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === \count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.title LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        return $queryBuilder
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return string[]
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $terms = array_unique(u($searchQuery)->replaceMatches('/[[:space:]]+/', ' ')->trim()->split(' '));

        // Ignore search terms that are too short
        return array_filter($terms, static function ($term) {
            return 2 <= $term->length();
        });
    }
}
```

#### Key Methods

**`findLatest(int $page = 1, ?Tag $tag = null): Paginator`**

Purpose: Fetch latest published posts with optional tag filtering

Features:
- Eager loading of author and tags (JOIN)
- Filters out future posts (publishedAt <= now)
- Sort by publish date descending
- Tag filtering using MEMBER OF
- Pagination support

Query optimization:
- `addSelect('a', 't')` prevents N+1 queries
- `innerJoin` for author (required)
- `leftJoin` for tags (optional)

**`findBySearchQuery(string $query, int $limit = 10): array`**

Purpose: Search posts by title

Features:
- Extracts search terms from query
- Filters terms < 2 characters
- OR conditions for each term
- Limits results
- Newest first

Example:
- Query: "symfony demo"
- SQL: `WHERE p.title LIKE '%symfony%' OR p.title LIKE '%demo%'`

**`extractSearchTerms(string $searchQuery): array`**

Purpose: Parse search query into individual terms

Features:
- Normalizes whitespace
- Splits on spaces
- Removes duplicates
- Filters short terms (< 2 chars)

Example:
- Input: "  symfony   demo  "
- Output: `["symfony", "demo"]`

### UserRepository

**Location**: `src/Repository/UserRepository.php`
**Entity**: `User`

Minimal implementation using default Doctrine methods:
- `findOneBy(['username' => $username])`
- `findOneBy(['email' => $email])`

### TagRepository

**Location**: `src/Repository/TagRepository.php`
**Entity**: `Tag`

Minimal implementation for future extensibility.

Uses default Doctrine methods:
- `findOneBy(['name' => $tagName])`
- `findAll()`

---

## Pagination Service

### Paginator

**Location**: `src/Pagination/Paginator.php`

Custom pagination wrapper for Doctrine queries.

```php
final class Paginator
{
    public const PAGE_SIZE = 10;

    private int $currentPage = 1;
    private int $numResults = 0;

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly int $pageSize = self::PAGE_SIZE,
    ) {}

    public function paginate(int $page = 1): self
    {
        $this->currentPage = max(1, $page);
        $firstResult = ($this->currentPage - 1) * $this->pageSize;

        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        if (0 === \count($this->queryBuilder->getDQLPart('join'))) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $paginator = new DoctrinePaginator($query, true);
        $paginator->setUseOutputWalkers(false);

        $this->numResults = $paginator->count();

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return (int) ceil($this->numResults / $this->pageSize);
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    public function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage + 1);
    }

    public function hasToPaginate(): bool
    {
        return $this->numResults > $this->pageSize;
    }

    public function getNumResults(): int
    {
        return $this->numResults;
    }

    public function getResults(): iterable
    {
        return $this->queryBuilder->getQuery()->getResult();
    }
}
```

#### Features

**Constant page size**: 10 items per page

**Query optimization**:
- Uses Doctrine `CountWalker` for efficient counting
- Disables output walkers for better performance
- Fetch-joined associations prevent N+1 queries

**Pagination info**:
- Current page number
- Total number of results
- Last page number
- Previous/next page availability

**Usage**:

```php
$qb = $postRepository->createQueryBuilder('p')
    ->orderBy('p.publishedAt', 'DESC');

$paginator = (new Paginator($qb))->paginate($page);

// In template
foreach ($paginator->getResults() as $post) {
    // Display post
}

if ($paginator->hasPreviousPage()) {
    // Show previous link
}
```

---

## Event-Driven Architecture

### CommentCreatedEvent

**Location**: `src/Event/CommentCreatedEvent.php`

Domain event dispatched when a comment is created.

```php
final class CommentCreatedEvent
{
    public function __construct(
        private readonly Comment $comment,
    ) {}

    public function getComment(): Comment
    {
        return $this->comment;
    }
}
```

Simple value object carrying comment data.

### CommentNotificationSubscriber

**Location**: `src/EventSubscriber/CommentNotificationSubscriber.php`

Event listener that sends email notifications.

```php
final readonly class CommentNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private string $sender,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();

        $linkToPost = $this->router->generate('blog_post', [
            'slug' => $comment->getPost()->getSlug(),
            '_fragment' => 'comment_'.$comment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = sprintf('New comment on "%s"', $comment->getPost()->getTitle());

        $body = sprintf(
            '%s (by %s) said: %s',
            $comment->getAuthor()->getFullName(),
            $comment->getAuthor()->getUsername(),
            $comment->getContent()
        );
        $body .= sprintf("\n\nFull report: %s", $linkToPost);

        $email = (new Email())
            ->from($this->sender)
            ->to($comment->getPost()->getAuthor()->getEmail())
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}
```

#### Features

**Auto-wired dependencies**:
- MailerInterface for sending emails
- EntityManagerInterface for database access
- RouterInterface for URL generation
- Email sender from configuration

**Email content**:
- Subject: "New comment on {post title}"
- Body: Comment author, content
- Link to specific comment (with fragment #comment_123)

**URL generation**:
```php
$this->router->generate('blog_post', [
    'slug' => $comment->getPost()->getSlug(),
    '_fragment' => 'comment_'.$comment->getId(),
], UrlGeneratorInterface::ABSOLUTE_URL);
```

Creates: `https://example.com/en/blog/posts/post-slug#comment_123`

#### Service Configuration

Auto-configured as event subscriber (no manual registration needed).

Sender email injected via `bind` in `services.yaml`:

```yaml
services:
    _defaults:
        bind:
            string $sender: '%app.notifications.email_sender%'
```

---

## Twig Extensions

### AppExtension

**Location**: `src/Twig/AppExtension.php`

Provides custom Twig functions for templates.

```php
final class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly array $enabledLocales,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', $this->getLocales(...)),
            new TwigFunction('is_rtl', $this->isRtl(...)),
        ];
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->enabledLocales;
    }

    public function isRtl(string $locale): bool
    {
        return \in_array($locale, ['ar', 'he', 'fa'], true);
    }
}
```

#### Functions

**`locales()`**: Returns array of enabled locales

Usage in templates:
```twig
{% for locale in locales() %}
    <a href="{{ path('homepage', {_locale: locale}) }}">{{ locale }}</a>
{% endfor %}
```

**`is_rtl(locale)`**: Check if locale uses right-to-left text direction

Usage in templates:
```twig
<html dir="{{ is_rtl(app.request.locale) ? 'rtl' : 'ltr' }}">
```

---

## Live Components

### BlogSearchComponent

**Location**: `src/Twig/Components/BlogSearchComponent.php`

Live component for real-time blog search using Symfony UX.

```php
#[AsLiveComponent]
final class BlogSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly PostRepository $postRepository,
    ) {}

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        return $this->postRepository->findBySearchQuery($this->query);
    }
}
```

#### Features

**Live property**: `query` is writable (updates from frontend)

**Auto-refresh**: Component re-renders when `query` changes

**Search method**: Uses PostRepository's `findBySearchQuery()`

#### Template

**Location**: `templates/components/BlogSearchComponent.html.twig`

```twig
<div {{ attributes }}>
    <input type="search" name="query" value="{{ query }}"
           data-model="query">

    <div>
        {% for post in this.posts %}
            <article>
                <h2>{{ post.title }}</h2>
                <p>{{ post.summary }}</p>
            </article>
        {% endfor %}
    </div>
</div>
```

**`data-model="query"`**: Binds input to LiveProp

**`this.posts`**: Calls `getPosts()` method

#### Usage

```twig
{{ component('BlogSearchComponent') }}
```

Live search updates results as user types.

---

## Utility Services

### Markdown Parser

**Location**: `src/Utils/Markdown.php`

Wrapper for CommonMark markdown parser.

```php
final readonly class Markdown
{
    private MarkdownConverter $parser;

    public function __construct()
    {
        $this->parser = new MarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function parse(string $source): string
    {
        return $this->parser->convert($source)->getContent();
    }
}
```

**Configuration**:
- Strips HTML input (security)
- Disables unsafe links

**Usage in templates**:
```twig
{{ post.content|markdown_to_html }}
```

### MentionParser

**Location**: `src/Utils/MentionParser.php`

Extract @mentions from text.

```php
final class MentionParser
{
    /**
     * @return string[]
     */
    public function parse(string $text): array
    {
        preg_match_all('/@([\w]+)/', $text, $matches);

        return $matches[1] ?? [];
    }
}
```

**Example**:
- Input: "Great work @jane_admin and @tom_admin!"
- Output: `["jane_admin", "tom_admin"]`

### Validator Utilities

**Location**: `src/Utils/Validator.php`

Custom validation helpers.

```php
final class Validator
{
    public function validateUsername(string $username): void
    {
        if (!preg_match('/^[a-z_]+$/', $username)) {
            throw new \InvalidArgumentException('The username can only contain lowercase letters and underscores.');
        }
    }

    public function validatePassword(string $plainPassword): void
    {
        if (mb_strlen(trim($plainPassword)) < 6) {
            throw new \InvalidArgumentException('The password must be at least 6 characters long.');
        }
    }

    public function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The email should look like a real email.');
        }
    }

    public function validateFullName(string $fullName): void
    {
        if (mb_strlen(trim($fullName)) < 2) {
            throw new \InvalidArgumentException('The full name is too short.');
        }
    }
}
```

Used in console commands for validating user input.

---

## Service Configuration

### Auto-wiring & Auto-configuration

**Location**: `config/services.yaml`

```yaml
services:
    _defaults:
        autowire: true      # Automatically inject dependencies
        autoconfigure: true # Auto-register as services, event subscribers, etc.
        bind:
            array $enabledLocales: '%kernel.enabled_locales%'
            string $defaultLocale: '%app.locale%'

    App\:
        resource: '../src/'
```

**Features**:

**Autowiring**: Automatically resolve constructor dependencies

**Autoconfigure**: Auto-register services by interface:
- EventSubscriberInterface → Event subscriber
- TwigExtension → Twig extension
- LiveComponentInterface → Live component
- FormTypeInterface → Form type

**Bind**: Global argument injection:
- `$enabledLocales` → Available locales array
- `$defaultLocale` → Default locale string

**Resource**: Auto-discover all classes in `src/`

### Readonly Services

All custom services use `readonly` for immutability:

```php
final readonly class CommentNotificationSubscriber
{
    public function __construct(
        private MailerInterface $mailer,
        private RouterInterface $router,
    ) {}
}
```

**Benefits**:
- Properties cannot be modified after construction
- Thread-safe
- Clear intent

---

## Service Summary

| Service | Type | Purpose |
|---------|------|---------|
| PostRepository | Repository | Query blog posts |
| UserRepository | Repository | Query users |
| TagRepository | Repository | Query tags |
| Paginator | Utility | Paginate query results |
| CommentCreatedEvent | Event | Domain event for new comments |
| CommentNotificationSubscriber | Event Subscriber | Email notifications |
| AppExtension | Twig Extension | Custom Twig functions |
| BlogSearchComponent | Live Component | Real-time search |
| Markdown | Utility | Parse Markdown to HTML |
| MentionParser | Utility | Extract @mentions |
| Validator | Utility | Validate user input |

All services are:
- ✅ Auto-wired (automatic dependency injection)
- ✅ Auto-configured (automatic registration)
- ✅ Readonly (immutable after construction)
- ✅ Final (cannot be extended)
- ✅ Type-safe (constructor property promotion)