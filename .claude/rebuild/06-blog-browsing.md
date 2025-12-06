# Phase 6: Blog Browsing (Public Features)

## Overview

**Goal**: Implement public blog listing, post viewing, search, and RSS feed
**Complexity**: Medium
**Dependencies**: Phase 2, 3, 5
**Estimated Files**: 8-10 files (controllers + templates)

## Memory Files Required

- **Primary**: [Controllers & Routing](../memory/controllers-routing.md), [Templates & Frontend](../memory/templates-frontend.md)
- **Reference**: [Services & Repositories](../memory/services-repositories.md)

## Packmind Standards Applied

- Symfony Controllers Best Practices
- Symfony Templates & Twig Best Practices

## Implementation Checklist

### 1. Blog Controller

- [ ] Create `src/Controller/BlogController.php`
  - [ ] Mark as `final class`
  - [ ] Extend `AbstractController`

- [ ] **Index action** - `#[Route('/blog/', name: 'blog_index')]`
  - [ ] Parameters: `int $page = 1`, `?string $tag = null`, `PostRepository $posts`
  - [ ] Get paginated posts: `$posts->findLatest($page, $tag)`
  - [ ] Render `blog/index.html.twig`
  - [ ] Pass: posts, tag (if filtered)

- [ ] **Post show action** - `#[Route('/blog/posts/{slug}', name: 'blog_post')]`
  - [ ] Use `EntityValueResolver` to auto-fetch Post by slug
  - [ ] Parameter: `#[MapEntity(mapping: ['slug' => 'slug'])] Post $post`
  - [ ] Render `blog/post_show.html.twig`

- [ ] **Search action** - `#[Route('/blog/search', name: 'blog_search')]`
  - [ ] Parameters: `Request $request`, `PostRepository $posts`
  - [ ] Get search query from request: `$query = $request->query->get('q', '')`
  - [ ] Search posts (implement search in PostRepository)
  - [ ] Render `blog/search.html.twig`

- [ ] **RSS feed action** - `#[Route('/blog/rss.xml', name: 'blog_rss')]`
  - [ ] Parameters: `PostRepository $posts`, `Response`
  - [ ] Get latest posts (limit 20)
  - [ ] Render `blog/rss.xml.twig` with XML content type
  - [ ] Set headers: `Content-Type: application/rss+xml`

### 2. Templates - Base Layout

- [ ] Create `templates/base.html.twig`
  - [ ] HTML5 doctype
  - [ ] Bootstrap 5 CSS
  - [ ] Blocks: `title`, `stylesheets`, `body`, `javascripts`
  - [ ] Navigation menu (links to blog, admin, login/logout)
  - [ ] Flash message display
  - [ ] Footer

### 3. Templates - Blog

- [ ] Create `templates/blog/index.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Display paginated posts
  - [ ] Each post: title, summary, author, date, tags
  - [ ] Tag filter display if active
  - [ ] Pagination controls
  - [ ] Link to individual posts using `path('blog_post', {slug: post.slug})`

- [ ] Create `templates/blog/post_show.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Display full post: title, content (markdown), author, date, tags
  - [ ] Comments section (display existing comments)
  - [ ] Comment form (if user is authenticated)
  - [ ] Edit/Delete buttons (if user has permission)

- [ ] Create `templates/blog/search.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Search form
  - [ ] Display search results
  - [ ] Show query and result count

- [ ] Create `templates/blog/rss.xml.twig`
  - [ ] XML RSS 2.0 format
  - [ ] Channel info: title, link, description
  - [ ] Items: posts with title, link, description, pubDate

### 4. Templates - Partials

- [ ] Create `templates/blog/_post_summary.html.twig`
  - [ ] Reusable post summary partial
  - [ ] Used in blog index and search results

- [ ] Create `templates/blog/_comment.html.twig`
  - [ ] Reusable comment display partial
  - [ ] Shows comment content, author, date

### 5. Repository Search Method

- [ ] Update `PostRepository`
  - [ ] Add `search(string $query): array` method
  - [ ] Search in title, summary, content fields
  - [ ] Use QueryBuilder with `LIKE` conditions
  - [ ] Order by publishedAt DESC

### 6. Navigation & Locale Handling

- [ ] Create `templates/_header.html.twig`
  - [ ] Site navigation
  - [ ] Locale switcher (en, fr, de, etc.)
  - [ ] User menu (login/logout, profile)

- [ ] Implement locale switching
  - [ ] All routes prefixed with `/{_locale}`
  - [ ] Locale parameter in route generation

### 7. Assets & Frontend

- [ ] Create `assets/styles/app.scss`
  - [ ] Import Bootstrap
  - [ ] Custom styles for blog
  - [ ] Responsive design

- [ ] Create `assets/app.js`
  - [ ] Import Stimulus
  - [ ] Import Bootstrap JavaScript
  - [ ] Initialize components

- [ ] Build assets:
  - [ ] Run: `php bin/console sass:build`
  - [ ] Run: `php bin/console importmap:install`

### 8. Create Fixtures for Posts

- [ ] Update `src/DataFixtures/AppFixtures.php`
  - [ ] Create 9 tags: lorem, ipsum, consectetur, adipiscing, incididunt, labore, voluptate, dolore, pariatur
  - [ ] Create 30 blog posts
  - [ ] Assign random 2-4 tags per post
  - [ ] Set authors to admin users
  - [ ] Set publishedAt to last 30 days (random)

### 9. Verification

- [ ] Load fixtures: `php bin/console doctrine:fixtures:load`
- [ ] Visit `/en/blog/` - should show paginated posts
- [ ] Click on post - should show full post
- [ ] Click on tag - should filter by tag
- [ ] Test search - enter query, see results
- [ ] Visit `/en/blog/rss.xml` - should show RSS feed
- [ ] Test locale switching
- [ ] Test responsive design (mobile/desktop)
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/Controller/`

## Success Criteria

✅ Blog index shows paginated posts
✅ Post detail page displays full content
✅ Tag filtering works
✅ Search functionality works
✅ RSS feed generates valid XML
✅ Navigation and locale switching work
✅ Assets built and loaded correctly
✅ All templates render properly
✅ Fixtures create 30 posts with tags

## Next Phase

[Phase 7: Admin Panel](./07-admin-panel.md)