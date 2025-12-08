# Plan 04: Template Architecture & Base Layout

**Priority:** 🟠 HIGH
**Estimated Time:** 4-5 hours
**Dependencies:** Plan 02 (Error pages)
**Status:** Ready to execute

---

## Context & Objective

Enhance the base template architecture to match the professional quality of walkthrought:
1. Upgrade base.html.twig with proper navigation, sidebar, and footer
2. Create admin/layout.html.twig for admin-specific layout
3. Extract flash messages into a reusable partial
4. Fix Bootstrap inconsistencies (currently mixing 4 and 5)
5. Add proper two-column layout structure

This is a foundation for all other template work.

---

## Reference Materials

### Memory Files
- `.claude/memory/templates-frontend.md` - Template patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 7: Templates & Frontend)

### Walkthrought Files
```bash
git show walkthrought:templates/base.html.twig
git show walkthrought:templates/admin/layout.html.twig
git show walkthrought:templates/default/_flash_messages.html.twig
```

---

## Prerequisites

- Bootstrap understanding (version decision needed)
- Error pages completed (Plan 02)
- Twig syntax knowledge

---

## Deliverables Checklist

### Code Changes
- [ ] `templates/base.html.twig` - Enhanced with navigation, sidebar, footer
- [ ] `templates/admin/layout.html.twig` - NEW admin-specific layout
- [ ] `templates/default/_flash_messages.html.twig` - NEW flash messages partial
- [ ] Update importmap for consistent Bootstrap version

### Testing
- [ ] Base template renders correctly
- [ ] Admin layout inherits from base
- [ ] Flash messages display properly
- [ ] Navigation works for all user states
- [ ] Sidebar block available

### Documentation
- [ ] Update memory file with template hierarchy

---

## Implementation Steps

### Step 1: Decide Bootstrap Version

**Current Issue:** Templates use Bootstrap 4 classes but importmap loads Bootstrap 5.3.8

**Decision:** Stick with Bootstrap 5.3.8 (newer) but update all templates to use Bootstrap 5 syntax.

**Key Bootstrap 4 → 5 Changes:**
- `.ml-*`, `.mr-*` → `.ms-*`, `.me-*` (start/end instead of left/right)
- `.pl-*`, `.pr-*` → `.ps-*`, `.pe-*`
- `.text-left`, `.text-right` → `.text-start`, `.text-end`
- `.float-left`, `.float-right` → `.float-start`, `.float-end`
- `.form-group` → removed (just use `.mb-3`)
- `.form-row` → `.row`
- Data attributes: `data-toggle` → `data-bs-toggle`

---

### Step 2: Create Flash Messages Partial

**File:** `templates/default/_flash_messages.html.twig`

```twig
{# Flash messages partial - displays all flash message types #}
{% for type, messages in app.flashes %}
    {% for message in messages %}
        <div class="alert alert-{{ type }} alert-dismissible fade show" role="alert">
            {{ message|trans }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {% endfor %}
{% endfor %}
```

**Note:** Bootstrap 5 uses `btn-close` instead of close button with `&times;`

---

### Step 3: Enhanced Base Template

**File:** `templates/base.html.twig`

```twig
<!DOCTYPE html>
<html lang="{{ app.request.locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{% block page_title %}Symfony Demo Application{% endblock %}</title>

    {% block stylesheets %}
        {{ importmap('app') }}
    {% endblock %}

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
</head>

<body>
    {# Navigation Bar #}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ path('homepage') }}">
                <strong>Symfony</strong> Demo
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('blog_index') }}">
                            {{ 'menu.homepage'|trans }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('blog_search') }}">
                            {{ 'menu.search'|trans }}
                        </a>
                    </li>

                    {% if is_granted('ROLE_ADMIN') %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('admin_post_index') }}">
                                {{ 'menu.admin'|trans }}
                            </a>
                        </li>
                    {% endif %}

                    {% if app.user %}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ app.user.fullName }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ path('user_edit') }}">
                                        {{ 'menu.profile'|trans }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ path('security_logout') }}">
                                        {{ 'menu.logout'|trans }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    {% else %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('security_login') }}">
                                {{ 'menu.login'|trans }}
                            </a>
                        </li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </nav>

    {# Main Content Area with Sidebar #}
    <div class="container mt-5 pt-4">
        {# Flash Messages #}
        {% include 'default/_flash_messages.html.twig' %}

        <div class="row">
            {# Main Content #}
            <div class="col-md-8 col-lg-9">
                {% block body %}{% endblock %}
            </div>

            {# Sidebar #}
            <div class="col-md-4 col-lg-3">
                {% block sidebar %}
                    <div class="card mb-3">
                        <div class="card-header">
                            {{ 'title.about'|trans }}
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                {{ 'help.app_description'|trans }}
                            </p>
                            <a href="https://symfony.com/doc" class="btn btn-sm btn-primary w-100" target="_blank">
                                {{ 'action.browse_documentation'|trans }}
                            </a>
                        </div>
                    </div>
                {% endblock %}
            </div>
        </div>
    </div>

    {# Footer #}
    <footer class="bg-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; {{ 'now'|date('Y') }} Symfony Demo Application
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="https://symfony.com" class="text-muted text-decoration-none" target="_blank">
                        {{ 'menu.symfony_project'|trans }}
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {% block javascripts %}
        {{ importmap('app') }}
    {% endblock %}
</body>
</html>
```

**Key Features:**
- Fixed top navbar with brand and responsive collapse
- User dropdown menu when authenticated
- Two-column layout (8/4 or 9/3 grid)
- Sidebar block for content-specific sidebars
- Flash messages include
- Proper Bootstrap 5 classes
- Footer with copyright
- Translation keys for all text

---

### Step 4: Create Admin Layout Template

**File:** `templates/admin/layout.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'title.admin'|trans }} - {{ parent() }}{% endblock %}

{% block body %}
    {# Admin Header #}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{% block admin_title %}{{ 'title.admin_panel'|trans }}{% endblock %}</h1>

        {# Admin Navigation #}
        <div class="btn-group" role="group">
            <a href="{{ path('admin_post_index') }}" class="btn btn-sm btn-outline-primary">
                {{ 'menu.posts'|trans }}
            </a>
            <a href="{{ path('admin_user_index') }}" class="btn btn-sm btn-outline-primary">
                {{ 'menu.users'|trans }}
            </a>
            <a href="{{ path('blog_index') }}" class="btn btn-sm btn-outline-secondary">
                {{ 'action.back_to_blog'|trans }}
            </a>
        </div>
    </div>

    {# Admin Content #}
    {% block admin_content %}{% endblock %}
{% endblock %}

{% block sidebar %}
    {# Admin pages typically don't need a sidebar, override if needed #}
    <div class="card mb-3">
        <div class="card-header">
            {{ 'title.quick_actions'|trans }}
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <a href="{{ path('admin_post_new') }}" class="btn btn-sm btn-success">
                    {{ 'action.create_post'|trans }}
                </a>
                <a href="{{ path('admin_user_new') }}" class="btn btn-sm btn-success">
                    {{ 'action.create_user'|trans }}
                </a>
            </div>
        </div>
    </div>

    {# Allow templates to add more sidebar content #}
    {% block admin_sidebar %}{% endblock %}
{% endblock %}
```

**Key Features:**
- Extends base.html.twig for consistency
- Admin header with title and quick navigation
- Dedicated admin_content block for page content
- Sidebar with quick actions
- Bootstrap 5 button groups and grid utilities

---

### Step 5: Add Translation Keys

**File:** `translations/messages.en.yaml`

Add these translations:

```yaml
# Menu translations
menu:
    homepage: 'Blog'
    search: 'Search'
    admin: 'Admin'
    profile: 'Profile'
    logout: 'Logout'
    login: 'Login'
    posts: 'Posts'
    users: 'Users'
    symfony_project: 'Symfony Project'

# Title translations
title:
    about: 'About'
    admin: 'Admin'
    admin_panel: 'Administration Panel'
    quick_actions: 'Quick Actions'

# Help text
help:
    app_description: 'This is the official Symfony Demo application showcasing best practices for building web applications.'

# Actions
action:
    browse_documentation: 'Browse Documentation'
    back_to_blog: 'Back to Blog'
    create_post: 'Create Post'
    create_user: 'Create User'
```

---

### Step 6: Update Existing Templates to Use New Structure

Templates that need updating to extend from base:

**Example: `templates/blog/index.html.twig`**

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'title.blog_index'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.blog_posts'|trans }}</h1>

    {# Existing blog content #}
{% endblock %}

{% block sidebar %}
    {{ parent() }}  {# Keep the default about section #}

    {# Add blog-specific sidebar content #}
    <div class="card mb-3">
        <div class="card-header">
            {{ 'title.rss_feed'|trans }}
        </div>
        <div class="card-body">
            <a href="{{ path('blog_rss') }}" class="btn btn-sm btn-warning w-100">
                {{ 'action.subscribe_rss'|trans }}
            </a>
        </div>
    </div>
{% endblock %}
```

---

### Step 7: Update Admin Templates to Use Admin Layout

**Example: `templates/admin/blog/index.html.twig`**

```twig
{% extends 'admin/layout.html.twig' %}

{% block admin_title %}{{ 'title.post_management'|trans }}{% endblock %}

{% block admin_content %}
    {# Existing admin blog index content #}
{% endblock %}
```

---

## Verification Criteria

### Visual Testing

1. **Navigate to homepage:**
   - ✓ Navbar appears at top
   - ✓ Navigation links visible
   - ✓ Content in main area (8/4 split)
   - ✓ Sidebar visible on right
   - ✓ Footer at bottom

2. **Test authenticated state:**
   - Login
   - ✓ User dropdown appears in navbar
   - ✓ Shows user's full name
   - ✓ Dropdown has Profile and Logout links

3. **Test admin pages:**
   - Login as admin
   - Navigate to `/en/admin/post`
   - ✓ Admin layout loads
   - ✓ Admin header with navigation
   - ✓ Quick actions sidebar
   - ✓ Maintains navbar from base

4. **Test flash messages:**
   - Perform an action that creates a flash (e.g., login)
   - ✓ Flash message displays with proper styling
   - ✓ Close button works

5. **Test responsive:**
   - Resize browser window
   - ✓ Navbar collapses on mobile
   - ✓ Sidebar stacks below content on mobile
   - ✓ No horizontal scrolling

### Browser Testing

Test in:
- Chrome/Edge (Chromium)
- Firefox
- Safari (if on Mac)

---

## Memory File Updates

**File:** `.claude/memory/templates-frontend.md`

Add this section:

```markdown
## Template Hierarchy

### Three-Level Inheritance

```
base.html.twig (Foundation)
├── admin/layout.html.twig (Admin-specific)
│   ├── admin/blog/index.html.twig
│   ├── admin/blog/edit.html.twig
│   └── admin/user/index.html.twig
├── blog/index.html.twig (Public pages)
├── blog/post_show.html.twig
└── user/edit.html.twig
```

### Base Template Structure

**`templates/base.html.twig`** provides:
- Fixed top navbar with responsive collapse
- User authentication state (login/logout)
- Two-column layout (8/4 grid)
- Sidebar block for content-specific additions
- Footer with links
- Flash messages include

### Admin Layout

**`templates/admin/layout.html.twig`** extends base and adds:
- Admin header with title
- Admin navigation (posts, users)
- Quick actions sidebar
- `admin_content` block for page content
- Override sidebar with admin-specific content

### Template Blocks

Available blocks in base template:
- `page_title` - Page title (shown in browser tab)
- `stylesheets` - Additional CSS
- `body` - Main content area
- `sidebar` - Right sidebar content
- `javascripts` - Additional JavaScript

Admin template adds:
- `admin_title` - Admin page heading
- `admin_content` - Admin page content
- `admin_sidebar` - Additional admin sidebar items

### Flash Messages

Flash messages are handled via `default/_flash_messages.html.twig` partial.
Automatically displays all flash message types with Bootstrap 5 alerts.

Supported types:
- `success` - Green alert
- `danger` - Red alert
- `warning` - Yellow alert
- `info` - Blue alert

Usage in controllers:
```php
$this->addFlash('success', 'message.translation.key');
```
```

---

## Context Reset Information

If resuming after context reset:

**Files Created:**
1. `templates/admin/layout.html.twig` - Admin-specific layout
2. `templates/default/_flash_messages.html.twig` - Flash messages partial

**Files Modified:**
1. `templates/base.html.twig` - Enhanced with navigation, sidebar, footer
2. `translations/messages.en.yaml` - Added navigation and title translations

**Key Changes:**
- Base template now has proper navbar, sidebar, footer
- Admin layout inherits from base
- Flash messages extracted to partial
- Bootstrap 5 classes throughout
- Two-column layout structure

**Templates Need Updating:**
All existing templates should now extend either `base.html.twig` or `admin/layout.html.twig`

**Next Plan:** `05-admin-interface-polish.md`

---

## Completion Checklist

- [ ] `base.html.twig` enhanced with full features
- [ ] `admin/layout.html.twig` created
- [ ] `default/_flash_messages.html.twig` created
- [ ] Translation keys added
- [ ] All admin templates updated to use admin/layout
- [ ] Visual testing completed across browsers
- [ ] Responsive testing completed
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  feat: enhance template architecture with layouts and navigation

  - Upgrade base template with navbar, sidebar, footer
  - Create admin/layout.html.twig for admin pages
  - Extract flash messages into reusable partial
  - Add user dropdown menu when authenticated
  - Implement two-column layout structure
  - Use Bootstrap 5 classes consistently
  - Add translation keys for navigation

  Provides professional foundation for all templates.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 05
