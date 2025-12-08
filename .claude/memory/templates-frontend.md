# Templates & Frontend

Complete view layer implementation and frontend architecture.

## Template Organization

```
templates/
├── base.html.twig                  # Base layout
├── default/
│   └── homepage.html.twig          # Homepage
├── blog/
│   ├── index.html.twig             # Blog listing (HTML)
│   ├── index.xml.twig              # RSS feed (XML)
│   ├── post_show.html.twig         # Single post view
│   ├── search.html.twig            # Search page
│   ├── _post.html.twig             # Post display partial (summary/full)
│   ├── _post_tags.html.twig        # Tag list display
│   ├── _comment.html.twig          # Single comment display
│   ├── _comment_form.html.twig     # Comment form partial
│   ├── _rss.html.twig              # RSS feed link component
│   └── comment_form_error.html.twig # Comment validation errors
├── admin/blog/
│   ├── index.html.twig             # Admin post list
│   ├── new.html.twig               # Create post
│   ├── edit.html.twig              # Edit post
│   └── show.html.twig              # Admin post view
├── user/
│   ├── edit.html.twig              # Edit profile
│   └── change_password.html.twig   # Change password
├── security/
│   └── login.html.twig             # Login form
├── form/
│   └── fields.html.twig            # Custom form field rendering
└── components/
    └── BlogSearchComponent.html.twig # Live search component
```

---

## Base Template

**Location**: `templates/base.html.twig`

Master layout extended by all pages.

```twig
<!DOCTYPE html>
<html lang="{{ app.request.locale }}" dir="{{ is_rtl(app.request.locale) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{% block title %}Symfony Demo{% endblock %}</title>
    {% block stylesheets %}
        {{ importmap('app') }}
    {% endblock %}
</head>
<body>
    <header>
        {% block header %}
            <nav>
                <a href="{{ path('homepage') }}">
                    Symfony Demo
                </a>

                {% if is_granted('ROLE_ADMIN') %}
                    <a href="{{ path('admin_post_index') }}">
                        {{ 'menu.admin'|trans }}
                    </a>
                {% endif %}

                {% if app.user %}
                    <span>{{ app.user.fullName }}</span>
                    <a href="{{ path('user_edit') }}">
                        {{ 'menu.user_profile'|trans }}
                    </a>
                    <a href="{{ path('security_logout') }}">
                        {{ 'menu.logout'|trans }}
                    </a>
                {% else %}
                    <a href="{{ path('security_login') }}">
                        {{ 'menu.login'|trans }}
                    </a>
                {% endif %}

                {# Locale switcher #}
                <div>
                    {% for locale in locales() %}
                        <a href="{{ path(app.request.attributes.get('_route'),
                                         app.request.attributes.get('_route_params')|merge({_locale: locale})) }}"
                           class="{{ app.request.locale == locale ? 'active' : '' }}">
                            {{ locale|upper }}
                        </a>
                    {% endfor %}
                </div>
            </nav>
        {% endblock %}
    </header>

    <main>
        {% block body %}{% endblock %}
    </main>

    <footer>
        {% block footer %}
            <p>&copy; {{ 'now'|date('Y') }} Symfony Demo</p>
        {% endblock %}
    </footer>

    {% block javascripts %}
        {{ importmap('app') }}
    {% endblock %}
</body>
</html>
```

### Key Features

**Language & Direction**:
```twig
<html lang="{{ app.request.locale }}" dir="{{ is_rtl(app.request.locale) ? 'rtl' : 'ltr' }}">
```
- Dynamic locale from request
- RTL support for Arabic, Hebrew, Farsi

**Asset Management**:
```twig
{{ importmap('app') }}
```
Uses Symfony Asset Mapper for modern asset bundling

**User Menu**:
```twig
{% if app.user %}
    <span>{{ app.user.fullName }}</span>
{% endif %}
```
Access current user via `app.user`

**Conditional Admin Link**:
```twig
{% if is_granted('ROLE_ADMIN') %}
    <a href="{{ path('admin_post_index') }}">Admin Panel</a>
{% endif %}
```

**Locale Switcher**:
```twig
{% for locale in locales() %}
    <a href="{{ path(app.request.attributes.get('_route'),
                     app.request.attributes.get('_route_params')|merge({_locale: locale})) }}">
        {{ locale|upper }}
    </a>
{% endfor %}
```
Maintains current route while switching locale

**Blocks for Extension**:
- `title` - Page title
- `stylesheets` - Additional CSS
- `header` - Header content
- `body` - Main content (required)
- `footer` - Footer content
- `javascripts` - Additional JS

---

## Blog Templates

### Blog Index

**Location**: `templates/blog/index.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'title.blog_index'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.blog_index'|trans }}</h1>

    {% if tagName %}
        <p>{{ 'label.filtered_by'|trans }} <strong>{{ tagName }}</strong></p>
    {% endif %}

    {% for post in paginator.results %}
        <article>
            <h2>
                <a href="{{ path('blog_post', {slug: post.slug}) }}">
                    {{ post.title }}
                </a>
            </h2>

            <p class="metadata">
                {{ 'post.posted_on'|trans }}
                <time datetime="{{ post.publishedAt|date('c') }}">
                    {{ post.publishedAt|format_datetime('long', 'medium', locale=app.request.locale) }}
                </time>
                {{ 'post.by'|trans }}
                {{ post.author.fullName }}
            </p>

            <p>{{ post.summary }}</p>

            <div>
                {% for tag in post.tags %}
                    <a href="{{ path('blog_index', {tag: tag.name}) }}">
                        {{ tag.name }}
                    </a>
                {% endfor %}
            </div>
        </article>
    {% else %}
        <p>{{ 'post.no_posts_found'|trans }}</p>
    {% endfor %}

    {# Pagination #}
    {% if paginator.hasToPaginate %}
        <nav>
            {% if paginator.hasPreviousPage %}
                <a href="{{ path('blog_index_paginated', {page: paginator.previousPage}) }}">
                    {{ 'action.previous'|trans }}
                </a>
            {% endif %}

            <span>{{ 'label.page'|trans }} {{ paginator.currentPage }} / {{ paginator.lastPage }}</span>

            {% if paginator.hasNextPage %}
                <a href="{{ path('blog_index_paginated', {page: paginator.nextPage}) }}">
                    {{ 'action.next'|trans }}
                </a>
            {% endif %}
        </nav>
    {% endif %}
{% endblock %}
```

**Features**:
- Tag filtering display
- Formatted publish date with timezone
- Author attribution
- Tag links for filtering
- Pagination controls
- Empty state message

### RSS Feed

**Location**: `templates/blog/index.xml.twig`

```twig
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ 'rss.title'|trans }}</title>
        <description>{{ 'rss.description'|trans }}</description>
        <link>{{ url('blog_index') }}</link>
        <atom:link href="{{ url('blog_rss') }}" rel="self" type="application/rss+xml"/>

        {% for post in paginator.results %}
            <item>
                <title>{{ post.title }}</title>
                <description>{{ post.summary }}</description>
                <pubDate>{{ post.publishedAt|date('r') }}</pubDate>
                <link>{{ url('blog_post', {slug: post.slug}) }}</link>
                <guid>{{ url('blog_post', {slug: post.slug}) }}</guid>
            </item>
        {% endfor %}
    </channel>
</rss>
```

**Features**:
- Standard RSS 2.0 format
- Absolute URLs with `url()` function
- RFC 2822 date format

### Single Post View

**Location**: `templates/blog/post_show.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ post.title }}{% endblock %}

{% block body %}
    <article>
        <h1>{{ post.title }}</h1>

        <p class="metadata">
            {{ 'post.posted_on'|trans }}
            <time datetime="{{ post.publishedAt|date('c') }}">
                {{ post.publishedAt|format_datetime('long', 'medium') }}
            </time>
            {{ 'post.by'|trans }}
            <a href="#">{{ post.author.fullName }}</a>
        </p>

        <div>
            {% for tag in post.tags %}
                <a href="{{ path('blog_index', {tag: tag.name}) }}">
                    {{ tag.name }}
                </a>
            {% endfor %}
        </div>

        <div>
            {{ post.content|markdown_to_html }}
        </div>
    </article>

    <section id="comments">
        <h2>{{ 'post.num_comments'|trans({count: post.comments|length}) }}</h2>

        {% for comment in post.comments %}
            <article id="comment_{{ comment.id }}">
                <p class="metadata">
                    <strong>{{ comment.author.fullName }}</strong>
                    {{ 'post.commented_on'|trans }}
                    <time datetime="{{ comment.publishedAt|date('c') }}">
                        {{ comment.publishedAt|format_datetime('medium', 'short') }}
                    </time>
                </p>
                <div>
                    {{ comment.content|nl2br }}
                </div>
            </article>
        {% endfor %}

        {% if is_granted('IS_AUTHENTICATED') %}
            {{ render(controller('App\\\\Controller\\\\BlogController::commentForm', {post: post})) }}
        {% else %}
            <p>
                <a href="{{ path('security_login') }}">{{ 'action.sign_in'|trans }}</a>
                {{ 'post.to_publish_a_comment'|trans }}
            </p>
        {% endif %}
    </section>
{% endblock %}
```

**Features**:
- Markdown rendering: `post.content|markdown_to_html`
- Comment count with pluralization
- Comment anchors: `id="comment_{{ comment.id }}"`
- Embedded comment form via `render(controller(...))`
- Login prompt for guests
- Newline to `<br>` conversion for comments

### Comment Form Partial

**Location**: `templates/blog/_comment_form.html.twig`

```twig
{{ form_start(form, {action: path('comment_new', {postSlug: post.slug})}) }}
    {{ form_widget(form) }}

    <button type="submit">{{ 'action.publish_comment'|trans }}</button>
{{ form_end(form) }}
```

**Features**:
- Partial template (prefixed with `_`)
- Custom action URL
- Form method: POST

---

## Admin Templates

### Admin Post Index

**Location**: `templates/admin/blog/index.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'title.admin_post_index'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.admin_post_index'|trans }}</h1>

    <a href="{{ path('admin_post_new') }}">
        {{ 'action.create_post'|trans }}
    </a>

    <table>
        <thead>
            <tr>
                <th>{{ 'label.title'|trans }}</th>
                <th>{{ 'label.published_at'|trans }}</th>
                <th>{{ 'label.actions'|trans }}</th>
            </tr>
        </thead>
        <tbody>
            {% for post in posts %}
                <tr>
                    <td>{{ post.title }}</td>
                    <td>{{ post.publishedAt|format_datetime('medium', 'short') }}</td>
                    <td>
                        <a href="{{ path('admin_post_show', {id: post.id}) }}">
                            {{ 'action.show'|trans }}
                        </a>
                        <a href="{{ path('admin_post_edit', {id: post.id}) }}">
                            {{ 'action.edit'|trans }}
                        </a>
                    </td>
                </tr>
            {% else %}
                <tr>
                    <td colspan="3">{{ 'post.no_posts_found'|trans }}</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
```

### Create/Edit Post

**Location**: `templates/admin/blog/new.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'title.new_post'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.new_post'|trans }}</h1>

    {{ form_start(form) }}
        {{ form_widget(form) }}

        <button type="submit">{{ 'action.save'|trans }}</button>
        <button type="submit" name="saveAndCreateNew">
            {{ 'action.save_and_create_new'|trans }}
        </button>
    {{ form_end(form) }}
{% endblock %}
```

**Features**:
- Two submit buttons (different names)
- Form rendered with single `form_widget(form)`
- Buttons in template, not form class

### Delete Confirmation

**Location**: `templates/admin/blog/show.html.twig` (excerpt)

```twig
<form method="post" action="{{ path('admin_post_delete', {id: post.id}) }}"
      onsubmit="return confirm('{{ 'post.delete_confirmation'|trans }}')">
    <input type="hidden" name="token" value="{{ csrf_token('delete') }}">
    <button type="submit">{{ 'action.delete_post'|trans }}</button>
</form>
```

**Features**:
- JavaScript confirmation dialog
- CSRF token for security
- POST method for deletion

---

## User Templates

### Edit Profile

**Location**: `templates/user/edit.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'title.edit_user'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.edit_user'|trans }}</h1>

    {{ form_start(form) }}
        {{ form_widget(form) }}

        <button type="submit">{{ 'action.save'|trans }}</button>
        <a href="{{ path('blog_index') }}">{{ 'action.cancel'|trans }}</a>
    {{ form_end(form) }}
{% endblock %}
```

### Change Password

**Location**: `templates/user/change_password.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'title.change_password'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.change_password'|trans }}</h1>

    {{ form_start(form) }}
        {{ form_widget(form) }}

        <button type="submit">{{ 'action.save'|trans }}</button>
        <a href="{{ path('user_edit') }}">{{ 'action.cancel'|trans }}</a>
    {{ form_end(form) }}
{% endblock %}
```

---

## Security Templates

### Login Form

**Location**: `templates/security/login.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'title.login'|trans }}{% endblock %}

{% block body %}
    <h1>{{ 'title.login'|trans }}</h1>

    {% if error %}
        <div class="alert alert-danger">
            {{ error.messageKey|trans(error.messageData) }}
        </div>
    {% endif %}

    <form action="{{ path('security_login') }}" method="post">
        <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

        <div>
            <label for="username">{{ 'label.username'|trans }}</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}" required autofocus>
        </div>

        <div>
            <label for="password">{{ 'label.password'|trans }}</label>
            <input type="password" id="password" name="_password" required>
        </div>

        <div>
            <input type="checkbox" id="remember_me" name="_remember_me" checked>
            <label for="remember_me">{{ 'label.remember_me'|trans }}</label>
        </div>

        <button type="submit">{{ 'action.sign_in'|trans }}</button>
    </form>
{% endblock %}
```

**Features**:
- CSRF token for login
- Remember-me checkbox (checked by default)
- Error message display
- Pre-filled username from last attempt
- Auto-focus on username field

---

## Flash Messages

**Location**: `templates/base.html.twig` (excerpt)

```twig
{% for type, messages in app.flashes %}
    {% for message in messages %}
        <div class="alert alert-{{ type }}">
            {{ message|trans }}
        </div>
    {% endfor %}
{% endfor %}
```

**Usage in Controllers**:
```php
$this->addFlash('success', 'post.created_successfully');
$this->addFlash('error', 'post.not_found');
```

**Types**:
- `success` - Green alert
- `error` - Red alert
- `warning` - Yellow alert
- `info` - Blue alert

---

## Twig Features Used

### Template Inheritance

```twig
{% extends 'base.html.twig' %}

{% block title %}Custom Title{% endblock %}

{% block body %}
    {{ parent() }}  {# Include parent block content #}
    <p>Additional content</p>
{% endblock %}
```

### Translations

```twig
{{ 'label.title'|trans }}
{{ 'post.num_comments'|trans({count: post.comments|length}) }}
```

### URL Generation

```twig
{# Relative URL #}
<a href="{{ path('blog_index') }}">Blog</a>

{# Absolute URL #}
<link href="{{ url('blog_rss') }}" rel="alternate" type="application/rss+xml">

{# With parameters #}
<a href="{{ path('blog_post', {slug: post.slug}) }}">{{ post.title }}</a>
```

### Date Formatting

```twig
{# ISO 8601 format for datetime attribute #}
<time datetime="{{ post.publishedAt|date('c') }}">

{# Localized format #}
{{ post.publishedAt|format_datetime('long', 'medium', locale=app.request.locale) }}

{# RFC 2822 for RSS #}
<pubDate>{{ post.publishedAt|date('r') }}</pubDate>

{# Custom format #}
{{ 'now'|date('Y') }}
```

### Markdown Rendering

```twig
{{ post.content|markdown_to_html }}
```

### Newlines to Break Tags

```twig
{{ comment.content|nl2br }}
```

### Controller Rendering

```twig
{{ render(controller('App\\\\Controller\\\\BlogController::commentForm', {post: post})) }}
```

### Authorization Checks

```twig
{% if is_granted('ROLE_ADMIN') %}
    <a href="{{ path('admin_post_index') }}">Admin</a>
{% endif %}

{% if is_granted(constant('App\\\\Security\\\\PostVoter::EDIT'), post) %}
    <a href="{{ path('admin_post_edit', {id: post.id}) }}">Edit</a>
{% endif %}
```

### Global Variables

```twig
{# Current user #}
{{ app.user.fullName }}

{# Request #}
{{ app.request.locale }}

{# Current route #}
{{ app.request.attributes.get('_route') }}
```

---

## Frontend Assets

### Asset Mapper

**Configuration**: `config/packages/asset_mapper.yaml`

```yaml
framework:
    asset_mapper:
        paths:
            - assets/
        excluded_patterns:
            - '*.scss'
```

### JavaScript Entry Point

**Location**: `assets/app.js`

```javascript
import './stimulus_bootstrap.js';

console.log('Symfony Demo App initialized');
```

**CRITICAL**: The file imports `./stimulus_bootstrap.js` (NOT `./bootstrap.js`)

### Stimulus Bootstrap

**Location**: `assets/stimulus_bootstrap.js`

```javascript
import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
```

### Import Maps

**Location**: `importmap.php`

```php
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
];
```

### Controllers JSON

**Location**: `assets/controllers.json`

Configuration for Stimulus controllers.

### SASS Compilation

**Configuration**: Symfonycasts SASS Bundle

Compiles `.scss` files to CSS automatically.

**Files**: `assets/styles/*.scss`

### Stimulus Controllers

**Location**: `assets/controllers/`

JavaScript interactivity using Stimulus framework.

**Example**: Search controller for live search

### Asset Installation

After setup or changes, install assets:

```bash
php bin/console importmap:install
```

This downloads JavaScript packages from CDN into `assets/vendor/`

---

## Best Practices Applied

### ✅ Snake Case File Names

```
post_show.html.twig
change_password.html.twig
```

### ✅ Underscore Prefix for Partials

```
_comment_form.html.twig
```

### ✅ Template Inheritance

All templates extend `base.html.twig`

### ✅ Translation Keys

All text uses translation keys, never hardcoded strings

### ✅ path() Function for URLs

Never hardcode URLs, always use `path()` or `url()`

### ✅ Buttons in Templates

Form buttons added in templates, not form classes

### ✅ parent() for Extension

```twig
{% block body %}
    {{ parent() }}
    <p>Additional content</p>
{% endblock %}
```

### ✅ render() for Embedded Controllers

```twig
{{ render(controller('...')) }}
```

### ✅ Filters for Transformation

```twig
{{ content|markdown_to_html }}
{{ comment|nl2br }}
{{ date|format_datetime('long', 'medium') }}
```

---

## Template Summary

| Template | Purpose | Features |
|----------|---------|----------|
| base.html.twig | Master layout | Header, nav, footer, locale switcher |
| blog/index.html.twig | Blog listing | Pagination, tag filtering |
| blog/index.xml.twig | RSS feed | XML format, absolute URLs |
| blog/post_show.html.twig | Single post | Markdown rendering, comments, embedded form |
| blog/_comment_form.html.twig | Comment form | Partial template, POST action |
| admin/blog/index.html.twig | Admin list | Table, action links |
| admin/blog/new.html.twig | Create post | Two submit buttons |
| admin/blog/edit.html.twig | Edit post | Pre-filled form |
| user/edit.html.twig | Edit profile | Disabled username field |
| user/change_password.html.twig | Change password | Current password validation |
| security/login.html.twig | Login | CSRF, remember-me, error display |
| components/BlogSearchComponent.html.twig | Live search | Stimulus live component |
---

## Error Page Architecture

### Location
Error pages are located in `templates/bundles/TwigBundle/Exception/`:
- `error.html.twig` - Generic error page (fallback)
- `error403.html.twig` - Access Denied (Forbidden)
- `error404.html.twig` - Page Not Found
- `error500.html.twig` - Internal Server Error

### Structure
All error pages:
- Extend `base.html.twig` for consistent branding
- Override `{% block sidebar %}` to hide sidebar
- Use translation keys for all text
- Provide contextual navigation options
- Use appropriate color schemes (danger, warning, primary)

### Status Code Display
- Prominently display HTTP status code (h1.display-1)
- Show human-friendly title and description
- Avoid technical jargon in production

### Navigation Options
Error pages should provide:
- Link to homepage/blog index
- Back button using `javascript:history.back()`
- Context-specific actions (login for 403, search for 404)

### Testing Error Pages
In development mode, use Symfony's preview controller:
```bash
/_error/{statusCode}
```

Examples:
- `/_error/403` - Preview 403 error
- `/_error/404` - Preview 404 error
- `/_error/500` - Preview 500 error

### Translation Keys
All error pages use `http_error_*.` translation keys:
- `http_error_403.name`, `http_error_403.title`, `http_error_403.description`
- `http_error_404.name`, `http_error_404.title`, `http_error_404.description`
- `http_error_500.name`, `http_error_500.title`, `http_error_500.description`

### Contextual Features by Error Type

**403 (Access Denied)**:
- Warning color scheme (yellow/orange)
- Shows different buttons based on authentication state:
  - Not logged in: Show "Sign in" and "Go to Homepage"
  - Logged in: Show "Go to Homepage" and "Go to Profile"
- Info alert with helpful explanation

**404 (Page Not Found)**:
- Primary color scheme (blue)
- Most user-friendly messaging (not user's fault)
- Multiple navigation options: homepage, search, back
- Suggestion text for next steps

**500 (Internal Server Error)**:
- Danger color scheme (red)
- Apologetic messaging (server's fault)
- Danger alert with team notification message
- Contact admin suggestion
- Limited navigation options (may not be safe to navigate)

**Generic Error**:
- Falls back to generic template
- Displays status code if available
- Provides basic navigation options
- Suitable for uncommon error codes (418, etc.)

---

## Template Hierarchy

### Three-Level Inheritance

```
base.html.twig (Foundation)
├── admin/layout.html.twig (Admin-specific)
│   ├── admin/blog/index.html.twig
│   ├── admin/blog/edit.html.twig
│   ├── admin/blog/show.html.twig
│   ├── admin/blog/new.html.twig
│   ├── admin/user/index.html.twig
│   └── admin/user/new.html.twig
├── blog/index.html.twig (Public pages)
├── blog/post_show.html.twig
└── user/edit.html.twig
```

### Base Template Structure

**`templates/base.html.twig`** provides:
- Fixed top navbar with responsive collapse
- User authentication state (login/logout with dropdown menu)
- Two-column layout (8/4 on medium, 9/3 on large screens)
- Sidebar block for content-specific additions
- Footer with copyright and Symfony link
- Flash messages include via partial
- Bootstrap 5 styling throughout

### Admin Layout

**`templates/admin/layout.html.twig`** extends base and adds:
- Admin header with title
- Admin navigation (posts, users, back to blog)
- Quick actions sidebar (create post, create user)
- `admin_content` block for page content
- Override sidebar with admin-specific content
- `admin_sidebar` block for additional sidebar items

### Template Blocks

Available blocks in base template:
- `page_title` - Page title (shown in browser tab)
- `stylesheets` - Additional CSS
- `body` - Main content area (8-9 columns)
- `sidebar` - Right sidebar content (4-3 columns)
- `javascripts` - Additional JavaScript

Admin template adds:
- `admin_title` - Admin page heading (replaces h1 in content)
- `admin_content` - Admin page content (inside body block)
- `admin_sidebar` - Additional admin sidebar items

### Flash Messages

Flash messages are handled via `default/_flash_messages.html.twig` partial.
Automatically displays all flash message types with Bootstrap 5 alerts.

Features:
- Dismissible alerts with close button
- Automatic fade show animation
- Translates message keys

Supported types:
- `success` - Green alert
- `danger` - Red alert  
- `warning` - Yellow alert
- `info` - Blue alert

Usage in controllers:
```php
$this->addFlash('success', 'message.translation.key');
```

### Navigation Bar

The navbar includes:
- Responsive collapse for mobile
- Brand link to homepage
- Blog and Search links
- Admin link (only for ROLE_ADMIN)
- User dropdown menu (when authenticated) with:
  - Full name display
  - Profile link
  - Logout link
- Login link (when not authenticated)

All navigation uses Bootstrap 5 navbar classes and is fully responsive.

### Two-Column Layout

Pages use a responsive two-column layout:
- Main content: `col-md-8 col-lg-9`
- Sidebar: `col-md-4 col-lg-3`
- Stacks vertically on mobile (< 768px)
- Proper spacing with `mt-5 pt-4` for fixed navbar

The sidebar can be overridden in any template:
```twig
{% block sidebar %}
    {{ parent() }}  {# Include default about section #}
    
    {# Add custom sidebar content #}
{% endblock %}
```

Or completely replaced:
```twig
{% block sidebar %}
    {# Custom sidebar only #}
{% endblock %}
```

Or hidden (error pages):
```twig
{% block sidebar %}{% endblock %}
```

---

## Blog Template Partials

Blog partials follow the DRY principle, eliminating code duplication and improving maintainability.

### Partial Files Location

Blog partials are in `templates/blog/`:
- `_post.html.twig` - Post display (summary or full)
- `_post_tags.html.twig` - Tag list display
- `_comment.html.twig` - Single comment display
- `_comment_form.html.twig` - Comment submission form
- `_rss.html.twig` - RSS feed link component

### Partial Naming Convention

- Prefix with underscore (`_`) to indicate partial template
- Use lowercase with underscores (snake_case)

### Post Partial Usage

**Display post summary:**
```twig
{% include 'blog/_post.html.twig' with {
    post: post,
    show_full_content: false
} %}
```

**Display full post:**
```twig
{% include 'blog/_post.html.twig' with {
    post: post,
    show_full_content: true
} %}
```

**Features:**
- Flexible display (summary or full content)
- Proper semantic HTML5 (article, header, footer)
- Formatted publication date with locale support
- Markdown rendering
- Includes tag display partial
- "Read more" button for summaries
- Horizontal rule between posts

### Tag Partial Usage

```twig
{% include 'blog/_post_tags.html.twig' with {tags: post.tags} %}
```

**Features:**
- Only renders if tags exist
- Clickable badges that filter by tag
- Bootstrap 5 badge styling
- Proper spacing between tags

### Comment Partial Usage

**Display comment:**
```twig
{% include 'blog/_comment.html.twig' with {comment: comment} %}
```

**Features:**
- Semantic HTML with proper structure
- Visual left border for hierarchy
- Author name and email
- Formatted timestamp with locale
- Markdown rendering for comment content
- Anchor ID for direct linking

**Display comment form:**
```twig
{% include 'blog/_comment_form.html.twig' with {form: commentForm, post: post} %}
```

**Features:**
- Card layout for visual separation
- Helpful placeholder text
- Markdown hint
- Comment moderation notice
- Proper Bootstrap 5 form styling
- Translation keys throughout

### RSS Link Usage (Sidebar)

```twig
{% block sidebar %}
    {{ parent() }}
    {% include 'blog/_rss.html.twig' %}
{% endblock %}
```

**Features:**
- Card format for sidebar
- RSS icon (Bootstrap Icons)
- Warning color (orange/yellow)
- Descriptive help text
- Full-width button

### Benefits of Partials

- **DRY principle** - Don't Repeat Yourself
- **Easier maintenance** - Change in one place
- **Testable in isolation** - Each partial can be tested independently
- **Reusable across templates** - Use in multiple places
- **Clear separation of concerns** - Each partial has a single responsibility

---

## Internationalization (i18n)

Complete multi-language support with 30 locales.

### Supported Locales

**30 Languages:**
- ar (Arabic), bg (Bulgarian), bs (Bosnian), ca (Catalan)
- cs (Czech), de (German), en (English), es (Spanish)
- eu (Basque), fr (French), hr (Croatian), id (Indonesian)
- it (Italian), ja (Japanese), lt (Lithuanian), ne (Nepali)
- nl (Dutch), pl (Polish), pt_BR (Brazilian Portuguese), ro (Romanian)
- ru (Russian), sk (Slovak), sl (Slovenian), sq (Albanian)
- sr_Cyrl (Serbian Cyrillic), sr_Latn (Serbian Latin)
- tr (Turkish), uk (Ukrainian), vi (Vietnamese), zh_CN (Chinese Simplified)

**Configuration:**
- `config/services.yaml`: `app.supported_locales` parameter (pipe-separated)
- `config/routes.yaml`: `_locale` requirement references parameter
- All routes prefixed with `/{_locale}/`

### Language Selector Component

**Location**: `templates/default/_language_selector.html.twig`

**Features:**
- Dropdown with all 30 languages in native names
- Globe icon (SVG)
- Shows current language
- Highlights active language
- Preserves current route and parameters when switching
- Bootstrap dropdown styling
- Scrollable menu (max-height: 400px)

**Usage in base template:**
```twig
{# Language Selector #}
{% include 'default/_language_selector.html.twig' %}
```

**Route preservation:**
```twig
{{ path(app.request.attributes.get('_route'),
        app.request.attributes.get('_route_params')|merge({_locale: code})) }}
```

### RTL (Right-to-Left) Support

**Enabled for**: Arabic (ar)

**Base template HTML tag:**
```twig
<html lang="{{ app.request.locale }}"
      dir="{% if app.request.locale == 'ar' %}rtl{% else %}ltr{% endif %}">
```

**RTL CSS**: `assets/styles/_rtl.scss`

**Includes:**
- Direction and text-align reversal
- Float direction reversal (start/end)
- Margin and padding reversal (ms/me, ps/pe)
- Dropdown menu positioning
- Navbar alignment
- Border side reversal

**Import in `assets/styles/app.scss`:**
```scss
@import 'rtl';
```

### Translation Files

**Format**: XLIFF 1.2 (`.xlf`)

**Location**: `translations/messages+intl-icu.{locale}.xlf`

**Structure:**
```xml
<?xml version="1.0"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="en" datatype="plaintext">
        <body>
            <trans-unit id="menu.homepage">
                <source>menu.homepage</source>
                <target>Blog</target>
            </trans-unit>
        </body>
    </file>
</xliff>
```

**Translation domains:**
- `messages`: UI text, labels, actions, titles
- No separate domain for validators (uses messages)

### Locale Redirection

**Event Subscriber**: `RedirectToPreferredLocaleSubscriber`

**Behavior:**
- Intercepts requests to root URL (`/`)
- Reads `Accept-Language` header
- Redirects to `/{locale}/` based on browser preference
- Falls back to default locale (`%kernel.default_locale%`)

**Implementation:**
```php
public function onKernelRequest(RequestEvent $event): void
{
    $request = $event->getRequest();

    if (!$event->isMainRequest() || $request->getPathInfo() !== '/') {
        return;
    }

    $preferredLocale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);
    $locale = $preferredLocale ?? $this->defaultLocale;

    $response = new RedirectResponse(
        $this->urlGenerator->generate('homepage', ['_locale' => $locale])
    );

    $event->setResponse($response);
}
```

### Translated Email Notifications

**CommentNotificationSubscriber** uses `TranslatorInterface`:

```php
public function __construct(
    private MailerInterface $mailer,
    private UrlGeneratorInterface $urlGenerator,
    private TranslatorInterface $translator,
    private string $sender,
) {
}

// Use translator for email subject
$subject = $this->translator->trans('notification.comment_created', [
    'postTitle' => $post->getTitle(),
]);
```

**Email template**: Uses `TemplatedEmail` with context

### Translation Key Categories

**Menu items**: `menu.*`
- `menu.homepage`, `menu.admin`, `menu.profile`, etc.

**Titles**: `title.*`
- `title.blog_index`, `title.edit_post`, etc.

**Actions**: `action.*`
- `action.create`, `action.edit`, `action.save`, etc.

**Labels**: `label.*`
- `label.title`, `label.author`, `label.tags`, etc.

**Help text**: `help.*`
- `help.post_summary`, `help.login_to_comment`, etc.

**Flash messages**: `{entity}.{action}_successfully`
- `post.created_successfully`, `user.updated_successfully`, etc.

**HTTP errors**: `http_error_*`
- `http_error_403.description`, `http_error_404.title`, etc.

**Notifications**: `notification.*`
- `notification.comment_created`

### Best Practices

1. **Always use translation keys** - Never hardcode text
2. **Use descriptive key names** - Follow namespace pattern
3. **Pass parameters for dynamic content** - `{count}`, `{postTitle}`, etc.
4. **Test with multiple locales** - Especially RTL languages
5. **Keep translations in sync** - Update all locale files when adding keys
6. **Use ICU message format** - For pluralization and advanced formatting
7. **Set locale in routes** - Use `{_locale}` parameter
8. **Preserve locale when generating URLs** - Pass `_locale` parameter

---
