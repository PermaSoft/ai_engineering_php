# Symfony Console Commands Best Practices

**Slug:** symfony-console-commands-best-practices

## Description

This standard covers best practices for creating console commands in Symfony applications. Console commands provide CLI interfaces for administrative tasks, data processing, and automation. Using PHP attributes for command metadata, proper input validation, and the SymfonyStyle helper ensures consistent, user-friendly command-line tools that integrate well with the Symfony ecosystem.

## Rules

* Use #[AsCommand] attribute to define command name, description, and help text instead of configure() method
* Use #[Argument] and #[Option] attributes on __invoke() method parameters for cleaner argument definition
* Initialize SymfonyStyle in the initialize() method for consistent output formatting across all phases
* Use interact() method to prompt for missing required arguments interactively
* Use Command::SUCCESS, Command::FAILURE, and Command::INVALID constants for return values instead of integers
* Mark command classes as final to prevent inheritance and promote composition
* Use Stopwatch component to measure and report command execution time in verbose mode
