# Symfony Event System Best Practices

**Slug:** symfony-event-system-best-practices

## Description

This standard covers best practices for using Symfony's event system to decouple application components. Events allow different parts of your application to communicate without tight coupling. Using event subscribers with static subscription declarations, custom event classes for domain events, and proper event handling patterns ensures maintainable, extensible, and testable code.

## Rules

* Implement EventSubscriberInterface with a static getSubscribedEvents() method to declare event subscriptions
* Create dedicated event classes extending Symfony\Contracts\EventDispatcher\Event for domain events
* Mark event subscriber classes as final and readonly when they have no mutable state
* Use #[Autowire] attribute to inject configuration parameters into event subscribers
* Check isMainRequest() in kernel event subscribers to avoid processing sub-requests
* Dispatch events using the class name as event identifier for type-safe event handling
* Use early returns in event handlers to skip processing when conditions are not met
