# Phase 7: Admin Panel (Post Management)

## Overview

**Goal**: Implement admin post CRUD operations with authorization
**Complexity**: Medium
**Dependencies**: Phase 2, 3, 4, 5
**Estimated Files**: 5-7 files (controller + templates)

## Memory Files Required

- **Primary**: [Controllers & Routing](../memory/controllers-routing.md), [Templates & Frontend](../memory/templates-frontend.md)
- **Reference**: [Security Architecture](../memory/security-architecture.md), [Forms & Validation](../memory/forms-validation.md)

## Packmind Standards Applied

- Symfony Controllers Best Practices
- Symfony Security Best Practices
- Symfony Templates & Twig Best Practices

## Implementation Checklist

### 1. Admin Controller

- [ ] Create `src/Controller/Admin/PostController.php`
  - [ ] Mark as `final class`
  - [ ] Extend `AbstractController`
  - [ ] All routes prefixed with `/admin/post/`
  - [ ] All actions require `ROLE_ADMIN`

- [ ] **Index action** - `#[Route('/', name: 'admin_post_index')]`
  - [ ] List all posts (admin's own posts only)
  - [ ] Pagination support
  - [ ] Inject: `PostRepository`, `#[CurrentUser] User $user`
  - [ ] Render `admin/post/index.html.twig`

- [ ] **New action** - `#[Route('/new', name: 'admin_post_new')]`
  - [ ] GET: Display empty PostType form
  - [ ] POST: Handle form submission
  - [ ] Set current user as author
  - [ ] Flash success message: `post.created_successfully`
  - [ ] Redirect to `admin_post_index`
  - [ ] Render `admin/post/new.html.twig`

- [ ] **Show action** - `#[Route('/{id}', name: 'admin_post_show')]`
  - [ ] Display single post details
  - [ ] Use `EntityValueResolver` for Post
  - [ ] Check `#[IsGranted('show', subject: 'post')]`
  - [ ] Render `admin/post/show.html.twig`

- [ ] **Edit action** - `#[Route('/{id}/edit', name: 'admin_post_edit')]`
  - [ ] Use `EntityValueResolver` for Post
  - [ ] Check `#[IsGranted('edit', subject: 'post')]` using PostVoter
  - [ ] GET: Display form pre-filled with post data
  - [ ] POST: Handle form submission and update
  - [ ] Flash success message: `post.updated_successfully`
  - [ ] Redirect to `admin_post_index`
  - [ ] Render `admin/post/edit.html.twig`

- [ ] **Delete action** - `#[Route('/{id}/delete', name: 'admin_post_delete', methods: ['POST'])]`
  - [ ] Use `EntityValueResolver` for Post
  - [ ] Check `#[IsGranted('delete', subject: 'post')]` using PostVoter
  - [ ] Validate CSRF token
  - [ ] Remove post from database
  - [ ] Flash success message: `post.deleted_successfully`
  - [ ] Redirect to `admin_post_index`

### 2. Admin Templates

- [ ] Create `templates/admin/post/index.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Page title: "Post Management"
  - [ ] "Create new post" button → links to `admin_post_new`
  - [ ] Table of posts: title, author, published date, actions
  - [ ] Actions: View, Edit, Delete (with CSRF)
  - [ ] Pagination controls

- [ ] Create `templates/admin/post/new.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Page title: "Create new post"
  - [ ] Render PostType form: `{{ form(form) }}`
  - [ ] Cancel button → links back to `admin_post_index`

- [ ] Create `templates/admin/post/edit.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Page title: "Edit post"
  - [ ] Render PostType form pre-filled
  - [ ] Cancel button → links back to `admin_post_index`
  - [ ] Delete button (separate form with CSRF)

- [ ] Create `templates/admin/post/show.html.twig`
  - [ ] Extend `base.html.twig`
  - [ ] Display full post details
  - [ ] Edit/Delete buttons (if authorized)
  - [ ] Back to list button

### 3. Form Rendering

- [ ] Update `PostType` form (if needed)
  - [ ] Ensure all fields have proper labels
  - [ ] Add help text for fields
  - [ ] Configure buttons in template (not form class)

- [ ] Add form buttons in templates
  - [ ] Submit button in new/edit templates
  - [ ] Cancel button (link, not submit)

### 4. Authorization Integration

- [ ] Verify `PostVoter` is applied
  - [ ] Edit/Delete actions check voter permissions
  - [ ] Only post authors can edit/delete their posts
  - [ ] Admin role hierarchy allows access

- [ ] Template authorization
  - [ ] Use `is_granted('edit', post)` in templates
  - [ ] Show/hide Edit button based on permission
  - [ ] Show/hide Delete button based on permission

### 5. Flash Messages

- [ ] Add flash message translations in `translations/messages.en.yaml`
  - [ ] `post.created_successfully`
  - [ ] `post.updated_successfully`
  - [ ] `post.deleted_successfully`

- [ ] Display flash messages in `base.html.twig`
  - [ ] Bootstrap alert styling
  - [ ] Success/error/info types

### 6. CSRF Protection

- [ ] Delete form with CSRF token
  - [ ] Generate token: `csrf_token('delete' ~ post.id)`
  - [ ] Verify token in controller
  - [ ] Return 400 if invalid

### 7. Navigation Update

- [ ] Update `templates/_header.html.twig`
  - [ ] Add "Admin" dropdown menu (visible only to ROLE_ADMIN)
  - [ ] Link to `admin_post_index`
  - [ ] Link to `admin_post_new`

### 8. Verification

- [ ] Login as `jane_admin` / `kitten`
- [ ] Visit `/en/admin/post/` - should show post list
- [ ] Click "Create new post" - should show form
- [ ] Create new post with title, summary, content, tags
- [ ] Verify slug is auto-generated
- [ ] Verify post appears in list
- [ ] Edit the post - should show pre-filled form
- [ ] Update post - should save changes
- [ ] Delete post - should remove from list
- [ ] Test authorization:
  - [ ] Login as `john_user` - should NOT see admin menu
  - [ ] Direct access to `/en/admin/post/` - should be denied (403)
- [ ] Test voter: Edit another admin's post - should be allowed
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/Controller/Admin/`

## Success Criteria

✅ Admin post CRUD operations work
✅ Authorization enforced (ROLE_ADMIN required)
✅ PostVoter applied for edit/delete
✅ CSRF protection on delete
✅ Flash messages displayed
✅ Forms render and validate correctly
✅ Navigation updated with admin menu
✅ All templates styled with Bootstrap

## Next Phase

[Phase 8: Comment System](./08-comment-system.md)