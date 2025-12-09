# Symfony Templates & Twig Best Practices

This standard defines best practices for working with Twig templates in Symfony applications. Templates are responsible for presentation logic only and should remain simple and readable. Following consistent naming conventions and template organization patterns makes your application easier to maintain and helps developers quickly locate and understand template files.

## Rules

* Use snake_case for template file names and variables to maintain consistency
* Define sidebar block in base template allowing child templates to override or extend with parent()
* Implement fixed navbar with user authentication state (login/logout) and conditional admin menu access
* Extract flash messages into default/_flash_messages.html.twig partial included in base template
* Create admin/layout.html.twig that extends base.html.twig for admin-specific pages with dedicated blocks
* Use /_error/{statusCode} preview controller in development to test custom error pages
* Provide contextual navigation options in error pages based on HTTP status and user authentication state
* Override sidebar block in error pages to hide sidebar and maintain clean error page layout
* Create custom error pages in templates/bundles/TwigBundle/Exception/ extending base layout for consistent branding
* Use the path() function to generate URLs from route names instead of hardcoding URLs
* Apply Twig filters for content transformation and sanitization before output
* Use render() function to embed controller actions within templates for reusable components
* Use parent() function to include parent template block content when extending functionality
* Use the trans filter or function with translation keys instead of hardcoded text
* Use template inheritance with extends to create a consistent layout structure across pages
* Prefix partial template fragments with an underscore to distinguish them from complete page templates
