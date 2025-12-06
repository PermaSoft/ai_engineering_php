# Phase 1: Project Setup & Configuration

## Overview

**Goal**: Initialize Symfony project with all dependencies, configuration files, and environment setup
**Complexity**: Low
**Dependencies**: None
**Estimated Files**: 15-20 configuration files

## Memory Files Required

- **Primary**: [Configuration & Setup](../.claude/memory/configuration-setup.md)
- **Reference**: [Symfony Demo App Index](../.claude/memory/symfony-demo-app-index.md)

## Packmind Standards Applied

- Symfony Configuration Best Practices (`.packmind/standards/symfony-configuration-best-practices.md`)

## Implementation Checklist

### 1. Project Initialization

- [ ] Create new Symfony project using Composer
  - Command: `composer create-project symfony/skeleton symfony-demo`
  - Version: Symfony 7.3
  - PHP requirement: >= 8.2

- [ ] Install webapp pack
  - Command: `composer require webapp`
  - Provides: Twig, form, validator, security-bundle, translation

### 2. Core Dependencies

- [ ] Install framework bundles
  - [ ] `symfony/apache-pack`
  - [ ] `symfony/asset-mapper`
  - [ ] `symfony/console`
  - [ ] `symfony/form`
  - [ ] `symfony/mailer`
  - [ ] `symfony/security-bundle`
  - [ ] `symfony/translation`
  - [ ] `symfony/validator`

- [ ] Install Doctrine ORM
  - [ ] `doctrine/orm` (^3.0)
  - [ ] `doctrine/doctrine-bundle` (^2.11)
  - [ ] `doctrine/doctrine-migrations-bundle` (^3.3)
  - [ ] `doctrine/dbal` (^4.0)

- [ ] Install Twig bundles
  - [ ] `symfony/twig-bundle`
  - [ ] `twig/extra-bundle`
  - [ ] `twig/intl-extra`
  - [ ] `twig/markdown-extra`

- [ ] Install UX components
  - [ ] `symfony/stimulus-bundle`
  - [ ] `symfony/ux-icons`
  - [ ] `symfony/ux-live-component`

- [ ] Install utilities
  - [ ] `league/commonmark`
  - [ ] `symfonycasts/sass-bundle`
  - [ ] `twbs/bootstrap` (^5)

### 3. Development Dependencies

- [ ] Install development tools
  - [ ] `symfony/debug-bundle` --dev
  - [ ] `symfony/maker-bundle` --dev
  - [ ] `symfony/web-profiler-bundle` --dev
  - [ ] `symfony/stopwatch` --dev

- [ ] Install testing dependencies
  - [ ] `phpunit/phpunit` (^11.3) --dev
  - [ ] `symfony/browser-kit` --dev
  - [ ] `symfony/css-selector` --dev
  - [ ] `doctrine/doctrine-fixtures-bundle` --dev
  - [ ] `dama/doctrine-test-bundle` (^8.0.2) --dev

- [ ] Install code quality tools
  - [ ] `phpstan/phpstan` (^2.0) --dev
  - [ ] `phpstan/phpstan-doctrine` (^2.0) --dev
  - [ ] `phpstan/phpstan-symfony` (^2.0) --dev

### 4. Configuration Files

#### 4.1 Service Container

- [ ] Configure `config/services.yaml`
  - [ ] Add parameters section
    - [ ] `app.locale: 'en'`
    - [ ] `app.notifications.email_sender: anonymous@example.com`
  - [ ] Configure service defaults
    - [ ] `autowire: true`
    - [ ] `autoconfigure: true`
  - [ ] Add bind section
    - [ ] `array $enabledLocales: '%kernel.enabled_locales%'`
    - [ ] `string $defaultLocale: '%app.locale%'`
  - [ ] Auto-discover services in `src/`
    - [ ] `App\: resource: '../src/'`

#### 4.2 Framework Configuration

- [ ] Configure `config/packages/framework.yaml`
  - [ ] Set secret from `%env(APP_SECRET)%`
  - [ ] Configure session
    - [ ] `handler_id: null`
    - [ ] `cookie_secure: auto`
    - [ ] `cookie_samesite: lax`
    - [ ] `storage_factory_id: session.storage.factory.native`
  - [ ] Enable CSRF protection
  - [ ] Enable HTTP cache
  - [ ] Configure asset mapper
    - [ ] Paths: `assets/`
    - [ ] Excluded patterns: `'*.scss'`

#### 4.3 Doctrine Configuration

- [ ] Configure `config/packages/doctrine.yaml`
  - [ ] DBAL settings
    - [ ] `url: '%env(resolve:DATABASE_URL)%'`
    - [ ] `profiling_collect_backtrace: '%kernel.debug%'`
    - [ ] `use_savepoints: true`
  - [ ] ORM settings
    - [ ] `auto_generate_proxy_classes: true`
    - [ ] `enable_lazy_ghost_objects: true`
    - [ ] `report_fields_where_declared: true`
    - [ ] `validate_xml_mapping: true`
    - [ ] `naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware`
    - [ ] `auto_mapping: true`
  - [ ] Entity mappings
    - [ ] Type: `attribute`
    - [ ] Dir: `'%kernel.project_dir%/src/Entity'`
    - [ ] Prefix: `'App\Entity'`

#### 4.4 Security Configuration (Minimal for now)

- [ ] Configure `config/packages/security.yaml`
  - [ ] Password hashers: `'auto'` for `PasswordAuthenticatedUserInterface`
  - [ ] Providers: Entity provider (will be configured in Phase 4)
  - [ ] Firewalls: Main firewall skeleton (will be configured in Phase 4)

#### 4.5 Twig Configuration

- [ ] Configure `config/packages/twig.yaml`
  - [ ] `file_name_pattern: '*.twig'`
  - [ ] Form themes: `['form/fields.html.twig']`

#### 4.6 Translation Configuration

- [ ] Configure `config/packages/translation.yaml`
  - [ ] `default_locale: en`
  - [ ] `default_path: '%kernel.project_dir%/translations'`
  - [ ] Fallbacks: `[en]`
  - [ ] Supported locales: `en|fr|de|es|cs|nl|ru|uk|ro|pt_BR|pl|it|ja|id|ca|sl|hr|zh_CN|bg|tr|lt`

#### 4.7 Routing Configuration

- [ ] Configure `config/routes.yaml`
  - [ ] Resource: `../src/Controller/`
  - [ ] Type: `attribute`
  - [ ] Prefix: `/{_locale}`
  - [ ] Requirements: `_locale: '%app.supported_locales%'`
  - [ ] Defaults: `_locale: '%app.locale%'`

#### 4.8 Asset Mapper Configuration

- [ ] Configure `config/packages/asset_mapper.yaml`
  - [ ] Paths: `['assets/']`
  - [ ] Excluded patterns: `['*.scss']`

### 5. Environment Variables

- [ ] Create `.env` file
  - [ ] `APP_ENV=dev`
  - [ ] `APP_SECRET=` (generate random secret)
  - [ ] `DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"`
  - [ ] `MAILER_DSN=null://null`

- [ ] Create `.env.test` file
  - [ ] `APP_ENV=test`
  - [ ] `SYMFONY_DEPRECATIONS_HELPER=disabled`
  - [ ] `DATABASE_URL="sqlite:///:memory:"`

- [ ] Add `.env.local` to `.gitignore` (should already be there)

### 6. Code Quality Configuration

- [ ] Create `phpstan.neon`
  - [ ] Level: 8
  - [ ] Paths: `[src, tests]`
  - [ ] Exclude: `src/Kernel.php`

- [ ] Create `.php-cs-fixer.dist.php`
  - [ ] Rules: `@Symfony`, `@PSR12`
  - [ ] Finder: `src/`, `tests/`

- [ ] Verify `phpunit.xml.dist` exists
  - [ ] Test suites configured
  - [ ] Bootstrap file set
  - [ ] DAMA Doctrine Test Bundle extension enabled

### 7. Asset Management

- [ ] Create `importmap.php`
  - [ ] Define `app` entry point
  - [ ] Add `@hotwired/stimulus` (^3.2.2)
  - [ ] Add `bootstrap` (^5.3.2)

- [ ] Create `assets/` directory structure
  - [ ] `assets/app.js` (JavaScript entry point)
  - [ ] `assets/styles/` (SASS files directory)
  - [ ] `assets/controllers/` (Stimulus controllers directory)

- [ ] Run `php bin/console importmap:install`

### 8. Directory Structure

- [ ] Verify/create all required directories
  - [ ] `src/Command/`
  - [ ] `src/Controller/`
  - [ ] `src/DataFixtures/`
  - [ ] `src/Entity/`
  - [ ] `src/Event/`
  - [ ] `src/EventSubscriber/`
  - [ ] `src/Form/`
  - [ ] `src/Pagination/`
  - [ ] `src/Repository/`
  - [ ] `src/Security/`
  - [ ] `src/Twig/`
  - [ ] `src/Utils/`
  - [ ] `templates/`
  - [ ] `tests/`
  - [ ] `translations/`
  - [ ] `migrations/`
  - [ ] `public/`
  - [ ] `var/cache/`
  - [ ] `var/log/`

### 9. Database Setup

- [ ] Create database
  - Command: `php bin/console doctrine:database:create`

- [ ] Verify database connection works

### 10. Verification

- [ ] Run Symfony requirements checker
  - Command: `symfony check:requirements`
  - All requirements should pass

- [ ] Clear cache
  - Command: `php bin/console cache:clear`

- [ ] Verify service container compiles
  - Command: `php bin/console debug:container`
  - Should list all auto-discovered services

- [ ] Verify routing works
  - Command: `php bin/console debug:router`
  - Should show locale-prefixed routes

- [ ] Start development server
  - Command: `symfony server:start` or `php -S localhost:8000 -t public/`
  - Should start without errors

- [ ] Verify web root accessible
  - Visit: `http://localhost:8000`
  - Should show Symfony welcome page

## Success Criteria

✅ All dependencies installed successfully
✅ All configuration files created and valid
✅ Service container compiles without errors
✅ Database created successfully
✅ Development server starts and serves Symfony welcome page
✅ PHPStan passes with level 8 (may have warnings, but no errors)
✅ All directories created according to structure

## Next Phase

Once this phase is complete, proceed to [Phase 2: Domain Model](./02-domain-model.md) to implement the Doctrine entities.

## Notes

- This phase establishes the foundation for all subsequent phases
- All configuration follows Symfony 7.3 best practices
- Security configuration is minimal - it will be expanded in Phase 4
- No business logic is implemented in this phase
- After this phase, the application should run but have no functionality yet
