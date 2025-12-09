# Packmind Recipes Index

This directory contains references to all 19 Symfony recipes available in Packmind.

## Recipe Categories

### Core Framework (5 recipes)
| Recipe | Description |
|--------|-------------|
| `add-a-crud-controller-action-in-symfony` | Add create, read, update, or delete actions with routing, forms, and authorization |
| `add-a-new-page-with-template-in-symfony` | Create new pages with controller, Twig template, and navigation |
| `add-a-console-command-in-symfony` | Build CLI commands with arguments, options, and interactive prompts |
| `add-an-event-subscriber-in-symfony` | Handle domain events, kernel events, or form events with decoupling |
| `add-flash-messages-for-user-feedback` | Display success, error, warning notifications after actions |

### Data Layer (5 recipes)
| Recipe | Description |
|--------|-------------|
| `add-a-doctrine-entity-with-relationships` | Create entities with ORM mapping, validation, and relationships |
| `add-a-tag-system-to-entities` | Implement ManyToMany tagging with custom form field and filtering |
| `add-a-comment-system-to-an-entity` | Build comment functionality with authentication and notifications |
| `add-pagination-to-a-list-page` | Paginate entity lists with Doctrine Paginator |
| `add-data-fixtures-for-testing` | Create test data with hashed passwords and relationships |

### Forms (2 recipes)
| Recipe | Description |
|--------|-------------|
| `add-a-form-type-in-symfony` | Create reusable form types with data transformers and events |
| `add-a-custom-form-field-type` | Build specialized inputs like date pickers or tag selectors |

### Security (3 recipes)
| Recipe | Description |
|--------|-------------|
| `add-user-authentication-with-login-form` | Implement form login with remember-me and logout |
| `add-a-voter-for-authorization-in-symfony` | Create fine-grained authorization logic beyond role checks |
| `add-user-profile-management` | Enable profile editing and password change with security |

### Frontend (3 recipes)
| Recipe | Description |
|--------|-------------|
| `add-a-live-search-component-with-symfony-ux` | Real-time search with Symfony UX Live Components |
| `add-an-rss-feed-in-symfony` | Generate RSS/XML feeds for content syndication |
| `add-multi-language-support-i18n-in-symfony` | Implement internationalization with locale detection |

### Testing (1 recipe)
| Recipe | Description |
|--------|-------------|
| `add-a-functional-test-in-symfony` | Create functional tests for controllers and forms |

---

## Usage

Recipes are available in Packmind and can be accessed via:
1. **Packmind CLI**: Run `packmind-cli` commands
2. **MCP Integration**: Use `mcp__packmind__get_recipe_details` with recipe slug
3. **Web Interface**: Browse recipes at app.packmind.com

## Quick Reference

To get full recipe details programmatically:
```
mcp__packmind__get_recipe_details(recipeSlug: "add-a-crud-controller-action-in-symfony")
```

Each recipe includes:
- **When to Use**: Scenarios where the recipe applies
- **Context Validation Checkpoints**: Questions to clarify before implementation
- **Recipe Steps**: Detailed step-by-step implementation guide with code examples
