# Plan 11: Internationalization System

**Priority:** 🟢 STANDARD
**Estimated Time:** 8-10 hours
**Dependencies:** Plan 10 (Form & Utils Tests)
**Status:** Ready to execute

---

## Context & Objective

Restore complete internationalization support (currently 75% gap - 37 of 38 translation files missing):
1. Create language selector UI component
2. Add translation files for all 38 supported locales
3. Implement RedirectToPreferredLocaleSubscriber for locale handling
4. Add RTL CSS support for Arabic/Hebrew
5. Update templates to use translation keys consistently
6. Configure translator in event subscribers

This restores the multi-language support that is a core feature of the Symfony Demo.

---

## Reference Materials

### Memory Files
- `.claude/memory/templates-frontend.md` - Template patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 11: I18n/L10n gap)

### Walkthrought Files
```bash
git show walkthrought:templates/default/_language_selector.html.twig
git show walkthrought:translations/messages+intl-icu.en.xlf
git show walkthrought:src/EventSubscriber/RedirectToPreferredLocaleSubscriber.php
git show walkthrought:assets/styles/_rtl.scss
```

### Supported Locales
38 languages: ar, be, bg, bs, ca, cs, de, el, en, es, eu, fa, fr, hr, hu, id, it, ja, lt, ne, nl, pl, pt, pt_BR, ro, ru, sk, sl, sq, sr_Cyrl, sr_Latn, tr, uk, vi, zh_CN, zh_TW

---

## Prerequisites

- Base template architecture complete (Plan 04)
- Understanding of Symfony translation system
- Knowledge of XLF format for translations

---

## Deliverables Checklist

### Code Changes
- [ ] `templates/default/_language_selector.html.twig` - Language switcher UI
- [ ] `translations/messages+intl-icu.*.xlf` - 38 translation files
- [ ] `src/EventSubscriber/RedirectToPreferredLocaleSubscriber.php` - Locale redirect
- [ ] `assets/styles/_rtl.scss` - RTL support
- [ ] Update `CommentNotificationSubscriber` with translator
- [ ] Update `config/services.yaml` - Locale configuration

### Testing
- [ ] Language selector displays all languages
- [ ] Switching language works
- [ ] RTL languages display correctly
- [ ] Email notifications translated
- [ ] All UI text uses translation keys

### Documentation
- [ ] Update memory file with i18n patterns

---

## Implementation Steps

### Step 1: Create Language Selector Component

**File:** `templates/default/_language_selector.html.twig`

```twig
{#
    Language selector modal for switching application locale.

    Displays all supported locales with native names.
#}

{% set locales = {
    'en': 'English',
    'ar': 'العربية',
    'be': 'Беларуская',
    'bg': 'Български',
    'bs': 'Bosanski',
    'ca': 'Català',
    'cs': 'Čeština',
    'de': 'Deutsch',
    'el': 'Ελληνικά',
    'es': 'Español',
    'eu': 'Euskara',
    'fa': 'فارسی',
    'fr': 'Français',
    'hr': 'Hrvatski',
    'hu': 'Magyar',
    'id': 'Bahasa Indonesia',
    'it': 'Italiano',
    'ja': '日本語',
    'lt': 'Lietuvių',
    'ne': 'नेपाली',
    'nl': 'Nederlands',
    'pl': 'Polski',
    'pt': 'Português',
    'pt_BR': 'Português (Brasil)',
    'ro': 'Română',
    'ru': 'Русский',
    'sk': 'Slovenčina',
    'sl': 'Slovenščina',
    'sq': 'Shqip',
    'sr_Cyrl': 'Српски',
    'sr_Latn': 'Srpski',
    'tr': 'Türkçe',
    'uk': 'Українська',
    'vi': 'Tiếng Việt',
    'zh_CN': '中文 (简体)',
    'zh_TW': '中文 (繁體)',
} %}

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
       data-bs-toggle="dropdown" aria-expanded="false">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/>
        </svg>
        {{ locales[app.request.locale]|default('English') }}
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown" style="max-height: 400px; overflow-y: auto;">
        {% for code, name in locales %}
            <li>
                <a class="dropdown-item{% if app.request.locale == code %} active{% endif %}"
                   href="{{ path(app.request.attributes.get('_route'), app.request.attributes.get('_route_params')|merge({_locale: code})) }}">
                    {{ name }}
                </a>
            </li>
        {% endfor %}
    </ul>
</li>
```

---

### Step 2: Add Language Selector to Base Template

**File:** `templates/base.html.twig`

Update navigation to include language selector before user dropdown:

```twig
<ul class="navbar-nav ms-auto">
    {# ... existing nav items ... #}

    {# Language Selector #}
    {% include 'default/_language_selector.html.twig' %}

    {# User dropdown or login #}
    {% if app.user %}
        {# ... user dropdown ... #}
    {% endif %}
</ul>
```

---

### Step 3: Create Translation Files Template

Due to space constraints, I'll show the template structure. You'll need to create 38 files.

**File:** `translations/messages+intl-icu.en.xlf` (English template)

```xml
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="en">
  <file id="messages.en">
    <unit id="note">
      <segment>
        <source>note</source>
        <target>NOTE</target>
      </segment>
    </unit>
    <unit id="tip">
      <segment>
        <source>tip</source>
        <target>TIP</target>
      </segment>
    </unit>

    <!-- Actions -->
    <unit id="action.show">
      <segment>
        <source>action.show</source>
        <target>Show</target>
      </segment>
    </unit>
    <unit id="action.edit">
      <segment>
        <source>action.edit</source>
        <target>Edit</target>
      </segment>
    </unit>
    <unit id="action.delete">
      <segment>
        <source>action.delete</source>
        <target>Delete</target>
      </segment>
    </unit>
    <unit id="action.create">
      <segment>
        <source>action.create</source>
        <target>Create</target>
      </segment>
    </unit>
    <unit id="action.save">
      <segment>
        <source>action.save</source>
        <target>Save changes</target>
      </segment>
    </unit>
    <unit id="action.cancel">
      <segment>
        <source>action.cancel</source>
        <target>Cancel</target>
      </segment>
    </unit>

    <!-- Add all other translation keys from messages.en.yaml -->
    <!-- Convert YAML format to XLF format -->

  </file>
</xliff>
```

**NOTE:** Create similar files for all 38 locales. For non-English locales, translate the `<target>` elements.

---

### Step 4: Create RedirectToPreferredLocaleSubscriber

**File:** `src/EventSubscriber/RedirectToPreferredLocaleSubscriber.php`

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirects users to their preferred locale when visiting the homepage.
 *
 * When accessing the root URL (/), redirects to /{_locale}/
 * based on the Accept-Language header.
 */
final readonly class RedirectToPreferredLocaleSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = [
        'en', 'ar', 'be', 'bg', 'bs', 'ca', 'cs', 'de', 'el', 'es', 'eu', 'fa',
        'fr', 'hr', 'hu', 'id', 'it', 'ja', 'lt', 'ne', 'nl', 'pl', 'pt', 'pt_BR',
        'ro', 'ru', 'sk', 'sl', 'sq', 'sr_Cyrl', 'sr_Latn', 'tr', 'uk', 'vi',
        'zh_CN', 'zh_TW',
    ];

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $defaultLocale,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle main requests to the homepage
        if (!$event->isMainRequest() || $request->getPathInfo() !== '/') {
            return;
        }

        // Get preferred locale from Accept-Language header
        $preferredLocale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);

        // Fallback to default locale if not supported
        $locale = $preferredLocale ?? $this->defaultLocale;

        // Redirect to localized homepage
        $response = new RedirectResponse(
            $this->urlGenerator->generate('homepage', ['_locale' => $locale])
        );

        $event->setResponse($response);
    }
}
```

**Configuration:** `config/services.yaml`

```yaml
services:
    App\EventSubscriber\RedirectToPreferredLocaleSubscriber:
        arguments:
            $defaultLocale: '%kernel.default_locale%'
```

---

### Step 5: Add RTL Support CSS

**File:** `assets/styles/_rtl.scss`

```scss
/*
 * Right-to-Left (RTL) support for Arabic, Hebrew, Farsi, etc.
 */

html[dir="rtl"] {
    direction: rtl;
    text-align: right;

    // Reverse float directions
    .float-start {
        float: right !important;
    }

    .float-end {
        float: left !important;
    }

    // Reverse text alignment
    .text-start {
        text-align: right !important;
    }

    .text-end {
        text-align: left !important;
    }

    // Reverse margins
    .ms-auto {
        margin-left: 0 !important;
        margin-right: auto !important;
    }

    .me-auto {
        margin-right: 0 !important;
        margin-left: auto !important;
    }

    // Reverse padding
    .ps-3 {
        padding-left: 0 !important;
        padding-right: 1rem !important;
    }

    .pe-3 {
        padding-right: 0 !important;
        padding-left: 1rem !important;
    }

    // Dropdown menus
    .dropdown-menu-end {
        right: auto !important;
        left: 0 !important;
    }

    // Navbar
    .navbar-nav {
        margin-left: 0;
        margin-right: auto;
    }

    // Borders
    .border-start {
        border-left: 0 !important;
        border-right: var(--bs-border-width) var(--bs-border-style) var(--bs-border-color) !important;
    }
}
```

Import in `assets/styles/app.scss`:

```scss
@import 'rtl';
```

---

### Step 6: Update Base Template for RTL

**File:** `templates/base.html.twig`

Update the HTML tag to support RTL:

```twig
<html lang="{{ app.request.locale }}"
      dir="{% if app.request.locale in ['ar', 'fa', 'he'] %}rtl{% else %}ltr{% endif %}">
```

---

### Step 7: Update CommentNotificationSubscriber with Translator

**File:** `src/EventSubscriber/CommentNotificationSubscriber.php`

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\CommentCreatedEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CommentNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator, // ✅ ADD TRANSLATOR
        private string $sender,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();
        $post = $comment->getPost();

        // Generate URL to post
        $postUrl = $this->urlGenerator->generate(
            'blog_post',
            ['slug' => $post->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // ✅ USE TRANSLATOR for email subject and body
        $subject = $this->translator->trans('notification.comment_created', [
            'postTitle' => $post->getTitle(),
        ]);

        $email = (new TemplatedEmail())
            ->from(Address::create($this->sender))
            ->to(new Address($post->getAuthor()->getEmail()))
            ->subject($subject)
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->context([
                'post' => $post,
                'comment' => $comment,
                'postUrl' => $postUrl,
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            // In production, you'd use a logger service here
        }
    }
}
```

Add translation keys:

```yaml
notification:
    comment_created: 'New comment on "{postTitle}"'
```

---

## Verification Criteria

### Test Language Selector

1. Navigate to any page
2. ✓ Language dropdown appears in navbar
3. ✓ Shows 38 languages with native names
4. ✓ Current language is highlighted
5. ✓ Click on a language
6. ✓ Page reloads in new language
7. ✓ URL contains locale parameter (e.g., `/fr/blog`)

### Test RTL Languages

1. Switch to Arabic (ar) or Farsi (fa)
2. ✓ Page layout mirrors (text alignment right)
3. ✓ Navbar items reversed
4. ✓ Comments indent on right side
5. ✓ All text displays correctly

### Test Locale Redirect

1. Visit root URL: `http://localhost/`
2. ✓ Automatically redirects to `/en/` (or browser's preferred language)
3. ✓ Accept-Language header respected

### Test Translations

1. Switch between multiple languages
2. ✓ All UI text translates
3. ✓ Flash messages translate
4. ✓ Form labels translate
5. ✓ Email notifications translate (check email content)

---

## Memory File Updates

**File:** `.claude/memory/templates-frontend.md`

Add section on internationalization patterns and translation system documentation.

---

## Context Reset Information

**Files Created:**
1. `templates/default/_language_selector.html.twig` - Language switcher
2. `translations/messages+intl-icu.*.xlf` - 38 translation files
3. `src/EventSubscriber/RedirectToPreferredLocaleSubscriber.php` - Locale redirect
4. `assets/styles/_rtl.scss` - RTL CSS support

**Files Modified:**
1. `templates/base.html.twig` - Added language selector, RTL support
2. `src/EventSubscriber/CommentNotificationSubscriber.php` - Added translator
3. `config/services.yaml` - Configured locale subscriber

**Next Plan:** `12-frontend-assets.md`

---

## Completion Checklist

- [ ] Language selector component created
- [ ] 38 translation files created (XLF format)
- [ ] RedirectToPreferredLocaleSubscriber implemented
- [ ] RTL CSS support added
- [ ] CommentNotificationSubscriber updated with translator
- [ ] Base template updated for RTL
- [ ] All locales tested
- [ ] RTL languages tested
- [ ] Memory file updated
- [ ] Git commit created

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 12