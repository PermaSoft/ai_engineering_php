# Phase 5: Forms & Validation

## Overview

**Goal**: Create form types, custom fields, data transformers, and validation
**Complexity**: Medium
**Dependencies**: Phase 2, 4
**Estimated Files**: 6-8 files

## Memory Files Required

- **Primary**: [Forms & Validation](../memory/forms-validation.md)
- **Reference**: [Domain Model](../memory/domain-model.md)

## Packmind Standards Applied

- Symfony Forms Best Practices

## Implementation Checklist

### 1. Post Form Type

- [ ] Create `src/Form/PostType.php`
  - [ ] Extend `AbstractType`
  - [ ] `data_class: Post::class`
  - [ ] Fields:
    - [ ] `title` (TextType)
    - [ ] `summary` (TextareaType)
    - [ ] `content` (TextareaType)
    - [ ] `publishedAt` (DateTimePickerType - custom field)
    - [ ] `tags` (TagsInputType - custom field)
  - [ ] Form events:
    - [ ] `FormEvents::SUBMIT` - generate slug from title
  - [ ] Use Symfony String `slugger` service
  - [ ] Inject dependencies via constructor

### 2. Comment Form Type

- [ ] Create `src/Form/CommentType.php`
  - [ ] Extend `AbstractType`
  - [ ] `data_class: Comment::class`
  - [ ] Fields:
    - [ ] `content` (TextareaType)
  - [ ] Simple form with single field

### 3. User Form Type

- [ ] Create `src/Form/UserType.php`
  - [ ] Extend `AbstractType`
  - [ ] `data_class: User::class`
  - [ ] Fields:
    - [ ] `fullName` (TextType)
    - [ ] `username` (TextType)
    - [ ] `email` (EmailType)

### 4. Change Password Form Type

- [ ] Create `src/Form/ChangePasswordType.php`
  - [ ] Fields:
    - [ ] `currentPassword` (PasswordType)
    - [ ] `newPassword` (RepeatedType with PasswordType)
  - [ ] Custom validation for current password
  - [ ] Password strength requirements

### 5. Custom Form Fields

- [ ] Create `src/Form/Type/DateTimePickerType.php`
  - [ ] Extends `DateTimeType`
  - [ ] Custom options for date/time picker widget
  - [ ] HTML5 datetime-local input

- [ ] Create `src/Form/Type/TagsInputType.php`
  - [ ] Extends `TextType`
  - [ ] Custom data transformer
  - [ ] Transforms comma-separated string to Tag collection

### 6. Data Transformers

- [ ] Create `src/Form/DataTransformer/TagArrayToStringTransformer.php`
  - [ ] Implements `DataTransformerInterface`
  - [ ] `transform(array $tags): string` - Tags to comma-separated string
  - [ ] `reverseTransform(string $string): array` - String to Tag entities
  - [ ] Inject TagRepository
  - [ ] Create new tags if not found

### 7. Custom Form Theme

- [ ] Create `templates/form/fields.html.twig`
  - [ ] Custom field rendering
  - [ ] Bootstrap 5 styling
  - [ ] Form row customizations
  - [ ] Error message formatting

- [ ] Update `config/packages/twig.yaml`
  - [ ] Add `form_themes: ['form/fields.html.twig']`

### 8. Verification

- [ ] Create test controller to render forms
- [ ] Test Post form renders with all fields
- [ ] Test Comment form renders
- [ ] Test User form renders
- [ ] Test TagsInputType transforms correctly
- [ ] Test DateTimePickerType renders correctly
- [ ] Test form validation (submit empty form)
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/Form/`

## Success Criteria

✅ All form types created and configured
✅ Custom field types working (DateTimePicker, TagsInput)
✅ Data transformers converting between tags and strings
✅ Form events generating slugs from titles
✅ Validation working with translation keys
✅ Custom form theme applied
✅ All forms render correctly

## Next Phase

[Phase 6: Blog Browsing](./06-blog-browsing.md)
