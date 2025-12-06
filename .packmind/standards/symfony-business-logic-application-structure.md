# Symfony Business Logic & Application Structure

This standard covers best practices for organizing business logic and application structure in Symfony. It emphasizes keeping controllers thin, using services for business logic, avoiding bundles for application code, and properly structuring your application using PHP namespaces. Following these practices results in a clean, maintainable codebase where concerns are properly separated and code is reusable.

## Rules

* Do not create bundles to organize application logic; use PHP namespaces instead
* Follow the standard Symfony directory structure with flat, self-explanatory organization
* Create dedicated service classes for complex business logic instead of placing it in controllers
* Use event subscribers for cross-cutting concerns and decoupling application components
* Use constants in entity classes for configuration values that rarely change
* Make services readonly and use constructor property promotion for dependency injection
* Use Doctrine repositories for database queries and keep query logic out of controllers
