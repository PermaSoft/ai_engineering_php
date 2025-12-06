# Symfony Entities & Doctrine Best Practices

This standard covers best practices for defining Doctrine entities in Symfony applications. Entities represent your domain model and database structure. Using PHP attributes for mapping and validation keeps entity configuration co-located with the code, making it easier to understand and maintain. These practices ensure clean, well-structured domain models that are both developer-friendly and performant.

## Rules

* Use PHP attributes to define Doctrine entity mapping instead of XML or YAML configuration
* Define validation constraints on entity properties using Symfony validation attributes
* Use typed properties with nullable types and initialize collections in the constructor
* Use UniqueEntity constraint at the class level to ensure database-level uniqueness with custom error messages
* Use OrderBy attribute on collections to define default sorting for relationship queries
* Use void return type for entity setters and proper Collection types for relationship getters
* Use cascade operations and orphanRemoval on relationships to manage related entities lifecycle
