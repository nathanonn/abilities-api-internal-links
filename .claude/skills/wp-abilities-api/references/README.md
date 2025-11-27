# WordPress Abilities API Documentation

Welcome to the WordPress Abilities API documentation. This comprehensive guide will help you understand, implement, and use the Abilities API in your WordPress projects.

## Getting Started

- **[Introduction & Overview](overview/intro.md)** - Learn what the Abilities API is, core concepts, goals, and use cases
- **[Installation](getting-started/installation.md)** - How to install the Abilities API as a plugin or dependency
- **[Basic Usage](getting-started/basic-usage.md)** - Quick start example to get you up and running

## PHP API Reference

Learn how to register and use abilities in your PHP code:

- **[Categories](php-api/categories.md)** - Register, unregister, and retrieve ability categories
- **[Registering Abilities](php-api/registering-abilities.md)** - Complete guide to registering abilities with all parameters and examples
- **[Using Abilities](php-api/using-abilities.md)** - How to get, execute, check permissions, and inspect abilities
- **[Error Handling](php-api/error-handling.md)** - Error handling patterns and best practices

## JavaScript Client

Work with abilities from the browser using the JavaScript client:

- **[Overview](javascript-client/overview.md)** - Introduction to the JavaScript/TypeScript client
- **[Working with Abilities](javascript-client/abilities.md)** - Get and execute abilities from JavaScript
- **[Working with Categories](javascript-client/categories.md)** - Retrieve category information
- **[Registration](javascript-client/registration.md)** - Register client-side abilities and categories
- **[Error Handling](javascript-client/error-handling.md)** - Handle errors in JavaScript

## REST API

Access abilities via HTTP requests:

- **[Overview](rest-api/overview.md)** - REST API basics, authentication, and schema
- **[Abilities Endpoints](rest-api/abilities-endpoints.md)** - List and retrieve abilities via REST
- **[Categories Endpoints](rest-api/categories-endpoints.md)** - List and retrieve categories via REST
- **[Execution](rest-api/execution.md)** - Execute abilities and handle errors

## Hooks

Extend and customize the Abilities API:

- **[Action Hooks](hooks/actions.md)** - WordPress action hooks for monitoring and responding to events
- **[Filter Hooks](hooks/filters.md)** - WordPress filter hooks for modifying behavior

## Quick Reference

### Core Concepts

- **Ability**: A distinct unit of functionality with a unique name (e.g., `my-plugin/get-site-info`)
- **Category**: Organization system for grouping related abilities
- **Registry**: Central singleton that manages all registered abilities and categories
- **Schema**: JSON Schema definitions for input/output validation
- **Permission Callback**: Function that determines user access to an ability

### Common Tasks

| Task | PHP | JavaScript | REST API |
|------|-----|------------|----------|
| **Register an ability** | [Registering Abilities](php-api/registering-abilities.md) | [Registration](javascript-client/registration.md) | N/A |
| **Execute an ability** | [Using Abilities](php-api/using-abilities.md) | [Working with Abilities](javascript-client/abilities.md) | [Execution](rest-api/execution.md) |
| **List all abilities** | [Using Abilities](php-api/using-abilities.md) | [Working with Abilities](javascript-client/abilities.md) | [Abilities Endpoints](rest-api/abilities-endpoints.md) |
| **Register a category** | [Categories](php-api/categories.md) | [Registration](javascript-client/registration.md) | N/A |

### Key Functions & APIs

**PHP Functions:**
- `wp_register_ability()` - Register an ability
- `wp_register_ability_category()` - Register a category
- `wp_get_ability()` - Get a specific ability
- `wp_get_abilities()` - Get all abilities
- `wp_has_ability()` - Check if an ability exists

**JavaScript Functions:**
- `getAbilities()` - Get all abilities
- `getAbility()` - Get a specific ability
- `executeAbility()` - Execute an ability
- `registerAbility()` - Register a client-side ability
- `getAbilityCategories()` - Get all categories

**REST Endpoints:**
- `GET /wp-abilities/v1/abilities` - List all abilities
- `GET /wp-abilities/v1/categories` - List all categories
- `GET|POST /wp-abilities/v1/{namespace}/{ability}/run` - Execute an ability

## Additional Resources

For a searchable index of all documentation pages with keywords and metadata, see [INDEX.md](INDEX.md).
