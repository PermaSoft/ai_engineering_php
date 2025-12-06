# Phase 9: User Profile Management

## Overview

**Goal**: Implement user profile editing and password management
**Complexity**: Low
**Dependencies**: Phase 2, 4, 5
**Estimated Files**: 3-4 files

## Memory Files Required

- **Primary**: [Controllers & Routing](../memory/controllers-routing.md), [Forms & Validation](../memory/forms-validation.md)
- **Reference**: [Security Architecture](../memory/security-architecture.md), [Templates & Frontend](../memory/templates-frontend.md)

## Packmind Standards Applied

- Symfony Controllers Best Practices
- Symfony Forms Best Practices
- Symfony Security Best Practices

## Implementation Checklist

### 1. User Profile Controller

- [ ] Create `src/Controller/UserController.php`
  - [ ] Mark as `final class`
  - [ ] Extend `AbstractController`
  - [ ] All routes require `ROLE_USER`

- [ ] **Edit profile action** - `#[Route('/profile/edit', name: 'user_edit_profile')]`
  - [ ] Inject `#[CurrentUser] User $user`
  - [ ] Create UserType form bound to current user
  - [ ] Handle GET (display form) and POST (update profile)
  - [ ] Fields editable: fullName, username, email
  - [ ] Validate unique username/email
  - [ ] Flash success: `profile.updated_successfully`
  - [ ] Persist changes
  - [ ] Render `user/edit_profile.html.twig`

- [ ] **Change password action** - `#[Route('/profile/change-password', name: 'user_change_password')]`
  - [ ] Inject `#[CurrentUser] User $user`
  - [ ] Inject `UserPasswordHasherInterface`
  - [ ] Create ChangePasswordType form
  - [ ] Validate current password is correct
  - [ ] Hash new password
  - [ ] Update user password
  - [ ] Flash success: `password.changed_successfully`
  - [ ] Redirect to profile edit
  - [ ] Render `user/change_password.html.twig`

### 2. Form Types

- [ ] Verify `src/Form/UserType.php` exists (from Phase 5)
  - [ ] Fields: fullName, username, email
  - [ ] `data_class: User::class`

- [ ] Verify `src/Form/ChangePasswordType.php` exists (from Phase 5)
  - [ ] Field: `currentPassword` (PasswordType)
  - [ ] Field: `newPassword` (RepeatedType)
    - [ ] Type: PasswordType
    - [ ] First options: label "New password"
    - [ ] Second options: label "Repeat password"
    - [ ] Invalid message: "The password fields must match"

- [ ] Add custom validation for current password
  - [ ] Create constraint or use callback
  - [ ] Verify current password matches user's password
  - [ ] Use `UserPasswordHasherInterface->isPasswordValid()`

### 3. Templates

- [ ] Create `templates/user/edit_profile.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Page title: "Edit Profile"
  - [ ] Render UserType form
  - [ ] Display current username, fullName, email
  - [ ] Save button
  - [ ] Link to change password page
  - [ ] Cancel button → home

- [ ] Create `templates/user/change_password.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Page title: "Change Password"
  - [ ] Render ChangePasswordType form
  - [ ] Current password field
  - [ ] New password field (repeated)
  - [ ] Save button
  - [ ] Cancel button → profile edit
  - [ ] Password requirements hint (min length, etc.)

### 4. Navigation Update

- [ ] Update `templates/_header.html.twig`
  - [ ] Add user dropdown menu (when authenticated)
  - [ ] Link to profile edit: `path('user_edit_profile')`
  - [ ] Link to change password: `path('user_change_password')`
  - [ ] Logout link
  - [ ] Display current username

### 5. Translation Keys

- [ ] Add to `translations/messages.en.yaml`
  - [ ] `profile.updated_successfully: "Your profile has been updated."`
  - [ ] `password.changed_successfully: "Your password has been changed."`
  - [ ] `password.current_invalid: "Current password is incorrect."`
  - [ ] `password.mismatch: "The password fields must match."`

### 6. Access Control

- [ ] Verify `config/packages/security.yaml` has access control:
  - [ ] `{ path: ^/profile/, role: ROLE_USER }`

- [ ] Users can only edit their own profile (enforced by CurrentUser injection)

### 7. Form Validation

- [ ] Test unique username validation
  - [ ] Try changing username to existing username
  - [ ] Should show error: "Username already exists"

- [ ] Test unique email validation
  - [ ] Try changing email to existing email
  - [ ] Should show error: "Email already in use"

- [ ] Test password change validation
  - [ ] Try wrong current password → error
  - [ ] Try mismatched new passwords → error
  - [ ] Try valid change → success

### 8. Verification

- [ ] Login as `john_user` / `kitten`
- [ ] Visit `/en/profile/edit` - should show profile form
- [ ] Update fullName - should save
- [ ] Try changing username to `jane_admin` - should fail (unique constraint)
- [ ] Visit `/en/profile/change-password`
- [ ] Enter wrong current password - should show error
- [ ] Enter correct current password + new password (repeated) - should succeed
- [ ] Logout and login with new password - should work
- [ ] Test as anonymous user:
  - [ ] Direct access to `/en/profile/edit` - should redirect to login
- [ ] Verify flash messages appear
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/Controller/`

## Success Criteria

✅ Profile edit page displays and updates user data
✅ Password change page works correctly
✅ Current password validation works
✅ New password hashing works
✅ Unique username/email validation enforced
✅ Flash messages displayed
✅ Navigation updated with user menu
✅ Access control enforced (ROLE_USER required)

## Next Phase

[Phase 10: Testing Suite](./10-testing-suite.md)