# Symfony Repository Query Patterns

This standard covers best practices for implementing Doctrine repository queries in Symfony applications. Repositories encapsulate database access logic and should provide clean, reusable query methods. Following these practices ensures performant queries, proper eager loading to avoid N+1 problems, and consistent search functionality.

## Rules

* Use addSelect() with join aliases to eager load related entities and avoid N+1 query problems
* Extract and normalize search terms before building LIKE queries to handle whitespace and duplicates
* Use MEMBER OF for filtering entities by ManyToMany collection membership instead of manual joins
* Return domain-specific types from repository methods (Paginator, typed arrays) instead of raw Doctrine results
* Add setMaxResults() limit to search and listing queries to prevent unbounded result sets
