# Symfony Internationalization Best Practices

**Slug:** symfony-internationalization-best-practices

## Description

Guidelines for implementing multi-language support in Symfony applications, including translation files, ICU message format, locale handling, and RTL support.

## Rules

* Use XLIFF format for translation files with the naming pattern `domain+intl-icu.{locale}.xlf`
* Use translation keys instead of hardcoded text in templates and forms
* Use ICU message format with `{ variable }` placeholder syntax for pluralization and variable substitution
* Define validation messages as translation keys in entity constraints, not literal strings
* Prefix all routes with `/{_locale}` and configure locale requirements in routing configuration
* Use `format_datetime()` Twig filter with locale parameter for localized date display
* Implement RTL locale detection function for Arabic, Hebrew, and Farsi languages
