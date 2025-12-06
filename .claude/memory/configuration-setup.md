# Configuration & Setup

Environment configuration, dependencies, and project setup.

## Project Setup from Scratch

### 1. Install Symfony

```bash
# Create new Symfony project
composer create-project symfony/skeleton symfony-demo

cd symfony-demo

# Install webapp pack (includes essential bundles)
composer require webapp
```

### 2. Install Dependencies

```bash
# Core framework bundles
composer require symfony/apache-pack
composer require symfony/asset-mapper
composer require symfony/console
composer require symfony/form
composer require symfony/mailer
composer require symfony/security-bundle
composer require symfony/translation
composer require symfony/validator

# Doctrine ORM
composer require doctrine/orm
composer require doctrine/doctrine-bundle
composer require doctrine/doctrine-migrations-bundle

# Twig templating
composer require symfony/twig-bundle
composer require twig/extra-bundle
composer require twig/intl-extra
composer require twig/markdown-extra

# UX components
composer require symfony/stimulus-bundle
composer require symfony/ux-icons
composer require symfony/ux-live-component

# Utilities
composer require league/commonmark
composer require symfonycasts/sass-bundle
composer require twbs/bootstrap

# Development tools
composer require --dev symfony/debug-bundle
composer require --dev symfony/maker-bundle
composer require --dev symfony/web-profiler-bundle
composer require --dev symfony/stopwatch

# Testing
composer require --dev phpunit/phpunit
composer require --dev symfony/browser-kit
composer require --dev symfony/css-selector
composer require --dev doctrine/doctrine-fixtures-bundle
composer require --dev dama/doctrine-test-bundle

# Code quality
composer require --dev phpstan/phpstan
composer require --dev phpstan/phpstan-doctrine
composer require --dev phpstan/phpstan-symfony
```

---

## Dependencies

### Production Dependencies

**Core Framework** (`composer.json`):

```json
{
    "require": {
        "php": ">=8.2",
        "ext-ctype": "*",
        "ext-iconv": "*",
        "ext-pdo_sqlite": "*",

        "symfony/apache-pack": "^1.0",
        "symfony/asset": "^7",
        "symfony/asset-mapper": "^7",
        "symfony/console": "^7",
        "symfony/dotenv": "^7",
        "symfony/expression-language": "^7",
        "symfony/flex": "^2",
        "symfony/form": "^7",
        "symfony/framework-bundle": "^7",
        "symfony/html-sanitizer": "^7",
        "symfony/http-client": "^7",
        "symfony/intl": "^7",
        "symfony/mailer": "^7",
        "symfony/monolog-bundle": "^3.7",
        "symfony/runtime": "^7",
        "symfony/security-bundle": "^7",
        "symfony/stimulus-bundle": "^2.12",
        "symfony/string": "^7",
        "symfony/translation": "^7",
        "symfony/twig-bundle": "^7",
        "symfony/ux-icons": "^2.20",
        "symfony/ux-live-component": "^2.6",
        "symfony/validator": "^7",
        "symfony/yaml": "^7",

        "doctrine/dbal": "^4.0",
        "doctrine/doctrine-bundle": "^2.11",
        "doctrine/doctrine-migrations-bundle": "^3.3",
        "doctrine/orm": "^3.0",

        "league/commonmark": "^2.1",
        "symfonycasts/sass-bundle": "^0.7",
        "twbs/bootstrap": "^5",
        "twig/extra-bundle": "^3.3",
        "twig/intl-extra": "^3.3",
        "twig/markdown-extra": "^3.3"
    }
}
```

### Development Dependencies

```json
{
    "require-dev": {
        "dama/doctrine-test-bundle": "^8.0.2",
        "doctrine/doctrine-fixtures-bundle": "^3.5",

        "phpstan/phpstan": "^2.0",
        "phpstan/phpstan-doctrine": "^2.0",
        "phpstan/phpstan-symfony": "^2.0",

        "phpunit/phpunit": "^11.3",

        "symfony/browser-kit": "^7",
        "symfony/css-selector": "^7",
        "symfony/debug-bundle": "^7",
        "symfony/maker-bundle": "^1.36",
        "symfony/stopwatch": "^7",
        "symfony/web-profiler-bundle": "^7"
    }
}
```

**Platform Requirements**:

```json
{
    "config": {
        "platform": {
            "php": "8.2.0"
        }
    }
}
```

---

## Configuration Files

### Service Container

**Location**: `config/services.yaml`

```yaml
parameters:
    # Application parameters
    app.locale: 'en'
    app.notifications.email_sender: anonymous@example.com

services:
    _defaults:
        # Auto-inject dependencies in constructor
        autowire: true

        # Auto-register services by interface (event subscribers, form types, etc.)
        autoconfigure: true

        # Bind scalar arguments globally
        bind:
            array $enabledLocales: '%kernel.enabled_locales%'
            string $defaultLocale: '%app.locale%'

    # Auto-discover all classes in src/ as services
    App\:
        resource: '../src/'
```

**Features**:
- **Parameters**: App-level config values (locale, email sender)
- **Autowiring**: Automatic dependency injection
- **Autoconfigure**: Auto-register by interface
- **Bind**: Global scalar argument injection
- **Resource**: Auto-discover services

### Framework Configuration

**Location**: `config/packages/framework.yaml`

```yaml
framework:
    secret: '%env(APP_SECRET)%'
    http_method_override: false
    handle_all_throwables: true
    php_errors:
        log: true

    # Session management
    session:
        handler_id: null
        cookie_secure: auto
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native

    # Form CSRF protection
    csrf_protection: true

    # HTTP cache
    http_cache: true

    # Asset mapper for modern asset management
    asset_mapper:
        paths:
            - assets/
        excluded_patterns:
            - '*.scss'
```

### Security Configuration

**Location**: `config/packages/security.yaml`

See [Security Architecture](./security-architecture.md) for complete details.

```yaml
security:
    # Password hashing
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    # User provider
    providers:
        database_users:
            entity:
                class: App\Entity\User
                property: username

    # Firewall
    firewalls:
        main:
            lazy: true
            provider: database_users
            form_login:
                login_path: security_login
                check_path: security_login
                enable_csrf: true
            logout:
                path: security_logout
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800

    # Access control
    access_control:
        - { path: ^/admin/, role: ROLE_ADMIN }
        - { path: ^/profile/, role: ROLE_USER }

    # Role hierarchy
    role_hierarchy:
        ROLE_ADMIN: ROLE_USER
```

### Doctrine Configuration

**Location**: `config/packages/doctrine.yaml`

```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        profiling_collect_backtrace: '%kernel.debug%'
        use_savepoints: true

    orm:
        auto_generate_proxy_classes: true
        enable_lazy_ghost_objects: true
        report_fields_where_declared: true
        validate_xml_mapping: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        mappings:
            App:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
        controller_resolver:
            auto_mapping: true
```

**Features**:
- **Database URL**: From environment variable
- **Profiling**: In debug mode only
- **Savepoints**: Transaction savepoint support
- **Lazy ghost objects**: Performance optimization
- **Attribute mapping**: PHP attributes for entity mapping
- **Auto-mapping**: Automatically discover entities in `src/Entity`

### Twig Configuration

**Location**: `config/packages/twig.yaml`

```yaml
twig:
    file_name_pattern: '*.twig'
    form_themes:
        - 'form/fields.html.twig'  # Custom form field rendering
    globals:
        # Global variables accessible in all templates
```

### Translation Configuration

**Location**: `config/packages/translation.yaml`

```yaml
framework:
    default_locale: en
    translator:
        default_path: '%kernel.project_dir%/translations'
        fallbacks:
            - en
```

**Supported Locales**:
`en|fr|de|es|cs|nl|ru|uk|ro|pt_BR|pl|it|ja|id|ca|sl|hr|zh_CN|bg|tr|lt`

### Routing Configuration

**Location**: `config/routes.yaml`

```yaml
controllers:
    resource:
        path: ../src/Controller/
        namespace: App\Controller
    type: attribute
    prefix: /{_locale}
    requirements:
        _locale: '%app.supported_locales%'
    defaults:
        _locale: '%app.locale%'
```

**Features**:
- Attribute-based routing
- Locale prefix on all routes
- Locale requirement regex
- Default locale fallback

### Asset Mapper Configuration

**Location**: `config/packages/asset_mapper.yaml`

```yaml
framework:
    asset_mapper:
        paths:
            - assets/
        excluded_patterns:
            - '*.scss'
```

SASS files compiled separately via `sass:build` command.

---

## Environment Variables

### .env File

**Location**: `.env`

```bash
# Application environment
APP_ENV=dev
APP_SECRET=your-secret-key-here

# Database
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# Mailer
MAILER_DSN=null://null
```

### .env.local (not committed)

**Location**: `.env.local` (gitignored)

Machine-specific overrides:

```bash
DATABASE_URL="mysql://user:pass@localhost/dbname"
MAILER_DSN=smtp://localhost
```

### Test Environment

**Location**: `.env.test`

```bash
APP_ENV=test
SYMFONY_DEPRECATIONS_HELPER=disabled
DATABASE_URL="sqlite:///:memory:"
```

**Features**:
- In-memory SQLite database
- Deprecations disabled

---

## Directory Structure

```
project/
├── assets/                  # Frontend assets
│   ├── app.js              # JavaScript entry point
│   ├── styles/             # SASS files
│   └── controllers/        # Stimulus controllers
├── bin/
│   └── console             # Symfony console
├── config/
│   ├── packages/           # Bundle configuration
│   ├── routes/             # Route definitions
│   ├── services.yaml       # Service container
│   └── routes.yaml         # Main routing config
├── migrations/             # Database migrations
├── public/
│   └── index.php           # Front controller
├── src/
│   ├── Command/            # Console commands
│   ├── Controller/         # HTTP controllers
│   ├── DataFixtures/       # Test data
│   ├── Entity/             # Doctrine entities
│   ├── Event/              # Domain events
│   ├── EventSubscriber/    # Event listeners
│   ├── Form/               # Form types
│   ├── Pagination/         # Pagination logic
│   ├── Repository/         # Doctrine repositories
│   ├── Security/           # Voters
│   ├── Twig/               # Twig extensions & components
│   ├── Utils/              # Utilities
│   └── Kernel.php          # Application kernel
├── templates/              # Twig templates
├── tests/                  # PHPUnit tests
├── translations/           # i18n files
├── var/
│   ├── cache/              # Cache
│   └── log/                # Logs
├── vendor/                 # Composer dependencies
├── .env                    # Environment variables
├── composer.json           # Dependencies
├── composer.lock           # Locked versions
├── importmap.php           # Asset import map
├── phpunit.xml.dist        # PHPUnit config
└── symfony.lock            # Symfony Flex lock file
```

---

## Database Setup

### Create Database

```bash
# Development
php bin/console doctrine:database:create

# Test
php bin/console doctrine:database:create --env=test
```

### Run Migrations

```bash
# Development
php bin/console doctrine:migrations:migrate

# Test
php bin/console doctrine:migrations:migrate --env=test
```

### Load Fixtures

```bash
# Development
php bin/console doctrine:fixtures:load

# Test
php bin/console doctrine:fixtures:load --env=test
```

### Create Migration

```bash
php bin/console make:migration
```

---

## Asset Management

### Install Assets

```bash
php bin/console importmap:install
```

### Build SASS

```bash
php bin/console sass:build

# Watch mode
php bin/console sass:build --watch
```

### Asset Mapper

Modern asset management without Node.js:

**Location**: `importmap.php`

```php
return [
    'app' => [
        'path' => 'app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.2',
    ],
];
```

---

## Console Commands

### Built-in Commands

```bash
# Cache clear
php bin/console cache:clear

# Cache warmup
php bin/console cache:warmup

# List routes
php bin/console debug:router

# List services
php bin/console debug:container

# Show env vars
php bin/console debug:dotenv

# Show config
php bin/console debug:config framework
```

### Custom Commands

**Create User**:
```bash
php bin/console app:add-user username password email@example.com --admin
```

**List Users**:
```bash
php bin/console app:list-users
```

---

## Development Server

### Symfony CLI (Recommended)

```bash
# Install Symfony CLI
brew install symfony-cli/tap/symfony-cli

# Start server
symfony server:start

# Stop server
symfony server:stop

# Check requirements
symfony check:requirements
```

### PHP Built-in Server

```bash
php -S localhost:8000 -t public/
```

---

## Code Quality Tools

### PHPStan (Static Analysis)

```bash
vendor/bin/phpstan analyse src/

# With configuration
vendor/bin/phpstan analyse -c phpstan.neon
```

**Configuration**: `phpstan.neon`

```neon
parameters:
    level: 8
    paths:
        - src
        - tests
    excludePaths:
        - src/Kernel.php
```

### PHP-CS-Fixer (Code Style)

```bash
# Check
vendor/bin/php-cs-fixer fix --dry-run

# Fix
vendor/bin/php-cs-fixer fix
```

**Configuration**: `.php-cs-fixer.dist.php`

```php
<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    );
```

### PHPUnit (Testing)

```bash
# Run all tests
php bin/phpunit

# With coverage
XDEBUG_MODE=coverage php bin/phpunit --coverage-html coverage/
```

---

## Production Deployment

### Optimize for Production

```bash
# Set environment to prod
export APP_ENV=prod

# Install dependencies (no dev)
composer install --no-dev --optimize-autoloader

# Clear and warmup cache
php bin/console cache:clear
php bin/console cache:warmup

# Build assets
php bin/console asset-map:compile
php bin/console sass:build
```

### Web Server Configuration

**Apache** (`.htaccess` in `public/`):

Automatically handled by `symfony/apache-pack`.

**Nginx**:

```nginx
server {
    server_name domain.com;
    root /var/www/project/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

### Environment Variables in Production

Use server environment or `.env.local`:

```bash
APP_ENV=prod
APP_SECRET=your-production-secret
DATABASE_URL=mysql://user:pass@localhost/dbname
MAILER_DSN=smtp://mailserver:587
```

**Never commit** `.env.local` or secrets to version control.

---

## Troubleshooting

### Clear Cache

```bash
php bin/console cache:clear
```

### Fix Permissions

```bash
chmod -R 777 var/
```

### Regenerate Autoload

```bash
composer dump-autoload
```

### Database Issues

```bash
# Drop and recreate
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

---

## Configuration Summary

| File | Purpose |
|------|---------|
| `config/services.yaml` | Service container, parameters |
| `config/packages/framework.yaml` | Framework core config |
| `config/packages/security.yaml` | Authentication & authorization |
| `config/packages/doctrine.yaml` | Database & ORM |
| `config/packages/twig.yaml` | Template engine |
| `config/routes.yaml` | Routing configuration |
| `.env` | Environment variables |
| `composer.json` | Dependencies |
| `phpunit.xml.dist` | Test configuration |
| `importmap.php` | Frontend assets |

---

## Quick Start Checklist

- [ ] Install Symfony CLI
- [ ] Create project: `symfony new symfony-demo`
- [ ] Install dependencies: `composer install`
- [ ] Configure `.env.local` with database URL
- [ ] Create database: `php bin/console doctrine:database:create`
- [ ] Run migrations: `php bin/console doctrine:migrations:migrate`
- [ ] Load fixtures: `php bin/console doctrine:fixtures:load`
- [ ] Build assets: `php bin/console sass:build`
- [ ] Start server: `symfony server:start`
- [ ] Visit: `https://localhost:8000`
- [ ] Login with: `jane_admin` / `kitten`