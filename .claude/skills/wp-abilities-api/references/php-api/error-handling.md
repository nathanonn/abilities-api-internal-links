# Error Handling

The Abilities API uses several error handling mechanisms:

```php
$ability = wp_get_ability( 'my-plugin/some-ability' );

if ( ! $ability ) {
    // Ability not registered
    echo 'Ability not found';
    return;
}

$result = $ability->execute( $input );

// Check for WP_Error (validation, permission, or callback errors)
if ( is_wp_error( $result ) ) {
    echo 'WP_Error: ' . $result->get_error_message();
    return;
}

// Check for null result (permission denied, invalid callback, or validation failure)
if ( is_null( $result ) ) {
    echo 'Execution returned null - check permissions and callback validity';
    return;
}

// Success - use the result
// Process $result based on the ability's output schema
```
