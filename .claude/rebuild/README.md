# Symfony Demo Application - Rebuild Plans

This directory contains comprehensive rebuild plans for recreating the Symfony Demo Application from scratch.

## Purpose

These plans enable you to rebuild the entire application in a modular, step-by-step fashion with the ability to reset context between phases. Each plan is self-contained and references only the specific memory files needed for that phase.

## Getting Started

1. **Start with the Master Plan**: [00-master-rebuild-plan.md](./00-master-rebuild-plan.md)
   - Overview of all 10 phases
   - Execution sequence
   - Progress tracking
   - Success criteria

2. **Execute Phases Sequentially**: Follow the numbered plans (01 through 10)

3. **Reset Context Between Phases**: After completing each phase, you can safely reset context before starting the next

## Phase Overview

| Phase | Plan File | Description | Complexity | Files |
|-------|-----------|-------------|------------|-------|
| 1 | [01-project-setup.md](./01-project-setup.md) | Initialize Symfony project, dependencies, configuration | Low | 15-20 |
| 2 | [02-domain-model.md](./02-domain-model.md) | Implement entities (User, Post, Comment, Tag) | Medium | 8 |
| 3 | [03-repositories-services.md](./03-repositories-services.md) | Create repositories, services, pagination, Twig extensions | Medium | 10-12 |
| 4 | [04-security-system.md](./04-security-system.md) | Authentication, authorization, voters, password hashing | High | 5-7 |
| 5 | [05-forms-validation.md](./05-forms-validation.md) | Form types, custom fields, data transformers | Medium | 6-8 |
| 6 | [06-blog-browsing.md](./06-blog-browsing.md) | Public blog listing, post viewing, search, RSS | Medium | 8-10 |
| 7 | [07-admin-panel.md](./07-admin-panel.md) | Admin post CRUD operations | Medium | 5-7 |
| 8 | [08-comment-system.md](./08-comment-system.md) | Comment creation, spam detection, notifications | Low | 3-5 |
| 9 | [09-user-profile.md](./09-user-profile.md) | User profile editing, password management | Low | 3-4 |
| 10 | [10-testing-suite.md](./10-testing-suite.md) | Functional tests, command tests, fixtures | High | 10-15 |

**Total Estimated Files**: ~75-95 files

## How to Use These Plans

### For Each Phase:

1. **Read the Plan**: Open the phase's markdown file
2. **Check Memory Files**: Review the "Memory Files Required" section
3. **Load Only Needed Memory**: Read only the memory files listed for that phase
4. **Follow the Checklist**: Work through each checkbox sequentially
5. **Verify Success**: Check all success criteria before proceeding
6. **Context Reset**: After verification, you can reset context for the next phase

### Context Management Strategy:

- **Master Plan**: Keep in context for reference
- **Current Phase Plan**: Load when working on that phase
- **Memory Files**: Load only those referenced in current phase
- **Packmind Standards**: Reference as needed (listed in each plan)

### Progress Tracking:

Track your progress in the master plan file by checking off completed phases.

## Plan Structure

Each plan file contains:

- **Overview**: Goal, complexity, dependencies, estimated files
- **Memory Files Required**: Specific memory files needed for this phase
- **Packmind Standards Applied**: Relevant coding standards
- **Implementation Checklist**: Step-by-step tasks with checkboxes
- **Success Criteria**: Verification that phase is complete
- **Next Phase**: Link to subsequent phase

## Memory Files

Plans reference these memory files (located in `.claude/memory/`):

- `symfony-demo-app-index.md` - Master index and overview
- `domain-model.md` - Entity specifications
- `security-architecture.md` - Security implementation
- `controllers-routing.md` - HTTP request handling
- `forms-validation.md` - Form types and validation
- `services-repositories.md` - Business logic and data access
- `templates-frontend.md` - Twig templates and frontend
- `testing-strategy.md` - Test organization and patterns
- `configuration-setup.md` - Dependencies and setup

## Packmind Standards

All implementations must follow these standards (located in `.packmind/standards/`):

- Symfony Business Logic & Application Structure
- Symfony Configuration Best Practices
- Symfony Controllers Best Practices
- Symfony Entities & Doctrine Best Practices
- Symfony Security Best Practices
- Symfony Forms Best Practices
- Symfony Templates & Twig Best Practices
- Symfony Testing Best Practices

## Dependencies Between Phases

```
Phase 1 (Setup)
    ├─→ Phase 2 (Domain Model)
    │       ├─→ Phase 3 (Repositories & Services)
    │       │       └─→ Phase 6 (Blog Browsing)
    │       │       └─→ Phase 8 (Comment System)
    │       ├─→ Phase 4 (Security System)
    │       │       ├─→ Phase 5 (Forms & Validation)
    │       │       │       ├─→ Phase 6 (Blog Browsing)
    │       │       │       ├─→ Phase 7 (Admin Panel)
    │       │       │       ├─→ Phase 8 (Comment System)
    │       │       │       └─→ Phase 9 (User Profile)
    │       │       └─→ Phase 7 (Admin Panel)
    │       │       └─→ Phase 9 (User Profile)
    │       └─→ Phase 10 (Testing Suite) [depends on ALL phases]
```

## Quick Reference Commands

### Database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console doctrine:schema:validate
```

### Development
```bash
symfony server:start
php bin/console cache:clear
php bin/console sass:build
php bin/console importmap:install
```

### Testing
```bash
php bin/phpunit
vendor/bin/phpstan analyse src/
XDEBUG_MODE=coverage php bin/phpunit --coverage-html coverage/
```

### User Management
```bash
php bin/console app:add-user username password email@example.com --admin
php bin/console app:list-users
```

## Success Metrics

The rebuild is complete when:

- ✅ All 10 phases implemented
- ✅ All tests pass
- ✅ Application runs without errors
- ✅ All fixtures load successfully
- ✅ All Packmind standards followed
- ✅ PHPStan passes level 8
- ✅ All entry points functional (blog, admin, profile, login, search)

## Troubleshooting

If you encounter issues:

1. **Check Dependencies**: Ensure previous phases are complete
2. **Verify Memory Files**: Confirm you're using the correct memory files
3. **Review Standards**: Ensure Packmind standards are being followed
4. **Run Verification**: Execute all verification steps in the phase plan
5. **Check Success Criteria**: Confirm all criteria are met before proceeding

## Additional Resources

- **CLAUDE.md**: Main project documentation and standards
- **Memory Files**: Detailed technical specifications
- **Packmind Standards**: Coding rules and best practices
- **Symfony Docs**: https://symfony.com/doc/current/

---

**Ready to begin?** Start with [00-master-rebuild-plan.md](./00-master-rebuild-plan.md)