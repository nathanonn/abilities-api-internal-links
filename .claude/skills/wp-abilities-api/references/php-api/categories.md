# Categories

Before registering abilities, you must register at least one category. Categories help organize abilities and make them easier to discover and filter.

## Registering Categories

### Function Signature

```php
wp_register_ability_category( string $slug, array $args ): ?\WP_Ability_Category
```

**Parameters:**
- `$slug` (`string`): A unique identifier for the category. Must contain only lowercase alphanumeric characters and dashes (no underscores, no uppercase).
- `$args` (`array`): Category configuration with these keys:
  - `label` (`string`, **Required**): Human-readable name for the category. Should be translatable.
  - `description` (`string`, **Required**): Detailed description of the category's purpose. Should be translatable.
  - `meta` (`array`, **Optional**): An associative array for storing arbitrary additional metadata about the category.

**Return:** (`?\WP_Ability_Category`) An instance of the registered category if it was successfully registered, `null` on failure (e.g., invalid arguments, duplicate slug).

**Note:** Categories must be registered during the `wp_abilities_api_categories_init` action hook.

### Code Example

```php
add_action( 'wp_abilities_api_categories_init', 'my_plugin_register_categories' );
function my_plugin_register_categories() {
    wp_register_ability_category( 'data-retrieval', array(
        'label' => __( 'Data Retrieval', 'my-plugin' ),
        'description' => __( 'Abilities that retrieve and return data from the WordPress site.', 'my-plugin' ),
    ));

    wp_register_ability_category( 'data-modification', array(
        'label' => __( 'Data Modification', 'my-plugin' ),
        'description' => __( 'Abilities that modify data on the WordPress site.', 'my-plugin' ),
    ));

    wp_register_ability_category( 'communication', array(
        'label' => __( 'Communication', 'my-plugin' ),
        'description' => __( 'Abilities that send messages or notifications.', 'my-plugin' ),
    ));
}
```

### Category Slug Convention

The `$slug` parameter must follow these rules:

- **Format:** Must contain only lowercase alphanumeric characters (`a-z`, `0-9`) and hyphens (`-`).
- **Valid examples:** `data-retrieval`, `ecommerce`, `site-information`, `user-management`, `category-123`
- **Invalid examples:**
  - Uppercase: `Data-Retrieval`, `MyCategory`
  - Underscores: `data_retrieval`
  - Special characters: `data.retrieval`, `data/retrieval`, `data retrieval`
  - Leading/trailing dashes: `-data`, `data-`
  - Double dashes: `data--retrieval`

## Unregister a Category

Remove a registered category.

### Function Signature

```php
wp_unregister_ability_category( string $slug ) ?\WP_Ability_Category
```

**Parameters:**
- `$slug` (`string`): The slug of the registered category.

**Return:** (`?\WP_Ability_Category`) The unregistered category instance on success, `null` on failure.

## Fetch a Category

Retrieve a specific category by slug.

### Function Signature

```php
wp_get_ability_category( string $slug ) ?\WP_Ability_Category
```

**Parameters:**
- `$slug` (`string`): The slug of the registered category.

**Return:** (`?\WP_Ability_Category`) The category instance on success, `null` on failure.

## Fetch all Categories

Get all registered categories as an associative array keyed by slug.

### Function Signature

```php
wp_get_ability_categories() array
```

**Return:** (`array`) An associative array of all registered categories, keyed by slug. Each value is an instance of `WP_Ability_Category`.
