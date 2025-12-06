# Phase 8: Comment System

## Overview

**Goal**: Implement comment creation, spam detection, and email notifications
**Complexity**: Low
**Dependencies**: Phase 2, 3, 4, 5
**Estimated Files**: 3-5 files

## Memory Files Required

- **Primary**: [Controllers & Routing](../memory/controllers-routing.md), [Services & Repositories](../memory/services-repositories.md)
- **Reference**: [Templates & Frontend](../memory/templates-frontend.md), [Domain Model](../memory/domain-model.md)

## Packmind Standards Applied

- Symfony Controllers Best Practices
- Symfony Business Logic & Application Structure

## Implementation Checklist

### 1. Comment Controller

- [ ] Update `src/Controller/BlogController.php`
  - [ ] Add comment form to `blog_post` action
  - [ ] Create CommentType form
  - [ ] Handle both GET (show post + form) and POST (submit comment)

- [ ] **Comment submission**
  - [ ] Create new Comment instance
  - [ ] Set post relationship: `$comment->setPost($post)`
  - [ ] Set author: inject `#[CurrentUser] User $user`
  - [ ] Validate form (spam detection runs automatically)
  - [ ] If valid:
    - [ ] Persist comment
    - [ ] Dispatch `CommentCreatedEvent`
    - [ ] Flash success: `comment.created_successfully`
    - [ ] Redirect to same post page
  - [ ] If spam detected:
    - [ ] Flash error: `comment.is_spam`
    - [ ] Re-render form with error

### 2. Comment Event

- [ ] Verify `src/Event/CommentCreatedEvent.php` exists
  - [ ] Property: `private readonly Comment $comment`
  - [ ] Constructor: `__construct(Comment $comment)`
  - [ ] Getter: `getComment(): Comment`

### 3. Comment Notification Subscriber

- [ ] Verify `src/EventSubscriber/CommentNotificationSubscriber.php` exists
  - [ ] Implements `EventSubscriberInterface`
  - [ ] Subscribe to `CommentCreatedEvent`
  - [ ] Inject `MailerInterface`
  - [ ] Inject `@app.notifications.email_sender` parameter

- [ ] **Send notification email**
  - [ ] Get comment from event
  - [ ] Get post author email
  - [ ] Create Email instance:
    - [ ] From: `app.notifications.email_sender`
    - [ ] To: post author email
    - [ ] Subject: "New comment on your post"
    - [ ] Body: comment content + link to post
  - [ ] Send using `$mailer->send($email)`

### 4. Templates - Comment Display

- [ ] Update `templates/blog/post_show.html.twig`
  - [ ] **Comments section**
    - [ ] Display count: `{{ post.comments|length }} comments`
    - [ ] Loop through comments: `{% for comment in post.comments %}`
    - [ ] Use partial: `{{ include('blog/_comment.html.twig', {comment: comment}) }}`

- [ ] **Comment form section** (only if authenticated)
  - [ ] Check: `{% if is_granted('ROLE_USER') %}`
  - [ ] Render CommentType form
  - [ ] Submit button: "Add comment"
  - [ ] Else: show "Login to comment" message

- [ ] Verify `templates/blog/_comment.html.twig` exists
  - [ ] Display comment content
  - [ ] Display author name
  - [ ] Display published date
  - [ ] Format: Bootstrap card/list item

### 5. Spam Detection Verification

- [ ] Spam detection is in Comment entity: `isLegitComment()`
  - [ ] Validation: `#[Assert\IsTrue(message: 'comment.is_spam')]`
  - [ ] Logic: rejects content containing '@'
  - [ ] Already implemented in Phase 2

### 6. Fixtures - Add Comments

- [ ] Update `src/DataFixtures/AppFixtures.php`
  - [ ] After creating posts, add 5 comments per post
  - [ ] Comment author: `john_user`
  - [ ] Comment content: random Lorem Ipsum (no '@' symbol)
  - [ ] Published dates: random within last 30 days
  - [ ] Total: 150 comments (30 posts × 5 comments)

### 7. Translation Keys

- [ ] Add to `translations/messages.en.yaml`
  - [ ] `comment.created_successfully: "Your comment has been posted!"`
  - [ ] `comment.is_spam: "Your comment appears to be spam."`
  - [ ] `comment.blank: "Comment cannot be blank."`
  - [ ] `comment.too_short: "Comment is too short (minimum 5 characters)."`
  - [ ] `comment.too_long: "Comment is too long (maximum 10,000 characters)."`

### 8. Email Configuration (Development)

- [ ] Update `.env` if needed
  - [ ] `MAILER_DSN=null://null` (for dev - emails logged, not sent)
  - [ ] Alternative: `MAILER_DSN=smtp://localhost:1025` (use MailHog/MailCatcher)

- [ ] Verify `app.notifications.email_sender` parameter in `config/services.yaml`
  - [ ] Should be: `anonymous@example.com`

### 9. Verification

- [ ] Load fixtures: `php bin/console doctrine:fixtures:load`
- [ ] Visit a blog post - should show 5 comments
- [ ] Login as `john_user` / `kitten`
- [ ] Add valid comment (without '@') - should succeed
- [ ] Verify comment appears in list
- [ ] Try adding spam comment (with '@') - should fail with error
- [ ] Verify flash messages work
- [ ] Check email sent (if using MailHog, check inbox)
- [ ] Test as anonymous user - should NOT see comment form
- [ ] Run PHPStan: `vendor/bin/phpstan analyse src/EventSubscriber/`

## Success Criteria

✅ Comment form displayed on post page (authenticated users only)
✅ Comment submission works
✅ Spam detection prevents '@' in comments
✅ Email notification sent to post author
✅ Comments displayed on post page
✅ Fixtures create 150 comments
✅ Flash messages work
✅ Translation keys defined

## Next Phase

[Phase 9: User Profile](./09-user-profile.md)