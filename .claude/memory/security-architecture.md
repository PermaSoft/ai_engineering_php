# Security Architecture

Complete security implementation details for authentication and authorization.

## Overview

The Symfony Demo uses a comprehensive security system with:
- Form-based authentication with username/password
- Password hashing with auto-algorithm selection
- Role-based access control (RBAC)
- Custom voters for fine-grained authorization
- CSRF protection on all sensitive forms
- Remember-me functionality
- Secure logout handling
- Switch user (impersonation) for testing

## Security Configuration

**Location**: `config/packages/security.yaml`

### Password Hashers

```yaml
security:
    password_hashers:
        # Use auto algorithm (Argon2i preferred, falls back to Bcrypt)
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
```

**Test Environment** (`config/packages/test/security.yaml`):
```yaml
security:
    password_hashers:
        # Lower cost for faster test execution
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
            algorithm: auto
            cost: 4
            time_cost: 3
            memory_cost: 10
```

### User Provider

```yaml
security:
    providers:
        database_users:
            entity:
                class: 'App\Entity\User'
                property: 'username'  # Use username for authentication
```

- Users loaded from database via Doctrine
- Authentication uses username field (not email)

### Firewall Configuration

```yaml
security:
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            lazy: true
            provider: database_users

            # Form login
            form_login:
                login_path: security_login
                check_path: security_login
                enable_csrf: true
                default_target_path: blog_index

            # Logout
            logout:
                path: security_logout
                target: homepage

            # Remember me cookie
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800  # 1 week in seconds
                path: /
                always_remember_me: true

            # Switch user functionality (login as another user)
            switch_user: true
```

**Key Features**:
- **Lazy loading**: Security context loaded only when needed
- **CSRF protection**: Enabled on login form
- **Remember me**: 1-week cookie duration
- **Default redirect**: Blog index after login
- **Switch user**: Admins can impersonate other users for testing

### Access Control Rules

```yaml
security:
    access_control:
        # Admin panel requires ROLE_ADMIN
        - { path: ^/admin/, role: ROLE_ADMIN }
        # Profile pages require authentication
        - { path: ^/profile/, role: ROLE_USER }
```

### Role Hierarchy

```yaml
security:
    role_hierarchy:
        ROLE_ADMIN: ROLE_USER
```

- `ROLE_ADMIN` inherits all `ROLE_USER` permissions
- Admins can access both admin panel and user profile pages

## Authentication Implementation

### Login Controller

**Location**: `src/Controller/SecurityController.php`

```php
#[Route('/login', name: 'security_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    if ($this->getUser()) {
        return $this->redirectToRoute('blog_index');
    }

    return $this->render('security/login.html.twig', [
        'error' => $authenticationUtils->getLastAuthenticationError(),
        'last_username' => $authenticationUtils->getLastUsername(),
    ]);
}
```

**Features**:
- Redirects authenticated users to blog index
- Displays authentication errors
- Pre-fills username from last attempt

### Login Template

**Location**: `templates/security/login.html.twig`

Key elements:
```twig
<form action="{{ path('security_login') }}" method="post">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

    <input type="text" name="_username" value="{{ last_username }}">
    <input type="password" name="_password">

    <input type="checkbox" name="_remember_me" checked>

    <button type="submit">Sign in</button>
</form>
```

### Logout Configuration

**Route**: `/logout`
**Name**: `security_logout`
**Target**: homepage

The logout is handled automatically by Symfony security system. No controller action needed.

## Authorization Implementation

### Role-Based Authorization

**Using Attributes on Controllers**:

```php
// Require any authenticated user
#[IsGranted('IS_AUTHENTICATED')]
public function commentNew(): Response { }

// Require specific role
#[Route('/admin/', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class BlogController { }
```

**Using in Templates**:

```twig
{% if is_granted('ROLE_ADMIN') %}
    <a href="{{ path('admin_post_index') }}">Admin Panel</a>
{% endif %}
```

**Using in Controllers**:

```php
$this->denyAccessUnlessGranted('ROLE_ADMIN');
```

### Custom Voter - PostVoter

**Location**: `src/Security/PostVoter.php`

**Purpose**: Control access to individual posts based on authorship

#### Permission Constants

```php
final class PostVoter extends Voter
{
    public const DELETE = 'delete';
    public const EDIT = 'edit';
    public const SHOW = 'show';
}
```

Using constants instead of magic strings:
- Enables IDE autocomplete
- Prevents typos
- Easy refactoring
- Searchable usage across codebase

#### Implementation

```php
protected function supports(string $attribute, mixed $subject): bool
{
    return $subject instanceof Post &&
           \in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
}

protected function voteOnAttribute(string $attribute, $post, TokenInterface $token, ?Vote $vote = null): bool
{
    $user = $token->getUser();

    // Deny if not logged in
    if (!$user instanceof User) {
        return false;
    }

    // Grant if user is the post author
    return $user === $post->getAuthor();
}
```

**Logic**: Only post authors can view, edit, or delete their own posts.

#### Usage in Controllers

```php
#[Route('/admin/post/{id}/edit', name: 'admin_post_edit')]
#[IsGranted(PostVoter::EDIT, subject: 'post')]
public function edit(Post $post): Response { }

#[Route('/admin/post/{id}/delete', name: 'admin_post_delete')]
#[IsGranted(PostVoter::DELETE, subject: 'post')]
public function delete(Request $request, Post $post): Response { }
```

#### Usage in Templates

```twig
{% if is_granted(constant('App\\Security\\PostVoter::EDIT'), post) %}
    <a href="{{ path('admin_post_edit', {id: post.id}) }}">Edit</a>
{% endif %}
```

## User Injection Patterns

### CurrentUser Attribute

Inject authenticated user directly into controller methods:

```php
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/comment/{postSlug}/new', name: 'comment_new')]
#[IsGranted('IS_AUTHENTICATED')]
public function commentNew(
    #[CurrentUser] User $user,
    Request $request,
    Post $post
): Response {
    $comment = new Comment();
    $comment->setAuthor($user);
    // ...
}
```

**Benefits**:
- Type-safe user object
- No need for `$this->getUser()`
- Automatically casts to User entity
- Clear method signature

### Traditional Method

```php
$user = $this->getUser();
if (!$user instanceof User) {
    throw new AccessDeniedException();
}
```

## CSRF Protection

### Login Form

```yaml
form_login:
    enable_csrf: true
```

Template token:
```twig
<input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">
```

### Logout

Automatic CSRF protection on logout

### Forms

All Symfony forms have built-in CSRF protection enabled by default.

### Delete Actions

Example from admin controller:

```php
#[Route('/{id}/delete', name: 'admin_post_delete')]
public function delete(Request $request, Post $post): Response
{
    if (!$this->isCsrfTokenValid('delete', (string) $request->request->get('token'))) {
        return $this->redirectToRoute('admin_post_index');
    }

    // Proceed with deletion
}
```

Template:
```twig
<form method="post" action="{{ path('admin_post_delete', {id: post.id}) }}"
      onsubmit="return confirm('Are you sure?')">
    <input type="hidden" name="token" value="{{ csrf_token('delete') }}">
    <button type="submit">Delete</button>
</form>
```

## Password Management

### Hashing Passwords

In fixtures or user creation:

```php
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

public function __construct(
    private readonly UserPasswordHasherInterface $passwordHasher,
) {}

public function createUser(string $plainPassword): void
{
    $user = new User();
    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
    $user->setPassword($hashedPassword);
}
```

### Changing Passwords

**Form**: `src/Form/ChangePasswordType.php`

```php
final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'label.current_password',
                'constraints' => [
                    new UserPassword(), // Validates current password
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'label.new_password'],
                'second_options' => ['label' => 'label.new_password_confirm'],
            ])
        ;
    }
}
```

**Controller**: `src/Controller/UserController.php`

```php
#[Route('/profile/change-password', name: 'user_change_password')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
{
    $user = $this->getUser();
    $form = $this->createForm(ChangePasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
        );

        $this->entityManager->flush();

        return $this->redirectToRoute('security_logout');
    }

    return $this->render('user/change_password.html.twig', [
        'form' => $form,
    ]);
}
```

**Features**:
- Validates current password before allowing change
- Requires password confirmation (repeated field)
- Auto-logout after password change for security
- Uses `IS_AUTHENTICATED_FULLY` (not remember-me)

## Security Best Practices Implemented

### ✅ Single Main Firewall
One firewall for the entire application (except dev tools)

### ✅ Explicit Access Control
- Attribute-based authorization on controllers
- Access control rules in security.yaml
- Custom voters for complex logic

### ✅ CSRF Protection
- Enabled on login
- Enabled on logout
- Enabled on all forms
- Manual tokens for delete operations

### ✅ Password Security
- Auto algorithm (Argon2i/Bcrypt)
- Current password validation on change
- Logout after password change

### ✅ Remember Me Security
- Secure cookie with kernel secret
- 1-week expiration
- Path-scoped to /

### ✅ Permission Constants
- Constants defined in Voter classes
- No magic strings in controllers/templates
- Searchable and refactorable

### ✅ Template Authorization
- `is_granted()` for conditional display
- UI elements hidden based on permissions
- Server-side validation always enforced

## Common Security Patterns

### Check if User is Logged In

```php
if ($this->getUser()) {
    // User is authenticated
}
```

### Get Current User (Type-Safe)

```php
$user = $this->getUser();
if (!$user instanceof User) {
    throw new AccessDeniedException();
}
```

Or use `#[CurrentUser]` attribute.

### Deny Access

```php
$this->denyAccessUnlessGranted('ROLE_ADMIN');
$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
```

### Check Permission Without Exception

```php
if ($this->isGranted('ROLE_ADMIN')) {
    // User has admin role
}

if ($this->isGranted(PostVoter::EDIT, $post)) {
    // User can edit this post
}
```

## Switch User (Impersonation)

The switch user feature allows administrators to impersonate other users for testing and debugging purposes.

### Configuration

**Location**: `config/packages/security.yaml`

```yaml
firewalls:
    main:
        switch_user: true
```

### Usage

#### In Templates (User List)

```twig
<a href="{{ path('blog_index', {'_switch_user': user.username}) }}">
    Login As {{ user.username }}
</a>
```

#### Switch Back to Original User

```twig
<a href="{{ path('blog_index', {'_switch_user': '_exit'}) }}">
    Exit Impersonation
</a>
```

Or append `?_switch_user=_exit` to any URL.

### Access Control

By default, only users with `ROLE_ALLOWED_TO_SWITCH` can use this feature. With the role hierarchy (`ROLE_ADMIN: ROLE_USER`), admins automatically have this permission.

### Implementation in Admin User Management

**Location**: `templates/admin/user/index.html.twig`

The user management page provides "Login As" links for each user (except the current user):

```twig
{% if app.user.id != user.id %}
    <a href="{{ path('blog_index', {'_switch_user': user.username}) }}"
       onclick="return confirm('Login as {{ user.username }}?')">
        Login As
    </a>
{% else %}
    <span>Current session</span>
{% endif %}
```

### Security Considerations

- Only admins can switch users
- Useful for testing user-specific functionality
- Demo and development feature (consider disabling in production)
- Can be restricted with custom voters if needed

## Test User Credentials

For development and testing:

```
Username: jane_admin
Password: kitten
Role: ROLE_ADMIN

Username: tom_admin
Password: kitten
Role: ROLE_ADMIN

Username: john_user
Password: kitten
Role: ROLE_USER
```

All users have email: `{username}@symfony.com`

## Testing Security

### Login in Tests

```php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyControllerTest extends WebTestCase
{
    public function testAdminAccess(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('jane_admin');
        $client->loginUser($user);

        $client->request('GET', '/admin/post/');

        $this->assertResponseIsSuccessful();
    }
}
```

### Test Access Denied

```php
public function testAdminAccessDeniedForRegularUser(): void
{
    $client = static::createClient();
    $user = static::getContainer()
        ->get(UserRepository::class)
        ->findOneByUsername('john_user');

    $client->loginUser($user);
    $client->request('GET', '/admin/post/');

    $this->assertResponseStatusCodeSame(403);
}
```
