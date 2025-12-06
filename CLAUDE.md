# Application Architecture & Rebuild Documentation

This project is the **Symfony Demo Application** - the official reference implementation demonstrating Symfony best practices. Complete documentation for understanding and rebuilding this application from scratch is available in the memory files.

## Master Index

For comprehensive documentation, start with the **[Symfony Demo App Master Index](./.claude/memory/symfony-demo-app-index.md)**

This index provides:
- High-level application overview
- Quick reference for all features
- Links to detailed documentation for each component
- Rebuild workflow and step-by-step instructions

## Detailed Documentation (Partial Disclosure Pattern)

Documentation is organized following the partial disclosure pattern - the master index provides overview and context, with links to detailed specifications:

- **[Domain Model](./.claude/memory/domain-model.md)** - Complete entity specifications, relationships, and validation rules
- **[Security Architecture](./.claude/memory/security-architecture.md)** - Authentication, authorization, voters, and security patterns
- **[Controllers & Routing](./.claude/memory/controllers-routing.md)** - Request handling, routing patterns, and controller implementation
- **[Forms & Validation](./.claude/memory/forms-validation.md)** - Form types, custom fields, data transformers, and validation
- **[Services & Repositories](./.claude/memory/services-repositories.md)** - Business logic, data access, events, and service patterns
- **[Templates & Frontend](./.claude/memory/templates-frontend.md)** - Twig templates, asset management, and frontend structure
- **[Testing Strategy](./.claude/memory/testing-strategy.md)** - Test organization, patterns, and coverage
- **[Configuration & Setup](./.claude/memory/configuration-setup.md)** - Dependencies, environment setup, and deployment

## Usage

When working on this project
1. Consult the **Packmind Standards** above for coding rules and best practices
2. Reference the **Master Index** for application architecture and feature overview
3. Dive into **detailed documentation** for specific component implementation details

This documentation enables full reconstruction of the application from scratch while maintaining consistency with Symfony best practices.

---

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
* Define validation constraints on entity properties using Symfony validation attributes
* Use cascade operations and orphanRemoval on relationships to manage related entities lifecycle
* Use OrderBy attribute on collections to define default sorting for relationship queries
* Use PHP attributes to define Doctrine entity mapping instead of XML or YAML configuration
* Use typed properties with nullable types and initialize collections in the constructor
* Use UniqueEntity constraint at the class level to ensure database-level uniqueness with custom error messages
* Use void return type for entity setters and proper Collection types for relationship getters

Full standard is available here for further request: [Symfony Entities & Doctrine Best Practices](.packmind/standards/symfony-entities-doctrine-best-practices.md)

## Standard: Symfony Security Best Practices

Guidelines for implementing security features in Symfony applications, including authentication, authorization, password hashing, and access control. :
* Define a single main firewall unless you have legitimately different authentication systems
* Define permission constants in Voter classes to avoid magic strings throughout the application
* Enable CSRF protection on forms and logout to prevent cross-site request forgery attacks
* Use is_granted() in templates to conditionally show UI elements based on permissions
* Use IsGranted attribute on controller methods to enforce authorization checks declaratively
* Use the auto password hasher to automatically select the best password hashing algorithm
* Use Voters to implement complex authorization logic instead of embedding it in controllers

Full standard is available here for further request: [Symfony Security Best Practices](.packmind/standards/symfony-security-best-practices.md)

## Standard: Symfony Forms Best Practices

Guidelines for creating and handling forms in Symfony applications, including form types, validation, rendering, and processing. :
* Add form buttons in templates rather than in form type classes, as buttons may vary by context
* Configure the data_class option to bind forms to entity classes
* Define forms as PHP classes extending AbstractType for reusability and testability
* Define validation constraints on the entity class, not on form fields, to ensure validation is reusable
* Handle both rendering and processing of forms in a single controller action to keep related logic together
* Inject services into form types via constructor dependency injection when needed
* Use form events to dynamically modify form data or fields during the form lifecycle
* Use translation keys for form labels and help text instead of hardcoded strings

Full standard is available here for further request: [Symfony Forms Best Practices](.packmind/standards/symfony-forms-best-practices.md)

## Standard: Symfony Templates & Twig Best Practices

Guidelines for creating and organizing Twig templates in Symfony applications, including naming conventions, template inheritance, and best practices for template logic. :
* Apply Twig filters for content transformation and sanitization before output
* Prefix partial template fragments with an underscore to distinguish them from complete page templates
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
<!-- end: Packmind standards -->

---

