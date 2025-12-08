# Plan 07: Form Enhancements & Widgets

**Priority:** 🟠 HIGH
**Estimated Time:** 5-6 hours
**Dependencies:** Plan 06 (Blog Partials)
**Status:** Ready to execute

---

## Context & Objective

Enhance form types with sophisticated UX features lost during rebuild:
1. Add Flatpickr JavaScript date picker to DateTimePickerType (currently uses basic HTML5)
2. Add tag autocomplete to TagsInputType via buildView method
3. Fix ChangePasswordType field mapping and add security attributes
4. Review UserType username editability
5. Improve TagArrayToStringTransformer performance (N+1 query issue)
6. Add form field templates for custom widgets

This restores the sophisticated form UX from walkthrought branch.

---

## Reference Materials

### Memory Files
- `.claude/memory/forms-validation.md` - Form types and patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 5: Forms & Validation)

### Walkthrought Files
```bash
git show walkthrought:src/Form/Type/DateTimePickerType.php
git show walkthrought:src/Form/Type/TagsInputType.php
git show walkthrought:src/Form/Type/ChangePasswordType.php
git show walkthrought:src/Form/DataTransformer/TagArrayToStringTransformer.php
git show walkthrought:importmap.php  # For Flatpickr config
```

---

## Prerequisites

- Understanding of Symfony form types
- JavaScript/Stimulus knowledge for Flatpickr
- Form system configured
- Asset mapper working

---

## Deliverables Checklist

### Code Changes
- [ ] Enhance `src/Form/Type/DateTimePickerType.php` with Flatpickr
- [ ] Enhance `src/Form/Type/TagsInputType.php` with buildView
- [ ] Fix `src/Form/Type/ChangePasswordType.php` mapping and security
- [ ] Optimize `src/Form/DataTransformer/TagArrayToStringTransformer.php`
- [ ] Add Flatpickr to importmap
- [ ] Create Stimulus controller for Flatpickr
- [ ] Add form templates (optional)

### Testing
- [ ] Test date picker renders and submits correctly
- [ ] Test tag autocomplete works
- [ ] Test password change form security attributes
- [ ] Test tag transformer performance
- [ ] Verify all forms still work

### Documentation
- [ ] Update memory file with form widget patterns

---

## Implementation Steps

### Step 1: Add Flatpickr to Importmap

**File:** `importmap.php`

Add Flatpickr dependency:

```php
<?php

return [
    // ... existing imports ...

    'flatpickr' => [
        'version' => '4.6.13',
    ],
    'flatpickr/dist/l10n' => [
        'version' => '4.6.13',
    ],
];
```

Then run:
```bash
php bin/console importmap:require flatpickr
```

---

### Step 2: Create Flatpickr Stimulus Controller

**File:** `assets/controllers/flatpickr_controller.js`

```javascript
import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';

/*
 * Stimulus controller for Flatpickr date/time picker
 *
 * Usage:
 * <input type="text"
 *        data-controller="flatpickr"
 *        data-flatpickr-enable-time-value="true"
 *        data-flatpickr-date-format-value="Y-m-d H:i:S"
 *        data-flatpickr-alt-format-value="F j, Y at H:i"
 * />
 */
export default class extends Controller {
    static values = {
        enableTime: { type: Boolean, default: false },
        dateFormat: { type: String, default: 'Y-m-d' },
        altFormat: { type: String, default: 'F j, Y' },
        time24hr: { type: Boolean, default: true },
    }

    connect() {
        this.flatpickr = flatpickr(this.element, {
            enableTime: this.enableTimeValue,
            dateFormat: this.dateFormatValue,
            altInput: true,
            altFormat: this.altFormatValue,
            time_24hr: this.time24hrValue,
            locale: this.getLocaleFromHtmlLang(),
        });
    }

    disconnect() {
        if (this.flatpickr) {
            this.flatpickr.destroy();
        }
    }

    getLocaleFromHtmlLang() {
        const htmlLang = document.documentElement.lang;
        if (htmlLang && htmlLang !== 'en') {
            // Import locale if not English
            import(`flatpickr/dist/l10n/${htmlLang}.js`)
                .then((localeModule) => {
                    if (this.flatpickr && localeModule.default && localeModule.default[htmlLang]) {
                        this.flatpickr.set('locale', localeModule.default[htmlLang]);
                    }
                })
                .catch(() => {
                    console.warn(`Flatpickr locale for "${htmlLang}" not found, using default.`);
                });
        }
        return 'default';
    }
}
```

**Key Features:**
- Configurable via Stimulus values
- Auto-loads locale based on HTML lang attribute
- Alternative display format for better UX
- Time support when needed
- Proper cleanup on disconnect

---

### Step 3: Add Flatpickr CSS

**File:** `assets/styles/app.css`

Add import at the top:

```css
@import 'flatpickr/dist/flatpickr.min.css';
```

Or if using importmap in the template directly, add to base template head:

**File:** `templates/base.html.twig`

```twig
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
```

---

### Step 4: Enhance DateTimePickerType

**File:** `src/Form/Type/DateTimePickerType.php`

```php
<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom DateTimePicker using Flatpickr JavaScript library.
 *
 * Provides a sophisticated date/time picker with:
 * - Internationalization support
 * - User-friendly calendar interface
 * - Time selection when enabled
 * - Consistent UX across browsers
 */
final class DateTimePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'html5' => false, // Disable HTML5 widget, use Flatpickr
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Add Stimulus controller and Flatpickr configuration
        $view->vars['attr'] = array_merge($view->vars['attr'] ?? [], [
            'data-controller' => 'flatpickr',
            'data-flatpickr-enable-time-value' => 'true',
            'data-flatpickr-date-format-value' => 'Y-m-d H:i:S',
            'data-flatpickr-alt-format-value' => 'F j, Y at H:i',
            'data-flatpickr-time-24hr-value' => 'true',
            'class' => trim(($view->vars['attr']['class'] ?? '') . ' form-control'),
            'autocomplete' => 'off',
        ]);
    }

    public function getParent(): string
    {
        return DateTimeType::class;
    }
}
```

**Changes:**
- `html5 => false` to disable native HTML5 picker
- `buildView()` adds Stimulus controller attributes
- Flatpickr configuration via data attributes
- Auto-complete disabled for better UX

---

### Step 5: Enhance TagsInputType with Autocomplete

**File:** `src/Form/Type/TagsInputType.php`

```php
<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Custom form field for tag input with autocomplete.
 *
 * Provides:
 * - Tag autocomplete suggestions
 * - Comma-separated tag input
 * - Automatic tag creation
 * - Data transformation between array and string
 */
final class TagsInputType extends AbstractType
{
    public function __construct(
        private readonly TagArrayToStringTransformer $transformer,
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Load all existing tags for autocomplete suggestions
        $tags = $this->tagRepository->findAll();

        // Pass tag names to the view
        $view->vars['tags'] = array_map(
            static fn($tag) => $tag->getName(),
            $tags
        );

        // Add attributes for JavaScript enhancement
        $view->vars['attr'] = array_merge($view->vars['attr'] ?? [], [
            'class' => trim(($view->vars['attr']['class'] ?? '') . ' form-control'),
            'data-tags' => json_encode($view->vars['tags']),
            'placeholder' => 'Enter tags separated by commas',
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
```

**Changes:**
- Added `buildView()` method that was missing
- Loads all tags from repository for autocomplete
- Passes tag list to view as JSON data attribute
- Ready for JavaScript enhancement

---

### Step 6: Optimize TagArrayToStringTransformer

**File:** `src/Form/DataTransformer/TagArrayToStringTransformer.php`

```php
<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms between Tag entity collection and comma-separated string.
 *
 * Performance optimization:
 * - Uses batch query instead of N+1 queries
 * - Creates only missing tags
 * - Handles whitespace and empty values
 */
final readonly class TagArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private TagRepository $tagRepository,
    ) {
    }

    /**
     * Transforms Tag collection to comma-separated string.
     *
     * @param Collection<int, Tag>|null $tags
     */
    public function transform(mixed $tags): string
    {
        if ($tags === null || $tags->isEmpty()) {
            return '';
        }

        return implode(', ', array_map(
            static fn(Tag $tag): string => $tag->getName(),
            $tags->toArray()
        ));
    }

    /**
     * Transforms comma-separated string to Tag collection.
     *
     * @return Collection<int, Tag>
     */
    public function reverseTransform(mixed $value): Collection
    {
        if (empty($value)) {
            return new ArrayCollection();
        }

        // Parse and clean tag names
        $names = array_filter(
            array_map('trim', explode(',', (string) $value)),
            static fn(string $name): bool => $name !== ''
        );

        if (empty($names)) {
            return new ArrayCollection();
        }

        // OPTIMIZATION: Batch query for existing tags instead of N+1 queries
        $existingTags = $this->tagRepository->findBy(['name' => $names]);

        // Map existing tags by name for quick lookup
        $existingTagsByName = [];
        foreach ($existingTags as $tag) {
            $existingTagsByName[$tag->getName()] = $tag;
        }

        // Identify new tag names
        $newNames = array_diff($names, array_keys($existingTagsByName));

        // Create new tags
        $tags = new ArrayCollection($existingTags);
        foreach ($newNames as $name) {
            $tags->add(new Tag($name));
        }

        return $tags;
    }
}
```

**Performance Fix:**
- **BEFORE:** N+1 query problem - one query per tag
- **AFTER:** Single batch query + difference calculation
- Maps existing tags by name for O(1) lookup
- Creates only missing tags

---

### Step 7: Fix ChangePasswordType Security

**File:** `src/Form/Type/ChangePasswordType.php`

```php
<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form for changing user password with security constraints.
 *
 * Security features:
 * - Requires current password verification
 * - Password confirmation field
 * - Autocomplete disabled
 * - Maximum length constraint
 */
final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'label.current_password',
                'mapped' => false, // Don't map to entity property
                'constraints' => [
                    new NotBlank([
                        'message' => 'password.not_blank',
                    ]),
                    new UserPassword([
                        'message' => 'password.mismatch',
                    ]),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // Don't map to entity property
                'first_options' => [
                    'label' => 'label.new_password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'label.password_confirmation',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'password.dont_match',
                'constraints' => [
                    new NotBlank([
                        'message' => 'password.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 128, // ✅ SECURITY: Maximum password length
                        'minMessage' => 'password.too_short',
                        'maxMessage' => 'password.too_long',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Don't bind to entity - we handle password manually
            'data_class' => null,
        ]);
    }
}
```

**Security Fixes:**
- ✅ `mapped => false` on password fields (don't auto-map to entity)
- ✅ `autocomplete` attributes for browser password managers
- ✅ `max: 128` constraint to prevent DoS attacks
- ✅ UserPassword constraint to verify current password
- ✅ Proper translation keys

---

### Step 8: Add Translation Keys

**File:** `translations/messages.en.yaml`

```yaml
# Form labels
label:
    current_password: 'Current Password'
    new_password: 'New Password'
    password_confirmation: 'Confirm New Password'
    tags: 'Tags'

# Password validation messages
password:
    not_blank: 'Please enter a password.'
    too_short: 'Password must be at least {{ limit }} characters long.'
    too_long: 'Password cannot exceed {{ limit }} characters.'
    dont_match: 'The passwords do not match.'
    mismatch: 'Current password is incorrect.'
```

---

### Step 9: Optional - Create Tags Input JavaScript Enhancement

If you want to add a visual tag widget (like Bootstrap TagsInput), create:

**File:** `assets/controllers/tags_input_controller.js`

```javascript
import { Controller } from '@hotwired/stimulus';

/*
 * Stimulus controller for enhanced tag input
 *
 * Provides:
 * - Visual tag badges
 * - Tag removal
 * - Autocomplete suggestions
 */
export default class extends Controller {
    static values = {
        tags: Array, // Available tags for autocomplete
    }

    connect() {
        const existingTags = this.tagsValue || [];

        // Simple implementation: show available tags as hints
        if (existingTags.length > 0) {
            this.addTagHints(existingTags);
        }
    }

    addTagHints(tags) {
        const hintContainer = document.createElement('div');
        hintContainer.className = 'form-text';
        hintContainer.innerHTML = `
            <small>
                <strong>Available tags:</strong> ${tags.join(', ')}
            </small>
        `;
        this.element.parentElement.appendChild(hintContainer);
    }
}
```

Then update TagsInputType:

```php
$view->vars['attr']['data-controller'] = 'tags-input';
$view->vars['attr']['data-tags-input-tags-value'] = json_encode($view->vars['tags']);
```

---

## Verification Criteria

### Test DateTimePicker

1. Navigate to admin post create/edit page
2. ✓ Date field shows Flatpickr calendar icon
3. ✓ Click on date field
4. ✓ Calendar popup appears
5. ✓ Can select date and time
6. ✓ Display format is user-friendly (e.g., "December 8, 2025 at 14:30")
7. ✓ Form submission works correctly
8. ✓ Date value persists correctly

### Test Tags Input

1. Navigate to admin post create/edit page
2. ✓ Tags field exists
3. ✓ Type tag names separated by commas
4. ✓ Available tags are shown (if JS enhancement added)
5. ✓ Existing tags load correctly when editing
6. ✓ New tags are created automatically
7. ✓ Tag transformer doesn't cause N+1 queries (check debug toolbar)

### Test Password Change Form

1. Navigate to user profile `/en/profile/edit`
2. Click "Change Password"
3. ✓ Three password fields appear
4. ✓ Current password required
5. ✓ New password and confirmation required
6. ✓ Autocomplete attributes present in HTML
7. ✓ Try password > 128 characters - should fail
8. ✓ Try wrong current password - should fail
9. ✓ Try mismatched new passwords - should fail
10. ✓ Valid password change works

### Performance Test

1. Enable Symfony profiler debug toolbar
2. Create/edit a post with 5+ tags
3. ✓ Check database queries
4. ✓ Should see ONE query for tags (findBy with IN clause)
5. ✓ NOT multiple individual findOneBy queries

---

## Memory File Updates

**File:** `.claude/memory/forms-validation.md`

Add this section:

```markdown
## Custom Form Widgets

### DateTimePickerType with Flatpickr

Uses Flatpickr JavaScript library for sophisticated date/time picking:

```php
$builder->add('publishedAt', DateTimePickerType::class, [
    'label' => 'label.publication_date',
]);
```

Features:
- Internationalized calendar
- Time selection support
- User-friendly display format
- Consistent UX across browsers
- Disabled HTML5 native picker

Configuration via Stimulus controller with data attributes.

### TagsInputType with Autocomplete

Provides tag input with autocomplete suggestions:

```php
$builder->add('tags', TagsInputType::class, [
    'label' => 'label.tags',
    'required' => false,
]);
```

Features:
- Loads existing tags for suggestions
- Comma-separated input
- Automatic tag creation
- Batch query optimization (no N+1)

Uses `TagArrayToStringTransformer` to convert between:
- **Model:** Collection<Tag>
- **View:** String (comma-separated)

### ChangePasswordType Security

Password change form with security constraints:

```php
$builder->add('changePassword', ChangePasswordType::class);
```

Security features:
- Current password verification (UserPassword constraint)
- Password confirmation via RepeatedType
- Autocomplete attributes for password managers
- Maximum length constraint (128 chars) to prevent DoS
- Fields marked as `mapped => false`

Controller must manually handle password hashing.

## Form Data Transformers

### TagArrayToStringTransformer

Performance-optimized transformer:
- Single batch query for existing tags
- Array difference to identify new tags
- Only creates missing tags

**BEFORE (N+1 problem):**
```php
// One query per tag
foreach ($names as $name) {
    $tag = $repo->findOneBy(['name' => $name]) ?? new Tag($name);
}
```

**AFTER (optimized):**
```php
// One batch query
$existingTags = $repo->findBy(['name' => $names]);
$newNames = array_diff($names, $existingTagNames);
```

## Stimulus Controllers

### flatpickr_controller.js

Initializes Flatpickr on form fields:
- Auto-loads locale based on HTML lang attribute
- Configurable via Stimulus values
- Proper cleanup on disconnect

### tags_input_controller.js (Optional)

Enhances tag input with visual feedback:
- Shows available tags as hints
- Can be extended for full tag widget
```

---

## Context Reset Information

If resuming after context reset:

**Files Modified:**
1. `src/Form/Type/DateTimePickerType.php` - Added Flatpickr support
2. `src/Form/Type/TagsInputType.php` - Added buildView for autocomplete
3. `src/Form/Type/ChangePasswordType.php` - Added security attributes
4. `src/Form/DataTransformer/TagArrayToStringTransformer.php` - Optimized queries
5. `importmap.php` - Added Flatpickr dependency
6. `translations/messages.en.yaml` - Added password messages

**Files Created:**
1. `assets/controllers/flatpickr_controller.js` - Flatpickr Stimulus controller
2. `assets/controllers/tags_input_controller.js` - Tags input enhancement (optional)

**Verification:**
- Test date picker on post form
- Test tag input and autocomplete
- Test password change form
- Check database queries for tag transformer
- Memory file updated

**Next Plan:** `08-testing-infrastructure.md`

---

## Completion Checklist

- [ ] Flatpickr added to importmap
- [ ] Flatpickr Stimulus controller created
- [ ] DateTimePickerType enhanced with Flatpickr
- [ ] TagsInputType enhanced with buildView
- [ ] ChangePasswordType security fixes applied
- [ ] TagArrayToStringTransformer optimized
- [ ] Translation keys added
- [ ] Date picker tested and working
- [ ] Tag input tested and working
- [ ] Password form tested and working
- [ ] Query optimization verified
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  feat: enhance form widgets with Flatpickr and tag autocomplete

  - Add Flatpickr date/time picker to DateTimePickerType
  - Add tag autocomplete suggestions to TagsInputType
  - Fix ChangePasswordType security (autocomplete, max length)
  - Optimize TagArrayToStringTransformer (fix N+1 query)
  - Create Stimulus controllers for form widgets
  - Add password validation messages

  Restores sophisticated form UX from walkthrought branch.
  Improves performance and security.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 08