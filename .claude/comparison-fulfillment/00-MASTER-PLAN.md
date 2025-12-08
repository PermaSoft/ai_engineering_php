P# Comparison Fulfillment: Master Plan

**Objective:** Systematically add all missing functionality from `walkthrought` branch to `rebuilding` branch while preserving improvements made in rebuilding.

**Strategy:** Incremental, context-resettable plan execution with verification at each step.

---

## Current State Summary

**Rebuilding Branch Status:** 57% complete
- ✅ Core entities and domain model
- ✅ Basic controllers and routing
- ✅ Forms and validation (with gaps)
- ✅ Admin user management (NEW)
- ✅ Strict type declarations
- ✅ Modern code quality

**Critical Gaps:**
- 🔴 Security vulnerabilities (5 issues)
- 🔴 Missing 42% of templates
- 🔴 Zero internationalization
- 🔴 36% reduced test coverage
- 🔴 Missing UX enhancements

---

## Plan Execution Order

Plans are ordered by **priority** and **dependency**. Each plan is self-contained and can be executed after a context reset by reading only the plan file and referenced memory files.

```
Priority 1: CRITICAL (Security & Stability)
├── Plan 01: Security Hardening
├── Plan 02: Error Handling & Pages
└── Plan 03: Entity & Repository Fixes

Priority 2: HIGH (Core Features)
├── Plan 04: Template Architecture & Base Layout
├── Plan 05: Admin Interface Polish
├── Plan 06: Blog UI Components & Partials
└── Plan 07: Form Enhancements & Widgets

Priority 3: MEDIUM (Testing & Quality)
├── Plan 08: Testing Infrastructure
├── Plan 09: Command Line Interface Tests
└── Plan 10: Form & Utility Tests

Priority 4: STANDARD (UX & Polish)
├── Plan 11: Internationalization System
├── Plan 12: Frontend Assets & Components
├── Plan 13: Event Subscribers & Cross-Cutting
└── Plan 14: RSS & Content Syndication

Priority 5: FINAL (Documentation)
├── Plan 15: Memory Files Update
└── Plan 16: Final Validation & Integration
```

---

## Plan Structure

Each plan follows this template:

```markdown
# Plan XX: [Feature Name]

## Context & Objective
[What needs to be built and why]

## Reference Materials
- Memory files: [List of relevant memory files]
- Comparison report: [Relevant sections]
- Walkthrought files: [Key files to reference]

## Prerequisites
[What must be completed before this plan]

## Deliverables Checklist
- [ ] Code artifacts
- [ ] Test files
- [ ] Documentation updates
- [ ] Memory file updates
- [ ] Configuration changes

## Implementation Steps
[Ordered steps with file paths and key decisions]

## Verification Criteria
[How to verify the plan was completed successfully]

## Context Reset Information
[Minimal info needed to continue after context reset]
```

---

## Plan Summaries

### Priority 1: CRITICAL

#### Plan 01: Security Hardening
**Files:** 5 files to modify
**Tests:** Security configuration validation
**Objective:** Fix 5 critical security vulnerabilities
- Add logout CSRF protection
- Implement logout after password change
- Disable always-remember-me
- Restrict switch_user to dev environment
- Add autocomplete="off" to password fields
- Add password max length constraint

**Memory Files:**
- `.claude/memory/security-architecture.md`
- `.claude/memory/forms-validation.md`

**Estimated Time:** 2-3 hours

---

#### Plan 02: Error Handling & Pages
**Files:** 4 new templates, 1 config update
**Tests:** Error page rendering tests
**Objective:** Create professional error pages
- 403 Forbidden page
- 404 Not Found page
- 500 Server Error page
- Generic error page
- Update memory files with error handling patterns

**Memory Files:**
- `.claude/memory/templates-frontend.md` (to update)
- New section in memory files

**Estimated Time:** 3-4 hours

---

#### Plan 03: Entity & Repository Fixes
**Files:** 4 entities, 3 repositories
**Tests:** Update entity tests
**Objective:** Fix technical debt in entities
- Replace string literals with Types constants
- Add null-safety cast to User::getUserIdentifier()
- Fix return types and documentation
- Update memory files

**Memory Files:**
- `.claude/memory/domain-model.md`

**Estimated Time:** 2 hours

---

### Priority 2: HIGH

#### Plan 04: Template Architecture & Base Layout
**Files:** 3 templates (base, admin layout, flash messages)
**Tests:** Template rendering tests
**Objective:** Restore professional template architecture
- Enhance base.html.twig with proper navigation
- Create admin/layout.html.twig
- Extract flash messages partial
- Add sidebar block structure
- Fix Bootstrap inconsistencies

**Memory Files:**
- `.claude/memory/templates-frontend.md`

**Estimated Time:** 4-5 hours

---

#### Plan 05: Admin Interface Polish
**Files:** 4 admin templates, CSS additions
**Tests:** Admin UI integration tests
**Objective:** Professional admin interface
- Add form partials (_form.html.twig, _delete_form.html.twig)
- Enhance admin/blog templates with proper styling
- Add action buttons and modals
- Create admin-specific CSS

**Memory Files:**
- `.claude/memory/templates-frontend.md`
- `.claude/memory/controllers-routing.md`

**Estimated Time:** 5-6 hours

---

#### Plan 06: Blog UI Components & Partials
**Files:** 5-6 blog partials, updated templates
**Tests:** Blog component rendering tests
**Objective:** Restore blog UI modularity
- Create _post.html.twig partial
- Create _post_tags.html.twig partial
- Create _comment.html.twig partial
- Create _comment_form.html.twig partial
- Create _rss.html.twig partial
- Update blog/index.html.twig to use partials
- Update blog/post_show.html.twig

**Memory Files:**
- `.claude/memory/templates-frontend.md`

**Estimated Time:** 4-5 hours

---

#### Plan 07: Form Enhancements & Widgets
**Files:** 3 form types, 2 templates, JS assets
**Tests:** Form widget tests, transformer tests
**Objective:** Restore sophisticated form UX
- Enhance DateTimePickerType with Flatpickr
- Add buildView to TagsInputType for autocomplete
- Fix ChangePasswordType mapping and validation
- Review UserType username editability
- Add form field templates

**Memory Files:**
- `.claude/memory/forms-validation.md`

**Estimated Time:** 5-6 hours

---

### Priority 3: MEDIUM

#### Plan 08: Testing Infrastructure
**Files:** PHPUnit config, test utilities, bootstrap
**Tests:** N/A (this creates test infrastructure)
**Objective:** Enhance testing foundation
- Update PHPUnit configuration
- Create AbstractCommandTestCase
- Add test fixtures and utilities
- Create test data factories

**Memory Files:**
- `.claude/memory/testing-strategy.md`

**Estimated Time:** 3-4 hours

---

#### Plan 09: Command Line Interface Tests
**Files:** 3 test files for commands
**Tests:** 8 test methods
**Objective:** Full CLI command test coverage
- tests/Command/AddUserCommandTest.php
- tests/Command/ListUsersCommandTest.php
- Test interactive and non-interactive modes
- Test email notification options

**Memory Files:**
- `.claude/memory/testing-strategy.md`

**Estimated Time:** 4-5 hours

---

#### Plan 10: Form & Utility Tests
**Files:** 2 test files
**Tests:** 17 test methods
**Objective:** Unit test coverage for forms and utils
- tests/Form/DataTransformer/TagArrayToStringTransformerTest.php (6 tests)
- tests/Utils/ValidatorTest.php (11 tests)
- Edge case coverage
- Exception handling verification

**Memory Files:**
- `.claude/memory/testing-strategy.md`
- `.claude/memory/forms-validation.md`

**Estimated Time:** 5-6 hours

---

### Priority 4: STANDARD

#### Plan 11: Internationalization System
**Files:** 38 translation files, language selector, RTL support
**Tests:** Translation loading tests
**Objective:** Restore full i18n support
- Create default/_language_selector.html.twig
- Add translations/messages+intl-icu.*.xlf for all locales
- Add RTL CSS support (_rtl.scss)
- Update templates to use translation keys
- Add locale redirection subscriber
- Update CommentNotificationSubscriber with translator

**Memory Files:**
- `.claude/memory/templates-frontend.md` (to update with i18n section)
- New i18n section in memory files

**Estimated Time:** 8-10 hours

---

#### Plan 12: Frontend Assets & Components
**Files:** Asset imports, icons, controllers, CSS
**Tests:** Asset loading tests
**Objective:** Restore frontend sophistication
- Add Tabler icons library (25+ icons)
- Add Flatpickr date picker
- Add Bootstrap TagsInput widget
- Add Highlight.js for code syntax
- Create admin.js entrypoint
- Add Stimulus controllers (login, csrf)
- Add RTL and theme SCSS files

**Memory Files:**
- `.claude/memory/templates-frontend.md`
- `.claude/memory/configuration-setup.md`

**Estimated Time:** 6-8 hours

---

#### Plan 13: Event Subscribers & Cross-Cutting
**Files:** 3 event subscribers
**Tests:** Event subscriber tests
**Objective:** Restore cross-cutting concerns
- RedirectToPreferredLocaleSubscriber (i18n routing)
- CheckRequirementsSubscriber (version checking)
- ControllerSubscriber (global template vars)
- Update memory files with event patterns

**Memory Files:**
- `.claude/memory/services-repositories.md` (to update)

**Estimated Time:** 4-5 hours

---

#### Plan 14: RSS & Content Syndication
**Files:** 1 template, controller updates
**Tests:** RSS feed validation tests
**Objective:** Add content syndication
- Create blog/index.xml.twig RSS template
- Update BlogController RSS route handling
- Add RSS feed link component
- Test feed validation

**Memory Files:**
- `.claude/memory/controllers-routing.md`
- `.claude/memory/templates-frontend.md`

**Estimated Time:** 2-3 hours

---

### Priority 5: FINAL

#### Plan 15: Memory Files Update
**Files:** Memory file updates
**Tests:** Documentation validation
**Objective:** Update all memory files with new patterns
- Document error handling patterns
- Document i18n system
- Document testing patterns
- Document form widgets
- Document event subscribers
- Update master index

**Memory Files:**
- All `.claude/memory/*.md` files

**Estimated Time:** 4-5 hours

---

#### Plan 16: Final Validation & Integration
**Files:** Integration tests
**Tests:** Full application test suite
**Objective:** Verify complete implementation
- Run full test suite
- Manual testing checklist
- Performance testing
- Security audit
- Standards compliance verification
- Update CLAUDE.md with new patterns

**Memory Files:**
- All memory files (validation)

**Estimated Time:** 6-8 hours

---

## Progress Tracking

Use this checklist to track plan completion:

```
Priority 1: CRITICAL
[ ] Plan 01: Security Hardening
[ ] Plan 02: Error Handling & Pages
[ ] Plan 03: Entity & Repository Fixes

Priority 2: HIGH
[ ] Plan 04: Template Architecture & Base Layout
[ ] Plan 05: Admin Interface Polish
[ ] Plan 06: Blog UI Components & Partials
[ ] Plan 07: Form Enhancements & Widgets

Priority 3: MEDIUM
[ ] Plan 08: Testing Infrastructure
[ ] Plan 09: Command Line Interface Tests
[ ] Plan 10: Form & Utility Tests

Priority 4: STANDARD
[ ] Plan 11: Internationalization System
[ ] Plan 12: Frontend Assets & Components
[ ] Plan 13: Event Subscribers & Cross-Cutting
[ ] Plan 14: RSS & Content Syndication

Priority 5: FINAL
[ ] Plan 15: Memory Files Update
[ ] Plan 16: Final Validation & Integration
```

---

## Total Estimated Time

**Critical (Priority 1):** 7-9 hours
**High (Priority 2):** 18-22 hours
**Medium (Priority 3):** 12-15 hours
**Standard (Priority 4):** 20-26 hours
**Final (Priority 5):** 10-13 hours

**TOTAL:** 67-85 hours (~2-3 weeks of focused work)

---

## Context Reset Protocol

When continuing after a context reset:

1. Read this master plan
2. Check progress tracking section
3. Read the specific plan file for next task
4. Read ONLY the referenced memory files
5. Read BRANCH_COMPARISON_REPORT.md relevant section
6. Execute the plan
7. Update progress tracking
8. Create completion report for the plan

---

## Success Criteria

The comparison fulfillment is complete when:

- ✅ All 16 plans executed
- ✅ All security issues resolved
- ✅ Test coverage reaches 90%+
- ✅ All templates restored (33 total)
- ✅ Full i18n support (38 languages)
- ✅ All Packmind standards at 90%+ compliance
- ✅ Memory files fully updated
- ✅ Application passes full test suite
- ✅ Manual testing checklist completed
- ✅ Final score: 95/100 (target from comparison report)

---

**Next Step:** Execute Plan 01 - Security Hardening

**File Location:** `.claude/comparison-fulfillment/01-security-hardening.md`
