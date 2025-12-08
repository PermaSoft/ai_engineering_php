# Plan 02: Error Handling & Pages

**Priority:** 🔴 CRITICAL
**Estimated Time:** 3-4 hours
**Dependencies:** None
**Status:** Ready to execute

---

## Context & Objective

Create professional error pages for HTTP errors (403, 404, 500, generic) to replace Symfony's default error pages. Currently, users see generic Symfony error screens which provide poor UX and potentially expose sensitive information in production.

Professional error pages:
- Improve user experience during errors
- Maintain branding consistency
- Provide helpful navigation options
- Hide sensitive technical details in production

---

## Reference Materials

### Memory Files
- `.claude/memory/templates-frontend.md` - Template structure and patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 7.4: Missing Templates Impact)

### Key Walkthrought Files
```bash
git show walkthrought:templates/bundles/TwigBundle/Exception/error.html.twig
git show walkthrought:templates/bundles/TwigBundle/Exception/error403.html.twig
git show walkthrought:templates/bundles/TwigBundle/Exception/error404.html.twig
git show walkthrought:templates/bundles/TwigBundle/Exception/error500.html.twig
```

### Translation Keys Needed
- `http_error.name` - Error title
- `http_error.description` - Error description
- Various status code titles and messages

---

## Prerequisites

- Base template (`templates/base.html.twig`) exists
- Translation system configured
- Read access to walkthrought branch

---

## Deliverables Checklist

### Code Changes
- [ ] `templates/bundles/TwigBundle/Exception/error.html.twig` - Generic error page
- [ ] `templates/bundles/TwigBundle/Exception/error403.html.twig` - Forbidden error
- [ ] `templates/bundles/TwigBundle/Exception/error404.html.twig` - Not found error
- [ ] `templates/bundles/TwigBundle/Exception/error500.html.twig` - Server error
- [ ] `translations/messages.en.yaml` - Add error translations

### Testing
- [ ] Test 403 error (access denied)
- [ ] Test 404 error (page not found)
- [ ] Test 500 error (server error)
- [ ] Test in dev vs prod mode

### Documentation
- [ ] Update memory file with error handling patterns
- [ ] Document how to test error pages in dev mode

---

## Implementation Steps

### Step 1: Create Directory Structure

```bash
mkdir -p templates/bundles/TwigBundle/Exception
```

**Rationale:** Symfony automatically uses templates in this location for error pages.

---

### Step 2: Create Generic Error Template

**File:** `templates/bundles/TwigBundle/Exception/error.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'http_error.name'|trans }}{% endblock %}

{% block body %}
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                {% if status_code is defined %}
                    <h1 class="display-1 text-danger">{{ status_code }}</h1>
                {% else %}
                    <h1 class="display-1 text-danger">Error</h1>
                {% endif %}

                <p class="lead">
                    {{ 'http_error.description'|trans }}
                </p>

                {% if status_text is defined %}
                    <p class="text-muted">{{ status_text }}</p>
                {% endif %}

                <hr class="my-4">

                <p>
                    <a href="{{ path('blog_index') }}" class="btn btn-primary">
                        {{ 'action.browse_app'|trans }}
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        {{ 'action.back'|trans }}
                    </a>
                </p>
            </div>
        </div>
    </div>
{% endblock %}

{% block sidebar %}{% endblock %}
```

**Key Features:**
- Extends base layout for consistency
- Displays status code prominently
- Provides navigation options
- Uses translation keys for i18n support
- Centers content for better UX

---

### Step 3: Create 403 Forbidden Error Template

**File:** `templates/bundles/TwigBundle/Exception/error403.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'http_error_403.name'|trans }}{% endblock %}

{% block body %}
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <h1 class="display-1 text-warning">403</h1>
                <h2 class="mb-4">{{ 'http_error_403.title'|trans }}</h2>

                <p class="lead">
                    {{ 'http_error_403.description'|trans }}
                </p>

                <hr class="my-4">

                <div class="alert alert-info" role="alert">
                    <strong>{{ 'label.note'|trans }}:</strong>
                    {{ 'http_error_403.help_text'|trans }}
                </div>

                <p class="mt-4">
                    {% if app.user %}
                        <a href="{{ path('blog_index') }}" class="btn btn-primary">
                            {{ 'action.browse_app'|trans }}
                        </a>
                        <a href="{{ path('user_edit') }}" class="btn btn-outline-secondary">
                            {{ 'action.go_to_profile'|trans }}
                        </a>
                    {% else %}
                        <a href="{{ path('security_login') }}" class="btn btn-primary">
                            {{ 'action.sign_in'|trans }}
                        </a>
                        <a href="{{ path('blog_index') }}" class="btn btn-outline-secondary">
                            {{ 'action.browse_app'|trans }}
                        </a>
                    {% endif %}
                </p>
            </div>
        </div>
    </div>
{% endblock %}

{% block sidebar %}{% endblock %}
```

**Key Features:**
- Contextual actions based on authentication status
- Helpful message explaining why access was denied
- Warning color scheme (yellow/orange)
- Links to login or profile

---

### Step 4: Create 404 Not Found Error Template

**File:** `templates/bundles/TwigBundle/Exception/error404.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'http_error_404.name'|trans }}{% endblock %}

{% block body %}
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <h1 class="display-1 text-primary">404</h1>
                <h2 class="mb-4">{{ 'http_error_404.title'|trans }}</h2>

                <p class="lead">
                    {{ 'http_error_404.description'|trans }}
                </p>

                <hr class="my-4">

                <p class="text-muted">
                    {{ 'http_error_404.suggestion'|trans }}
                </p>

                <div class="mt-4">
                    <a href="{{ path('blog_index') }}" class="btn btn-primary">
                        {{ 'action.browse_app'|trans }}
                    </a>
                    <a href="{{ path('blog_search') }}" class="btn btn-outline-secondary">
                        {{ 'action.search'|trans }}
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        {{ 'action.back'|trans }}
                    </a>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block sidebar %}{% endblock %}
```

**Key Features:**
- Most user-friendly error (not their fault)
- Multiple navigation options (home, search, back)
- Helpful suggestion text
- Primary color scheme (blue)

---

### Step 5: Create 500 Server Error Template

**File:** `templates/bundles/TwigBundle/Exception/error500.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'http_error_500.name'|trans }}{% endblock %}

{% block body %}
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <h1 class="display-1 text-danger">500</h1>
                <h2 class="mb-4">{{ 'http_error_500.title'|trans }}</h2>

                <p class="lead">
                    {{ 'http_error_500.description'|trans }}
                </p>

                <hr class="my-4">

                <div class="alert alert-danger" role="alert">
                    <strong>{{ 'label.error'|trans }}:</strong>
                    {{ 'http_error_500.help_text'|trans }}
                </div>

                <p class="text-muted small">
                    {{ 'http_error_500.contact_admin'|trans }}
                </p>

                <p class="mt-4">
                    <a href="{{ path('blog_index') }}" class="btn btn-primary">
                        {{ 'action.browse_app'|trans }}
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        {{ 'action.back'|trans }}
                    </a>
                </p>
            </div>
        </div>
    </div>
{% endblock %}

{% block sidebar %}{% endblock %}
```

**Key Features:**
- Serious error indication (red)
- Apology message (server's fault)
- Suggestion to contact admin
- Limited actions (might not be safe to navigate)

---

### Step 6: Add Translation Keys

**File:** `translations/messages.en.yaml`

Add these translations:

```yaml
# HTTP Error Pages
http_error:
    name: 'Error'
    description: 'An error occurred while processing your request. Please try again.'

http_error_403:
    name: 'Access Denied'
    title: 'Access Denied'
    description: 'You do not have permission to access this page.'
    help_text: 'If you believe this is an error, please contact the administrator or try logging in with a different account.'

http_error_404:
    name: 'Page Not Found'
    title: 'Page Not Found'
    description: 'The page you are looking for does not exist.'
    suggestion: 'You can browse the blog, search for content, or return to the previous page.'

http_error_500:
    name: 'Server Error'
    title: 'Internal Server Error'
    description: 'We apologize for the inconvenience. An unexpected error has occurred.'
    help_text: 'Our team has been notified and is working to resolve the issue.'
    contact_admin: 'If the problem persists, please contact the administrator.'

# Common Actions
action:
    browse_app: 'Browse Blog'
    browse_app: 'Go to Homepage'
    back: 'Go Back'
    go_to_profile: 'Go to Profile'
    search: 'Search'

# Labels
label:
    note: 'Note'
    error: 'Error'
```

---

## Verification Criteria

### Test Error Pages in Dev Mode

Symfony doesn't show custom error pages in dev mode by default. To test:

#### Method 1: Use Preview Error Controller

```bash
# Test 403
symfony open:local/_error/403

# Test 404
symfony open:local/_error/404

# Test 500
symfony open:local/_error/500
```

#### Method 2: Temporarily Switch to Prod Mode

```bash
# In .env.local
APP_ENV=prod
APP_DEBUG=0

# Clear cache
php bin/console cache:clear

# Test by navigating to non-existent pages or restricted areas
# Remember to switch back to dev when done!
```

### Manual Testing Checklist

#### Test 403 - Access Denied
1. Logout from application
2. Try to access `/en/admin/post`
3. ✓ Should see custom 403 error page
4. ✓ Should display "Access Denied" heading
5. ✓ Should show login button (not logged in)
6. Login as regular user
7. Try to access `/en/admin/post` again
8. ✓ Should see custom 403 error page
9. ✓ Should show profile and home buttons (logged in but not admin)

#### Test 404 - Not Found
1. Navigate to `/en/posts/nonexistent-slug-12345`
2. ✓ Should see custom 404 error page
3. ✓ Should display "404" prominently
4. ✓ Should show search, home, and back buttons
5. ✓ Should use friendly, non-technical language

#### Test 500 - Server Error
1. Use preview controller: `/_error/500`
2. ✓ Should see custom 500 error page
3. ✓ Should display "500" prominently
4. ✓ Should show apologetic message
5. ✓ Should suggest contacting admin
6. ✓ Should use red/danger color scheme

#### Test Generic Error
1. Use preview controller: `/_error/418` (teapot error)
2. ✓ Should see generic error page
3. ✓ Should display status code if available
4. ✓ Should provide navigation options

---

## Memory File Updates

**File:** `.claude/memory/templates-frontend.md`

Add this section:

```markdown
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
```

---

## Context Reset Information

If resuming after context reset:

**Files Created:**
1. `templates/bundles/TwigBundle/Exception/error.html.twig`
2. `templates/bundles/TwigBundle/Exception/error403.html.twig`
3. `templates/bundles/TwigBundle/Exception/error404.html.twig`
4. `templates/bundles/TwigBundle/Exception/error500.html.twig`

**Files Modified:**
1. `translations/messages.en.yaml` - Added error page translations

**Verification:**
- Test all 4 error pages using `/_error/{code}`
- Verify translations display correctly
- Check navigation links work
- Memory file updated

**Next Plan:** `03-entity-repository-fixes.md`

---

## Additional Enhancements (Optional)

### Add Custom Error Images

Create a simple SVG illustration for each error type:
- 403: Lock icon
- 404: Magnifying glass or compass
- 500: Wrench or gear

### Add Error Code Reference

For developers, add a comment with the error code:
```twig
<!-- Error Code: {{ status_code }} - {{ status_text }} -->
```

### Log User-Facing Errors

Add logging to track when users encounter errors:
```php
// In error page template (dev mode only)
{% if app.debug %}
    <!-- Triggered at: {{ "now"|date("Y-m-d H:i:s") }} -->
{% endif %}
```

---

## Completion Checklist

- [ ] 4 error page templates created
- [ ] Translation keys added
- [ ] All error pages tested
- [ ] Dev mode preview tested
- [ ] Prod mode behavior verified
- [ ] Memory file updated
- [ ] Git commit created with message:
  ```
  feat: add professional error pages (403, 404, 500)

  - Create custom error page templates
  - Add translations for all error messages
  - Provide contextual navigation options
  - Maintain consistent branding
  - Hide sidebar on error pages

  Improves UX by replacing generic Symfony error pages.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 03
