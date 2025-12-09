# Packmind Package Organization

This document defines the logical grouping of all Symfony standards and recipes for this project.

## Current Status

**Standards:** 15 total (11 in `symfony` package, 4 unassigned)
**Recipes:** 19 total (0 in any package)

### Standards NOT in symfony package (need to be added):
- `symfony-console-commands-best-practices`
- `symfony-event-system-best-practices`
- `symfony-internationalization-best-practices`
- `symfony-twig-components-best-practices`

---

## Package Structure

### 1. symfony-core
**Description:** Core Symfony framework patterns for controllers, configuration, and application structure.

**Standards:**
- `symfony-controllers-best-practices` - Controller implementation patterns
- `symfony-configuration-best-practices` - Service and environment configuration
- `symfony-business-logic-application-structure` - Business logic organization
- `symfony-console-commands-best-practices` - CLI command patterns
- `symfony-event-system-best-practices` - Event-driven architecture

**Recipes:**
- `add-a-crud-controller-action-in-symfony` - CRUD controller actions
- `add-a-new-page-with-template-in-symfony` - New page creation
- `add-a-console-command-in-symfony` - Console command creation
- `add-an-event-subscriber-in-symfony` - Event subscriber creation
- `add-flash-messages-for-user-feedback` - Flash message patterns

---

### 2. symfony-data
**Description:** Database layer patterns including Doctrine entities, repositories, and data management.

**Standards:**
- `symfony-entities-doctrine-best-practices` - Entity design and mapping
- `symfony-repository-query-patterns` - Query optimization patterns
- `symfony-string-component-slug-generation` - String utilities and slugs

**Recipes:**
- `add-a-doctrine-entity-with-relationships` - Entity creation
- `add-a-tag-system-to-entities` - Tagging functionality
- `add-a-comment-system-to-an-entity` - Comment system
- `add-pagination-to-a-list-page` - Pagination patterns
- `add-data-fixtures-for-testing` - Test data fixtures

---

### 3. symfony-forms
**Description:** Form handling, validation, and custom form components.

**Standards:**
- `symfony-forms-best-practices` - Form creation and handling

**Recipes:**
- `add-a-form-type-in-symfony` - Form type creation
- `add-a-custom-form-field-type` - Custom field types with data transformers

---

### 4. symfony-security
**Description:** Authentication, authorization, and security patterns.

**Standards:**
- `symfony-security-best-practices` - Security implementation patterns

**Recipes:**
- `add-user-authentication-with-login-form` - Login form authentication
- `add-a-voter-for-authorization-in-symfony` - Custom voter authorization
- `add-user-profile-management` - User profile features

---

### 5. symfony-frontend
**Description:** Frontend layer including Twig templates, Stimulus controllers, and internationalization.

**Standards:**
- `symfony-templates-twig-best-practices` - Template patterns
- `symfony-twig-components-best-practices` - Twig component patterns
- `symfony-stimulus-controllers-best-practices` - JavaScript interactivity
- `symfony-internationalization-best-practices` - Multi-language support

**Recipes:**
- `add-a-live-search-component-with-symfony-ux` - Live search with Symfony UX
- `add-an-rss-feed-in-symfony` - RSS feed generation
- `add-multi-language-support-i18n-in-symfony` - i18n implementation

---

### 6. symfony-testing
**Description:** Testing strategies, functional tests, and quality assurance.

**Standards:**
- `symfony-testing-best-practices` - Testing patterns and assertions

**Recipes:**
- `add-a-functional-test-in-symfony` - Functional test creation

---

## Summary Table

| Package | Standards | Recipes | Focus Area |
|---------|-----------|---------|------------|
| symfony-core | 5 | 5 | Framework fundamentals |
| symfony-data | 3 | 5 | Database & entities |
| symfony-forms | 1 | 2 | Form handling |
| symfony-security | 1 | 3 | Authentication & authorization |
| symfony-frontend | 4 | 3 | Templates & UI |
| symfony-testing | 1 | 1 | Quality assurance |
| **Total** | **15** | **19** | |

## Setup Instructions

To implement this organization in Packmind:

1. **Create packages** in Packmind web UI:
   - Navigate to Packages section
   - Create each package with name and description from above

2. **Assign standards** to packages:
   - Open each standard
   - Add to the appropriate package(s)

3. **Assign recipes** to packages:
   - Open each recipe
   - Add to the appropriate package(s)

4. **Pull updated configuration**:
   ```bash
   packmind-cli pull symfony-core
   packmind-cli pull symfony-data
   packmind-cli pull symfony-forms
   packmind-cli pull symfony-security
   packmind-cli pull symfony-frontend
   packmind-cli pull symfony-testing
   ```

## Alternative: Single Package Distribution

If you prefer to keep everything in a single `symfony` package, all 15 standards and 19 recipes can remain in the existing package. The current `symfony` package already contains 11 standards.

### Standards not yet in symfony package (add via Packmind UI):
- `symfony-console-commands-best-practices`
- `symfony-event-system-best-practices`
- `symfony-internationalization-best-practices`
- `symfony-twig-components-best-practices`

### Recipes not yet in any package (add via Packmind UI):
All 19 recipes need to be assigned to packages. See `recipes/README.md` for full list.

---

## Local File Structure

```
.packmind/
├── PACKAGE_ORGANIZATION.md    # This file
├── standards/                  # 15 standard files (pulled + manually added)
│   ├── symfony-business-logic-application-structure.md
│   ├── symfony-configuration-best-practices.md
│   ├── symfony-console-commands-best-practices.md
│   ├── symfony-controllers-best-practices.md
│   ├── symfony-entities-doctrine-best-practices.md
│   ├── symfony-event-system-best-practices.md
│   ├── symfony-forms-best-practices.md
│   ├── symfony-internationalization-best-practices.md
│   ├── symfony-repository-query-patterns.md
│   ├── symfony-security-best-practices.md
│   ├── symfony-stimulus-controllers-best-practices.md
│   ├── symfony-string-component-slug-generation.md
│   ├── symfony-templates-twig-best-practices.md
│   ├── symfony-testing-best-practices.md
│   └── symfony-twig-components-best-practices.md
└── recipes/
    └── README.md               # Index of all 19 recipes with categories
```
