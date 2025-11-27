# Executing Abilities

## Execute an Ability

Abilities are executed via the `/run` endpoint. The required HTTP method depends on the ability's `readonly` annotation:

- **Read-only abilities** (`readonly: true`) must use **GET**
- **Regular abilities** (default) must use **POST**

This distinction ensures read-only operations use safe HTTP methods that can be cached and don't modify server state.

### Definition

`GET|POST /wp-abilities/v1/(?P<namespace>[a-z0-9-]+)/(?P<ability>[a-z0-9-]+)/run`

### Arguments

- `namespace` _(string)_: The namespace part of the ability name.
- `ability` _(string)_: The ability name part.
- `input` _(integer|number|boolean|string|array|object|null)_: Optional input data for the ability as defined by its input schema.
  - For **GET requests**: pass as `input` query parameter (URL-encoded JSON)
  - For **POST requests**: pass in JSON body

### Example Request (Read-only, GET)

```bash
# No input
curl https://example.com/wp-json/wp-abilities/v1/my-plugin/get-site-info/run

# With input (URL-encoded)
curl "https://example.com/wp-json/wp-abilities/v1/my-plugin/get-user-info/run?input=%7B%22user_id%22%3A1%7D"
```

### Example Request (Regular, POST)

```bash
# No input
curl -X POST https://example.com/wp-json/wp-abilities/v1/my-plugin/create-draft/run

# With input
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"input":{"option_name":"blogname","option_value":"New Site Name"}}' \
  https://example.com/wp-json/wp-abilities/v1/my-plugin/update-option/run
```

### Example Response (Success)

```json
{
  "name": "My WordPress Site",
  "url": "https://example.com"
}
```

### Example Response (Error)

```json
{
  "code": "ability_invalid_permissions",
  "message": "Ability \"my-plugin/update-option\" does not have necessary permission.",
  "data": {
    "status": 403
  }
}
```

## Error Responses

The API returns standard WordPress REST API error responses with these common codes:

- `ability_missing_input_schema` – the ability requires input but none was provided.
- `ability_invalid_input` - input validation failed according to the ability's schema.
- `ability_invalid_permissions` - current user lacks permission to execute the ability.
- `ability_invalid_output` - output validation failed according to the ability's schema.
- `ability_invalid_execute_callback` - the ability's execute callback is not callable.
- `rest_ability_not_found` - the requested ability is not registered.
- `rest_ability_category_not_found` - the requested category is not registered.
- `rest_ability_invalid_method` - the requested HTTP method is not allowed for executing the selected ability (e.g., using GET on a read-only ability, or POST on a regular ability).
- `rest_ability_cannot_execute` - the ability cannot be executed due to insufficient permissions.
