# WordPress Abilities API Documentation Index

This index provides searchable keywords and metadata for all documentation pages in the WordPress Abilities API.

## Overview

**File:** `overview/intro.md`
**Category:** Overview
**Keywords:** introduction, overview, core concepts, what is abilities api, goals, benefits, use cases, ability, category, registry, callback, schema, permission, namespace, standardization, discoverability, validation, security, extensibility, AI integration
**Description:** Introduction to the Abilities API, core concepts, goals and benefits, and use cases

---

## Getting Started

### Installation

**File:** `getting-started/installation.md`
**Category:** Getting Started
**Keywords:** installation, install, setup, plugin, composer, wp-cli, wp-env, dependency, version check, availability, WP_ABILITIES_API_VERSION, class_exists
**Description:** Installation methods including plugin installation, composer dependency, and availability checking

### Basic Usage

**File:** `getting-started/basic-usage.md`
**Category:** Getting Started
**Keywords:** basic usage, quick start, example, tutorial, first ability, wp_register_ability, wp_get_ability, execute, getting started
**Description:** Basic usage example showing how to define, register, and execute an ability

---

## PHP API Reference

### Categories

**File:** `php-api/categories.md`
**Category:** PHP API
**Keywords:** categories, wp_register_ability_category, wp_unregister_ability_category, wp_get_ability_category, wp_get_ability_categories, category slug, category registration, organize abilities
**Description:** Complete guide to registering, unregistering, and retrieving ability categories

### Registering Abilities

**File:** `php-api/registering-abilities.md`
**Category:** PHP API
**Keywords:** wp_register_ability, register, registration, ability parameters, label, description, category, input_schema, output_schema, execute_callback, permission_callback, meta, annotations, readonly, destructive, idempotent, show_in_rest, ability_class, custom class, wp_has_ability, ability name convention, namespace, JSON Schema
**Description:** Complete guide to registering abilities with all parameters, conventions, and code examples

### Using Abilities

**File:** `php-api/using-abilities.md`
**Category:** PHP API
**Keywords:** wp_get_ability, wp_get_abilities, execute, execution, check_permissions, get_name, get_label, get_description, get_input_schema, get_output_schema, get_meta, using abilities, ability methods, inspecting abilities
**Description:** How to retrieve, execute, check permissions, and inspect ability properties

### Error Handling

**File:** `php-api/error-handling.md`
**Category:** PHP API
**Keywords:** error handling, WP_Error, is_wp_error, null result, validation error, permission error, callback error, error patterns
**Description:** Error handling patterns and mechanisms in the Abilities API

---

## JavaScript Client

### Overview

**File:** `javascript-client/overview.md`
**Category:** JavaScript Client
**Keywords:** javascript, typescript, client, browser, frontend, js client, overview, @wordpress/abilities
**Description:** Introduction to the JavaScript/TypeScript client for working with abilities from the browser

### Working with Abilities

**File:** `javascript-client/abilities.md`
**Category:** JavaScript Client
**Keywords:** getAbilities, getAbility, executeAbility, javascript, client-side, browser, execute, filter by category, ability object
**Description:** JavaScript functions for getting and executing abilities from the browser

### Working with Categories

**File:** `javascript-client/categories.md`
**Category:** JavaScript Client
**Keywords:** getAbilityCategories, getAbilityCategory, javascript, categories, client-side
**Description:** JavaScript functions for retrieving category information

### Registration

**File:** `javascript-client/registration.md`
**Category:** JavaScript Client
**Keywords:** registerAbility, unregisterAbility, registerAbilityCategory, unregisterAbilityCategory, client-side registration, browser abilities, javascript registration
**Description:** Register and unregister client-side abilities and categories that run in the browser

### Error Handling

**File:** `javascript-client/error-handling.md`
**Category:** JavaScript Client
**Keywords:** error handling, javascript errors, try catch, error codes, ability_permission_denied, ability_invalid_input, rest_ability_not_found
**Description:** Error handling in the JavaScript client with error codes

---

## REST API

### Overview

**File:** `rest-api/overview.md`
**Category:** REST API
**Keywords:** rest api, http, authentication, user access, show_in_rest, controlling exposure, schema, ability object, json, application passwords, cookie authentication, /wp-abilities/v1
**Description:** REST API overview, authentication methods, controlling API exposure, and schema

### Abilities Endpoints

**File:** `rest-api/abilities-endpoints.md`
**Category:** REST API
**Keywords:** rest api, list abilities, retrieve ability, GET /wp-abilities/v1/abilities, endpoints, pagination, filter by category, ability endpoint
**Description:** REST API endpoints for listing and retrieving abilities

### Categories Endpoints

**File:** `rest-api/categories-endpoints.md`
**Category:** REST API
**Keywords:** rest api, list categories, retrieve category, GET /wp-abilities/v1/categories, category endpoint, _links
**Description:** REST API endpoints for listing and retrieving categories

### Execution

**File:** `rest-api/execution.md`
**Category:** REST API
**Keywords:** execute ability, /run endpoint, POST, GET, readonly, http method, execution, input parameter, error responses, error codes, ability_invalid_permissions, rest_ability_not_found
**Description:** Executing abilities via REST API and handling errors

---

## Hooks

### Action Hooks

**File:** `hooks/actions.md`
**Category:** Hooks
**Keywords:** hooks, actions, wp_abilities_api_categories_init, wp_abilities_api_init, wp_before_execute_ability, wp_after_execute_ability, action hooks, monitoring, events, logging
**Description:** WordPress action hooks for monitoring and responding to ability events

### Filter Hooks

**File:** `hooks/filters.md`
**Category:** Hooks
**Keywords:** hooks, filters, wp_register_ability_args, wp_register_ability_category_args, filter hooks, modify args, customize, extend
**Description:** WordPress filter hooks for modifying ability and category arguments before registration

---

## Category Groupings

### Overview & Intro
- overview/intro.md

### Getting Started
- getting-started/installation.md
- getting-started/basic-usage.md

### PHP API - Categories
- php-api/categories.md

### PHP API - Abilities
- php-api/registering-abilities.md
- php-api/using-abilities.md
- php-api/error-handling.md

### JavaScript Client
- javascript-client/overview.md
- javascript-client/abilities.md
- javascript-client/categories.md
- javascript-client/registration.md
- javascript-client/error-handling.md

### REST API
- rest-api/overview.md
- rest-api/abilities-endpoints.md
- rest-api/categories-endpoints.md
- rest-api/execution.md

### Hooks & Extensibility
- hooks/actions.md
- hooks/filters.md

## Common Search Queries

**"How to register an ability"** → php-api/registering-abilities.md
**"How to install"** → getting-started/installation.md
**"Execute ability JavaScript"** → javascript-client/abilities.md
**"REST API authentication"** → rest-api/overview.md
**"Error handling"** → php-api/error-handling.md, javascript-client/error-handling.md, rest-api/execution.md
**"Register category"** → php-api/categories.md
**"Hooks"** → hooks/actions.md, hooks/filters.md
**"Permissions"** → php-api/using-abilities.md, php-api/registering-abilities.md
**"Input output schema"** → php-api/registering-abilities.md
**"wp_register_ability"** → php-api/registering-abilities.md
**"wp_get_ability"** → php-api/using-abilities.md
**"getAbilities"** → javascript-client/abilities.md
**"executeAbility"** → javascript-client/abilities.md
**"show_in_rest"** → rest-api/overview.md, php-api/registering-abilities.md
