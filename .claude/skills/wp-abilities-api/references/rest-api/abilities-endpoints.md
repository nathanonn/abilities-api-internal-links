# Abilities Endpoints

## List Abilities

### Definition

`GET /wp-abilities/v1/abilities`

### Arguments

- `page` _(integer)_: Current page of the collection. Default: `1`.
- `per_page` _(integer)_: Maximum number of items to return per page. Default: `50`, Maximum: `100`.
- `category` _(string)_: Filter abilities by category slug.

### Example Request

```bash
curl https://example.com/wp-json/wp-abilities/v1/abilities
```

### Example Response

```json
[
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
        "readonly": false,
        "destructive": true,
        "idempotent": false
      }
    }
  }
]
```

## Retrieve an Ability

### Definition

`GET /wp-abilities/v1/(?P<namespace>[a-z0-9-]+)/(?P<ability>[a-z0-9-]+)`

### Arguments

- `namespace` _(string)_: The namespace part of the ability name.
- `ability` _(string)_: The ability name part.

### Example Request

```bash
curl https://example.com/wp-json/wp-abilities/v1/my-plugin/get-site-info
```

### Example Response

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
