# Symfony Twig Components Best Practices

**Slug:** symfony-twig-components-best-practices

## Description

This standard covers best practices for creating Twig components in Symfony applications using Symfony UX. Twig components encapsulate template logic and markup into reusable classes, while Live Components add real-time interactivity without writing JavaScript. Following these practices ensures consistent, maintainable, and performant UI components.

## Rules

* Use #[AsTwigComponent] attribute to register a class as a Twig component with a descriptive name
* Use #[AsLiveComponent] with #[LiveProp] for stateful, interactive components that update without page reload
* Include DefaultActionTrait in Live Components for proper default action handling
* Place component templates in templates/components/ directory with matching component name
* Mark writable: true only on #[LiveProp] properties that should be modifiable by the frontend
* Use #[LiveAction] attribute on methods that should be callable from the frontend
