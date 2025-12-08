# Plan 13: Event Subscribers & Cross-Cutting Concerns

**Priority:** 🟢 STANDARD
**Estimated Time:** 4-5 hours
**Dependencies:** Plan 12 (Frontend Assets)
**Status:** Ready to execute

---

## Context & Objective

Implement missing event subscribers for cross-cutting concerns:
1. **CheckRequirementsSubscriber** - Verify PHP/Symfony version compatibility
2. **ControllerSubscriber** - Add global template variables to all responses
3. **RedirectToPreferredLocaleSubscriber** - Already done in Plan 11

These subscribers handle application-wide concerns cleanly without cluttering controllers.

---

## Reference Materials

### Memory Files
- `.claude/memory/services-repositories.md` - Event patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 6.3: Missing Event Subscribers)

### Walkthrought Files
```bash
git show walkthrought:src/EventSubscriber/CheckRequirementsSubscriber.php
git show walkthrought:src/EventSubscriber/ControllerSubscriber.php
```

---

## Prerequisites

- Understanding of Symfony event system
- Knowledge of kernel events
- Event dispatcher configured

---

## Deliverables Checklist

### Code Changes
- [ ] `src/EventSubscriber/CheckRequirementsSubscriber.php` - Version checking
- [ ] `src/EventSubscriber/ControllerSubscriber.php` - Global template vars
- [ ] Update `config/services.yaml` if needed
- [ ] Add error templates for version mismatch

### Testing
- [ ] Test version checking with invalid versions
- [ ] Test global template variables available
- [ ] Test subscriber priority order
- [ ] Verify no performance impact

### Documentation
- [ ] Update memory file with event subscriber patterns

---

## Implementation Steps

### Step 1: Create CheckRequirementsSubscriber

**File:** `src/EventSubscriber/CheckRequirementsSubscriber.php`

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Checks application requirements before processing requests.
 *
 * Verifies:
 * - Minimum PHP version
 * - Minimum Symfony version
 * - Required PHP extensions
 *
 * Throws HTTP 500 error if requirements are not met.
 */
final class CheckRequirementsSubscriber implements EventSubscriberInterface
{
    private const REQUIRED_PHP_VERSION = '8.2.0';
    private const REQUIRED_SYMFONY_VERSION = '7.2.0';
    private const REQUIRED_EXTENSIONS = ['pdo', 'pdo_sqlite', 'intl', 'mbstring'];

    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to run early
            KernelEvents::REQUEST => ['onKernelRequest', 512],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Only check on main request, not sub-requests
        if (!$event->isMainRequest()) {
            return;
        }

        $this->checkPhpVersion();
        $this->checkSymfonyVersion();
        $this->checkExtensions();
    }

    private function checkPhpVersion(): void
    {
        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            throw new HttpException(
                500,
                sprintf(
                    'This application requires PHP %s or higher. You are running PHP %s.',
                    self::REQUIRED_PHP_VERSION,
                    PHP_VERSION
                )
            );
        }
    }

    private function checkSymfonyVersion(): void
    {
        $currentVersion = \Symfony\Component\HttpKernel\Kernel::VERSION;

        if (version_compare($currentVersion, self::REQUIRED_SYMFONY_VERSION, '<')) {
            throw new HttpException(
                500,
                sprintf(
                    'This application requires Symfony %s or higher. You are running Symfony %s.',
                    self::REQUIRED_SYMFONY_VERSION,
                    $currentVersion
                )
            );
        }
    }

    private function checkExtensions(): void
    {
        $missingExtensions = [];

        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if (!empty($missingExtensions)) {
            throw new HttpException(
                500,
                sprintf(
                    'The following PHP extensions are required but not loaded: %s',
                    implode(', ', $missingExtensions)
                )
            );
        }
    }
}
```

**Note:** This subscriber runs on every request with high priority. In production, you might want to cache the check result or only run in dev environment.

**Optional optimization for production:**

```php
public function onKernelRequest(RequestEvent $event): void
{
    // Skip checks in production (already verified during deployment)
    if ($event->getRequest()->server->get('APP_ENV') === 'prod') {
        return;
    }

    // ... rest of checks
}
```

---

### Step 2: Create ControllerSubscriber

**File:** `src/EventSubscriber/ControllerSubscriber.php`

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Adds global variables to all Twig templates.
 *
 * Global variables:
 * - app_version: Application version from composer.json
 * - symfony_version: Current Symfony version
 * - php_version: Current PHP version
 * - environment: Current environment (dev, prod, test)
 */
final readonly class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private string $projectDir,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Only process main requests
        if (!$event->isMainRequest()) {
            return;
        }

        // Add global Twig variables
        $this->twig->addGlobal('app_version', $this->getAppVersion());
        $this->twig->addGlobal('symfony_version', \Symfony\Component\HttpKernel\Kernel::VERSION);
        $this->twig->addGlobal('php_version', PHP_VERSION);
    }

    private function getAppVersion(): string
    {
        $composerFile = $this->projectDir . '/composer.json';

        if (!file_exists($composerFile)) {
            return 'unknown';
        }

        $composerData = json_decode((string) file_get_contents($composerFile), true);

        return $composerData['version'] ?? 'dev';
    }
}
```

**Configuration:** Add to `config/services.yaml`

```yaml
services:
    App\EventSubscriber\ControllerSubscriber:
        arguments:
            $projectDir: '%kernel.project_dir%'
```

---

### Step 3: Use Global Variables in Templates

Now you can use these global variables in any template:

**File:** `templates/base.html.twig` (footer example)

```twig
<footer class="bg-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; {{ 'now'|date('Y') }} Symfony Demo Application
                    <small class="ms-2">v{{ app_version }}</small>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Symfony {{ symfony_version }} | PHP {{ php_version }}
                </small>
            </div>
        </div>
    </div>
</footer>
```

---

### Step 4: Add Version Info to Admin Dashboard (Optional)

**File:** `templates/admin/dashboard.html.twig` (or admin sidebar)

```twig
<div class="card mb-3">
    <div class="card-header">
        {{ 'title.system_info'|trans }}
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-6">Application:</dt>
            <dd class="col-sm-6">{{ app_version }}</dd>

            <dt class="col-sm-6">Symfony:</dt>
            <dd class="col-sm-6">{{ symfony_version }}</dd>

            <dt class="col-sm-6">PHP:</dt>
            <dd class="col-sm-6">{{ php_version }}</dd>

            <dt class="col-sm-6">Environment:</dt>
            <dd class="col-sm-6">
                <span class="badge bg-{% if app.environment == 'prod' %}success{% else %}warning{% endif %}">
                    {{ app.environment }}
                </span>
            </dd>
        </dl>
    </div>
</div>
```

---

### Step 5: Create Unit Tests for Subscribers

**File:** `tests/EventSubscriber/CheckRequirementsSubscriberTest.php`

```php
<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\CheckRequirementsSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class CheckRequirementsSubscriberTest extends TestCase
{
    private CheckRequirementsSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new CheckRequirementsSubscriber();
    }

    public function testSubscribesToKernelRequest(): void
    {
        $events = CheckRequirementsSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
    }

    public function testDoesNotCheckSubRequests(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        $event = new RequestEvent(
            $kernel,
            $request,
            HttpKernelInterface::SUB_REQUEST // Sub-request
        );

        // Should not throw exception for sub-requests
        $this->subscriber->onKernelRequest($event);
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }

    public function testAllowsMainRequestWithValidRequirements(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        $event = new RequestEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Current environment has valid PHP/Symfony versions, so this should pass
        $this->subscriber->onKernelRequest($event);
        $this->assertTrue(true);
    }

    // Note: Testing version failures is difficult in unit tests
    // since we can't change PHP_VERSION or Symfony version at runtime
    // These would be better as integration tests with different environments
}
```

---

### Step 6: Add Translation Keys

**File:** `translations/messages.en.yaml`

```yaml
# System information
title:
    system_info: 'System Information'

# Error messages
error:
    php_version: 'PHP version %required% or higher is required. You are running %current%.'
    symfony_version: 'Symfony version %required% or higher is required. You are running %current%.'
    missing_extensions: 'Required PHP extensions: %extensions%'
```

---

## Verification Criteria

### Test CheckRequirementsSubscriber

1. **In development:**
   ```bash
   # Application should start normally
   symfony server:start
   # Visit any page - should work fine
   ```

2. **Test version check (optional):**
   - Temporarily change REQUIRED_PHP_VERSION to a higher version
   - Visit any page
   - Should see HTTP 500 error with version message

### Test ControllerSubscriber

1. **Check global variables available:**
   - View page source
   - Check footer or inspect Twig debug toolbar
   - Should see app_version, symfony_version, php_version

2. **Verify values:**
   ```twig
   {# In any template #}
   {{ dump(app_version) }}
   {{ dump(symfony_version) }}
   {{ dump(php_version) }}
   ```

### Performance Check

```bash
# Run performance test
php bin/console debug:event-dispatcher

# Verify subscribers are registered
# Check priority order
```

---

## Memory File Updates

**File:** `.claude/memory/services-repositories.md`

Add this section:

```markdown
## Event Subscribers

### Application Event Subscribers

#### CheckRequirementsSubscriber

Validates system requirements on every request:
- PHP version >= 8.2.0
- Symfony version >= 7.2.0
- Required extensions: pdo, pdo_sqlite, intl, mbstring

Priority: 512 (runs early)

Optimization: Can be disabled in production after initial verification.

#### ControllerSubscriber

Adds global Twig variables on every controller call:
- `app_version` - Application version from composer.json
- `symfony_version` - Current Symfony version
- `php_version` - Current PHP version

Usage in templates:
```twig
<footer>
    Version: {{ app_version }}
    Symfony {{ symfony_version }} | PHP {{ php_version }}
</footer>
```

#### RedirectToPreferredLocaleSubscriber

Redirects root URL (/) to localized homepage based on Accept-Language header.

Priority: Default

#### CommentNotificationSubscriber

Sends email notifications when comments are created.

Listens to: CommentCreatedEvent

### Event Subscriber Patterns

#### Basic Structure

```php
final readonly class MySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Handle event
    }
}
```

#### Priority

Higher numbers run earlier:
- 512: Very early (CheckRequirements)
- 0: Default
- -512: Very late

#### Main vs Sub Requests

Always check request type:
```php
if (!$event->isMainRequest()) {
    return;
}
```

### Testing Event Subscribers

Test subscribed events:
```php
$events = MySubscriber::getSubscribedEvents();
$this->assertArrayHasKey(KernelEvents::REQUEST, $events);
```

Mock events for testing:
```php
$kernel = $this->createMock(HttpKernelInterface::class);
$request = new Request();
$event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
```
```

---

## Context Reset Information

**Files Created:**
1. `src/EventSubscriber/CheckRequirementsSubscriber.php` - Version checking
2. `src/EventSubscriber/ControllerSubscriber.php` - Global template vars
3. `tests/EventSubscriber/CheckRequirementsSubscriberTest.php` - Tests

**Files Modified:**
1. `config/services.yaml` - Added ControllerSubscriber configuration
2. `templates/base.html.twig` - Use global variables in footer
3. `translations/messages.en.yaml` - Added system info translations

**Next Plan:** `14-rss-syndication.md`

---

## Completion Checklist

- [ ] CheckRequirementsSubscriber implemented
- [ ] ControllerSubscriber implemented
- [ ] Unit tests created
- [ ] Global variables available in templates
- [ ] Footer displays version info
- [ ] Translation keys added
- [ ] All subscribers registered and working
- [ ] No performance degradation
- [ ] Memory file updated
- [ ] Git commit created

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 14