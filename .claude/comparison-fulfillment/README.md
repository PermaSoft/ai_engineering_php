# Comparison Fulfillment Process

This directory contains a systematic plan to bring the `rebuilding` branch to 95%+ completeness by adding missing functionality from the `walkthrought` branch while preserving improvements.

---

## Quick Start

### 1. Understand Current State

Read the **branch comparison report**:
```bash
cat BRANCH_COMPARISON_REPORT.md
```

**Key Findings:**
- Rebuilding branch: 57% complete
- 5 critical security issues
- 42% of templates missing
- 36% reduced test coverage
- Minimal internationalization

---

### 2. Review Master Plan

```bash
cat .claude/comparison-fulfillment/00-MASTER-PLAN.md
```

The master plan contains:
- 16 sequential plans organized by priority
- Estimated 67-85 hours total work (2-3 weeks)
- Context reset protocol for resuming work
- Success criteria and completion tracking

---

### 3. Execute Plans Sequentially

Plans are numbered and prioritized:

**Priority 1: CRITICAL (7-9 hours)**
- ✅ Plan 01: Security Hardening
- ✅ Plan 02: Error Handling & Pages
- ✅ Plan 03: Entity & Repository Fixes

**Priority 2: HIGH (18-22 hours)**
- Plan 04: Template Architecture & Base Layout
- Plan 05: Admin Interface Polish
- Plan 06: Blog UI Components & Partials
- Plan 07: Form Enhancements & Widgets

**Priority 3: MEDIUM (12-15 hours)**
- Plan 08: Testing Infrastructure
- Plan 09: Command Line Interface Tests
- Plan 10: Form & Utility Tests

**Priority 4: STANDARD (20-26 hours)**
- Plan 11: Internationalization System
- Plan 12: Frontend Assets & Components
- Plan 13: Event Subscribers & Cross-Cutting
- Plan 14: RSS & Content Syndication

**Priority 5: FINAL (10-13 hours)**
- Plan 15: Memory Files Update
- Plan 16: Final Validation & Integration

---

## How to Execute a Plan

### Step 1: Open Plan File

```bash
cat .claude/comparison-fulfillment/01-security-hardening.md
```

### Step 2: Review Context & Objective

Each plan contains:
- Clear objective statement
- Reference materials (memory files, comparison report)
- Prerequisites
- Deliverables checklist

### Step 3: Read Referenced Memory Files

Example for Plan 01:
```bash
cat .claude/memory/security-architecture.md
cat .claude/memory/forms-validation.md
```

### Step 4: Follow Implementation Steps

Plans contain:
- Step-by-step instructions
- File paths and code examples
- Before/after comparisons
- Rationale for each change

### Step 5: Verify Completion

Each plan includes:
- Manual testing checklist
- Automated test commands
- Verification criteria
- Expected outcomes

### Step 6: Update Progress

In `00-MASTER-PLAN.md`, mark the plan as complete:
```markdown
[✓] Plan 01: Security Hardening
```

### Step 7: Commit Changes

Use the suggested commit message from the plan:
```bash
git add .
git commit -m "fix: security hardening - CSRF, logout, remember-me, autocomplete

- Add CSRF protection to logout
- Force logout after password change
- Disable always-remember-me (require opt-in)
- Move switch_user to dev environment only
- Add autocomplete attributes to password fields
- Add max password length constraint

Resolves 5 critical security issues identified in branch comparison."
```

---

## Context Reset Protocol

If you need to resume work after a break:

### Step 1: Check Progress

```bash
cat .claude/comparison-fulfillment/00-MASTER-PLAN.md
# Look for the last completed plan in the checklist
```

### Step 2: Open Next Plan

```bash
cat .claude/comparison-fulfillment/0X-plan-name.md
```

### Step 3: Read "Context Reset Information"

Each plan has a section at the end with:
- Files modified
- Verification steps
- Next plan reference

### Step 4: Resume Work

Continue with the next uncompleted plan.

---

## Plan Structure

Every plan file follows this structure:

```markdown
# Plan XX: [Feature Name]

## Context & Objective
[What and why]

## Reference Materials
- Memory files
- Comparison report sections
- Walkthrought files to reference

## Prerequisites
[What must be done first]

## Deliverables Checklist
- [ ] Code changes
- [ ] Tests
- [ ] Documentation
- [ ] Configuration

## Implementation Steps
Step 1: ...
Step 2: ...
[Detailed instructions]

## Verification Criteria
[How to verify completion]

## Memory File Updates
[What to document]

## Context Reset Information
[How to resume]

## Completion Checklist
[Final checklist before moving on]
```

---

## Available Plans

### Created Plans

1. ✅ **00-MASTER-PLAN.md** - Overview and progress tracking
2. ✅ **01-security-hardening.md** - Fix 5 critical security issues
3. ✅ **02-error-handling-pages.md** - Create professional error pages
4. ⏳ **03-entity-repository-fixes.md** - To be created
5. ⏳ **04-template-architecture.md** - To be created
6. ⏳ **05-admin-interface.md** - To be created
7. ⏳ **06-blog-components.md** - To be created
8. ⏳ **07-form-enhancements.md** - To be created
9. ⏳ **08-testing-infrastructure.md** - To be created
10. ⏳ **09-cli-tests.md** - To be created
11. ⏳ **10-form-utils-tests.md** - To be created
12. ⏳ **11-internationalization.md** - To be created
13. ⏳ **12-frontend-assets.md** - To be created
14. ⏳ **13-event-subscribers.md** - To be created
15. ⏳ **14-rss-syndication.md** - To be created
16. ⏳ **15-memory-updates.md** - To be created
17. ⏳ **16-final-validation.md** - To be created

### Legend
- ✅ Plan created and ready
- ⏳ Plan to be created
- 🟢 Plan completed
- 🔵 Plan in progress

---

## Tips for Success

### 1. Work Incrementally

Complete one plan at a time. Don't skip ahead or work on multiple plans simultaneously.

### 2. Verify Early and Often

Run tests after each significant change:
```bash
php bin/phpunit
symfony server:start
```

### 3. Commit Frequently

Create a git commit after completing each plan or major step.

### 4. Read Referenced Files

Always read the memory files and comparison report sections referenced in each plan.

### 5. Test Manually

Automated tests are important, but manual testing catches UX issues.

### 6. Update Documentation

Keep memory files up to date as you implement features.

### 7. Use Context Reset

If you feel overwhelmed or confused, reset context:
1. Save your work
2. Commit changes
3. Close editor
4. Reopen and read master plan
5. Read next plan file only

---

## Common Commands

### Running Tests
```bash
# All tests
php bin/phpunit

# Specific test file
php bin/phpunit tests/Controller/BlogControllerTest.php

# With coverage
php bin/phpunit --coverage-html coverage
```

### Starting Server
```bash
# Symfony CLI
symfony server:start

# Built-in PHP server
php -S localhost:8000 -t public
```

### Viewing Application
```bash
# Open in browser
symfony open:local

# Or manually
open http://localhost:8000
```

### Clearing Cache
```bash
php bin/console cache:clear
```

### Checking Code Style
```bash
# If PHP-CS-Fixer is installed
vendor/bin/php-cs-fixer fix
```

---

## Troubleshooting

### Issue: Tests Failing

**Solution:**
1. Clear cache: `php bin/console cache:clear`
2. Check database: `php bin/console doctrine:schema:validate`
3. Reset test database: `php bin/console doctrine:schema:drop --force --env=test`
4. Recreate: `php bin/console doctrine:schema:create --env=test`
5. Load fixtures: `php bin/console doctrine:fixtures:load --env=test -n`

### Issue: Templates Not Rendering

**Solution:**
1. Check Twig syntax
2. Clear cache
3. Check file permissions
4. Verify template path in controller

### Issue: Can't Access Git Show Commands

**Solution:**
```bash
# Ensure you're in the git repository
cd /Users/nicolas/code/PermaSoft/AI_Engineering_php

# Verify branches exist
git branch -a

# Try fetching
git fetch --all
```

### Issue: Context Lost / Confused

**Solution:**
1. Stop working
2. Read `00-MASTER-PLAN.md`
3. Read current plan file from scratch
4. Follow implementation steps exactly
5. Don't improvise or skip steps

---

## Success Metrics

Track your progress:

```
┌──────────────────────────────────────────────────┐
│ Metric                 │ Current │ Target │ Gap  │
├────────────────────────┼─────────┼────────┼──────┤
│ Overall Completion     │   57%   │  95%   │ +38% │
│ Security Issues        │    5    │   0    │  -5  │
│ Templates              │   19    │  33    │ +14  │
│ Test Coverage          │   64%   │  90%   │ +26% │
│ Translation Files      │    1    │  38    │ +37  │
│ Standards Compliance   │   75%   │  90%   │ +15% │
└──────────────────────────────────────────────────┘
```

Update after each completed plan!

---

## Getting Help

### If Stuck on a Plan

1. Re-read the plan from the beginning
2. Check the comparison report section
3. Read the referenced memory files
4. Look at walkthrought files: `git show walkthrought:path/to/file`
5. Ask for clarification with specific questions

### If Plan Seems Wrong

Plans are generated based on the comparison report. If something seems incorrect:
1. Verify against comparison report
2. Check walkthrought branch implementation
3. Consider if the difference is intentional (rebuilding improvement)
4. Document your decision

---

## Final Notes

- **Quality over Speed**: Take time to understand each change
- **Test Everything**: Manual + automated testing
- **Document as You Go**: Update memory files
- **Commit Often**: Small, logical commits
- **Stay Organized**: One plan at a time

**Estimated Completion**: 2-3 weeks of focused work
**Target Score**: 95/100 (from current 57/100)

---

**Current Status:** Ready to begin
**Next Action:** Execute Plan 01 - Security Hardening
**File:** `.claude/comparison-fulfillment/01-security-hardening.md`

Good luck! 🚀