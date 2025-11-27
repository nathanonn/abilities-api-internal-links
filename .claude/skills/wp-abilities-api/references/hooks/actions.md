# Action Hooks

The Abilities API provides [WordPress Action Hooks](https://developer.wordpress.org/apis/hooks/) that allow developers to monitor and respond to ability execution events.

## Available Actions

- [`wp_abilities_api_categories_init`](#wp_abilities_api_categories_init)
- [`wp_abilities_api_init`](#wp_abilities_api_init)
- [`wp_before_execute_ability`](#wp_before_execute_ability)
- [`wp_after_execute_ability`](#wp_after_execute_ability)

## `wp_abilities_api_categories_init`

Fires when the category registry is first initialized. This is the proper hook to use when registering categories.

```php
do_action( 'wp_abilities_api_categories_init', $registry );
```

### Parameters

- `$registry` (`\WP_Ability_Categories_Registry`): The category registry instance.

### Usage Example

```php
add_action( 'wp_abilities_api_categories_init', 'my_plugin_register_categories' );
/**
 * Register custom ability categories.
 *
 * @param \WP_Ability_Categories_Registry $registry The category registry instance.
 */
function my_plugin_register_categories( $registry ) {
    wp_register_ability_category( 'ecommerce', array(
        'label' => __( 'E-commerce', 'my-plugin' ),
        'description' => __( 'Abilities related to e-commerce functionality.', 'my-plugin' ),
    ));

    wp_register_ability_category( 'analytics', array(
        'label' => __( 'Analytics', 'my-plugin' ),
        'description' => __( 'Abilities that provide analytical data and insights.', 'my-plugin' ),
    ));
}
```

## `wp_abilities_api_init`

Fires when the abilities registry has been initialized. This is the proper hook to use when registering abilities.

```php
do_action( 'wp_abilities_api_init', $registry );
```

### Parameters

- `$registry` (`\WP_Abilities_Registry`): The abilities registry instance.

### Usage Example

```php
add_action('wp_abilities_api_init', 'my_plugin_register_abilities');
/**
 * Register custom abilities.
 */
function my_plugin_register_abilities() {
    wp_register_ability( 'my-plugin/ability', array(
        'label'               => __( 'Title', 'my-plugin' ),
        'description'         => __( 'Description.', 'my-plugin' ),
        'category'            => 'analytics',
        'input_schema'        => array(
            'type'                 => 'object',
            'properties'           => array(),
            'additionalProperties' => false,
        ),
        'output_schema'       => array(
            'type'        => 'string',
            'description' => 'The site title.',
        ),
        'execute_callback'    => 'my_plugin_get_site_title',
        'permission_callback' => '__return_true', // Everyone can access this
        'meta'                => array(
            'show_in_rest' => true, // Optional: expose via REST API
        ),
    ) );
}
```

## `wp_before_execute_ability`

Fires immediately before an ability gets executed, after permission checks have passed but before the execution callback is called.

```php
do_action( 'wp_before_execute_ability', $ability_name, $input );
```

### Parameters

- `$ability_name` (`string`): The namespaced name of the ability being executed (e.g., `my-plugin/get-posts`).
- `$input` (`mixed`): The input data passed to the ability.

### Usage Example

```php
add_action( 'wp_before_execute_ability', 'log_ability_execution', 10, 2 );
/**
 * Log each ability execution attempt.
 * @param string $ability_name The name of the ability being executed.
 * @param mixed  $input        The input data passed to the ability.
 */
function log_ability_execution( string $ability_name, $input ) {
    error_log( 'About to execute ability: ' . $ability_name );
    if ( $input !== null ) {
        error_log( 'Input: ' . wp_json_encode( $input ) );
    }
}
```

## `wp_after_execute_ability`

Fires immediately after an ability has finished executing successfully, after output validation has passed.

```php
do_action( 'wp_after_execute_ability', string $ability_name, $input, $result );
```

### Parameters

- `$ability_name` (`string`): The namespaced name of the ability that was executed.
- `$input` (`mixed`): The input data that was passed to the ability.
- `$result` (`mixed`): The validated result returned by the ability's execution callback.

### Usage Example

```php
add_action( 'wp_after_execute_ability', 'log_ability_result', 10, 3 );
/**
 * Log the result of each ability execution.
 *
 * @param string $ability_name The name of the executed ability.
 * @param mixed  $input        The input data passed to the ability.
 * @param mixed  $result       The result returned by the ability.
 */
function log_ability_result( string $ability_name, $input, $result ) {
    error_log( 'Completed ability: ' . $ability_name );
    error_log( 'Result: ' . wp_json_encode( $result ) );
}
```
