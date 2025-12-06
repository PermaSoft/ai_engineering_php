# Symfony Controllers Best Practices

This standard defines best practices for creating and organizing controllers in Symfony applications. Controllers are the entry point for handling HTTP requests and should be kept thin, focusing on coordinating the request/response cycle. Following these practices ensures maintainable, testable, and consistent controller code across your Symfony application.

## Rules

* Extend AbstractController to access helper methods for rendering templates, redirects, and security checks
* Use PHP attributes to configure routing, caching, and security directly on controller methods
* Inject dependencies via constructor or method arguments instead of accessing services from the container
* Use EntityValueResolver to automatically fetch Doctrine entities from route parameters when appropriate
* Use MapEntity attribute when route parameter names don't match entity property names
* Mark controller classes as final to prevent inheritance and promote composition over inheritance
* Use CurrentUser attribute to inject the authenticated user directly into controller methods
