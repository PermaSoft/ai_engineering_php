# Symfony Templates & Twig Best Practices

This standard defines best practices for working with Twig templates in Symfony applications. Templates are responsible for presentation logic only and should remain simple and readable. Following consistent naming conventions and template organization patterns makes your application easier to maintain and helps developers quickly locate and understand template files.

## Rules

* Use snake_case for template file names and variables to maintain consistency
* Prefix partial template fragments with an underscore to distinguish them from complete page templates
* Use template inheritance with extends to create a consistent layout structure across pages
* Use the trans filter or function with translation keys instead of hardcoded text
* Use parent() function to include parent template block content when extending functionality
* Use render() function to embed controller actions within templates for reusable components
* Apply Twig filters for content transformation and sanitization before output
* Use the path() function to generate URLs from route names instead of hardcoding URLs
