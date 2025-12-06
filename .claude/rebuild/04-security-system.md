# Phase 4: Security System

## Overview

**Goal**: Implement authentication, authorization, voters, and password hashing
**Complexity**: High
**Dependencies**: Phase 1, 2
**Estimated Files**: 5-7 files

## Memory Files Required

- **Primary**: [Security Architecture](../memory/security-architecture.md)
- **Reference**: [Domain Model](../memory/domain-model.md), [Controllers & Routing](../memory/controllers-routing.md)

## Packmind Standards Applied

- Symfony Security Best Practices

## Implementation Checklist

### 1. Security Configuration

- [ ] Complete `config/packages/security.yaml`
  - [ ] Password hashers: `'auto'` for `PasswordAuthenticatedUserInterface`
  - [ ] Provider: `database_users` using User entity, property: `username`
  - [ ] Main firewall:
    - [ ] `lazy: true`
    - [ ] `provider: database_users`
    - [ ] `form_login` with login_path and check_path
    - [ ] `enable_csrf: true` on form_login
    - [ ] `logout` path and CSRF protection
    - [ ] `remember_me` with secret and lifetime (604800 = 1 week)
  - [ ] Access control rules:
    - [ ] `^/admin/` requires `ROLE_ADMIN`
    - [ ] `^/profile/` requires `ROLE_USER`
  - [ ] Role hierarchy:
    - [ ] `ROLE_ADMIN: ROLE_USER`

### 2. Voters

- [ ] Create `src/Security/PostVoter.php`
  - [ ] Define constants: `SHOW`, `EDIT`, `DELETE`
  - [ ] Implement `supports()` method
  - [ ] Implement `voteOnAttribute()` method
  - [ ] Logic:
    - [ ] `SHOW`: always true (public)
    - [ ] `EDIT`: user must be post author
    - [ ] `DELETE`: user must be post author

### 3. Security Controller

- [ ] Create `src/Controller/SecurityController.php`
  - [ ] `#[Route('/login', name: 'security_login')]`
  - [ ] `login(AuthenticationUtils $authenticationUtils)` method
    - [ ] Get last authentication error
    - [ ] Get last username entered
    - [ ] Render login template
  - [ ] `#[Route('/logout', name: 'security_logout')]`
    - [ ] Empty method (handled by security system)

### 4. Login Template

- [ ] Create `templates/security/login.html.twig`
  - [ ] Display authentication error if present
  - [ ] Login form with CSRF protection
  - [ ] Username field (pre-filled if error)
  - [ ] Password field
  - [ ] Remember me checkbox
  - [ ] Submit button

### 5. Console Commands for User Management

- [ ] Create `src/Command/AddUserCommand.php`
  - [ ] Command name: `app:add-user`
  - [ ] Arguments: username, password, email
  - [ ] Option: --admin (adds ROLE_ADMIN)
  - [ ] Creates user with hashed password
  - [ ] Persists to database

- [ ] Create `src/Command/ListUsersCommand.php`
  - [ ] Command name: `app:list-users`
  - [ ] Lists all users with roles
  - [ ] Table output format

### 6. Fixtures with Users

- [ ] Create `src/DataFixtures/AppFixtures.php`
  - [ ] Create 3 users:
    - [ ] `jane_admin` (ROLE_ADMIN), password: "kitten"
    - [ ] `tom_admin` (ROLE_ADMIN), password: "kitten"
    - [ ] `john_user` (ROLE_USER), password: "kitten"
  - [ ] Inject `UserPasswordHasherInterface`
  - [ ] Hash passwords before persisting

### 7. Verification

- [ ] Load fixtures: `php bin/console doctrine:fixtures:load`
- [ ] Test login command: `php bin/console app:add-user testuser password test@example.com`
- [ ] Test list command: `php bin/console app:list-users`
- [ ] Start server and visit `/en/login`
- [ ] Test login with `jane_admin` / `kitten`
- [ ] Verify remember-me cookie works
- [ ] Test logout
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/`

## Success Criteria

✅ Security configuration complete
✅ Voters implemented for post access control
✅ Login/logout working
✅ User fixtures loaded (3 users)
✅ Console commands for user management working
✅ Remember-me functionality working
✅ CSRF protection enabled
✅ Password hashing working

## Next Phase

[Phase 5: Forms & Validation](./05-forms-validation.md)
