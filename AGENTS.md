<!-- start: Packmind standards -->
# Packmind Standards

Before starting your work, make sure to review the coding standards relevant to your current task.

Always consult the sections that apply to the technology, framework, or type of contribution you are working on.

All rules and guidelines defined in these standards are mandatory and must be followed consistently.

Failure to follow these standards may lead to inconsistencies, errors, or rework. Treat them as the source of truth for how code should be written, structured, and maintained.

## Standard: Symfony Business Logic & Application Structure

Guidelines for organizing business logic, services, and application structure in Symfony applications following best practices for maintainability and reusability. :
* Create dedicated service classes for complex business logic instead of placing it in controllers
* Do not create bundles to organize application logic; use PHP namespaces instead
* Follow the standard Symfony directory structure with flat, self-explanatory organization
* Make services readonly and use constructor property promotion for dependency injection
* Use constants in entity classes for configuration values that rarely change
* Use Doctrine repositories for database queries and keep query logic out of controllers
* Use event subscribers for cross-cutting concerns and decoupling application components

Full standard is available here for further request: [Symfony Business Logic & Application Structure](.packmind/standards/symfony-business-logic-application-structure.md)

## Standard: Symfony Configuration Best Practices

Guidelines for configuring Symfony applications, including service configuration, parameters, environment variables, and secrets management. :
* Define application parameters in config/services.yaml with the app. prefix for clarity
* Enable autowiring and autoconfigure in service defaults to minimize manual service configuration
* Make services private by default to enforce proper dependency injection
* Use environment variables for infrastructure configuration that varies between machines
* Use Symfony secrets management for sensitive configuration values like API keys
* Use the bind key to define scalar argument values once and apply them to all services
* Use YAML format for service configuration as it is concise and beginner-friendly

Full standard is available here for further request: [Symfony Configuration Best Practices](.packmind/standards/symfony-configuration-best-practices.md)

## Standard: Symfony Controllers Best Practices

Guidelines for implementing controllers in Symfony applications following official best practices for routing, dependency injection, and request handling. :
* Extend AbstractController to access helper methods for rendering templates, redirects, and security checks
* Inject dependencies via constructor or method arguments instead of accessing services from the container
* Mark controller classes as final to prevent inheritance and promote composition over inheritance
* Use CurrentUser attribute to inject the authenticated user directly into controller methods
* Use EntityValueResolver to automatically fetch Doctrine entities from route parameters when appropriate
* Use MapEntity attribute when route parameter names don't match entity property names
* Use PHP attributes to configure routing, caching, and security directly on controller methods

Full standard is available here for further request: [Symfony Controllers Best Practices](.packmind/standards/symfony-controllers-best-practices.md)

## Standard: Symfony Entities & Doctrine Best Practices

Guidelines for creating Doctrine entities in Symfony applications, including mapping configuration, validation constraints, and entity design patterns. :
* Add null-safe cast in UserInterface::getUserIdentifier() to prevent TypeError when username is null
* Define validation constraints on entity properties using Symfony validation attributes
* Use cascade operations and orphanRemoval on relationships to manage related entities lifecycle
* Use Doctrine\DBAL\Types\Types constants for column type definitions instead of string literals
* Use OrderBy attribute on collections to define default sorting for relationship queries
* Use PHP attributes to define Doctrine entity mapping instead of XML or YAML configuration
* Use typed properties with nullable types and initialize collections in the constructor
* Use UniqueEntity constraint at the class level to ensure database-level uniqueness with custom error messages
* Use void return type for entity setters and proper Collection types for relationship getters

Full standard is available here for further request: [Symfony Entities & Doctrine Best Practices](.packmind/standards/symfony-entities-doctrine-best-practices.md)

## Standard: Symfony Security Best Practices

Guidelines for implementing security features in Symfony applications, including authentication, authorization, password hashing, and access control. :
* Add autocomplete attributes to password fields (current-password, new-password) for proper browser handling
* Define a single main firewall unless you have legitimately different authentication systems
* Define permission constants in Voter classes to avoid magic strings throughout the application
* Disable always_remember_me and require explicit user opt-in via checkbox for persistent sessions
* Enable CSRF protection on forms and logout to prevent cross-site request forgery attacks
* Logout users after password change using Security::logout() to invalidate existing sessions
* Use config/packages/dev/security.yaml for dev-only security features like switch_user
* Use is_granted() in templates to conditionally show UI elements based on permissions
* Use IsGranted attribute on controller methods to enforce authorization checks declaratively
* Use the auto password hasher to automatically select the best password hashing algorithm
* Use Voters to implement complex authorization logic instead of embedding it in controllers

Full standard is available here for further request: [Symfony Security Best Practices](.packmind/standards/symfony-security-best-practices.md)

## Standard: Symfony Forms Best Practices

Guidelines for creating and handling forms in Symfony applications, including form types, validation, rendering, and processing. :
* Add form buttons in templates rather than in form type classes, as buttons may vary by context
* Add mapped: false to form fields that don't directly map to entity properties
* Configure the data_class option to bind forms to entity classes
* Define forms as PHP classes extending AbstractType for reusability and testability
* Define validation constraints on the entity class, not on form fields, to ensure validation is reusable
* Handle both rendering and processing of forms in a single controller action to keep related logic together
* Inject services into form types via constructor dependency injection when needed
* Set maximum password length constraint (128 characters) to prevent performance issues
* Use form events to dynamically modify form data or fields during the form lifecycle
* Use translation keys for form labels and help text instead of hardcoded strings

Full standard is available here for further request: [Symfony Forms Best Practices](.packmind/standards/symfony-forms-best-practices.md)

## Standard: Symfony Templates & Twig Best Practices

Guidelines for creating and organizing Twig templates in Symfony applications, including naming conventions, template inheritance, and best practices for template logic. :
* Apply Twig filters for content transformation and sanitization before output
* Create admin/layout.html.twig that extends base.html.twig for admin-specific pages with dedicated blocks
* Create custom error pages in templates/bundles/TwigBundle/Exception/ extending base layout for consistent branding
* Define sidebar block in base template allowing child templates to override or extend with parent()
* Extract flash messages into default/_flash_messages.html.twig partial included in base template
* Implement fixed navbar with user authentication state (login/logout) and conditional admin menu access
* Override sidebar block in error pages to hide sidebar and maintain clean error page layout
* Prefix partial template fragments with an underscore to distinguish them from complete page templates
* Provide contextual navigation options in error pages based on HTTP status and user authentication state
* Use /_error/{statusCode} preview controller in development to test custom error pages
* Use parent() function to include parent template block content when extending functionality
* Use render() function to embed controller actions within templates for reusable components
* Use snake_case for template file names and variables to maintain consistency
* Use template inheritance with extends to create a consistent layout structure across pages
* Use the path() function to generate URLs from route names instead of hardcoding URLs
* Use the trans filter or function with translation keys instead of hardcoded text

Full standard is available here for further request: [Symfony Templates & Twig Best Practices](.packmind/standards/symfony-templates-twig-best-practices.md)

## Standard: Symfony Testing Best Practices

Guidelines for writing effective tests in Symfony applications, including functional tests, smoke tests, and testing best practices. :
* Extend WebTestCase for functional tests that test controllers and HTTP interactions
* Hard-code URLs in functional tests instead of generating them from routes to catch broken public URLs
* Use assertResponseHeaderSame() and similar assertions to verify response properties
* Use followRedirects() to automatically follow redirects in test scenarios
* Use loginUser() method to authenticate users in tests instead of manually handling authentication
* Use smoke tests with data providers to verify all application URLs load successfully
* Use the Crawler component to select and assert on specific elements in HTML responses

Full standard is available here for further request: [Symfony Testing Best Practices](.packmind/standards/symfony-testing-best-practices.md)

## Standard: Symfony Stimulus Controllers Best Practices

Guidelines for creating Stimulus controllers in Symfony applications for frontend interactivity with proper target handling, action binding, and Turbo integration. :
* Clean up event listeners and intervals in disconnect() to prevent memory leaks during Turbo navigation
* Name action methods with descriptive verbs matching the DOM event they handle (e.g., toggle, submit, dismiss)
* Place controller files in assets/controllers/ with snake_case naming matching the data-controller attribute
* Use data-action attribute syntax with event->controller#method format for binding actions in HTML
* Use static targets and values arrays to declare controller dependencies for IDE support and documentation

Full standard is available here for further request: [Symfony Stimulus Controllers Best Practices](.packmind/standards/symfony-stimulus-controllers-best-practices.md)

## Standard: Symfony Repository Query Patterns

Guidelines for writing efficient Doctrine repository queries in Symfony with proper eager loading, search optimization, and pagination support. :
* Add setMaxResults() limit to search and listing queries to prevent unbounded result sets
* Extract and normalize search terms before building LIKE queries to handle whitespace and duplicates
* Return domain-specific types from repository methods (Paginator, typed arrays) instead of raw Doctrine results
* Use addSelect() with join aliases to eager load related entities and avoid N+1 query problems
* Use MEMBER OF for filtering entities by ManyToMany collection membership instead of manual joins

Full standard is available here for further request: [Symfony Repository Query Patterns](.packmind/standards/symfony-repository-query-patterns.md)

## Standard: Symfony String Component & Slug Generation

Guidelines for using Symfony String component utilities for Unicode-safe string manipulation, slug generation, and text processing. :
* Chain String component methods fluently for complex text transformations
* Inject SluggerInterface to generate URL-safe slugs instead of manual string replacement
* Use form events with SluggerInterface to auto-generate slugs from titles on form submission
* Use ignoreCase() for case-insensitive string comparisons to handle Unicode properly
* Use the u() function from Symfony String component for Unicode-safe string operations instead of PHP string functions

Full standard is available here for further request: [Symfony String Component & Slug Generation](.packmind/standards/symfony-string-component-slug-generation.md)
<!-- end: Packmind standards -->