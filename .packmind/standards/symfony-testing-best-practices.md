# Symfony Testing Best Practices

This standard defines best practices for testing Symfony applications. Testing ensures your application works correctly and continues to work as you make changes. Symfony provides excellent testing tools through PHPUnit integration. Following these practices helps you write maintainable, fast, and reliable tests that provide confidence in your application's behavior.

## Rules

* Extend WebTestCase for functional tests that test controllers and HTTP interactions
* Use smoke tests with data providers to verify all application URLs load successfully
* Hard-code URLs in functional tests instead of generating them from routes to catch broken public URLs
* Use loginUser() method to authenticate users in tests instead of manually handling authentication
* Use followRedirects() to automatically follow redirects in test scenarios
* Use the Crawler component to select and assert on specific elements in HTML responses
* Use assertResponseHeaderSame() and similar assertions to verify response properties
