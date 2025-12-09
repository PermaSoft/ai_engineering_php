# Symfony Forms Best Practices

This standard defines best practices for working with Symfony forms. Forms should be defined as reusable PHP classes that can be used across multiple controllers and contexts. Validation should be defined on the underlying entity rather than the form itself. Following these practices leads to maintainable, reusable form code that separates concerns properly between validation logic and form presentation.

## Rules

* Handle both rendering and processing of forms in a single controller action to keep related logic together
* Use translation keys for form labels and help text instead of hardcoded strings
* Use form events to dynamically modify form data or fields during the form lifecycle
* Configure the data_class option to bind forms to entity classes
* Add form buttons in templates rather than in form type classes, as buttons may vary by context
* Define validation constraints on the entity class, not on form fields, to ensure validation is reusable
* Set maximum password length constraint (128 characters) to prevent performance issues
* Define forms as PHP classes extending AbstractType for reusability and testability
* Inject services into form types via constructor dependency injection when needed
* Add mapped: false to form fields that don't directly map to entity properties
