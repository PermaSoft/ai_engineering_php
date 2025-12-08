# Plan 15: Memory Files Update

**Priority:** 🟦 FINAL
**Estimated Time:** 4-5 hours
**Dependencies:** Plans 01-14 completed
**Status:** Ready to execute

---

## Context & Objective

Comprehensively update all memory files to document:
1. All new patterns implemented (Plans 01-14)
2. Error handling architecture
3. Internationalization system
4. Testing infrastructure and patterns
5. Form widgets and enhancements
6. Event subscribers
7. RSS feed implementation
8. Frontend assets and Stimulus controllers

This ensures the memory files accurately reflect the complete rebuilt application.

---

## Reference Materials

### Memory Files to Update
- `.claude/memory/symfony-demo-app-index.md` - Master index
- `.claude/memory/domain-model.md` - Entity fixes
- `.claude/memory/security-architecture.md` - Security hardening
- `.claude/memory/controllers-routing.md` - RSS, routes
- `.claude/memory/forms-validation.md` - Form enhancements
- `.claude/memory/services-repositories.md` - Event subscribers
- `.claude/memory/templates-frontend.md` - Templates, i18n, assets
- `.claude/memory/testing-strategy.md` - Test infrastructure
- `.claude/memory/configuration-setup.md` - Config updates

### Plans Completed
Review all plans 01-14 for documentation updates needed.

---

## Prerequisites

- All plans 01-14 completed and verified
- Changes committed to git
- Understanding of all implemented features

---

## Deliverables Checklist

### Documentation Updates
- [ ] Master index updated with new features
- [ ] Domain model updated with entity fixes
- [ ] Security architecture updated with fixes
- [ ] Controllers updated with RSS route
- [ ] Forms updated with widget enhancements
- [ ] Services updated with event subscribers
- [ ] Templates updated with i18n, partials, assets
- [ ] Testing updated with infrastructure
- [ ] Configuration updated with new settings

### Verification
- [ ] All cross-references correct
- [ ] Code examples match actual implementation
- [ ] No broken links between files
- [ ] Complete feature coverage

---

## Implementation Steps

### Step 1: Update Master Index

**File:** `.claude/memory/symfony-demo-app-index.md`

Add/update these sections:

```markdown
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
- Password change triggers logout
- Removed always-remember-me
- Switch user restricted to dev environment
- Password field autocomplete attributes
- Password maximum length constraint

### Error Handling (Plan 02)
- Custom error pages for 403, 404, 500
- User-friendly error messages
- Contextual navigation on errors
- Translation support

### Entity Improvements (Plan 03)
- Uses Doctrine Types constants (Types::INTEGER, etc.)
- Null-safety in User::getUserIdentifier()
- Improved documentation

### Template Architecture (Plan 04-06)
- Enhanced base layout with navigation
- Admin-specific layout template
- Blog UI partials (post, tags, comments)
- Flash messages partial
- Two-column layout structure
- Bootstrap 5 consistent usage

### Form Enhancements (Plan 07)
- Flatpickr date/time picker
- Tag autocomplete via buildView
- Optimized TagArrayToStringTransformer (no N+1)
- Enhanced password security

### Testing Infrastructure (Plan 08-10)
- AbstractCommandTestCase base class
- TestUtilities factory methods
- Comprehensive CLI command tests
- Form transformer unit tests
- Validator utility tests
- DAMA doctrine test bundle integration

### Internationalization (Plan 11)
- 38 translation files (XLF format)
- Language selector UI component
- RedirectToPreferredLocaleSubscriber
- RTL CSS support
- Translated email notifications

### Frontend Assets (Plan 12)
- Flatpickr date picker
- Highlight.js syntax highlighting
- Tabler icons library
- Admin-specific JavaScript entrypoint
- Stimulus controllers (csrf, login, flatpickr)

### Event Subscribers (Plan 13)
- CheckRequirementsSubscriber (version validation)
- ControllerSubscriber (global template variables)
- RedirectToPreferredLocaleSubscriber (locale handling)
- CommentNotificationSubscriber (with translator)

### Content Syndication (Plan 14)
- RSS 2.0 feed implementation
- RSS auto-discovery meta tag
- Feed validation compliant

## Technology Stack

### Backend
- **PHP:** 8.2+ with strict types
- **Symfony:** 7.2+ framework
- **Doctrine ORM:** 3.0+ for database access
- **Twig:** 3.0+ templating engine

### Frontend
- **Bootstrap:** 5.3.8 CSS framework
- **Stimulus:** 3.2.2 JavaScript framework
- **Flatpickr:** 4.6.13 date picker
- **Highlight.js:** 11.9.0 code syntax
- **Tabler Icons:** 2.44.0 icon library
- **Turbo:** 7.3.0 fast navigation

### Testing
- **PHPUnit:** 11.3 testing framework
- **DAMA:** Doctrine test bundle
- **WebTestCase:** Functional testing

### Quality Tools
- **PHPStan:** 2.0 static analysis
- **PHP-CS-Fixer:** Code style
- **Symfony Debug Toolbar:** Development

## Quick Start

```bash
# Install dependencies
composer install
php bin/console importmap:install

# Setup database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Start server
symfony server:start

# Run tests
php bin/phpunit

# Generate coverage
php bin/phpunit --coverage-html var/coverage
```
```

---

### Step 2: Update Security Architecture

**File:** `.claude/memory/security-architecture.md`

Add section on security hardening:

```markdown
## Security Hardening (Post-Rebuild)

### Logout CSRF Protection

**File:** `config/packages/security.yaml`

```yaml
firewalls:
    main:
        logout:
            path: security_logout
            enable_csrf: true  # ✅ Protects against CSRF attacks
```

### Password Change Security

After password change, users are automatically logged out:

```php
public function changePassword(
    Request $request,
    #[CurrentUser] User $user,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager,
    Security $security,
): Response {
    // ... password update logic ...

    // ✅ SECURITY: Logout user after password change
    return $security->logout(validateCsrfToken: false)
        ?? $this->redirectToRoute('homepage');
}
```

### Remember-Me Configuration

```yaml
remember_me:
    secret: '%kernel.secret%'
    lifetime: 604800  # 1 week
    always_remember_me: false  # ✅ User must opt-in
```

### Switch User Restriction

```yaml
# config/packages/dev/security.yaml
security:
    firewalls:
        main:
            switch_user: true  # ✅ Only available in dev
```

### Password Field Security

```php
// ChangePasswordType
'attr' => [
    'autocomplete' => 'new-password',  # Browser security
],
'constraints' => [
    new Length([
        'min' => 6,
        'max' => 128,  # ✅ Prevents DoS attacks
    ]),
],
```
```

---

### Step 3: Update Forms & Validation

**File:** `.claude/memory/forms-validation.md`

Add comprehensive form widget documentation as outlined in Plans 07.

---

### Step 4: Update Templates & Frontend

**File:** `.claude/memory/templates-frontend.md`

This is the largest update. Add:

1. **Error Page Architecture** (from Plan 02)
2. **Template Hierarchy** (from Plan 04)
3. **Blog Template Partials** (from Plan 06)
4. **Internationalization System** (from Plan 11)
5. **Asset Management** (from Plan 12)
6. **RSS Feed** (from Plan 14)

Example sections:

```markdown
## Internationalization

### Language Selector Component

**Location:** `templates/default/_language_selector.html.twig`

Dropdown menu with 38 supported locales displaying native language names.

**Usage:**
```twig
<ul class="navbar-nav">
    {% include 'default/_language_selector.html.twig' %}
</ul>
```

### Translation Files

**Location:** `translations/messages+intl-icu.*.xlf`

Format: XLIFF 2.0 (XML Localization Interchange File Format)

38 supported locales:
- Western: en, de, es, fr, it, nl, pt, pt_BR
- Eastern European: be, bg, bs, cs, hr, hu, pl, ro, ru, sk, sl, sq, sr_Cyrl, sr_Latn, uk
- Asian: ja, zh_CN, zh_TW, vi, id, ne
- Middle Eastern: ar, fa, tr
- Other: ca, el, eu, lt

### RTL Support

**File:** `assets/styles/_rtl.scss`

Automatically applied when locale is Arabic (ar), Farsi (fa), or Hebrew (he).

Features:
- Reversed text direction
- Mirrored layouts
- Adjusted margins/padding
- Correct border directions

### Locale Detection

**Subscriber:** `RedirectToPreferredLocaleSubscriber`

- Detects browser language from Accept-Language header
- Redirects root URL (/) to localized homepage
- Falls back to default locale (en)

## Frontend Assets

### Asset Management

**File:** `importmap.php`

Dependencies:
- Bootstrap 5.3.8
- Stimulus 3.2.2
- Turbo 7.3.0
- Flatpickr 4.6.13
- Highlight.js 11.9.0
- Tabler Icons 2.44.0

### Entrypoints

**app.js** - Main application:
- Bootstrap initialization
- Stimulus controllers
- Syntax highlighting
- Turbo navigation

**admin.js** - Admin-specific:
- Extends app.js
- Delete confirmations
- Auto-hide alerts
- Admin UI enhancements

### Stimulus Controllers

**flatpickr_controller.js**
- Date/time picker initialization
- Locale auto-detection
- Configurable via data attributes

**csrf_controller.js**
- CSRF token handling for AJAX
- Automatic token injection

**login_controller.js**
- Remember username
- Auto-focus fields
- Show/hide password

### Code Syntax Highlighting

**Library:** Highlight.js 11.9.0

Automatically highlights `<pre><code>` blocks.

Supported languages: PHP, JavaScript, SQL, YAML, JSON, etc.

Theme: GitHub (light theme)

## RSS Feed

### RSS Route

**URL:** `/rss.xml`

**Controller:** `BlogController::rss()`

Returns latest blog posts in RSS 2.0 format.

### RSS Template

**File:** `templates/blog/index.xml.twig`

Features:
- RSS 2.0 compliant
- Atom self-link namespace
- Localized metadata
- Post tags as categories
- Author information
- Absolute URLs
- RFC 2822 date format

### Auto-Discovery

```html
<link rel="alternate" type="application/rss+xml"
      title="Symfony Demo Blog"
      href="{{ url('blog_rss') }}">
```

Enables browser auto-detection of RSS feed.
```

---

### Step 5: Update Testing Strategy

**File:** `.claude/memory/testing-strategy.md`

Add all testing infrastructure patterns from Plans 08-10.

---

### Step 6: Update Services & Repositories

**File:** `.claude/memory/services-repositories.md`

Add event subscriber documentation from Plan 13.

---

### Step 7: Update Master Index Table of Contents

Ensure all links work and point to correct sections:

```markdown
## Detailed Documentation

### [Domain Model](./domain-model.md)
- Entity specifications and relationships
- Validation rules and constraints
- **Entity improvements with Types constants** ✅

### [Security Architecture](./security-architecture.md)
- Authentication and authorization
- **Security hardening measures** ✅
- **CSRF protection** ✅
- Voter implementation

### [Controllers & Routing](./controllers-routing.md)
- Request handling patterns
- **RSS feed implementation** ✅
- Entity value resolvers

### [Forms & Validation](./forms-validation.md)
- Form type classes
- **Custom widgets (Flatpickr, TagsInput)** ✅
- **Data transformers** ✅
- Validation constraints

### [Services & Repositories](./services-repositories.md)
- Business logic patterns
- **Event subscribers** ✅
- Repository query patterns

### [Templates & Frontend](./templates-frontend.md)
- Template architecture
- **Internationalization (38 languages)** ✅
- **Blog UI partials** ✅
- **Error pages** ✅
- **Frontend assets** ✅
- **Stimulus controllers** ✅

### [Testing Strategy](./testing-strategy.md)
- **Testing infrastructure** ✅
- **Command tests** ✅
- **Unit tests** ✅
- Test patterns and best practices
```

---

## Verification Criteria

### Completeness Check

For each plan 01-14:
- [ ] Key features documented
- [ ] Code examples included
- [ ] File locations specified
- [ ] Usage patterns explained

### Cross-Reference Validation

- [ ] All internal links work
- [ ] Plan references are accurate
- [ ] Code examples match actual code
- [ ] No orphaned sections

### Accuracy Verification

- [ ] File paths correct
- [ ] Code syntax valid
- [ ] Commands work
- [ ] Configuration accurate

### Review Checklist

Read through each memory file and verify:
1. Is this section up to date?
2. Does it reference new features?
3. Are examples current?
4. Are there missing sections?
5. Do links work?

---

## Context Reset Information

**Files Modified:**
All 9 memory files updated with comprehensive documentation of Plans 01-14.

**Verification:**
- All memory files reviewed
- Cross-references validated
- Code examples verified
- Links tested
- Complete feature coverage

**Next Plan:** `16-final-validation.md`

---

## Completion Checklist

- [ ] Master index updated
- [ ] Domain model updated
- [ ] Security architecture updated
- [ ] Controllers & routing updated
- [ ] Forms & validation updated
- [ ] Services & repositories updated
- [ ] Templates & frontend updated (largest update)
- [ ] Testing strategy updated
- [ ] Configuration & setup updated
- [ ] All cross-references validated
- [ ] Code examples match implementation
- [ ] No broken links
- [ ] Complete feature coverage
- [ ] Git commit created:
  ```
  docs: update all memory files with Plans 01-14 implementations

  - Document security hardening measures
  - Document error handling architecture
  - Document template architecture and partials
  - Document form widget enhancements
  - Document testing infrastructure
  - Document internationalization system (38 languages)
  - Document frontend assets and Stimulus controllers
  - Document event subscribers
  - Document RSS feed implementation
  - Update master index with all new features
  - Validate cross-references and links

  Memory files now accurately reflect the complete rebuilt application.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 16