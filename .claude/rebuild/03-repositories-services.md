# Phase 3: Repositories & Services

## Overview

**Goal**: Create repositories, business services, pagination, and Twig extensions
**Complexity**: Medium
**Dependencies**: Phase 1, 2
**Estimated Files**: 10-12 files

## Memory Files Required

- **Primary**: [Services & Repositories](../memory/services-repositories.md)
- **Reference**: [Domain Model](../memory/domain-model.md)

## Packmind Standards Applied

- Symfony Business Logic & Application Structure
- Symfony Configuration Best Practices

## Implementation Checklist

### 1. Repository Methods

- [ ] **UserRepository** (`src/Repository/UserRepository.php`)
  - [ ] Implement `PasswordUpgraderInterface`
  - [ ] `upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void`

- [ ] **PostRepository** (`src/Repository/PostRepository.php`)
  - [ ] `findLatest(int $page = 1, string $tag = null): Pagerfanta`
  - [ ] `createQueryBuilder()` methods for posts
  - [ ] Tag filtering support

- [ ] **TagRepository** (`src/Repository/TagRepository.php`)
  - [ ] `findByName(string $name): ?Tag`
  - [ ] Tag lookup methods

### 2. Pagination Service

- [ ] Create `src/Pagination/Paginator.php`
  - [ ] Wrapper around Pagerfanta library
  - [ ] `paginate(QueryBuilder $queryBuilder, int $page = 1, int $limit = Paginator::PAGE_SIZE): Pagerfanta`
  - [ ] `PAGE_SIZE` constant (default: 13)
  - [ ] Add as readonly service with constructor property promotion

### 3. Event System

- [ ] Create domain events in `src/Event/`
  - [ ] `CommentCreatedEvent.php` - dispatched when comment is created

- [ ] Create event subscribers in `src/EventSubscriber/`
  - [ ] `CommentNotificationSubscriber.php` - sends email notifications
  - [ ] Implement `EventSubscriberInterface`
  - [ ] Subscribe to `CommentCreatedEvent`
  - [ ] Inject `MailerInterface`

### 4. Twig Extensions

- [ ] Create `src/Twig/SourceCodeExtension.php`
  - [ ] Twig extension for showing source code
  - [ ] `show_source_code(Controller $controller, string $method): string`
  - [ ] Returns file contents for code display

- [ ] Create Twig components in `src/Twig/Components/`
  - [ ] Components for reusable UI elements (if using UX Live Components)

### 5. Utility Classes

- [ ] Create `src/Utils/Slugger.php` (if needed)
  - [ ] Service for generating slugs from titles
  - [ ] Uses Symfony String component

- [ ] Create `src/Utils/Markdown.php` (if needed)
  - [ ] Service for parsing markdown content
  - [ ] Uses league/commonmark

### 6. Service Configuration

- [ ] Verify all services auto-registered in `config/services.yaml`
- [ ] Add any needed bind parameters
- [ ] Configure event subscribers

### 7. Verification

- [ ] Run: `php bin/console debug:container` - verify all services registered
- [ ] Run: `php bin/console debug:event-dispatcher` - verify event subscribers
- [ ] Run: `php bin/console debug:twig` - verify Twig extensions
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/`

## Success Criteria

✅ All repository methods implemented
✅ Pagination service working
✅ Event system configured
✅ Twig extensions registered
✅ All services auto-discovered
✅ PHPStan passes level 8

## Next Phase

[Phase 4: Security System](./04-security-system.md)
