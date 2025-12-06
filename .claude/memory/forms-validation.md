# Forms & Validation

Complete form implementation and validation patterns.

## Form Organization

```
src/Form/
├── Type/
│   ├── DateTimePickerType.php     # Custom datetime field
│   ├── TagsInputType.php           # Custom tags field with transformers
│   └── DataTransformer/
│       ├── TagArrayToStringTransformer.php
│       └── CollectionToArrayTransformer.php
├── PostType.php                    # Blog post form
├── CommentType.php                 # Comment form
├── UserType.php                    # User profile form
└── ChangePasswordType.php          # Password change form
```

## Form Best Practices Applied

### ✅ Dedicated Form Classes
Forms defined in PHP classes extending `AbstractType`:

```php
final class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Form field configuration
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
```

### ✅ Validation on Entities
Constraints defined on entity properties, not form fields.

### ✅ Translation Keys
All labels and help text use translation keys:

```php
$builder->add('title', null, [
    'label' => 'label.title',
    'help' => 'help.post_title',
]);
```

### ✅ Service Injection
Form types are services and can inject dependencies:

```php
public function __construct(
    private readonly SluggerInterface $slugger,
) {}
```

### ✅ Form Events
Business logic in form events:

```php
->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
    // Auto-generate slug from title
})
```

### ✅ Data Transformers
Complex field transformations:

```php
$builder->get('tags')->addModelTransformer(new TagArrayToStringTransformer($this->tags));
```

---

## PostType - Blog Post Form

**Location**: `src/Form/PostType.php`
**Entity**: `Post`

### Implementation

```php
final class PostType extends AbstractType
{
    public function __construct(
        private readonly SluggerInterface $slugger,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title',
            ])
            ->add('summary', TextareaType::class, [
                'help' => 'help.post_summary',
                'label' => 'label.summary',
            ])
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'help' => 'help.post_content',
                'label' => 'label.content',
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'help' => 'help.post_publication',
            ])
            ->add('tags', TagsInputType::class, [
                'label' => 'label.tags',
                'required' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Post $post */
                $post = $event->getData();
                if (null === $post->getSlug() && null !== $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($post->getTitle())->lower());
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
```

### Features

**Auto-focus on title**: First field gets focus
**Textarea for summary**: Multiple lines for summary
**Large content field**: 20 rows for main content
**Custom datetime picker**: `DateTimePickerType` for publish date
**Custom tags input**: `TagsInputType` with comma-separated input
**Slug auto-generation**: Form event creates slug from title on submit

### Form Event - Slug Generation

```php
->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
    $post = $event->getData();
    if (null === $post->getSlug() && null !== $post->getTitle()) {
        $post->setSlug($this->slugger->slug($post->getTitle())->lower());
    }
})
```

**When**: After form submission
**Logic**: If slug is empty, generate from title
**Result**: URL-friendly lowercase slug

**Example**: "Lorem Ipsum Dolor" → "lorem-ipsum-dolor"

### Validation

Validation defined on `Post` entity:

```php
#[Assert\NotBlank]
private ?string $title = null;

#[Assert\NotBlank(message: 'post.blank_summary')]
#[Assert\Length(max: 255)]
private ?string $summary = null;

#[Assert\NotBlank(message: 'post.blank_content')]
#[Assert\Length(min: 10, minMessage: 'post.too_short_content')]
private ?string $content = null;

#[Assert\Count(max: 4, maxMessage: 'post.too_many_tags')]
private Collection $tags;
```

### Template Rendering

**Template**: `templates/admin/blog/new.html.twig`

Form rendered with:
```twig
{{ form_start(form) }}
    {{ form_widget(form) }}

    <button type="submit" name="saveAndCreateNew">Save and create new</button>
    <button type="submit">Save</button>
{{ form_end(form) }}
```

Buttons added in template (not form class).

---

## CommentType - Comment Form

**Location**: `src/Form/CommentType.php`
**Entity**: `Comment`

### Implementation

```php
final class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', null, [
                'attr' => ['rows' => 10],
                'label' => 'label.content',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
```

### Features

Simple form with single field:
- **content**: Textarea with 10 rows

### Validation

Defined on `Comment` entity:

```php
#[Assert\NotBlank(message: 'comment.blank')]
#[Assert\Length(
    min: 5,
    minMessage: 'comment.too_short',
    max: 10000,
    maxMessage: 'comment.too_long'
)]
private ?string $content = null;

#[Assert\IsTrue(message: 'comment.is_spam')]
public function isLegitComment(): bool
{
    $containsInvalidCharacters = null !== u($this->content)->indexOf('@');
    return !$containsInvalidCharacters;
}
```

**Spam Detection**: Custom validation method rejects comments with '@' symbol.

---

## UserType - Profile Edit Form

**Location**: `src/Form/UserType.php`
**Entity**: `User`

### Implementation

```php
final class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'label.username',
                'disabled' => true,
            ])
            ->add('fullName', null, [
                'label' => 'label.fullname',
            ])
            ->add('email', null, [
                'label' => 'label.email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
```

### Features

**Username disabled**: Cannot change username (display only)
**Full name editable**: Can update display name
**Email editable**: Can update email address

### Validation

Defined on `User` entity:

```php
#[Assert\NotBlank]
#[Assert\Length(min: 2, max: 50)]
private ?string $username = null;

#[Assert\NotBlank]
private ?string $fullName = null;

#[Assert\Email]
private ?string $email = null;
```

---

## ChangePasswordType - Password Change Form

**Location**: `src/Form/ChangePasswordType.php`
**Entity**: None (DTO form)

### Implementation

```php
final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'label.current_password',
                'constraints' => [
                    new UserPassword(),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'password.dont_match',
                'first_options' => ['label' => 'label.new_password'],
                'second_options' => ['label' => 'label.new_password_confirm'],
            ])
        ;
    }
}
```

### Features

**Current password validation**: `UserPassword` constraint verifies current password
**Repeated password**: Confirmation field ensures no typos
**No entity binding**: Form not bound to entity (DTO pattern)

### Validation

**CurrentPassword**:
```php
new UserPassword()
```
Validates that current password matches user's actual password.

**NewPassword**:
```php
RepeatedType::class
```
Two fields must match, validated automatically.

---

## Custom Field Types

### DateTimePickerType

**Location**: `src/Form/Type/DateTimePickerType.php`

Custom datetime input field for publish date selection.

```php
final class DateTimePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
        ]);
    }

    public function getParent(): string
    {
        return DateTimeType::class;
    }
}
```

**Features**:
- Extends `DateTimeType`
- Single text input (not 3 dropdowns)
- HTML5 datetime input

### TagsInputType

**Location**: `src/Form/Type/TagsInputType.php`

Custom field for comma-separated tags input.

```php
final class TagsInputType extends AbstractType
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new TagArrayToStringTransformer($this->tags), true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'attr' => [
                'placeholder' => 'tag.placeholder',
                'class' => 'form-control',
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
```

**Features**:
- Text input for comma-separated tags
- Two-way data transformation
- Collection ↔ Array ↔ String

**Input**: "lorem, ipsum, dolor"
**Output**: Collection of Tag entities

---

## Data Transformers

### TagArrayToStringTransformer

**Location**: `src/Form/Type/DataTransformer/TagArrayToStringTransformer.php`

Transforms between array of Tag entities and comma-separated string.

```php
final class TagArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    /**
     * @param Tag[] $tags
     */
    public function transform($tags): string
    {
        return implode(',', $tags);
    }

    /**
     * @return Tag[]
     */
    public function reverseTransform($string): array
    {
        if ('' === $string || null === $string) {
            return [];
        }

        $names = array_filter(array_unique(array_map('trim', explode(',', $string))));

        return array_map(function ($name) {
            return $this->tags->findOneBy(['name' => $name]) ?? new Tag($name);
        }, $names);
    }
}
```

**Transform** (Model → View):
- `[Tag("lorem"), Tag("ipsum")]` → `"lorem,ipsum"`

**Reverse Transform** (View → Model):
- `"lorem, ipsum, dolor"` → `[Tag("lorem"), Tag("ipsum"), Tag("dolor")]`
- Trims whitespace
- Removes duplicates
- Creates new Tag if not found

### CollectionToArrayTransformer

**Location**: `src/Form/Type/DataTransformer/CollectionToArrayTransformer.php`

Transforms between Doctrine Collection and PHP array.

```php
final class CollectionToArrayTransformer implements DataTransformerInterface
{
    /**
     * @param Collection<int, Tag>|null $value
     * @return Tag[]
     */
    public function transform($value): array
    {
        if (null === $value) {
            return [];
        }

        return $value->toArray();
    }

    /**
     * @param Tag[]|null $value
     */
    public function reverseTransform($value): Collection
    {
        return new ArrayCollection($value ?? []);
    }
}
```

**Transform** (Model → View):
- `Collection<Tag>` → `array<Tag>`

**Reverse Transform** (View → Model):
- `array<Tag>` → `ArrayCollection<Tag>`

---

## Form Rendering Customization

### Custom Form Theme

**Location**: `templates/form/fields.html.twig`

Custom rendering for specific form fields.

**Configuration**: `config/packages/twig.yaml`
```yaml
twig:
    form_themes:
        - 'form/fields.html.twig'
```

### Tags Field Rendering

```twig
{% block tags_input_widget %}
    {% set type = type|default('text') %}
    <input type="{{ type }}" {{ block('widget_attributes') }} value="{{ value }}">

    <script>
        // Bootstrap Tags Input integration
        $('[name="{{ full_name }}"]').tagsinput({
            tagClass: 'badge badge-primary',
            itemValue: function(item) { return item.name; },
            itemText: function(item) { return item.name; }
        });
    </script>
{% endblock %}
```

Integrates with Bootstrap Tags Input JavaScript library.

---

## Validation Constraint Examples

### Entity-Level Constraints

**Unique Entity**:
```php
#[UniqueEntity(fields: ['slug'], errorPath: 'title', message: 'post.slug_unique')]
class Post
```

**Collection Count**:
```php
#[Assert\Count(max: 4, maxMessage: 'post.too_many_tags')]
private Collection $tags;
```

### Property-Level Constraints

**Not Blank**:
```php
#[Assert\NotBlank(message: 'post.blank_content')]
private ?string $content = null;
```

**Length**:
```php
#[Assert\Length(min: 10, minMessage: 'post.too_short_content')]
private ?string $content = null;

#[Assert\Length(max: 255)]
private ?string $summary = null;
```

**Email**:
```php
#[Assert\Email]
private ?string $email = null;
```

### Custom Method Constraint

**Is True**:
```php
#[Assert\IsTrue(message: 'comment.is_spam')]
public function isLegitComment(): bool
{
    $containsInvalidCharacters = null !== u($this->content)->indexOf('@');
    return !$containsInvalidCharacters;
}
```

### Form-Specific Constraints

**User Password**:
```php
->add('currentPassword', PasswordType::class, [
    'constraints' => [
        new UserPassword(),
    ],
])
```

Validates that password matches currently authenticated user's password.

---

## Form Processing Pattern

### Standard Controller Pattern

```php
public function edit(Request $request, EntityManagerInterface $entityManager): Response
{
    $post = new Post();

    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($post);
        $entityManager->flush();

        $this->addFlash('success', 'post.created_successfully');

        return $this->redirectToRoute('admin_post_index');
    }

    return $this->render('admin/blog/new.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}
```

**Steps**:
1. Create entity or fetch existing
2. Create form with entity
3. Handle request (populate from POST data)
4. Check if submitted and valid
5. Save to database
6. Flash message
7. Redirect (PRG pattern)
8. Or render form (GET or validation errors)

### Multiple Submit Buttons

```php
if ($form->isSubmitted() && $form->isValid()) {
    $entityManager->persist($post);
    $entityManager->flush();

    $this->addFlash('success', 'post.created_successfully');

    if ($form->get('saveAndCreateNew')->isClicked()) {
        return $this->redirectToRoute('admin_post_new');
    }

    return $this->redirectToRoute('admin_post_index');
}
```

**Template**:
```twig
<button type="submit" name="saveAndCreateNew">Save and create new</button>
<button type="submit">Save</button>
```

---

## Translation Keys

All user-facing text uses translation keys:

**Labels**:
- `label.title`
- `label.summary`
- `label.content`
- `label.published_at`
- `label.tags`

**Help Text**:
- `help.post_summary`
- `help.post_content`
- `help.post_publication`

**Validation Messages**:
- `post.blank_summary`
- `post.blank_content`
- `post.too_short_content`
- `post.too_many_tags`
- `post.slug_unique`
- `comment.blank`
- `comment.too_short`
- `comment.too_long`
- `comment.is_spam`

**Flash Messages**:
- `post.created_successfully`
- `post.updated_successfully`
- `post.deleted_successfully`
- `user.updated_successfully`

**Translation Files**: `translations/messages.{locale}.yaml`

---

## Form Summary Table

| Form | Entity | Fields | Custom Features |
|------|--------|--------|-----------------|
| PostType | Post | title, summary, content, publishedAt, tags | Slug auto-generation, custom date picker, tags input |
| CommentType | Comment | content | Spam detection validation |
| UserType | User | username (disabled), fullName, email | Username read-only |
| ChangePasswordType | None (DTO) | currentPassword, newPassword | Password verification, repeated field |
| DateTimePickerType | N/A | Single datetime field | Custom datetime input |
| TagsInputType | N/A | Text input | Data transformers for Collection ↔ String |
