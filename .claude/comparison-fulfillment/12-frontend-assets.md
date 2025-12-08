# Plan 12: Frontend Assets & Components

**Priority:** 🟢 STANDARD
**Estimated Time:** 6-8 hours
**Dependencies:** Plan 11 (Internationalization)
**Status:** Ready to execute

---

## Context & Objective

Restore frontend sophistication with missing assets and JavaScript components:
1. Add Tabler Icons library (25+ icons for UI)
2. Enhance Flatpickr integration (already started in Plan 07)
3. Add Highlight.js for code syntax highlighting
4. Create admin.js entrypoint for admin-specific JavaScript
5. Add Stimulus controllers (CSRF, login enhancement)
6. Configure all assets in importmap

This addresses the missing frontend assets gap (30 files, ~75% reduction).

---

## Reference Materials

### Memory Files
- `.claude/memory/templates-frontend.md` - Asset management
- `BRANCH_COMPARISON_REPORT.md` (Section 7.3: Asset Management)

### Walkthrought Files
```bash
git show walkthrought:importmap.php
git show walkthrought:assets/app.js
git show walkthrought:assets/admin.js
git show walkthrought:assets/controllers/csrf_controller.js
```

---

## Prerequisites

- Asset Mapper configured
- Stimulus framework working
- Understanding of Symfony UX and importmap

---

## Deliverables Checklist

### Code Changes
- [ ] Update `importmap.php` with all dependencies
- [ ] Create `assets/admin.js` - Admin entrypoint
- [ ] Create `assets/controllers/csrf_controller.js` - CSRF handling
- [ ] Create `assets/controllers/login_controller.js` - Login enhancement
- [ ] Update `assets/app.js` - Main entrypoint enhancements
- [ ] Add Highlight.js configuration

### Testing
- [ ] All JavaScript loads without errors
- [ ] Syntax highlighting works on code blocks
- [ ] Admin-specific JS works
- [ ] Icons display correctly
- [ ] Stimulus controllers activate

### Documentation
- [ ] Update memory file with asset patterns

---

## Implementation Steps

### Step 1: Update Importmap with All Dependencies

**File:** `importmap.php`

```php
<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'admin' => [
        'path' => './assets/admin.js',
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
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'flatpickr' => [
        'version' => '4.6.13',
    ],
    'flatpickr/dist/flatpickr.min.css' => [
        'version' => '4.6.13',
        'type' => 'css',
    ],
    'flatpickr/dist/l10n' => [
        'version' => '4.6.13',
    ],
    '@tabler/icons' => [
        'version' => '2.44.0',
    ],
    'highlight.js' => [
        'version' => '11.9.0',
    ],
    'highlight.js/styles/github.css' => [
        'version' => '11.9.0',
        'type' => 'css',
    ],
    'symfony-ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
];
```

Then install missing packages:

```bash
php bin/console importmap:require @tabler/icons
php bin/console importmap:require highlight.js
php bin/console importmap:require flatpickr
```

---

### Step 2: Enhance Main App Entrypoint

**File:** `assets/app.js`

```javascript
/*
 * Main application entrypoint
 */

// Start Stimulus application
import './bootstrap.js';

// Import Bootstrap
import 'bootstrap';

// Import styles
import './styles/app.scss';

// Import Turbo for fast page loads
import '@hotwired/turbo';

// Initialize syntax highlighting for code blocks
import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';

document.addEventListener('DOMContentLoaded', () => {
    // Highlight all code blocks
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);
    });
});

// Re-highlight code blocks after Turbo navigation
document.addEventListener('turbo:load', () => {
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);
    });
});

// Console welcome message
console.log('Symfony Demo Application loaded');
```

---

### Step 3: Create Admin Entrypoint

**File:** `assets/admin.js`

```javascript
/*
 * Admin-specific JavaScript entrypoint
 *
 * Loads additional features for admin panel:
 * - Enhanced UI interactions
 * - Confirmation dialogs
 * - Admin-specific components
 */

// Import main app (includes Stimulus, Bootstrap, etc.)
import './app.js';

// Admin-specific styles (if any)
// import './styles/admin.scss';

// Admin-specific functionality
document.addEventListener('DOMContentLoaded', () => {
    // Add confirmation to delete buttons
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (e) => {
            const message = element.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // Auto-hide success alerts after 5 seconds
    document.querySelectorAll('.alert-success').forEach((alert) => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

console.log('Admin panel loaded');
```

---

### Step 4: Create CSRF Protection Stimulus Controller

**File:** `assets/controllers/csrf_controller.js`

```javascript
import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for CSRF token handling
 *
 * Automatically adds CSRF token to fetch requests
 *
 * Usage:
 * <div data-controller="csrf" data-csrf-token-value="{{ csrf_token('..') }}">
 *     <button data-action="click->csrf#makeRequest">Send</button>
 * </div>
 */
export default class extends Controller {
    static values = {
        token: String,
    }

    async makeRequest(event) {
        event.preventDefault();

        const url = event.currentTarget.dataset.url;
        if (!url) {
            console.error('No URL specified for CSRF-protected request');
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.tokenValue,
                },
                body: JSON.stringify({}),
            });

            if (response.ok) {
                console.log('Request successful');
                // Handle success (e.g., show message, redirect)
            } else {
                console.error('Request failed:', response.status);
            }
        } catch (error) {
            console.error('Request error:', error);
        }
    }
}
```

---

### Step 5: Create Login Enhancement Stimulus Controller

**File:** `assets/controllers/login_controller.js`

```javascript
import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for login form enhancements
 *
 * Features:
 * - Remember username
 * - Auto-focus on page load
 * - Show/hide password toggle
 *
 * Usage:
 * <form data-controller="login">
 *     <input type="text" data-login-target="username" />
 *     <input type="password" data-login-target="password" />
 *     <button type="button" data-action="click->login#togglePassword">Show</button>
 * </form>
 */
export default class extends Controller {
    static targets = ['username', 'password', 'passwordToggle']

    connect() {
        // Auto-focus username field
        if (this.hasUsernameTarget && !this.usernameTarget.value) {
            this.usernameTarget.focus();
        }

        // Load remembered username from localStorage
        const rememberedUsername = localStorage.getItem('remembered_username');
        if (rememberedUsername && this.hasUsernameTarget) {
            this.usernameTarget.value = rememberedUsername;
            // Focus password instead if username is filled
            if (this.hasPasswordTarget) {
                this.passwordTarget.focus();
            }
        }
    }

    disconnect() {
        // Remember username when leaving page
        if (this.hasUsernameTarget && this.usernameTarget.value) {
            localStorage.setItem('remembered_username', this.usernameTarget.value);
        }
    }

    togglePassword(event) {
        event.preventDefault();

        if (!this.hasPasswordTarget) return;

        const passwordField = this.passwordTarget;
        const currentType = passwordField.getAttribute('type');

        if (currentType === 'password') {
            passwordField.setAttribute('type', 'text');
            event.currentTarget.textContent = 'Hide';
        } else {
            passwordField.setAttribute('type', 'password');
            event.currentTarget.textContent = 'Show';
        }
    }
}
```

---

### Step 6: Add Icon Usage Helper

Create a Twig extension for easy icon usage:

**File:** `src/Twig/IconExtension.php`

```php
<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for rendering Tabler icons.
 */
final class IconExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', [$this, 'renderIcon'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Renders a Tabler icon.
     *
     * Usage: {{ icon('pencil', 'text-primary') }}
     */
    public function renderIcon(string $name, string $class = '', int $size = 24): string
    {
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-%s %s" width="%d" height="%d" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><use href="/tabler-icons.svg#tabler-%s" /></svg>',
            $name,
            $class,
            $size,
            $size,
            $name
        );
    }
}
```

**Note:** You would need to create/download the Tabler icons sprite SVG file or use the npm package.

---

### Step 7: Update Admin Templates to Use Admin Entrypoint

**File:** `templates/admin/layout.html.twig`

Update the javascripts block:

```twig
{% block javascripts %}
    {{ importmap('admin') }} {# Use admin entrypoint instead of app #}
{% endblock %}
```

---

### Step 8: Add Syntax Highlighting Support to Templates

**File:** `templates/blog/post_show.html.twig`

Ensure code blocks are properly formatted:

```twig
{# Code blocks in markdown will be highlighted automatically #}
{# Make sure they have the <pre><code> structure #}

<div class="post-content">
    {{ post.content|markdown_to_html }}
</div>

{# Markdown filter should output: #}
{# <pre><code class="language-php">...</code></pre> #}
```

---

## Verification Criteria

### Test Asset Loading

```bash
# Check browser console
# Should see no errors
# Should see: "Symfony Demo Application loaded"
# In admin: "Admin panel loaded"
```

### Test Icons

If using icon extension:
```twig
{{ icon('pencil') }}
{{ icon('trash', 'text-danger') }}
```

Should render Tabler icons correctly.

### Test Syntax Highlighting

1. Create a post with code:
   ````markdown
   ```php
   <?php
   echo "Hello World";
   ```
   ````

2. View post
3. ✓ Code block has syntax highlighting
4. ✓ Colors and formatting applied
5. ✓ GitHub theme CSS loaded

### Test Admin JavaScript

1. Login as admin
2. Navigate to post management
3. Try to delete a post with `data-confirm` attribute
4. ✓ Confirmation dialog appears
5. ✓ Success alerts auto-hide after 5 seconds

### Test Stimulus Controllers

1. Login page should auto-focus username
2. Flatpickr should work on date fields (from Plan 07)
3. No console errors

---

## Memory File Updates

**File:** `.claude/memory/templates-frontend.md`

Add comprehensive documentation about:
- Asset management with importmap
- Stimulus controllers
- Icon system
- Code syntax highlighting
- Admin-specific assets

---

## Context Reset Information

**Files Created:**
1. `assets/admin.js` - Admin entrypoint
2. `assets/controllers/csrf_controller.js` - CSRF controller
3. `assets/controllers/login_controller.js` - Login enhancements
4. `src/Twig/IconExtension.php` - Icon helper

**Files Modified:**
1. `importmap.php` - Added all dependencies
2. `assets/app.js` - Enhanced with Highlight.js
3. `templates/admin/layout.html.twig` - Use admin entrypoint

**Next Plan:** `13-event-subscribers.md`

---

## Completion Checklist

- [ ] Importmap updated with all dependencies
- [ ] Admin entrypoint created
- [ ] CSRF controller created
- [ ] Login controller created
- [ ] Highlight.js integrated
- [ ] Icon system implemented
- [ ] All assets load without errors
- [ ] Syntax highlighting works
- [ ] Admin JS features work
- [ ] Memory file updated
- [ ] Git commit created

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 13