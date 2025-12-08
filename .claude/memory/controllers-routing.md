# Controllers & Routing

Complete HTTP request handling patterns and routing configuration.

## Routing Architecture

### Global Route Prefix

All routes prefixed with `/{_locale}` for internationalization:

**Location**: `config/routes.yaml`

```yaml
controllers:
    resource:
        path: ../src/Controller/
        namespace: App\Controller
    type: attribute
    prefix: /{_locale}
    requirements:
        _locale: '%app.supported_locales%'
    defaults:
        _locale: '%app.locale%'
```

**Supported locales**: `en|fr|de|es|cs|nl|ru|uk|ro|pt_BR|pl|it|ja|id|ca|sl|hr|zh_CN|bg|tr|lt`

**Examples**:
- `/en/blog/` - English
- `/fr/blog/` - French
- `/de/blog/` - German

### Homepage Route

 Root route redirects to default locale:

```yaml
index:
    path: /
    controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction
    defaults:
        path: /%app.locale%/
        permanent: false
```

Then locale-specific homepage is handled by DefaultController.

## Controller Organization

```
src/Controller/
├── BlogController.php          # Public blog (view posts, comments, search)
├── SecurityController.php      # Login/logout
├── UserController.php          # User profile management
├── DefaultController.php       # Locale-aware homepage
└── Admin/
    ├── BlogController.php      # Admin post management (CRUD)
    └── UserController.php      # Admin user management
```

## Public Controllers

### BlogController

**Location**: `src/Controller/BlogController.php`
**Route Prefix**: `/blog`
**Access**: Public (except comment creation)

#### Index / Paginated Listing

```php
#[Route('/', name: 'blog_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
#[Route('/page/{page}', name: 'blog_index_paginated', defaults: ['_format' => 'html'], requirements: ['page' => Requirement::POSITIVE_INT], methods: ['GET'])]
#[Cache(smaxage: 10)]
public function index(Request $request, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
{
    $tag = null;
    if ($request->query->has('tag')) {
        $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
    }

    $latestPosts = $posts->findLatest($page, $tag);

    return $this->render('blog/index.'.$_format.'.twig', [
        'paginator' => $latestPosts,
        'tagName' => $tag?->getName(),
    ]);
}
```

**Features**:
- Pagination support (default page 1)
- Tag filtering via query parameter `?tag=lorem`
- Shared-max-age cache header (10 seconds)
- Template determined by `_format` (html or xml for RSS)

**URLs**:
- `/blog/` - First page
- `/blog/page/2` - Page 2
- `/blog/?tag=lorem` - Filter by tag

#### RSS Feed

```php
#[Route('/rss.xml', name: 'blog_rss', defaults: ['page' => '1', '_format' => 'xml'], methods: ['GET'])]
```

**Implementation**:
- Shares same `index()` method as HTML blog index
- Uses `_format` routing parameter to determine template format
- Renders `templates/blog/index.xml.twig` instead of `index.html.twig`
- Returns RSS 2.0 compliant XML feed

**Template**: `templates/blog/index.xml.twig`
- RSS 2.0 format with Atom namespace for self-link
- Includes channel metadata (title, description, link, language)
- Each post as an `<item>` with title, link, description, pubDate
- Post tags included as `<category>` elements
- Author information in each item
- Absolute URLs generated with `url()` function
- RFC 2822 date format for `<pubDate>`

**Auto-Discovery**:
The RSS feed is advertised in the HTML `<head>` section via:
```html
<link rel="alternate" type="application/rss+xml"
      title="Symfony Demo Blog"
      href="{{ url('blog_rss') }}">
```

This enables browsers and feed readers to automatically detect the RSS feed.

#### Single Post View

```php
#[Route('/posts/{slug:post}', name: 'blog_post', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
public function postShow(Post $post): Response
{
    return $this->render('blog/post_show.html.twig', ['post' => $post]);
}
```

**Features**:
- EntityValueResolver auto-fetches Post by slug
- `{slug:post}` syntax maps slug parameter to Post entity
- Slug validation: ASCII alphanumeric + hyphens

**URL**: `/blog/posts/lorem-ipsum-dolor-sit-amet`

#### Comment Creation

```php
#[Route('/comment/{postSlug}/new', name: 'comment_new', requirements: ['postSlug' => Requirement::ASCII_SLUG], methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED')]
public function commentNew(
    #[CurrentUser] User $user,
    Request $request,
    #[MapEntity(mapping: ['postSlug' => 'slug'])] Post $post,
    EventDispatcherInterface $eventDispatcher,
    EntityManagerInterface $entityManager,
): Response {
    $comment = new Comment();
    $comment->setAuthor($user);
    $post->addComment($comment);

    $form = $this->createForm(CommentType::class, $comment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($comment);
        $entityManager->flush();

        $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

        return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()], Response::HTTP_SEE_OTHER);
    }

    return $this->render('blog/comment_form_error.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}
```

**Features**:
- Requires authentication (`IS_AUTHENTICATED`)
- `#[CurrentUser]` injects authenticated user
- `#[MapEntity]` maps postSlug parameter to Post.slug property
- Dispatches event after successful comment creation
- Returns 303 redirect on success
- Renders error template on validation failure

**URL**: `/blog/comment/lorem-ipsum-dolor-sit-amet/new` (POST)

#### Comment Form Embedding

```php
public function commentForm(Post $post): Response
{
    $form = $this->createForm(CommentType::class);

    return $this->render('blog/_comment_form.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}
```

**Usage**: Called via `render()` in templates (no route needed)

```twig
{{ render(controller('App\\Controller\\BlogController::commentForm', {post: post})) }}
```

#### Search

```php
#[Route('/search', name: 'blog_search', methods: ['GET'])]
public function search(Request $request): Response
{
    return $this->render('blog/search.html.twig', [
        'query' => (string) $request->query->get('q', '')
    ]);
}
```

**Features**:
- Renders search page (actual search done via Live Component)
- Query parameter: `?q=search+terms`

**URL**: `/blog/search?q=symfony`

---

### SecurityController

**Location**: `src/Controller/SecurityController.php`
**Access**: Public

#### Login

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
- Redirects authenticated users to blog
- Displays authentication errors
- Pre-fills username from last attempt

**URL**: `/login`

#### Logout

```php
#[Route('/logout', name: 'security_logout')]
public function logout(): never
{
    throw new \Exception('Don\'t forget to activate logout in security.yaml');
}
```

**Note**: Actual logout handled by Symfony security system. Method never executed.

**URL**: `/logout`

---

### UserController

**Location**: `src/Controller/UserController.php`
**Route Prefix**: `/profile`
**Access**: Requires `ROLE_USER`

#### Edit Profile

```php
#[Route('/edit', name: 'user_edit')]
#[IsGranted('IS_AUTHENTICATED')]
public function edit(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        $this->addFlash('success', 'user.updated_successfully');

        return $this->redirectToRoute('user_edit');
    }

    return $this->render('user/edit.html.twig', [
        'user' => $user,
        'form' => $form,
    ]);
}
```

**Features**:
- Single action for both display and submission
- Flash message on success
- Redirects to same page after save (PRG pattern)

**URL**: `/profile/edit`

#### Change Password

```php
#[Route('/change-password', name: 'user_change_password')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $form = $this->createForm(ChangePasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
        );

        $entityManager->flush();

        return $this->redirectToRoute('security_logout');
    }

    return $this->render('user/change_password.html.twig', [
        'form' => $form,
    ]);
}
```

**Features**:
- Requires full authentication (not remember-me)
- Validates current password (in form type)
- Auto-logout after password change
- Uses ChangePasswordType form

**URL**: `/profile/change-password`

---

## Admin Controllers

### Admin BlogController

**Location**: `src/Controller/Admin/BlogController.php`
**Route Prefix**: `/admin/post`
**Access**: Requires `ROLE_ADMIN`

**Class Declaration**:
```php
#[Route('/admin/post')]
#[IsGranted('ROLE_ADMIN')]
final class BlogController extends AbstractController
```

#### Admin Post Index

```php
#[Route('/', name: 'admin_post_index')]
public function index(#[CurrentUser] User $author, PostRepository $posts): Response
{
    $authorPosts = $posts->findBy(['author' => $author], ['publishedAt' => 'DESC']);

    return $this->render('admin/blog/index.html.twig', ['posts' => $authorPosts]);
}
```

**Features**:
- Shows only current admin's posts
- Sorted by publish date descending

**URL**: `/admin/post/`

#### Create Post

```php
#[Route('/new', name: 'admin_post_new')]
public function new(#[CurrentUser] User $author, Request $request, EntityManagerInterface $entityManager): Response
{
    $post = new Post();
    $post->setAuthor($author);

    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($post);
        $entityManager->flush();

        $this->addFlash('success', 'post.created_successfully');

        if ($form->get('saveAndCreateNew')->isClicked()) {
            return $this->redirectToRoute('admin_post_new');
        }

        return $this->redirectToRoute('admin_post_index');
    }

    return $this->render('admin/blog/new.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}
```

**Features**:
- Auto-assigns current admin as author
- Flash message on success
- "Save and create new" button support
- Auto-generates slug via form event

**URL**: `/admin/post/new`

#### View Post

```php
#[Route('/{id:post}', name: 'admin_post_show', requirements: ['id' => Requirement::POSITIVE_INT])]
#[IsGranted(PostVoter::SHOW, subject: 'post')]
public function show(Post $post): Response
{
    return $this->render('admin/blog/show.html.twig', [
        'post' => $post,
    ]);
}
```

**Features**:
- EntityValueResolver fetches post by ID
- Custom voter ensures only author can view
- ID must be positive integer

**URL**: `/admin/post/1`

#### Edit Post

```php
#[Route('/{id:post}/edit', name: 'admin_post_edit', requirements: ['id' => Requirement::POSITIVE_INT])]
#[IsGranted(PostVoter::EDIT, subject: 'post')]
public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'post.updated_successfully');

        return $this->redirectToRoute('admin_post_edit', ['id' => $post->getId()]);
    }

    return $this->render('admin/blog/edit.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}
```

**Features**:
- Custom voter ensures only author can edit
- PRG pattern (redirect to same edit page)
- Flash message on success

**URL**: `/admin/post/1/edit`

#### Delete Post

```php
#[Route('/{id}/delete', name: 'admin_post_delete', requirements: ['id' => Requirement::POSITIVE_INT], methods: ['POST'])]
#[IsGranted(PostVoter::DELETE, subject: 'post')]
public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
{
    if (!$this->isCsrfTokenValid('delete', (string) $request->request->get('token'))) {
        return $this->redirectToRoute('admin_post_index');
    }

    $post->getTags()->clear();
    $entityManager->remove($post);
    $entityManager->flush();

    $this->addFlash('success', 'post.deleted_successfully');

    return $this->redirectToRoute('admin_post_index');
}
```

**Features**:
- POST only (no GET deletion)
- CSRF token validation
- Custom voter ensures only author can delete
- Clears tags before deletion
- Comments auto-deleted via orphanRemoval

**URL**: `/admin/post/1/delete` (POST)

---

### Admin UserController

**Location**: `src/Controller/Admin/UserController.php`
**Route Prefix**: `/admin/users`
**Access**: Requires `ROLE_ADMIN`

**Class Declaration**:
```php
#[Route('/admin/users')]
#[IsGranted(User::ROLE_ADMIN)]
final class UserController extends AbstractController
```

#### User List

```php
#[Route('/', name: 'admin_user_index', methods: ['GET'])]
public function index(UserRepository $users): Response
{
    return $this->render('admin/user/index.html.twig', [
        'users' => $users->findAll(),
    ]);
}
```

**Features**:
- Lists all users in the system
- Shows username, full name, email, and roles
- Provides "Login As" functionality via switch_user
- Highlights current logged-in user

**URL**: `/admin/users/`

#### Create User

```php
#[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): Response {
    $user = new User();
    $form = $this->createForm(AdminUserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('password')->getData();
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'user.created_successfully');

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('admin/user/new.html.twig', [
        'user' => $user,
        'form' => $form,
    ]);
}
```

**Features**:
- Admin can create users with all properties
- Password hashing via UserPasswordHasherInterface
- Role assignment (ROLE_USER, ROLE_ADMIN)
- Uses AdminUserType form with password field
- PRG pattern with 303 redirect

**URL**: `/admin/users/new`

**Switch User Feature**:
The user list template provides "Login As" links using Symfony's built-in `switch_user` functionality:

```twig
<a href="{{ path('blog_index', {'_switch_user': user.username}) }}">
    Login As
</a>
```

To switch back to the original admin account, use:
```
?_switch_user=_exit
```

This is enabled in `config/packages/security.yaml`:
```yaml
firewalls:
    main:
        switch_user: true
```

---

## Default Controller

**Location**: `src/Controller/DefaultController.php`

```php
#[Route('/')]
final class DefaultController extends AbstractController
{
    /**
     * Homepage - shows navigation to blog and admin sections.
     */
    #[Route('', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('default/homepage.html.twig');
    }
}
```

**Features**:
- Renders locale-aware homepage
- Shows blog navigation for all visitors
- Shows admin links (user management, post management) for admins
- Shows login link for unauthenticated users
- Displays demo credentials information

**URL**: `/{_locale}/` (e.g., `/en/`, `/fr/`)

---

## Controller Best Practices Applied

### ✅ Extend AbstractController
All controllers extend `AbstractController` for helper methods:
- `render()`
- `redirectToRoute()`
- `addFlash()`
- `createForm()`
- `getUser()`
- `isGranted()`
- `denyAccessUnlessGranted()`

### ✅ Final Classes
All controllers marked as `final` to prevent inheritance.

### ✅ Dependency Injection
Services injected via constructor or method arguments:

```php
public function edit(
    Request $request,
    Post $post,
    EntityManagerInterface $entityManager
): Response
```

### ✅ PHP Attributes for Configuration
Routing, caching, security configured via attributes:

```php
#[Route('/blog/posts/{slug:post}', name: 'blog_post')]
#[Cache(smaxage: 10)]
#[IsGranted('ROLE_ADMIN')]
```

### ✅ CurrentUser Attribute
Type-safe user injection:

```php
public function index(#[CurrentUser] User $author): Response
```

### ✅ EntityValueResolver
Auto-fetch entities from route parameters:

```php
#[Route('/{id:post}')]
public function show(Post $post): Response
```

### ✅ MapEntity for Custom Mapping
Map when parameter name != property name:

```php
#[MapEntity(mapping: ['postSlug' => 'slug'])]
Post $post
```

### ✅ Single Action for Form Display + Processing
Both GET and POST in same method:

```php
public function edit(Request $request): Response
{
    $form = $this->createForm(...);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle submission
        return $this->redirectToRoute(...);
    }

    // Display form
    return $this->render(...);
}
```

### ✅ Flash Messages
User feedback after actions:

```php
$this->addFlash('success', 'post.created_successfully');
```

### ✅ POST-Redirect-GET Pattern
Redirect after successful form submission:

```php
if ($form->isSubmitted() && $form->isValid()) {
    $entityManager->flush();
    return $this->redirectToRoute('admin_post_index');
}
```

### ✅ HTTP Method Restrictions
Specify allowed methods:

```php
#[Route('/delete', methods: ['POST'])]
```

### ✅ Route Requirements
Validate route parameters:

```php
#[Route('/page/{page}', requirements: ['page' => Requirement::POSITIVE_INT])]
```

## Route Summary Table

| URL | Route Name | Controller | Method | Auth | Description |
|-----|------------|------------|--------|------|-------------|
| `/` | homepage | DefaultController::index | GET | Public | Homepage |
| `/blog/` | blog_index | BlogController::index | GET | Public | Blog listing |
| `/blog/page/{page}` | blog_index_paginated | BlogController::index | GET | Public | Paginated blog |
| `/blog/rss.xml` | blog_rss | BlogController::index | GET | Public | RSS feed |
| `/blog/posts/{slug}` | blog_post | BlogController::postShow | GET | Public | View post |
| `/blog/comment/{slug}/new` | comment_new | BlogController::commentNew | POST | Auth | Create comment |
| `/blog/search` | blog_search | BlogController::search | GET | Public | Search page |
| `/login` | security_login | SecurityController::login | GET | Public | Login form |
| `/logout` | security_logout | SecurityController::logout | GET | Public | Logout |
| `/profile/edit` | user_edit | UserController::edit | GET/POST | User | Edit profile |
| `/profile/change-password` | user_change_password | UserController::changePassword | GET/POST | User | Change password |
| `/admin/post/` | admin_post_index | Admin\BlogController::index | GET | Admin | Admin post list |
| `/admin/post/new` | admin_post_new | Admin\BlogController::new | GET/POST | Admin | Create post |
| `/admin/post/{id}` | admin_post_show | Admin\BlogController::show | GET | Admin | View post |
| `/admin/post/{id}/edit` | admin_post_edit | Admin\BlogController::edit | GET/POST | Admin | Edit post |
| `/admin/post/{id}/delete` | admin_post_delete | Admin\BlogController::delete | POST | Admin | Delete post |
| `/admin/users/` | admin_user_index | Admin\UserController::index | GET | Admin | List all users |
| `/admin/users/new` | admin_user_new | Admin\UserController::new | GET/POST | Admin | Create user |

**Note**: All routes (except `/`) are prefixed with `/{_locale}`.
