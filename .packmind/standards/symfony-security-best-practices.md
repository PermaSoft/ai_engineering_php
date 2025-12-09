# Symfony Security Best Practices

This standard covers security best practices for Symfony applications. Security encompasses authentication (who you are), authorization (what you can do), password management, and protecting against common vulnerabilities. Following these practices ensures your application implements security correctly, using Symfony's security component effectively while avoiding common pitfalls and security vulnerabilities.

## Rules

* Use is_granted() in templates to conditionally show UI elements based on permissions
* Use IsGranted attribute on controller methods to enforce authorization checks declaratively
* Use Voters to implement complex authorization logic instead of embedding it in controllers
* Define a single main firewall unless you have legitimately different authentication systems
* Use the auto password hasher to automatically select the best password hashing algorithm
* Use config/packages/dev/security.yaml for dev-only security features like switch_user
* Disable always_remember_me and require explicit user opt-in via checkbox for persistent sessions
* Add autocomplete attributes to password fields (current-password, new-password) for proper browser handling
* Logout users after password change using Security::logout() to invalidate existing sessions
* Enable CSRF protection on forms and logout to prevent cross-site request forgery attacks
* Define permission constants in Voter classes to avoid magic strings throughout the application
