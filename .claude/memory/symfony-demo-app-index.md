# Symfony Demo Application - Master Index

## Overview

This is the official Symfony Demo Application - a reference implementation showcasing Symfony best practices for building modern web applications. It's a full-featured blogging platform with authentication, content management, and commenting functionality.

**Purpose**: Educational reference application demonstrating production-ready Symfony patterns
**Type**: Multi-user blogging platform
**Framework**: Symfony 7.3
**PHP**: >=8.2

## Core Features

- Blog post management (CRUD operations)
- Comment system with spam detection
- User authentication (form login, remember-me, CSRF protection)
- Role-based access control (User, Admin)
- Admin user management (create users, switch user/impersonation)
- Tag-based content organization
- **Multi-locale support (38 languages with language selector)** ✅ NEW
- **RTL support for Arabic/Hebrew/Farsi** ✅ NEW
- Locale-aware homepage with navigation
- Live search functionality
- Email notifications (with i18n support)
- **RSS feed generation** ✅ NEW
- Console commands for user management
- **Professional error pages (403, 404, 500)** ✅ NEW
- **Code syntax highlighting** ✅ NEW

## Recent Enhancements (Rebuilding Branch)

### Security Hardening (Plan 01)
- Logout CSRF protection enabled
- Password change triggers logout for security
- Remember-me now requires user opt-in (not always-on)
- Switch user feature restricted to dev environment only
- Password field autocomplete attributes for browser security
- Password maximum length constraint (128 chars) to prevent DoS

### Error Handling (Plan 02)
- Custom error pages for 403 Forbidden, 404 Not Found, 500 Server Error
- User-friendly error messages with contextual navigation
- Translation support for error pages
- Consistent branding with base layout

### Entity Improvements (Plan 03)
- Uses Doctrine `Types` constants (Types::INTEGER, Types::STRING, etc.)
- Null-safety in User::getUserIdentifier()
- Improved code quality and IDE support

### Template Architecture (Plans 04-06)
- Enhanced base layout with proper navigation and sidebar structure
- Admin-specific layout template (`admin/layout.html.twig`)
- Blog UI partials: `_post.html.twig`, `_post_tags.html.twig`, `_comment.html.twig`, `_comment_form.html.twig`
- Flash messages extracted to partial (`default/_flash_messages.html.twig`)
- Two-column layout structure with content and sidebar blocks
- Bootstrap 5 consistent usage throughout

### Form Enhancements (Plan 07)
- Flatpickr date/time picker with locale support
- Tag autocomplete via `buildView()` method
- Optimized `TagArrayToStringTransformer` (eliminates N+1 queries)
- Enhanced password field security attributes

### Testing Infrastructure (Plans 08-10)
- `AbstractCommandTestCase` base class for CLI testing
- `TestUtilities` factory methods for test data
- Comprehensive CLI command tests (AddUserCommand, ListUsersCommand, DeleteUserCommand)
- Form transformer unit tests
- Validator utility tests
- DAMA Doctrine Test Bundle integration for database transaction management

### Internationalization (Plan 11)
- 38 translation files in XLIFF 2.0 format (`messages+intl-icu.*.xlf`)
- Language selector UI component (`default/_language_selector.html.twig`)
- `RedirectToPreferredLocaleSubscriber` for automatic locale detection from browser
- RTL CSS support (`_rtl.scss`) for Arabic, Farsi, and Hebrew
- Translated email notifications via `TranslatorInterface`

### Frontend Assets (Plan 12)
- Flatpickr 4.6.13 for date picking
- Highlight.js 11.9.0 for code syntax highlighting
- Tabler Icons 2.44.0 icon library (25+ icons)
- Admin-specific JavaScript entrypoint (`admin.js`)
- Stimulus controllers: `flatpickr_controller.js`, `csrf_controller.js`, `login_controller.js`
- RTL and theme SCSS files

### Event Subscribers (Plan 13)
- `CheckRequirementsSubscriber` - PHP/Symfony version validation
- `ControllerSubscriber` - Global template variables injection
- `RedirectToPreferredLocaleSubscriber` - Locale detection and redirection
- `CommentNotificationSubscriber` - Email notifications with translation support

### Content Syndication (Plan 14)
- RSS 2.0 feed implementation (`/rss.xml`)
- RSS template with proper XML structure (`blog/index.xml.twig`)
- RSS auto-discovery meta tag in base layout
- Feed validation compliant with RSS 2.0 specification

## Quick Reference

### Application Entry Points

- **Homepage**: `/` or `/{_locale}/` - Locale-aware landing page with navigation to blog and admin sections
- **Public Blog**: `/blog/` - Browse posts, view individual posts, add comments (when authenticated)
- **Admin Panel**: `/admin/post/` - Create, edit, delete posts (ROLE_ADMIN only)
- **Admin Users**: `/admin/users/` - Manage users, create new users, switch user (ROLE_ADMIN only)
- **User Profile**: `/profile/edit` - Manage user profile and password (ROLE_USER)
- **Login**: `/login` - Authentication page
- **Search**: `/blog/search` - Search posts by keywords

### Key Technology Stack

#### Backend
- **PHP:** 8.2+ with strict types
- **Symfony:** 7.2+ framework
- **Doctrine ORM:** 3.0+ for database access
- **Twig:** 3.0+ templating engine

#### Frontend
- **Bootstrap:** 5.3.8 CSS framework
- **Stimulus:** 3.2.2 JavaScript framework
- **Flatpickr:** 4.6.13 date picker
- **Highlight.js:** 11.9.0 code syntax
- **Tabler Icons:** 2.44.0 icon library
- **Turbo:** 7.3.0 fast navigation

#### Testing
- **PHPUnit:** 11.3 testing framework
- **DAMA:** Doctrine test bundle
- **WebTestCase:** Functional testing

#### Quality Tools
- **PHPStan:** 2.0 static analysis
- **PHP-CS-Fixer:** Code style
- **Symfony Debug Toolbar:** Development

## Detailed Documentation

For complete rebuilding instructions, consult these specialized documentation files:

### [Domain Model](./domain-model.md)
Complete entity specifications with properties, relationships, and validation rules:
- User entity (authentication, roles)
- Post entity (blog posts with tags and comments)
- Comment entity (with spam detection)
- Tag entity (content organization)
- **Entity improvements with Types constants** ✅

### [Security Architecture](./security-architecture.md)
Authentication and authorization implementation:
- Form login configuration
- Password hashing strategy
- Role hierarchy
- Custom voters for fine-grained access control
- **CSRF protection (logout, forms)** ✅
- **Remember-me with opt-in** ✅
- **Security hardening measures** ✅

### [Controllers & Routing](./controllers-routing.md)
HTTP request handling patterns:
- Public blog controllers
- Admin panel controllers
- User profile management
- Route configuration with localization
- Entity value resolvers
- Current user injection
- **RSS feed implementation** ✅

### [Forms & Validation](./forms-validation.md)
Form handling and user input validation:
- Form type classes
- **Custom widgets (Flatpickr, TagsInput)** ✅
- **Data transformers (optimized)** ✅
- Validation constraints
- Form events for business logic
- **Password security enhancements** ✅

### [Services & Repositories](./services-repositories.md)
Business logic and data access:
- Repository query patterns
- Custom pagination service
- **Event subscribers (CheckRequirements, Controller, Locale, Notification)** ✅
- Service dependency injection
- Twig extensions

### [Templates & Frontend](./templates-frontend.md)
View layer and user interface:
- Template architecture and layouts
- **Internationalization (38 languages)** ✅
- **Blog UI partials and components** ✅
- **Error pages (403, 404, 500)** ✅
- **Frontend assets (Flatpickr, Highlight.js, Tabler Icons)** ✅
- **Stimulus controllers** ✅
- **RTL support** ✅
- **RSS feed template** ✅

### [Testing Strategy](./testing-strategy.md)
Quality assurance approach:
- **Testing infrastructure (AbstractCommandTestCase, TestUtilities)** ✅
- **Command tests (comprehensive coverage)** ✅
- **Unit tests (transformers, validators)** ✅
- Functional tests
- Test data fixtures
- Database transaction management
- Test organization patterns

### [Configuration & Setup](./configuration-setup.md)
Environment and dependency configuration:
- Service container setup
- Package dependencies
- Environment variables
- Parameter configuration
- Database migrations

## Directory Structure

```
/
├── config/               # Application configuration
│   ├── packages/        # Bundle-specific config
│   ├── routes/          # Route definitions
│   └── services.yaml    # Service container config
├── public/              # Web root for assets
├── src/                 # Application source code
│   ├── Command/         # Console commands
│   ├── Controller/      # HTTP request handlers
│   ├── Entity/          # Doctrine domain models
│   ├── Event/           # Custom events
│   ├── EventSubscriber/ # Event listeners
│   ├── Form/            # Form type definitions
│   ├── Pagination/      # Pagination logic
│   ├── Repository/      # Doctrine repositories
│   ├── Security/        # Authorization voters
│   ├── Twig/            # Twig extensions
│   └── Utils/           # Utility classes
├── templates/           # Twig templates
├── tests/               # PHPUnit test suite
├── translations/        # i18n files
└── migrations/          # Database migrations
```

## Data Model Summary

**Users** (3 fixtures):
- jane_admin, tom_admin (ROLE_ADMIN)
- john_user (ROLE_USER)
- All passwords: "kitten"

**Posts** (30 fixtures):
- Each post has title, slug, summary, content, publish date
- Authored by admin users
- 2-4 tags per post
- 5 comments per post

**Tags** (9 fixtures):
- lorem, ipsum, consectetur, adipiscing, incididunt, labore, voluptate, dolore, pariatur

**Comments**:
- 150 total (5 per post)
- All authored by john_user
- Simple spam detection (rejects content with '@')

## Packmind Standards Applied

This application follows comprehensive Symfony best practices as defined in the Packmind standards:

- ✓ Symfony Business Logic & Application Structure
- ✓ Symfony Configuration Best Practices
- ✓ Symfony Controllers Best Practices
- ✓ Symfony Entities & Doctrine Best Practices
- ✓ Symfony Security Best Practices
- ✓ Symfony Forms Best Practices
- ✓ Symfony Templates & Twig Best Practices
- ✓ Symfony Testing Best Practices

See CLAUDE.md for full standard details.

## Rebuild Workflow

To rebuild this application from scratch:

1. **Setup** - Review [Configuration & Setup](./configuration-setup.md) for dependencies and environment
2. **Domain** - Implement entities from [Domain Model](./domain-model.md)
3. **Data** - Create repositories and services from [Services & Repositories](./services-repositories.md)
4. **Security** - Implement authentication from [Security Architecture](./security-architecture.md)
5. **Forms** - Build form types from [Forms & Validation](./forms-validation.md)
6. **Controllers** - Implement request handlers from [Controllers & Routing](./controllers-routing.md)
7. **Views** - Create templates from [Templates & Frontend](./templates-frontend.md)
8. **Tests** - Add test coverage from [Testing Strategy](./testing-strategy.md)
9. **Data** - Load fixtures and verify functionality

## Additional Resources

- **Official Docs**: https://symfony.com/doc/current/
- **Demo Repo**: https://github.com/symfony/demo
- **Best Practices**: https://symfony.com/doc/current/best_practices.html