# Phase 10: Testing Suite

## Overview

**Goal**: Implement comprehensive test coverage with functional tests, command tests, and fixtures
**Complexity**: High
**Dependencies**: All previous phases (1-9)
**Estimated Files**: 10-15 test files

## Memory Files Required

- **Primary**: [Testing Strategy](../memory/testing-strategy.md)
- **Reference**: All memory files (for understanding features to test)

## Packmind Standards Applied

- Symfony Testing Best Practices

## Implementation Checklist

### 1. Test Configuration

- [ ] Verify `phpunit.xml.dist` exists and is configured
  - [ ] Bootstrap: `tests/bootstrap.php`
  - [ ] Test suite: `<directory>tests</directory>`
  - [ ] DAMA Doctrine Test Bundle extension enabled
  - [ ] Environment variables set for test environment

- [ ] Verify `.env.test` exists
  - [ ] `APP_ENV=test`
  - [ ] `DATABASE_URL="sqlite:///:memory:"`
  - [ ] `SYMFONY_DEPRECATIONS_HELPER=disabled`

- [ ] Create test database schema
  - [ ] Command: `php bin/console doctrine:schema:create --env=test`

### 2. Functional Tests - Blog

- [ ] Create `tests/Controller/BlogControllerTest.php`
  - [ ] Extend `WebTestCase`
  - [ ] Use `loginUser()` for authentication

- [ ] **Test blog index**
  - [ ] `testIndex()` - GET `/en/blog/`
  - [ ] Assert response is successful
  - [ ] Assert paginated posts displayed
  - [ ] Assert pagination controls present

- [ ] **Test post show**
  - [ ] `testPostShow()` - GET `/en/blog/posts/{slug}`
  - [ ] Use Crawler to select elements
  - [ ] Assert post title, content, author displayed
  - [ ] Assert comments displayed

- [ ] **Test tag filtering**
  - [ ] `testTagFilter()` - GET `/en/blog/?tag=lorem`
  - [ ] Assert only posts with tag displayed
  - [ ] Assert tag name shown in filter

- [ ] **Test search**
  - [ ] `testSearch()` - GET `/en/blog/search?q=keyword`
  - [ ] Assert search results displayed
  - [ ] Assert result count shown

- [ ] **Test RSS feed**
  - [ ] `testRssFeed()` - GET `/en/blog/rss.xml`
  - [ ] Assert response content type: `application/rss+xml`
  - [ ] Assert valid XML structure
  - [ ] Assert posts present in feed

### 3. Functional Tests - Admin

- [ ] Create `tests/Controller/Admin/PostControllerTest.php`
  - [ ] Extend `WebTestCase`

- [ ] **Test admin access control**
  - [ ] `testAdminIndexRequiresAuthentication()` - anonymous → 302 redirect
  - [ ] `testAdminIndexRequiresAdminRole()` - ROLE_USER → 403 forbidden
  - [ ] `testAdminIndexAllowsAdmin()` - ROLE_ADMIN → 200 success

- [ ] **Test post creation**
  - [ ] `testAdminCanCreatePost()`
  - [ ] Login as admin
  - [ ] GET `/en/admin/post/new`
  - [ ] Submit form with title, summary, content, tags
  - [ ] Follow redirect
  - [ ] Assert post created in database
  - [ ] Assert flash message displayed

- [ ] **Test post editing**
  - [ ] `testAdminCanEditOwnPost()`
  - [ ] Login as admin, create post
  - [ ] GET `/en/admin/post/{id}/edit`
  - [ ] Submit form with updated data
  - [ ] Assert post updated in database

- [ ] **Test post deletion**
  - [ ] `testAdminCanDeleteOwnPost()`
  - [ ] Login as admin, create post
  - [ ] POST `/en/admin/post/{id}/delete` with CSRF token
  - [ ] Assert post removed from database
  - [ ] Assert flash message

- [ ] **Test voter authorization**
  - [ ] `testAdminCannotEditOtherAdminPost()` - verify voter blocks (or allows based on logic)

### 4. Functional Tests - Security

- [ ] Create `tests/Controller/SecurityControllerTest.php`
  - [ ] Extend `WebTestCase`

- [ ] **Test login page**
  - [ ] `testLoginPage()` - GET `/en/login`
  - [ ] Assert login form displayed
  - [ ] Assert username and password fields present

- [ ] **Test successful login**
  - [ ] `testLoginWithValidCredentials()`
  - [ ] Submit login form with `jane_admin` / `kitten`
  - [ ] Follow redirect
  - [ ] Assert user is authenticated
  - [ ] Assert redirected to homepage

- [ ] **Test failed login**
  - [ ] `testLoginWithInvalidCredentials()`
  - [ ] Submit login form with wrong password
  - [ ] Assert error message displayed
  - [ ] Assert user is NOT authenticated

- [ ] **Test logout**
  - [ ] `testLogout()`
  - [ ] Login, then GET `/en/logout`
  - [ ] Assert user is logged out

### 5. Functional Tests - User Profile

- [ ] Create `tests/Controller/UserControllerTest.php`
  - [ ] Extend `WebTestCase`

- [ ] **Test profile edit**
  - [ ] `testUserCanEditProfile()`
  - [ ] Login as `john_user`
  - [ ] GET `/en/profile/edit`
  - [ ] Submit form with updated fullName
  - [ ] Assert profile updated in database

- [ ] **Test password change**
  - [ ] `testUserCanChangePassword()`
  - [ ] Login, change password
  - [ ] Logout, login with new password
  - [ ] Assert login successful

### 6. Functional Tests - Comments

- [ ] Create `tests/Controller/CommentControllerTest.php`
  - [ ] Extend `WebTestCase`

- [ ] **Test comment submission**
  - [ ] `testAuthenticatedUserCanComment()`
  - [ ] Login, visit post
  - [ ] Submit comment form
  - [ ] Assert comment created
  - [ ] Assert comment displayed on page

- [ ] **Test spam detection**
  - [ ] `testSpamCommentRejected()`
  - [ ] Submit comment with '@' symbol
  - [ ] Assert comment rejected
  - [ ] Assert error message displayed

- [ ] **Test anonymous cannot comment**
  - [ ] `testAnonymousCannotComment()`
  - [ ] Visit post as anonymous
  - [ ] Assert comment form NOT displayed

### 7. Command Tests

- [ ] Create `tests/Command/AddUserCommandTest.php`
  - [ ] Extend `TestCase`
  - [ ] Use `CommandTester`

- [ ] **Test add user command**
  - [ ] `testAddUserCommand()`
  - [ ] Execute: `app:add-user testuser password test@example.com`
  - [ ] Assert command succeeds
  - [ ] Assert user created in database
  - [ ] Assert password hashed

- [ ] **Test add admin user**
  - [ ] `testAddAdminUser()`
  - [ ] Execute with `--admin` option
  - [ ] Assert user has ROLE_ADMIN

- [ ] Create `tests/Command/ListUsersCommandTest.php`
  - [ ] `testListUsersCommand()`
  - [ ] Create fixtures users
  - [ ] Execute: `app:list-users`
  - [ ] Assert output contains usernames

### 8. Smoke Tests

- [ ] Create `tests/ApplicationAvailabilityTest.php`
  - [ ] Extend `WebTestCase`
  - [ ] Use data provider for URL list

- [ ] **Test all public URLs**
  - [ ] Data provider: all public routes
  - [ ] Test each URL returns 200 or valid redirect
  - [ ] URLs to test:
    - [ ] `/en/` (homepage)
    - [ ] `/en/blog/`
    - [ ] `/en/blog/posts/{slug}`
    - [ ] `/en/blog/search`
    - [ ] `/en/blog/rss.xml`
    - [ ] `/en/login`

- [ ] **Test authenticated URLs**
  - [ ] Data provider: authenticated routes
  - [ ] Login before testing
  - [ ] `/en/profile/edit`
  - [ ] `/en/profile/change-password`

- [ ] **Test admin URLs**
  - [ ] Data provider: admin routes
  - [ ] Login as admin
  - [ ] `/en/admin/post/`
  - [ ] `/en/admin/post/new`

### 9. Test Fixtures

- [ ] Verify `src/DataFixtures/AppFixtures.php` works in test environment
  - [ ] Load fixtures: `php bin/console doctrine:fixtures:load --env=test`
  - [ ] Should create 3 users, 30 posts, 150 comments, 9 tags

- [ ] Tests can use fixtures or create their own data
  - [ ] Use `EntityManager` to persist test data
  - [ ] DAMA bundle wraps each test in transaction (auto-rollback)

### 10. Test Helpers

- [ ] Create test helper methods (optional)
  - [ ] `createAuthenticatedClient(User $user): KernelBrowser`
  - [ ] `createPost(User $author, array $data): Post`
  - [ ] `createComment(Post $post, User $author, string $content): Comment`

### 11. Coverage & Quality

- [ ] Run all tests
  - [ ] Command: `php bin/phpunit`
  - [ ] All tests should pass

- [ ] Generate code coverage (optional)
  - [ ] Command: `XDEBUG_MODE=coverage php bin/phpunit --coverage-html coverage/`
  - [ ] Review coverage report
  - [ ] Aim for >80% coverage on controllers

- [ ] Run PHPStan on tests
  - [ ] Command: `vendor/bin/phpstan analyse tests/`
  - [ ] Should pass level 8

### 12. Verification

- [ ] Run full test suite: `php bin/phpunit`
- [ ] All tests pass
- [ ] No deprecation warnings
- [ ] Test database transactions work (DAMA bundle)
- [ ] Verify test isolation (tests can run in any order)
- [ ] Run specific test: `php bin/phpunit tests/Controller/BlogControllerTest.php`
- [ ] Run tests with verbose output: `php bin/phpunit --testdox`

## Success Criteria

✅ All functional tests pass
✅ All command tests pass
✅ Smoke tests verify all URLs work
✅ Test coverage >80% on controllers
✅ DAMA Doctrine Test Bundle working (transaction rollback)
✅ Tests are isolated and can run in any order
✅ PHPStan passes on tests
✅ Test environment configured correctly

## Application Complete

**Congratulations!** All 10 phases are complete. The Symfony Demo Application has been fully rebuilt with:

- ✅ Complete domain model with entities and relationships
- ✅ Security system with authentication and authorization
- ✅ Blog features (listing, viewing, search, RSS)
- ✅ Admin panel for post management
- ✅ Comment system with spam detection
- ✅ User profile management
- ✅ Comprehensive test coverage
- ✅ All Packmind standards followed

## Final Verification Checklist

- [ ] All fixtures load successfully
- [ ] Application runs: `symfony server:start`
- [ ] Visit homepage: `https://localhost:8000`
- [ ] Browse blog posts
- [ ] Login as admin, create/edit/delete posts
- [ ] Add comments
- [ ] All tests pass: `php bin/phpunit`
- [ ] PHPStan passes: `vendor/bin/phpstan analyse`
- [ ] Assets built: `php bin/console sass:build`

The application is production-ready! 🎉