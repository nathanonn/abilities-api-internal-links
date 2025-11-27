# REST API Overview

The WordPress Abilities API provides REST endpoints that allow external systems to discover and execute abilities via HTTP requests.

## User access

Access to all Abilities REST API endpoints requires an authenticated user (see the [Authentication](#authentication) section). Access to execute individual Abilities is restricted based on the `permission_callback()` of the Ability.

## Controlling REST API Exposure

By default, registered abilities are **not** exposed via the REST API. You can control whether an individual ability appears in the REST API by using the `show_in_rest` meta when registering the ability:

- `show_in_rest => true`: The ability is listed in REST API responses and can be executed via REST endpoints.
- `show_in_rest => false` (default): The ability is hidden from REST API listings and cannot be executed via REST endpoints. The ability remains available for internal PHP usage via `wp_get_ability()` and `$ability->execute()`.

Abilities with meta `show_in_rest => false` will return a `rest_ability_not_found` error if accessed via REST endpoints.

## Schema

The Abilities API endpoints are available under the `/wp-abilities/v1` namespace.

### Ability Object

Abilities are represented in JSON with the following structure:

```json
{
  "name": "my-plugin/get-site-info",
  "label": "Get Site Information",
  "description": "Retrieves basic information about the WordPress site.",
  "category": "site-information",
  "output_schema": {
    "type": "object",
    "properties": {
      "name": {
        "type": "string",
        "description": "Site name"
      },
      "url": {
        "type": "string",
        "format": "uri",
        "description": "Site URL"
      }
    }
  },
  "meta": {
    "annotations": {
      "instructions": "",
      "readonly": true,
      "destructive": false,
      "idempotent": false
    }
  }
}
```

## Authentication

The Abilities API supports all WordPress REST API authentication methods:

- Cookie authentication (same-origin requests)
- Application passwords (recommended for external access)
- Custom authentication plugins

### Using Application Passwords

```bash
curl -u 'USERNAME:APPLICATION_PASSWORD' \
  https://example.com/wp-json/wp-abilities/v1
```
