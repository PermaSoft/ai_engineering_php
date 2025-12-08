# Plan 01: Security Hardening

**Priority:** 🔴 CRITICAL
**Estimated Time:** 2-3 hours
**Dependencies:** None
**Status:** Ready to execute

---

## Context & Objective

Fix 5 critical security vulnerabilities identified in the branch comparison:
1. Missing logout CSRF protection
2. User not logged out after password change
3. Always-remember-me enabled by default
4. Switch user enabled in production
5. Missing autocomplete="off" on password fields

These are security issues that could lead to CSRF attacks, session hijacking, and credential exposure.

---

## Reference Materials

### Memory Files
- `.claude/memory/security-architecture.md` - Authentication and authorization patterns
- `.claude/memory/forms-validation.md` - Form security patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 4: Security Architecture)

### Key Walkthrought Files
- `git show walkthrought:config/packages/security.yaml` - Security configuration
- `git show walkthrought:src/Controller/UserController.php` - Password change with logout
- `git show walkthrought:src/Form/ChangePasswordType.php` - Secure password form

### Current Rebuilding Files
- `config/packages/security.yaml`
- `src/Controller/UserController.php`
- `src/Form/ChangePasswordType.php`

---

## Prerequisites

- Git access to both branches
- Write access to rebuilding branch
- PHPUnit installed for testing

---

## Deliverables Checklist

### Code Changes
- [ ] `config/packages/security.yaml` - Add CSRF to logout, disable always_remember_me, move switch_user to dev
- [ ] `src/Controller/UserController.php` - Add logout after password change
- [ ] `src/Form/ChangePasswordType.php` - Add autocomplete="off", add max length, fix mapping

### Testing
- [ ] Manual test: Logout CSRF protection
- [ ] Manual test: Password change forces logout
- [ ] Manual test: Remember me requires checkbox
- [ ] Manual test: Switch user only in dev environment

### Documentation
- [ ] Add comment in security.yaml explaining changes
- [ ] Update memory file with security patterns

### Configuration
- [ ] Create `config/packages/dev/security.yaml` for dev-only features

---

## Implementation Steps

### Step 1: Fix Logout CSRF Protection

**File:** `config/packages/security.yaml`

**Current Code:**
```yaml
logout:
    path: security_logout
```

**Updated Code:**
```yaml
logout:
    path: security_logout
    target: blog_index
    enable_csrf: true  # Protect against CSRF attacks on logout
```

**Rationale:** CSRF protection prevents attackers from logging users out via malicious links.

---

### Step 2: Disable Always-Remember-Me

**File:** `config/packages/security.yaml`

**Current Code:**
```yaml
remember_me:
    secret: '%kernel.secret%'
    lifetime: 604800
    path: /
    always_remember_me: true  # ✗ Forces persistent sessions
```

**Updated Code:**
```yaml
remember_me:
    secret: '%kernel.secret%'
    lifetime: 604800
    path: /
    always_remember_me: false  # Users must opt-in via checkbox
    remember_me_parameter: '_remember_me'  # Checkbox field name
```

**Rationale:** Users should explicitly opt-in to persistent sessions for security.

---

### Step 3: Move Switch User to Dev Environment

**File:** `config/packages/security.yaml`

**Current Code:**
```yaml
main:
    switch_user: true  # ✗ Enabled in production
```

**Updated Code:**
```yaml
main:
    # Remove switch_user from main config
```

**New File:** `config/packages/dev/security.yaml`

```yaml
security:
    firewalls:
        main:
            switch_user: true  # Only enabled in dev environment
```

**Rationale:** Switch user (impersonation) should never be available in production.

---

### Step 4: Add Logout After Password Change

**File:** `src/Controller/UserController.php`

**Current Code:**
```php
#[Route('/change-password', name: 'user_change_password')]
public function changePassword(
    #[CurrentUser] User $user,
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager
): Response {
    $form = $this->createForm(ChangePasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
        );
        $entityManager->flush();

        $this->addFlash('success', 'password.changed_successfully');
        return $this->redirectToRoute('user_edit');  // ✗ User stays logged in
    }

    return $this->render('user/change_password.html.twig', [
        'form' => $form,
    ]);
}
```

**Updated Code:**
```php
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/change-password', name: 'user_change_password', methods: ['GET', 'POST'])]
public function changePassword(
    #[CurrentUser] User $user,
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager,
    Security $security
): Response {
    $form = $this->createForm(ChangePasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
        );
        $entityManager->flush();

        // Security best practice: logout user after password change
        // This forces re-authentication with the new password
        return $security->logout(validateCsrfToken: false) ?? $this->redirectToRoute('blog_index');
    }

    return $this->render('user/change_password.html.twig', [
        'form' => $form,
    ]);
}
```

**Key Changes:**
1. Add `Security` service injection
2. Call `$security->logout()` after password change
3. Add `methods: ['GET', 'POST']` to route attribute (bonus fix)
4. Remove flash message (user is being logged out)
5. Redirect to blog_index instead of user_edit

**Rationale:** Logging out after password change prevents session hijacking if password was compromised.

---

### Step 5: Enhance ChangePasswordType Security

**File:** `src/Form/ChangePasswordType.php`

**Current Code:**
```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('currentPassword', PasswordType::class, [
            'label' => 'label.current_password',
            'constraints' => [new UserPassword()],
        ])
        ->add('newPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => ['label' => 'label.new_password'],
            'second_options' => ['label' => 'label.new_password_confirm'],
            'constraints' => [
                new NotBlank(),
                new Length(min: 6, minMessage: 'password.too_short'),
            ],
        ])
    ;
}
```

**Updated Code:**
```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('currentPassword', PasswordType::class, [
            'label' => 'label.current_password',
            'constraints' => [new UserPassword()],
            'mapped' => false,
            'attr' => [
                'autocomplete' => 'current-password',  // Proper autocomplete hint
            ],
        ])
        ->add('newPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'label.new_password',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'second_options' => [
                'label' => 'label.new_password_confirm',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'mapped' => false,
            'constraints' => [
                new NotBlank(),
                new Length(
                    min: 6,
                    max: 128,  // Add maximum length
                    minMessage: 'password.too_short',
                    maxMessage: 'password.too_long'
                ),
            ],
        ])
    ;
}
```

**Key Changes:**
1. Add `'mapped' => false` to both fields (they don't map directly to User entity)
2. Change autocomplete to proper HTML5 values (`current-password`, `new-password`)
3. Add `max: 128` to Length constraint
4. Add `maxMessage` for better error handling

**Rationale:**
- `autocomplete` hints help password managers handle fields correctly
- `mapped: false` clarifies these fields don't directly map to entity properties
- Max length prevents potential performance issues

---

### Step 6: Add Login Template Remember Me Checkbox

**File:** `templates/security/login.html.twig`

**Add checkbox before submit button:**

```twig
{# ... existing form fields ... #}

<div class="form-check mb-3">
    <input type="checkbox" class="form-check-input" id="remember_me" name="_remember_me">
    <label class="form-check-label" for="remember_me">
        {{ 'label.remember_me'|trans }}
    </label>
</div>

<button type="submit" class="btn btn-primary btn-block">
    {{ 'action.sign_in'|trans }}
</button>
```

**Rationale:** Since we disabled `always_remember_me`, users need a checkbox to opt-in.

---

## Verification Criteria

### Automated Tests

Run existing tests to ensure no regression:
```bash
php bin/phpunit
```

### Manual Testing Checklist

#### Test 1: Logout CSRF Protection
1. Login to application
2. Try to logout via GET request: `/en/logout`
3. ✓ Should fail or redirect (CSRF token required)
4. Click logout link in UI
5. ✓ Should successfully logout

#### Test 2: Password Change Forces Logout
1. Login to application
2. Navigate to profile → Change Password
3. Enter current password and new password
4. Submit form
5. ✓ Should be logged out automatically
6. ✓ Should be redirected to homepage
7. Try to login with NEW password
8. ✓ Should successfully login

#### Test 3: Remember Me Requires Checkbox
1. Logout if logged in
2. Go to login page
3. Enter valid credentials WITHOUT checking "Remember me"
4. Login
5. Close browser (simulate session end)
6. ✓ Should require login again (no persistent session)
7. Login again WITH "Remember me" checked
8. Close and reopen browser
9. ✓ Should still be logged in (persistent session)

#### Test 4: Switch User Only in Dev
1. Check `APP_ENV=prod` in `.env`
2. Login as admin
3. Try to access `/_switch_user?_switch_user=john_user`
4. ✓ Should fail (403 or 404)
5. Change to `APP_ENV=dev`
6. Try again
7. ✓ Should successfully switch to john_user

---

## Memory File Updates

**File:** `.claude/memory/security-architecture.md`

Add this section:

```markdown
## Security Hardening Checklist

### Password Change Security
- Always logout users after password change
- Use `Security::logout()` service
- Redirect to public page after logout
- Do not display flash message (user is logged out)

### Form Security
- Add `autocomplete` attributes to password fields:
  - `current-password` for existing password
  - `new-password` for new password fields
- Add `mapped: false` to form fields that don't map to entities
- Set maximum password length (128 characters)

### Session Security
- Disable `always_remember_me` in security.yaml
- Provide opt-in checkbox for remember me
- Use `remember_me_parameter: '_remember_me'`
- Enable CSRF protection on logout

### Development vs Production
- Use `config/packages/dev/security.yaml` for dev-only features
- Never enable `switch_user` in production
- Keep sensitive debugging features in dev environment only
```

---

## Context Reset Information

If resuming after context reset:

**Files Modified:**
1. `config/packages/security.yaml` - CSRF, remember me, switch user
2. `config/packages/dev/security.yaml` - NEW FILE for dev switch user
3. `src/Controller/UserController.php` - Logout after password change
4. `src/Form/ChangePasswordType.php` - Autocomplete and mapping
5. `templates/security/login.html.twig` - Remember me checkbox

**Verification:**
- Run `php bin/phpunit` - All tests pass
- Manual testing checklist completed
- Memory file updated

**Next Plan:** `02-error-handling-pages.md`

---

## Rollback Procedure

If issues arise:

```bash
# Restore original files from git
git checkout HEAD -- config/packages/security.yaml
git checkout HEAD -- src/Controller/UserController.php
git checkout HEAD -- src/Form/ChangePasswordType.php
git rm config/packages/dev/security.yaml

# Verify application works
symfony server:start
```

---

## Completion Checklist

- [ ] All 5 security issues fixed
- [ ] 4 files modified
- [ ] 1 new file created
- [ ] All tests passing
- [ ] Manual testing completed
- [ ] Memory file updated
- [ ] Git commit created with message:
  ```
  fix: security hardening - CSRF, logout, remember-me, autocomplete

  - Add CSRF protection to logout
  - Force logout after password change
  - Disable always-remember-me (require opt-in)
  - Move switch_user to dev environment only
  - Add autocomplete attributes to password fields
  - Add max password length constraint

  Resolves 5 critical security issues identified in branch comparison.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 02