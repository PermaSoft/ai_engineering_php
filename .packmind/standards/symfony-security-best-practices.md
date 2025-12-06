# Symfony Security Best Practices

This standard covers security best practices for Symfony applications. Security encompasses authentication (who you are), authorization (what you can do), password management, and protecting against common vulnerabilities. Following these practices ensures your application implements security correctly, using Symfony's security component effectively while avoiding common pitfalls and security vulnerabilities.

## Rules

* Use the auto password hasher to automatically select the best password hashing algorithm
* Define a single main firewall unless you have legitimately different authentication systems
* Use Voters to implement complex authorization logic instead of embedding it in controllers
* Use IsGranted attribute on controller methods to enforce authorization checks declaratively
* Define permission constants in Voter classes to avoid magic strings throughout the application
* Use is_granted() in templates to conditionally show UI elements based on permissions
* Enable CSRF protection on forms and logout to prevent cross-site request forgery attacks
