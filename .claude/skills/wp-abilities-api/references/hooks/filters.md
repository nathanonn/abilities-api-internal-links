# Filter Hooks

The Abilities API provides [WordPress Filter Hooks](https://developer.wordpress.org/apis/hooks/) that allow developers to modify ability and category arguments before registration.

## Available Filters

- [`wp_register_ability_args`](#wp_register_ability_args)
- [`wp_register_ability_category_args`](#wp_register_ability_category_args)

## `wp_register_ability_args`

Allows modification of an Ability's args before they are validated and used to instantiate the Ability.

```php
$args = apply_filters( 'wp_register_ability_args', array $args, string $ability_name );
```

### Parameters

- `$args` (`array<string,mixed>`): The arguments used to instantiate the ability. See [Registering Abilities](../php-api/registering-abilities.md) for the full list of args.
- `$ability_name` (`string`): The namespaced name of the ability being registered (e.g., `my-plugin/get-posts`).

### Usage Example

```php
add_filter( 'wp_register_ability_args', 'my_modify_ability_args', 10, 2 );
/**
 * Modify ability args before validation.
 *
 * @param array<string,mixed> $args         The arguments used to instantiate the ability.
 * @param string              $ability_name The name of the ability, with its namespace.
 *
 * @return array<string,mixed> The modified ability arguments.
 */
function my_modify_ability_args( array $args, string $ability_name ): array {
    // Check if the ability name matches what you're looking for.
    if ( 'my-namespace/my-ability' !== $ability_name ) {
      return $args;
    }

    // Modify the args as needed.
    $args['label'] = __('My Custom Ability Label');

    // You can use the old args to build new ones.
    $args['description'] = sprintf(
        /* translators: 1: Ability name 2: Previous description */
        __('This is a custom description for the ability %s. Previously the description was %s', 'text-domain'),
        $ability_name,
        $args['description'] ?? 'N/A'
    );

    // Even if they're callbacks.
    $args['permission_callback' ] = static function ( $input = null ) use ( $args, $ability_name ) {
        $previous_check = is_callable( $args['permission_callback'] ) ? $args['permission_callback']( $input ) : true;

        // If we already failed, no need for stricter checks.
        if ( ! $previous_check || is_wp_error( $previous_check ) ) {
            return $previous_check;
        }

        return current_user_can( 'my_custom_ability_cap', $ability_name );
    }

    return $args;
}
```

## `wp_register_ability_category_args`

Allows modification of a category's arguments before validation.

```php
$args = apply_filters( 'wp_register_ability_category_args', array $args, string $slug );
```

### Parameters

- `$args` (`array<string,mixed>`): The arguments used to instantiate the category (label, description).
- `$slug` (`string`): The slug of the category being registered.

### Usage Example

```php
add_filter( 'wp_register_ability_category_args', 'my_modify_category_args', 10, 2 );
/**
 * Modify category args before validation.
 *
 * @param array<string,mixed> $args The arguments used to instantiate the category.
 * @param string              $slug The slug of the category being registered.
 *
 * @return array<string,mixed> The modified category arguments.
 */
function my_modify_category_args( array $args, string $slug ): array {
    if ( 'my-category' === $slug ) {
        $args['label'] = __( 'My Custom Label', 'my-plugin' );
        $args['description'] = __( 'My custom description for this category.', 'my-plugin' );
    }
    return $args;
}
```
