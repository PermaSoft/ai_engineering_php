# Plan 06: Blog UI Components & Partials

**Priority:** 🟠 HIGH
**Estimated Time:** 4-5 hours
**Dependencies:** Plan 04 (Template Architecture)
**Status:** Ready to execute

---

## Context & Objective

Create reusable blog UI partials to eliminate code duplication and improve maintainability:
1. Extract post rendering into `_post.html.twig` partial
2. Create `_post_tags.html.twig` for tag display
3. Create `_comment.html.twig` for comment rendering
4. Create `_comment_form.html.twig` for comment submission
5. Create `_rss.html.twig` for RSS feed link
6. Update blog templates to use these partials

This follows the DRY principle and matches walkthrought's modular template architecture (42% of templates are currently missing).

---

## Reference Materials

### Memory Files
- `.claude/memory/templates-frontend.md` - Template patterns and partials
- `BRANCH_COMPARISON_REPORT.md` (Section 7: Templates & Frontend)

### Walkthrought Files
```bash
git show walkthrought:templates/blog/_post.html.twig
git show walkthrought:templates/blog/_post_tags.html.twig
git show walkthrought:templates/blog/_comment.html.twig
git show walkthrought:templates/blog/_comment_form.html.twig
git show walkthrought:templates/blog/_rss.html.twig
```

---

## Prerequisites

- Base template enhanced (Plan 04)
- Bootstrap 5 knowledge
- Understanding of Twig includes and partials

---

## Deliverables Checklist

### Code Changes
- [ ] `templates/blog/_post.html.twig` - Post display partial
- [ ] `templates/blog/_post_tags.html.twig` - Tag list display
- [ ] `templates/blog/_comment.html.twig` - Single comment display
- [ ] `templates/blog/_comment_form.html.twig` - Comment submission form
- [ ] `templates/blog/_rss.html.twig` - RSS feed link component
- [ ] Update `templates/blog/index.html.twig` to use partials
- [ ] Update `templates/blog/post_show.html.twig` to use partials
- [ ] Add translation keys for blog UI

### Testing
- [ ] Blog index renders posts using partial
- [ ] Post show page renders with partials
- [ ] Tags display correctly
- [ ] Comment form works
- [ ] RSS link appears in sidebar

### Documentation
- [ ] Update memory file with partial patterns

---

## Implementation Steps

### Step 1: Create Post Display Partial

**File:** `templates/blog/_post.html.twig`

```twig
{#
    Displays a blog post summary or full content.

    Parameters:
        - post (required): Post entity
        - show_full_content (optional, default: false): Whether to show full content
#}

<article class="post mb-4">
    <header class="post-header mb-3">
        <h2 class="post-title">
            {% if show_full_content|default(false) %}
                {{ post.title }}
            {% else %}
                <a href="{{ path('blog_post', {slug: post.slug}) }}">
                    {{ post.title }}
                </a>
            {% endif %}
        </h2>

        <div class="post-metadata text-muted small">
            <span>
                {{ 'post.published_at'|trans }}
                <time datetime="{{ post.publishedAt|date('Y-m-d H:i:s') }}">
                    {{ post.publishedAt|format_datetime('long', 'short', locale=app.request.locale) }}
                </time>
            </span>
            •
            <span>
                {{ 'post.author'|trans }}:
                <strong>{{ post.author.fullName }}</strong>
            </span>
        </div>
    </header>

    <div class="post-content">
        {% if show_full_content|default(false) %}
            {{ post.content|markdown_to_html }}
        {% else %}
            {{ post.summary|markdown_to_html }}
        {% endif %}
    </div>

    <footer class="post-footer mt-3">
        {% include 'blog/_post_tags.html.twig' with {tags: post.tags} %}

        {% if not show_full_content|default(false) %}
            <div class="mt-2">
                <a href="{{ path('blog_post', {slug: post.slug}) }}" class="btn btn-sm btn-primary">
                    {{ 'action.read_more'|trans }}
                </a>
            </div>
        {% endif %}
    </footer>
</article>

{% if not loop.last|default(false) %}
    <hr>
{% endif %}
```

**Key Features:**
- Flexible display (summary or full content)
- Proper semantic HTML5 (article, header, footer)
- Formatted publication date with locale support
- Markdown rendering
- Includes tag display partial
- "Read more" button for summaries
- Horizontal rule between posts

---

### Step 2: Create Post Tags Partial

**File:** `templates/blog/_post_tags.html.twig`

```twig
{#
    Displays tags for a post.

    Parameters:
        - tags (required): Collection of Tag entities
#}

{% if tags|length > 0 %}
    <div class="post-tags">
        {% for tag in tags %}
            <a href="{{ path('blog_index', {tag: tag.name}) }}"
               class="badge bg-secondary text-decoration-none me-1">
                {{ tag.name }}
            </a>
        {% endfor %}
    </div>
{% endif %}
```

**Key Features:**
- Only renders if tags exist
- Clickable badges that filter by tag
- Bootstrap 5 badge styling
- Proper spacing between tags

---

### Step 3: Create Comment Display Partial

**File:** `templates/blog/_comment.html.twig`

```twig
{#
    Displays a single comment.

    Parameters:
        - comment (required): Comment entity
#}

<div class="comment mb-4 border-start border-4 border-primary ps-3" id="comment-{{ comment.id }}">
    <div class="comment-header d-flex justify-content-between align-items-start mb-2">
        <div>
            <strong>{{ comment.author.fullName }}</strong>
            <span class="text-muted small">
                ({{ comment.author.email }})
            </span>
        </div>
        <time class="text-muted small" datetime="{{ comment.publishedAt|date('Y-m-d H:i:s') }}">
            {{ comment.publishedAt|format_datetime('medium', 'short', locale=app.request.locale) }}
        </time>
    </div>

    <div class="comment-content">
        {{ comment.content|markdown_to_html }}
    </div>
</div>
```

**Key Features:**
- Semantic HTML with proper structure
- Visual left border for hierarchy
- Author name and email
- Formatted timestamp with locale
- Markdown rendering for comment content
- Anchor ID for direct linking

---

### Step 4: Create Comment Form Partial

**File:** `templates/blog/_comment_form.html.twig`

```twig
{#
    Displays comment submission form.

    Parameters:
        - form (required): Comment form instance
#}

<div class="comment-form card">
    <div class="card-header">
        <h4 class="mb-0">{{ 'title.add_comment'|trans }}</h4>
    </div>
    <div class="card-body">
        {{ form_start(form) }}
            <div class="mb-3">
                {{ form_label(form.content) }}
                {{ form_widget(form.content, {
                    attr: {
                        class: 'form-control',
                        rows: 10,
                        placeholder: 'help.comment_placeholder'|trans
                    }
                }) }}
                {{ form_errors(form.content) }}
                <div class="form-text">
                    {{ 'help.markdown_enabled'|trans }}
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="form-text">
                    {{ 'help.comment_moderation'|trans }}
                </div>
                <button type="submit" class="btn btn-primary">
                    {{ 'action.publish_comment'|trans }}
                </button>
            </div>
        {{ form_end(form) }}
    </div>
</div>
```

**Key Features:**
- Card layout for visual separation
- Helpful placeholder text
- Markdown hint
- Comment moderation notice
- Proper Bootstrap 5 form styling
- Translation keys throughout

---

### Step 5: Create RSS Feed Link Partial

**File:** `templates/blog/_rss.html.twig`

```twig
{# RSS feed subscription link for sidebar #}

<div class="card mb-3">
    <div class="card-header">
        {{ 'title.rss_feed'|trans }}
    </div>
    <div class="card-body">
        <p class="card-text small">
            {{ 'help.rss_description'|trans }}
        </p>
        <a href="{{ path('blog_rss') }}" class="btn btn-warning btn-sm w-100" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-rss" viewBox="0 0 16 16">
                <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                <path d="M5.5 12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-3-8.5a1 1 0 0 1 1-1c5.523 0 10 4.477 10 10a1 1 0 1 1-2 0 8 8 0 0 0-8-8 1 1 0 0 1-1-1zm0 4a1 1 0 0 1 1-1 6 6 0 0 1 6 6 1 1 0 1 1-2 0 4 4 0 0 0-4-4 1 1 0 0 1-1-1z"/>
            </svg>
            {{ 'action.subscribe_rss'|trans }}
        </a>
    </div>
</div>
```

**Key Features:**
- Card format for sidebar
- RSS icon (Bootstrap Icons)
- Warning color (orange/yellow)
- Descriptive help text
- Full-width button

---

### Step 6: Update Blog Index Template

**File:** `templates/blog/index.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ 'title.blog_index'|trans }}{% endblock %}

{% block body %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ 'title.blog_posts'|trans }}</h1>
    </div>

    {% if posts|length > 0 %}
        {% for post in posts %}
            {% include 'blog/_post.html.twig' with {
                post: post,
                show_full_content: false
            } %}
        {% endfor %}

        {# Pagination #}
        <nav aria-label="Page navigation">
            {{ knp_pagination_render(posts) }}
        </nav>
    {% else %}
        <div class="alert alert-info">
            {{ 'help.no_posts_found'|trans }}
        </div>
    {% endif %}
{% endblock %}

{% block sidebar %}
    {{ parent() }}

    {# RSS Feed Link #}
    {% include 'blog/_rss.html.twig' %}
{% endblock %}
```

**Changes:**
- Uses `_post.html.twig` partial for each post
- Cleaner, more maintainable code
- Adds RSS feed to sidebar
- Keeps default about section

---

### Step 7: Update Post Show Template

**File:** `templates/blog/post_show.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block page_title %}{{ post.title }}{% endblock %}

{% block body %}
    {# Post Content #}
    {% include 'blog/_post.html.twig' with {
        post: post,
        show_full_content: true
    } %}

    {# Comments Section #}
    {% if post.comments|length > 0 %}
        <section id="comments" class="mt-5">
            <h3>{{ 'title.comments'|trans }} ({{ post.comments|length }})</h3>
            <hr>

            {% for comment in post.comments %}
                {% include 'blog/_comment.html.twig' with {comment: comment} %}
            {% endfor %}
        </section>
    {% endif %}

    {# Comment Form #}
    {% if is_granted('IS_AUTHENTICATED_FULLY') %}
        <section id="add-comment" class="mt-5">
            {% include 'blog/_comment_form.html.twig' with {form: commentForm} %}
        </section>
    {% else %}
        <div class="alert alert-info mt-5">
            <a href="{{ path('security_login') }}">{{ 'action.sign_in'|trans }}</a>
            {{ 'help.sign_in_to_comment'|trans }}
        </div>
    {% endif %}
{% endblock %}

{% block sidebar %}
    {{ parent() }}

    {# Author Info #}
    <div class="card mb-3">
        <div class="card-header">
            {{ 'title.author'|trans }}
        </div>
        <div class="card-body">
            <strong>{{ post.author.fullName }}</strong>
            <p class="text-muted small mb-0">
                {{ post.author.email }}
            </p>
        </div>
    </div>

    {# RSS Feed #}
    {% include 'blog/_rss.html.twig' %}

    {# Edit Actions (for admins) #}
    {% if is_granted('edit', post) %}
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning">
                {{ 'title.admin_actions'|trans }}
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ path('admin_post_edit', {id: post.id}) }}" class="btn btn-sm btn-warning">
                        {{ 'action.edit_post'|trans }}
                    </a>
                </div>
            </div>
        </div>
    {% endif %}
{% endblock %}
```

**Changes:**
- Uses all new partials
- Clean separation of concerns
- Enhanced sidebar with author info
- Admin edit button for authorized users

---

### Step 8: Add Translation Keys

**File:** `translations/messages.en.yaml`

Add these translations:

```yaml
# Post-related translations
post:
    published_at: 'Published on'
    author: 'Author'

# Title translations
title:
    blog_index: 'Blog'
    blog_posts: 'Blog Posts'
    comments: 'Comments'
    add_comment: 'Add a Comment'
    rss_feed: 'RSS Feed'
    author: 'About the Author'
    admin_actions: 'Admin Actions'

# Actions
action:
    read_more: 'Read more'
    publish_comment: 'Publish comment'
    subscribe_rss: 'Subscribe to RSS'
    sign_in: 'Sign in'
    edit_post: 'Edit Post'

# Help text
help:
    no_posts_found: 'No blog posts found.'
    comment_placeholder: 'Write your comment here... (Markdown supported)'
    markdown_enabled: 'You can use Markdown to format your comment.'
    comment_moderation: 'Comments are moderated and may not appear immediately.'
    rss_description: 'Subscribe to our RSS feed to get notified of new blog posts.'
    sign_in_to_comment: 'to post a comment.'
```

---

## Verification Criteria

### Test Blog Index Page

1. Navigate to `/en/blog`
2. ✓ Posts display using partial
3. ✓ Each post shows title, metadata, summary
4. ✓ Tags appear as clickable badges
5. ✓ "Read more" button on each post
6. ✓ RSS feed link in sidebar
7. ✓ Pagination works
8. ✓ No code duplication visible

### Test Post Detail Page

1. Click on a blog post
2. ✓ Full post content displays
3. ✓ Tags appear correctly
4. ✓ Comments section shows
5. ✓ Each comment uses comment partial
6. ✓ Comment form appears (when logged in)
7. ✓ Author info in sidebar
8. ✓ RSS feed in sidebar
9. ✓ Admin can see edit button

### Test Comment Submission

1. Login as user
2. Navigate to a post
3. ✓ Comment form appears
4. ✓ Submit a comment
5. ✓ Comment appears in list
6. ✓ Markdown rendering works

### Test Tag Filtering

1. Click on a tag badge
2. ✓ Redirects to blog index with tag filter
3. ✓ Only posts with that tag appear

---

## Memory File Updates

**File:** `.claude/memory/templates-frontend.md`

Add this section:

```markdown
## Blog Template Partials

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

### Tag Partial Usage

```twig
{% include 'blog/_post_tags.html.twig' with {tags: post.tags} %}
```

### Comment Partial Usage

**Display comment:**
```twig
{% include 'blog/_comment.html.twig' with {comment: comment} %}
```

**Display comment form:**
```twig
{% include 'blog/_comment_form.html.twig' with {form: commentForm} %}
```

### RSS Link Usage (Sidebar)

```twig
{% block sidebar %}
    {{ parent() }}
    {% include 'blog/_rss.html.twig' %}
{% endblock %}
```

### Benefits of Partials
- DRY principle (Don't Repeat Yourself)
- Easier maintenance (change in one place)
- Testable in isolation
- Reusable across templates
- Clear separation of concerns
```

---

## Context Reset Information

If resuming after context reset:

**Files Created:**
1. `templates/blog/_post.html.twig` - Post display partial
2. `templates/blog/_post_tags.html.twig` - Tag display
3. `templates/blog/_comment.html.twig` - Comment display
4. `templates/blog/_comment_form.html.twig` - Comment form
5. `templates/blog/_rss.html.twig` - RSS feed link

**Files Modified:**
1. `templates/blog/index.html.twig` - Now uses post partial
2. `templates/blog/post_show.html.twig` - Now uses all partials
3. `translations/messages.en.yaml` - Added blog UI translations

**Verification:**
- Browse blog index, verify posts render correctly
- View individual post, verify comments and form work
- Check RSS link appears in sidebar
- Test tag filtering
- Memory file updated

**Next Plan:** `07-form-enhancements.md`

---

## Completion Checklist

- [ ] 5 blog partial templates created
- [ ] Blog index template updated to use partials
- [ ] Post show template updated to use partials
- [ ] Translation keys added
- [ ] All partials tested individually
- [ ] Tag filtering works
- [ ] Comment display works
- [ ] Comment form submission works
- [ ] RSS link displays
- [ ] Memory file updated
- [ ] Git commit created:
  ```
  feat: add blog UI partials for modularity and reusability

  - Create _post.html.twig for post display (summary/full)
  - Create _post_tags.html.twig for tag badges
  - Create _comment.html.twig for comment display
  - Create _comment_form.html.twig for comment submission
  - Create _rss.html.twig for RSS feed link
  - Update blog templates to use partials
  - Add translation keys for blog UI

  Eliminates code duplication and improves maintainability.
  Follows DRY principle and Symfony best practices.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 07
