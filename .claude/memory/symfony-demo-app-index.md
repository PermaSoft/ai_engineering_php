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
- User authentication (form login, remember-me)
- Role-based access control (User, Admin)
- Admin user management (create users, switch user/impersonation)
- Tag-based content organization
- Multi-locale support (internationalization)
- Locale-aware homepage with navigation
- Live search functionality
- Email notifications
- RSS feed generation
- Console commands for user management

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

- **Backend**: Symfony 7.3, Doctrine ORM 3.0, PHP 8.2+
- **Frontend**: Bootstrap 5, Stimulus, Symfony UX Live Components
- **Database**: SQLite (dev/test), Doctrine DBAL 4.0
- **Testing**: PHPUnit 11.3, DAMA Test Bundle
- **Assets**: Symfony Asset Mapper, SASS compilation
- **Quality Tools**: PHPStan 2.0, PHP-CS-Fixer

## Detailed Documentation

For complete rebuilding instructions, consult these specialized documentation files:

### [Domain Model](./domain-model.md)
Complete entity specifications with properties, relationships, and validation rules:
- User entity (authentication, roles)
- Post entity (blog posts with tags and comments)
- Comment entity (with spam detection)
- Tag entity (content organization)

### [Security Architecture](./security-architecture.md)
Authentication and authorization implementation:
- Form login configuration
- Password hashing strategy
- Role hierarchy
- Custom voters for fine-grained access control
- CSRF protection
- Remember-me functionality

### [Controllers & Routing](./controllers-routing.md)
HTTP request handling patterns:
- Public blog controllers
- Admin panel controllers
- User profile management
- Route configuration with localization
- Entity value resolvers
- Current user injection

### [Forms & Validation](./forms-validation.md)
Form handling and user input validation:
- Form type classes
- Custom field types (DateTimePicker, TagsInput)
- Data transformers
- Validation constraints
- Form events for business logic

### [Services & Repositories](./services-repositories.md)
Business logic and data access:
- Repository query patterns
- Custom pagination service
- Event-driven architecture
- Service dependency injection
- Twig extensions

### [Templates & Frontend](./templates-frontend.md)
View layer and user interface:
- Template organization
- Layout inheritance
- Twig components
- Asset management
- Frontend JavaScript (Stimulus)

### [Testing Strategy](./testing-strategy.md)
Quality assurance approach:
- Functional tests
- Command tests
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