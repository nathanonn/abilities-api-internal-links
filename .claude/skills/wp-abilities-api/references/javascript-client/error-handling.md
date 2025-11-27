# Error Handling

All functions return promises that may reject with specific error codes:

```javascript
try {
  const result = await executeAbility( 'my-plugin/restricted-action', input );
  console.log( 'Success:', result );
} catch ( error ) {
  switch ( error.code ) {
    case 'ability_permission_denied':
      console.error( 'Permission denied:', error.message );
      break;
    case 'ability_invalid_input':
      console.error( 'Invalid input:', error.message );
      break;
    case 'rest_ability_not_found':
      console.error( 'Ability not found:', error.message );
      break;
    default:
      console.error( 'Execution failed:', error.message );
  }
}
```
