# Plan 14: RSS & Content Syndication

**Priority:** 🟢 STANDARD
**Estimated Time:** 2-3 hours
**Dependencies:** Plan 13 (Event Subscribers)
**Status:** Ready to execute

---

## Context & Objective

Add RSS feed functionality for blog syndication:
1. Create RSS XML template for blog posts
2. Update BlogController to handle RSS route
3. Add RSS feed link to blog templates
4. Test RSS feed validation

This is a simple but important feature for blog subscribers.

---

## Reference Materials

### Memory Files
- `.claude/memory/controllers-routing.md` - Controller patterns
- `.claude/memory/templates-frontend.md` - Template patterns
- `BRANCH_COMPARISON_REPORT.md` (Section 14: RSS & Content Syndication)

### Walkthrought Files
```bash
git show walkthrought:templates/blog/index.xml.twig
git show walkthrought:src/Controller/BlogController.php
```

---

## Prerequisites

- BlogController implemented
- Blog posts exist with published dates
- Understanding of RSS 2.0 format

---

## Deliverables Checklist

### Code Changes
- [ ] `templates/blog/index.xml.twig` - RSS feed template
- [ ] Update `src/Controller/BlogController.php` - Add RSS route
- [ ] Blog templates already have RSS link (from Plan 06)
- [ ] Add RSS route to routing

### Testing
- [ ] RSS feed validates (W3C Feed Validator)
- [ ] Feed displays posts correctly
- [ ] Dates format correctly
- [ ] Feed is discoverable in browser

### Documentation
- [ ] Update memory files with RSS pattern

---

## Implementation Steps

### Step 1: Add RSS Route to BlogController

**File:** `src/Controller/BlogController.php`

Add this method:

```php
#[Route('/rss.xml', name: 'blog_rss', methods: ['GET'])]
public function rss(PostRepository $posts): Response
{
    $latestPosts = $posts->findLatest();

    $response = $this->render('blog/index.xml.twig', [
        'posts' => $latestPosts,
    ]);

    $response->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

    return $response;
}
```

**Key features:**
- Uses `.xml.twig` template
- Sets proper Content-Type header for RSS
- Fetches latest posts using existing repository method

---

### Step 2: Create RSS Feed Template

**File:** `templates/blog/index.xml.twig`

```xml
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ 'rss.title'|trans }}</title>
        <description>{{ 'rss.description'|trans }}</description>
        <link>{{ url('blog_index') }}</link>
        <atom:link href="{{ url('blog_rss') }}" rel="self" type="application/rss+xml" />
        <language>{{ app.request.locale }}</language>
        <lastBuildDate>{{ 'now'|date('r') }}</lastBuildDate>
        <generator>Symfony Demo Application</generator>
        <pubDate>{{ 'now'|date('r') }}</pubDate>

        {% for post in posts %}
        <item>
            <title>{{ post.title }}</title>
            <link>{{ url('blog_post', {slug: post.slug}) }}</link>
            <guid isPermaLink="true">{{ url('blog_post', {slug: post.slug}) }}</guid>
            <pubDate>{{ post.publishedAt|date('r') }}</pubDate>
            <description><![CDATA[{{ post.summary|raw }}]]></description>
            <author>{{ post.author.email }} ({{ post.author.fullName }})</author>
            {% for tag in post.tags %}
            <category>{{ tag.name }}</category>
            {% endfor %}
        </item>
        {% endfor %}
    </channel>
</rss>
```

**Key features:**
- RSS 2.0 compliant format
- Atom namespace for self-link
- Proper date formatting (RFC 2822 format via 'r' filter)
- CDATA for summary (safe HTML)
- Tags as categories
- Author email and name
- Localized title and description
- Absolute URLs using `url()` function

---

### Step 3: Add Translation Keys

**File:** `translations/messages.en.yaml`

```yaml
# RSS Feed
rss:
    title: 'Symfony Demo Blog'
    description: 'Latest blog posts from the Symfony Demo Application'

# Already added in Plan 06, but verify:
action:
    subscribe_rss: 'Subscribe to RSS'

title:
    rss_feed: 'RSS Feed'

help:
    rss_description: 'Subscribe to our RSS feed to get notified of new blog posts.'
```

---

### Step 4: Add RSS Auto-Discovery Meta Tag

**File:** `templates/base.html.twig`

Add to the `<head>` section:

```twig
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{% block page_title %}Symfony Demo Application{% endblock %}</title>

    {# RSS Auto-discovery #}
    <link rel="alternate" type="application/rss+xml"
          title="{{ 'rss.title'|trans }}"
          href="{{ url('blog_rss') }}">

    {% block stylesheets %}
        {{ importmap('app') }}
    {% endblock %}

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
</head>
```

**Benefit:** Browsers will automatically detect the RSS feed and show an RSS icon.

---

### Step 5: Verify RSS Link in Sidebar (Already Done in Plan 06)

The RSS link component was already created in Plan 06:

**File:** `templates/blog/_rss.html.twig` (already exists)

Just verify it's included in blog templates:

```twig
{% block sidebar %}
    {{ parent() }}
    {% include 'blog/_rss.html.twig' %}
{% endblock %}
```

---

### Step 6: Add RSS Link to Blog Index Header (Optional Enhancement)

**File:** `templates/blog/index.html.twig`

Add RSS link to page header:

```twig
{% block body %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ 'title.blog_posts'|trans }}</h1>

        <a href="{{ path('blog_rss') }}" class="btn btn-sm btn-warning" target="_blank">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm1.5 2.5c5.523 0 10 4.477 10 10a1 1 0 1 1-2 0 8 8 0 0 0-8-8 1 1 0 0 1 0-2zm0 4a6 6 0 0 1 6 6 1 1 0 1 1-2 0 4 4 0 0 0-4-4 1 1 0 0 1 0-2zm.5 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
            </svg>
            {{ 'action.subscribe_rss'|trans }}
        </a>
    </div>

    {# ... rest of blog index content ... #}
{% endblock %}
```

---

## Verification Criteria

### Test RSS Feed in Browser

1. Navigate to `/en/blog/rss.xml`
2. ✓ XML content displays
3. ✓ Posts are listed with titles, links, dates
4. ✓ Tags appear as categories
5. ✓ No XML parsing errors
6. ✓ Content-Type header is `application/rss+xml`

### Validate RSS Feed

1. Copy RSS feed URL: `http://localhost:8000/en/blog/rss.xml`
2. Visit W3C Feed Validator: https://validator.w3.org/feed/
3. Paste URL and validate
4. ✓ Feed validates without errors
5. ✓ All required elements present
6. ✓ Dates in correct format

### Test Auto-Discovery

1. Visit blog index in Firefox or Chrome
2. ✓ Browser shows RSS feed icon in address bar (some browsers)
3. ✓ Can subscribe directly from browser

### Test RSS Readers

Test with RSS readers:
- Feedly
- The Old Reader
- RSS reader browser extension

1. Add feed URL to reader
2. ✓ Feed is recognized
3. ✓ Posts display correctly
4. ✓ Links work
5. ✓ Images load (if any in content)

---

## Memory File Updates

**File:** `.claude/memory/controllers-routing.md`

Add this section:

```markdown
## RSS Feed Implementation

### BlogController RSS Route

```php
#[Route('/rss.xml', name: 'blog_rss', methods: ['GET'])]
public function rss(PostRepository $posts): Response
{
    $latestPosts = $posts->findLatest();

    $response = $this->render('blog/index.xml.twig', [
        'posts' => $latestPosts,
    ]);

    $response->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

    return $response;
}
```

Key points:
- Uses `.xml.twig` template extension
- Sets proper RSS Content-Type header
- Returns latest posts

### RSS Template

Location: `templates/blog/index.xml.twig`

Format: RSS 2.0 with Atom namespace for self-link

Key elements:
- Channel metadata (title, description, link)
- Language from request locale
- Items with title, link, guid, pubDate, description
- Categories from post tags
- Author email and name

Date format: RFC 2822 (using 'r' format in Twig)

### Auto-Discovery

Add to `<head>` in base template:
```html
<link rel="alternate" type="application/rss+xml"
      title="Symfony Demo Blog"
      href="{{ url('blog_rss') }}">
```

### Testing RSS Feeds

- W3C Feed Validator: https://validator.w3.org/feed/
- RSS readers: Feedly, The Old Reader
- Browser auto-discovery (Firefox, Chrome)
```

---

**File:** `.claude/memory/templates-frontend.md`

Add RSS template documentation.

---

## Context Reset Information

**Files Created:**
1. `templates/blog/index.xml.twig` - RSS feed template

**Files Modified:**
1. `src/Controller/BlogController.php` - Added RSS route method
2. `templates/base.html.twig` - Added RSS auto-discovery meta tag
3. `templates/blog/index.html.twig` - Added RSS button (optional)
4. `translations/messages.en.yaml` - Added RSS translations

**Verification:**
- Visit `/en/blog/rss.xml` - feed displays
- Validate feed at W3C validator
- Test in RSS reader
- Check auto-discovery works
- Memory files updated

**Next Plan:** `15-memory-updates.md`

---

## Completion Checklist

- [ ] RSS template created
- [ ] BlogController RSS route added
- [ ] RSS auto-discovery meta tag added
- [ ] Translation keys added
- [ ] Feed validates at W3C validator
- [ ] Feed works in RSS readers
- [ ] Auto-discovery works in browsers
- [ ] Dates format correctly (RFC 2822)
- [ ] Links are absolute URLs
- [ ] Memory files updated
- [ ] Git commit created:
  ```
  feat: add RSS feed for blog syndication

  - Create RSS 2.0 feed template (index.xml.twig)
  - Add BlogController RSS route with proper headers
  - Add RSS auto-discovery meta tag
  - Include tags as categories
  - Use proper date formatting (RFC 2822)
  - Add translation keys for RSS metadata

  Enables blog content syndication via RSS readers.
  Feed validates against W3C RSS specification.
  ```

---

**Status:** ⏳ PENDING
**When Complete:** Update master plan and proceed to Plan 15