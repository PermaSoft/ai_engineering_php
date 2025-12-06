# Symfony Configuration Best Practices

This standard covers configuration best practices for Symfony applications. Proper configuration management distinguishes between infrastructure configuration (varies per environment), application configuration (consistent across environments), and sensitive data (requires encryption). Following these practices ensures your application is portable, secure, and easy to configure across different environments.

## Rules

* Use YAML format for service configuration as it is concise and beginner-friendly
* Enable autowiring and autoconfigure in service defaults to minimize manual service configuration
* Use environment variables for infrastructure configuration that varies between machines
* Define application parameters in config/services.yaml with the app. prefix for clarity
* Use the bind key to define scalar argument values once and apply them to all services
* Make services private by default to enforce proper dependency injection
* Use Symfony secrets management for sensitive configuration values like API keys
