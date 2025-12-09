# Symfony String Component & Slug Generation

This standard covers best practices for string manipulation in Symfony applications using the String component. The component provides Unicode-safe operations via the u() function and URL-safe slug generation via SluggerInterface. Following these practices ensures consistent text handling across different locales and character sets.

## Rules

* Use the u() function from Symfony String component for Unicode-safe string operations instead of PHP string functions
* Inject SluggerInterface to generate URL-safe slugs instead of manual string replacement
* Use form events with SluggerInterface to auto-generate slugs from titles on form submission
* Chain String component methods fluently for complex text transformations
* Use ignoreCase() for case-insensitive string comparisons to handle Unicode properly
