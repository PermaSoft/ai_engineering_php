# Plan 05: Admin Interface Polish

**Priority:** 🟠 HIGH
**Estimated Time:** 5-6 hours
**Dependencies:** Plan 04 (Template Architecture)
**Status:** Ready to execute

---

## Context & Objective

Transform admin interface from basic inline-styled templates to professional, polished admin panel:
1. Create reusable form partials (`_form.html.twig`, `_delete_form.html.twig`)
2. Replace inline styles with proper Bootstrap classes
3. Add action buttons, modals, and professional table styling
4. Improve UX with icons, badges, and better visual hierarchy
5. Ensure consistency across all admin pages

---

## Reference Materials

### Memory Files
- `.claude/memory/templates-frontend.md` - Template patterns
- `.claude/memory/controllers-routing.md` - Admin controller patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 7.4: Missing Templates Impact)

### Walkthrought Files
```bash
git show walkthrought:templates/admin/blog/_form.html.twig
git show walkthrought:templates/admin/blog/_delete_form.html.twig
git show walkthrought:templates/admin/blog/index.html.twig
git show walkthrought:templates/admin/blog/edit.html.twig
```

---

## Prerequisites

- Plan 04 completed (admin/layout.html.twig exists)
- Bootstrap 5 understanding
- Forms knowledge

---

## Deliverables Checklist

### Code Changes
- [ ] `templates/admin/blog/_form.html.twig` - Reusable post form partial
- [ ] `templates/admin/blog/_delete_form.html.twig` - Delete confirmation partial
- [ ] `templates/admin/blog/index.html.twig` - Enhanced with proper table styling
- [ ] `templates/admin/blog/new.html.twig` - Use form partial
- [ ] `templates/admin/blog/edit.html.twig` - Use form partial
- [ ] `templates/admin/blog/show.html.twig` - Enhanced with action buttons
- [ ] `templates/admin/user/index.html.twig` - Remove inline styles
- [ ] `templates/admin/user/new.html.twig` - Professional styling

### Testing
- [ ] All admin pages load correctly
- [ ] Form partials render properly
- [ ] Delete confirmations work
- [ ] Buttons and actions functional
- [ ] Responsive on mobile

---

## Implementation Steps

### Step 1: Create Post Form Partial

**File:** `templates/admin/blog/_form.html.twig`

```twig
{# Reusable blog post form partial #}
{{ form_start(form, {'attr': {'novalidate': 'novalidate'}}) }}
    <div class="row">
        <div class="col-md-8">
            {{ form_row(form.title, {
                'label': 'label.title'|trans,
                'attr': {'autofocus': true}
            }) }}

            {{ form_row(form.summary, {
                'label': 'label.summary'|trans,
                'help': 'help.post_summary'|trans
            }) }}

            {{ form_row(form.content, {
                'label': 'label.content'|trans,
                'attr': {'rows': 20}
            }) }}
        </div>

        <div class="col-md-4">
            {{ form_row(form.publishedAt, {
                'label': 'label.published_at'|trans
            }) }}

            {{ form_row(form.tags, {
                'label': 'label.tags'|trans,
                'help': 'help.post_tags'|trans
            }) }}

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    {{ button_label|default('action.save'|trans) }}
                </button>

                {% if show_save_and_create_new|default(false) %}
                    <button type="submit" name="saveAndCreateNew" class="btn btn-secondary">
                        {{ 'action.save_and_create_new'|trans }}
                    </button>
                {% endif %}

                <a href="{{ path('admin_post_index') }}" class="btn btn-outline-secondary">
                    {{ 'action.cancel'|trans }}
                </a>
            </div>
        </div>
    </div>
{{ form_end(form) }}
```

**Key Features:**
- Two-column layout (8/4 grid)
- Form in left column, meta/actions in right
- Autofocus on title field
- Help text for summary and tags
- Conditional "Save and Create New" button
- Cancel button back to index

---

### Step 2: Create Delete Form Partial

**File:** `templates/admin/blog/_delete_form.html.twig`

```twig
{# Delete confirmation form with CSRF protection #}
<form method="post" action="{{ path('admin_post_delete', {id: post.id}) }}" onsubmit="return confirm('{{ 'action.delete_confirmation'|trans }}');">
    <input type="hidden" name="token" value="{{ csrf_token('delete') }}">
    <button type="submit" class="btn btn-danger">
        {{ button_label|default('action.delete'|trans) }}
    </button>
</form>
```

**Key Features:**
- CSRF token protection
- JavaScript confirmation dialog
- Customizable button label
- POST method for safety

---

### Step 3: Enhanced Admin Post Index

**File:** `templates/admin/blog/index.html.twig`

```twig
{% extends 'admin/layout.html.twig' %}

{% block admin_title %}{{ 'title.post_management'|trans }}{% endblock %}

{% block admin_content %}
    <div class="mb-3">
        <a href="{{ path('admin_post_new') }}" class="btn btn-success">
            {{ 'action.create_post'|trans }}
        </a>
    </div>

    {% if posts|length > 0 %}
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ 'label.title'|trans }}</th>
                        <th class="text-center" style="width: 10%">{{ 'label.published'|trans }}</th>
                        <th class="text-center" style="width: 15%">{{ 'label.author'|trans }}</th>
                        <th class="text-end" style="width: 15%">{{ 'label.actions'|trans }}</th>
                    </tr>
                </thead>
                <tbody>
                {% for post in posts %}
                    <tr>
                        <td>
                            <a href="{{ path('admin_post_show', {id: post.id}) }}" class="text-decoration-none">
                                {{ post.title }}
                            </a>
                            {% if post.tags|length > 0 %}
                                <br>
                                <small class="text-muted">
                                    {% for tag in post.tags %}
                                        <span class="badge bg-secondary">{{ tag.name }}</span>
                                    {% endfor %}
                                </small>
                            {% endif %}
                        </td>
                        <td class="text-center">
                            {{ post.publishedAt|date('Y-m-d') }}
                        </td>
                        <td class="text-center">
                            {{ post.author.fullName }}
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ path('admin_post_show', {id: post.id}) }}"
                                   class="btn btn-outline-primary">
                                    {{ 'action.view'|trans }}
                                </a>
                                <a href="{{ path('admin_post_edit', {id: post.id}) }}"
                                   class="btn btn-outline-secondary">
                                    {{ 'action.edit'|trans }}
                                </a>
                            </div>
                        </td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    {% else %}
        <div class="alert alert-info">
            {{ 'help.no_posts_found'|trans }}
        </div>
    {% endif %}
{% endblock %}
```

**Key Features:**
- Professional table with striped rows
- Tags displayed as badges
- Formatted dates
- Button group for actions
- Empty state message
- No inline styles!

---

### Step 4: Admin Post New Template

**File:** `templates/admin/blog/new.html.twig`

```twig
{% extends 'admin/layout.html.twig' %}

{% block admin_title %}{{ 'title.create_post'|trans }}{% endblock %}

{% block admin_content %}
    {{ include('admin/blog/_form.html.twig', {
        button_label: 'action.create'|trans,
        show_save_and_create_new: true
    }) }}
{% endblock %}
```

**Key Features:**
- Uses form partial
- Customizes button label
- Shows "Save and Create New" option

---

### Step 5: Admin Post Edit Template

**File:** `templates/admin/blog/edit.html.twig`

```twig
{% extends 'admin/layout.html.twig' %}

{% block admin_title %}{{ 'title.edit_post'|trans }}{% endblock %}

{% block admin_content %}
    <div class="mb-3">
        <a href="{{ path('admin_post_show', {id: post.id}) }}" class="btn btn-sm btn-link">
            {{ 'action.back_to_post'|trans }}
        </a>
    </div>

    {{ include('admin/blog/_form.html.twig', {
        button_label: 'action.save_changes'|trans
    }) }}
{% endblock %}

{% block admin_sidebar %}
    {{ parent() }}

    <div class="card border-danger mb-3">
        <div class="card-header bg-danger text-white">
            {{ 'title.danger_zone'|trans }}
        </div>
        <div class="card-body">
            <p class="card-text text-muted small">
                {{ 'help.delete_post_warning'|trans }}
            </p>
            {{ include('admin/blog/_delete_form.html.twig') }}
        </div>
    </div>
{% endblock %}
```

**Key Features:**
- Uses form partial
- Delete action in "danger zone" sidebar
- Back link to show page

---

### Step 6: Admin Post Show Template

**File:** `templates/admin/blog/show.html.twig`

```twig
{% extends 'admin/layout.html.twig' %}

{% block admin_title %}{{ post.title }}{% endblock %}

{% block admin_content %}
    <article class="post">
        <div class="post-metadata mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-info">{{ 'label.author'|trans }}: {{ post.author.fullName }}</span>
                    <span class="badge bg-secondary">{{ post.publishedAt|date('Y-m-d H:i') }}</span>
                </div>
                <div class="btn-group btn-group-sm">
                    <a href="{{ path('admin_post_edit', {id: post.id}) }}" class="btn btn-primary">
                        {{ 'action.edit'|trans }}
                    </a>
                    <a href="{{ path('admin_post_index') }}" class="btn btn-outline-secondary">
                        {{ 'action.back_to_list'|trans }}
                    </a>
                </div>
            </div>
        </div>

        <div class="post-summary mb-3">
            <strong>{{ 'label.summary'|trans }}:</strong>
            <p class="text-muted">{{ post.summary }}</p>
        </div>

        <div class="post-tags mb-4">
            <strong>{{ 'label.tags'|trans }}:</strong>
            {% for tag in post.tags %}
                <span class="badge bg-secondary">{{ tag.name }}</span>
            {% else %}
                <span class="text-muted">{{ 'help.no_tags'|trans }}</span>
            {% endfor %}
        </div>

        <hr>

        <div class="post-content">
            {{ post.content|nl2br }}
        </div>

        <hr class="my-4">

        <h3>{{ 'title.comments'|trans }} ({{ post.comments|length }})</h3>

        {% if post.comments|length > 0 %}
            {% for comment in post.comments %}
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <strong>{{ comment.author.fullName }}</strong>
                            <small class="text-muted">{{ comment.publishedAt|date('Y-m-d H:i') }}</small>
                        </div>
                        <p class="mb-0 mt-2">{{ comment.content }}</p>
                    </div>
                </div>
            {% endfor %}
        {% else %}
            <p class="text-muted">{{ 'help.no_comments_yet'|trans }}</p>
        {% endif %}
    </article>
{% endblock %}
```

**Key Features:**
- Post metadata with badges
- Action buttons for edit and back
- Summary, tags, and content display
- Comments section
- Professional card-based layout

---

### Step 7: Clean Up Admin User Templates

**File:** `templates/admin/user/index.html.twig`

Remove all inline styles and use Bootstrap classes:

```twig
{% extends 'admin/layout.html.twig' %}

{% block admin_title %}{{ 'title.user_management'|trans }}{% endblock %}

{% block admin_content %}
    <div class="mb-3">
        <a href="{{ path('admin_user_new') }}" class="btn btn-success">
            {{ 'action.create_user'|trans }}
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ 'label.username'|trans }}</th>
                    <th>{{ 'label.fullname'|trans }}</th>
                    <th>{{ 'label.email'|trans }}</th>
                    <th class="text-center">{{ 'label.roles'|trans }}</th>
                    <th class="text-end">{{ 'label.actions'|trans }}</th>
                </tr>
            </thead>
            <tbody>
            {% for user in users %}
                <tr>
                    <td>{{ user.username }}</td>
                    <td>{{ user.fullName }}</td>
                    <td>{{ user.email }}</td>
                    <td class="text-center">
                        {% for role in user.roles %}
                            {% if role == 'ROLE_ADMIN' %}
                                <span class="badge bg-danger">Admin</span>
                            {% else %}
                                <span class="badge bg-primary">User</span>
                            {% endif %}
                        {% endfor %}
                    </td>
                    <td class="text-end">
                        {% if is_granted('ROLE_ADMIN') and app.user.username != user.username %}
                            <a href="{{ path('homepage', {'_switch_user': user.username}) }}"
                               class="btn btn-sm btn-outline-secondary">
                                {{ 'action.login_as'|trans }}
                            </a>
                        {% endif %}
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
{% endblock %}
```

---

### Step 8: Add Translation Keys

**File:** `translations/messages.en.yaml`

```yaml
# Admin translations
title:
    post_management: 'Post Management'
    user_management: 'User Management'
    create_post: 'Create New Post'
    edit_post: 'Edit Post'
    danger_zone: 'Danger Zone'
    comments: 'Comments'

label:
    title: 'Title'
    summary: 'Summary'
    content: 'Content'
    published_at: 'Published At'
    published: 'Published'
    tags: 'Tags'
    author: 'Author'
    actions: 'Actions'
    username: 'Username'
    fullname: 'Full Name'
    email: 'Email'
    roles: 'Roles'

action:
    create: 'Create'
    create_post: 'Create Post'
    create_user: 'Create User'
    save: 'Save'
    save_changes: 'Save Changes'
    save_and_create_new: 'Save and Create New'
    cancel: 'Cancel'
    delete: 'Delete'
    delete_confirmation: 'Are you sure you want to delete this item?'
    view: 'View'
    edit: 'Edit'
    back_to_post: 'Back to Post'
    back_to_list: 'Back to List'
    login_as: 'Login As'

help:
    post_summary: 'Short description shown in listings'
    post_tags: 'Comma-separated tags (max 4)'
    no_posts_found: 'No posts found. Create your first post!'
    delete_post_warning: 'Deleting a post cannot be undone.'
    no_tags: 'No tags'
    no_comments_yet: 'No comments yet.'
```

---

## Verification Criteria

### Admin Blog Management

1. **Index Page** (`/en/admin/post`)
   - ✓ Professional table with striped rows
   - ✓ Tags shown as badges
   - ✓ Dates formatted nicely
   - ✓ Action buttons in button group
   - ✓ No inline styles

2. **New Post** (`/en/admin/post/new`)
   - ✓ Form renders from partial
   - ✓ Two-column layout
   - ✓ "Save and Create New" button shows
   - ✓ All form fields work

3. **Edit Post** (`/en/admin/post/{id}/edit`)
   - ✓ Form renders from partial
   - ✓ Delete button in danger zone sidebar
   - ✓ CSRF token present in delete form
   - ✓ Confirmation dialog on delete

4. **Show Post** (`/en/admin/post/{id}`)
   - ✓ Post details displayed nicely
   - ✓ Metadata with badges
   - ✓ Comments section
   - ✓ Edit/back buttons

### Admin User Management

1. **User Index** (`/en/admin/users`)
   - ✓ No inline styles
   - ✓ Proper Bootstrap table
   - ✓ Role badges colored correctly
   - ✓ "Login As" button functional

---

## Memory File Updates

**File:** `.claude/memory/templates-frontend.md`

Add admin templates section:

```markdown
## Admin Interface Patterns

### Form Partials

Admin forms use reusable partials:

**`admin/blog/_form.html.twig`** - Blog post form
- Two-column layout (8/4 grid)
- Form fields in left column
- Meta and actions in right column
- Conditional "Save and Create New" button
- Variables: `button_label`, `show_save_and_create_new`

**`admin/blog/_delete_form.html.twig`** - Delete confirmation
- CSRF protection
- JavaScript confirmation
- Variable: `button_label`

### Admin Table Styling

Tables use:
- `.table.table-striped.table-hover.align-middle`
- `.table-responsive` wrapper
- `.table-light` for thead
- `.btn-group.btn-group-sm` for action buttons
- Badges for status/tags/roles

### Danger Zone Pattern

Destructive actions (delete) in separate sidebar card:
```twig
<div class="card border-danger mb-3">
    <div class="card-header bg-danger text-white">
        Danger Zone
    </div>
    <div class="card-body">
        {# Delete form #}
    </div>
</div>
```
```

---

## Context Reset Information

**Files Created:**
1. `templates/admin/blog/_form.html.twig` - Reusable post form
2. `templates/admin/blog/_delete_form.html.twig` - Delete confirmation

**Files Modified:**
1. `templates/admin/blog/index.html.twig` - Professional table
2. `templates/admin/blog/new.html.twig` - Uses form partial
3. `templates/admin/blog/edit.html.twig` - Uses form partial, danger zone
4. `templates/admin/blog/show.html.twig` - Enhanced display
5. `templates/admin/user/index.html.twig` - Removed inline styles
6. `translations/messages.en.yaml` - Added admin translations

**Next Plan:** `06-blog-components-partials.md`

---

## Completion Checklist

- [ ] Form partials created
- [ ] All admin blog templates enhanced
- [ ] Admin user templates cleaned
- [ ] No inline styles remain
- [ ] Translation keys added
- [ ] Visual testing completed
- [ ] Delete confirmations work
- [ ] Memory file updated
- [ ] Git commit:
  ```
  feat: polish admin interface with partials and styling

  - Create reusable form partials for post CRUD
  - Add delete confirmation partial with CSRF
  - Replace inline styles with Bootstrap classes
  - Enhance tables with professional styling
  - Add danger zone pattern for destructive actions
  - Improve UX with badges, buttons, and layouts

  Admin interface now matches professional standards.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 06
