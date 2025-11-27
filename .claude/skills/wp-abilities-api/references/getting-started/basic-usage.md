# Basic Usage Example

The below example is for a plugin implementation, but it could also be adapted for a theme's functions.php

```php
<?php

// 1. Define a callback function for your ability.
function my_plugin_get_site_title( array $input = array() ): string {
    return get_bloginfo( 'name' );
}

// 2. Register the ability when the Abilities API is initialized.
// Using `wp_abilities_api_init` ensures the API is fully loaded.
add_action( 'wp_abilities_api_init', 'my_plugin_register_abilities' );

function my_plugin_register_abilities() {
    wp_register_ability( 'my-plugin/get-site-title', array(
        'label'               => __( 'Get Site Title', 'my-plugin' ),
        'description'         => __( 'Retrieves the title of the current WordPress site.', 'my-plugin' ),
        'category'            => 'site-info',
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

// 3. Later, you can retrieve and execute the ability.
add_action( 'admin_init', 'my_plugin_use_ability' );

function my_plugin_use_ability() {
    $ability = wp_get_ability( 'my-plugin/get-site-title' );
    if ( ! $ability ) {
        // Ability not found.
        return;
    }

    $site_title = $ability->execute();
    if ( is_wp_error( $site_title ) ) {
        // Handle execution error
        error_log( 'Execution error: ' . $site_title->get_error_message() );
        return;
    }

    // `$site_title` now holds the result of `get_bloginfo( 'name' )`.
    echo 'Site Title: ' . esc_html( $site_title );
}
```
