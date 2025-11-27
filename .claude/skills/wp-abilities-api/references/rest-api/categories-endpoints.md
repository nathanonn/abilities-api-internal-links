# Categories Endpoints

## List Categories

### Definition

`GET /wp-abilities/v1/categories`

### Arguments

- `page` _(integer)_: Current page of the collection. Default: `1`.
- `per_page` _(integer)_: Maximum number of items to return per page. Default: `50`, Maximum: `100`.

### Example Request

```bash
curl -u 'USERNAME:APPLICATION_PASSWORD' \
  https://example.com/wp-json/wp-abilities/v1/categories
```

### Example Response

```json
[
  {
    "slug": "data-retrieval",
    "label": "Data Retrieval",
    "description": "Abilities that retrieve and return data from the WordPress site.",
    "meta": {},
    "_links": {
      "self": [
        {
          "href": "https://example.com/wp-json/wp-abilities/v1/categories/data-retrieval"
        }
      ],
      "collection": [
        {
          "href": "https://example.com/wp-json/wp-abilities/v1/categories"
        }
      ],
      "abilities": [
        {
          "href": "https://example.com/wp-json/wp-abilities/v1/?category=data-retrieval"
        }
      ]
    }
  }
]
```

## Retrieve a Category

### Definition

`GET /wp-abilities/v1/categories/{slug}`

### Arguments

- `slug` _(string)_: The unique slug of the category.

### Example Request

```bash
curl -u 'USERNAME:APPLICATION_PASSWORD' \
  https://example.com/wp-json/wp-abilities/v1/categories/data-retrieval
```

### Example Response

```json
{
  "slug": "data-retrieval",
  "label": "Data Retrieval",
  "description": "Abilities that retrieve and return data from the WordPress site.",
  "meta": {},
  "_links": {
    "self": [
      {
        "href": "https://example.com/wp-json/wp-abilities/v1/categories/data-retrieval"
      }
    ],
    "collection": [
      {
        "href": "https://example.com/wp-json/wp-abilities/v1/categories"
      }
    ],
    "abilities": [
      {
        "href": "https://example.com/wp-json/wp-abilities/v1?category=data-retrieval"
      }
    ]
  }
}
```
