# Symfony Stimulus Controllers Best Practices

This standard covers best practices for creating Stimulus controllers in Symfony UX applications. Stimulus provides a lightweight JavaScript framework for adding interactivity to HTML. Following these practices ensures consistent, maintainable frontend code that integrates well with Symfony's asset management and Turbo navigation.

## Rules

* Use static targets and values arrays to declare controller dependencies for IDE support and documentation
* Name action methods with descriptive verbs matching the DOM event they handle (e.g., toggle, submit, dismiss)
* Clean up event listeners and intervals in disconnect() to prevent memory leaks during Turbo navigation
* Use data-action attribute syntax with event->controller#method format for binding actions in HTML
* Place controller files in assets/controllers/ with snake_case naming matching the data-controller attribute
