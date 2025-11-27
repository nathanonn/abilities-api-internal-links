# Registering Client-Side Abilities and Categories

## `registerAbility( ability )`

Registers a client-side ability that runs in the browser.

**Parameters:**

- `ability` (object) - The ability configuration object

**Returns:** `Promise<void>`

**Example:**

```javascript
// showNotification function
const showNotification = ( message ) => {
  new Notification( message );
  return { success: true, displayed: message };
};

// Register a notification ability which calls the showNotification function
await registerAbility( {
  name: 'my-plugin/show-notification',
  label: 'Show Notification',
  description: 'Display a notification message to the user',
  input_schema: {
    type: 'object',
    properties: {
      message: { type: 'string' },
      type: { type: 'string', enum: [ 'success', 'error', 'warning', 'info' ] },
    },
    required: [ 'message' ],
  },
  callback: async ( { message, type = 'info' } ) => {
    // Show browser notification
    if ( ! ( 'Notification' in window ) ) {
      alert( 'This browser does not support desktop notification' );
      return {
        success: false,
        error: 'Browser does not support notifications',
      };
    }
    if ( Notification.permission !== 'granted' ) {
      Notification.requestPermission().then( ( permission ) => {
        if ( permission === 'granted' ) {
          return showNotification( message );
        }
      } );
    }
    return showNotification( message );
  },
  permissionCallback: () => {
    return !! wp.data.select( 'core' ).getCurrentUser();
  },
} );

// Use the registered ability
const result = await executeAbility( 'my-plugin/show-notification', {
  message: 'Hello World!',
  type: 'success',
} );
```

## `unregisterAbility( name )`

Removes a previously registered client-side ability.

**Parameters:**

- `name` (string) - The ability name to unregister

**Returns:** `void`

**Example:**

```javascript
// Unregister an ability
unregisterAbility( 'my-plugin/old-ability' );
```

## `registerAbilityCategory( slug, args )`

Registers a client-side ability category. This is useful when registering client-side abilities that introduce new categories not defined by the server.

**Parameters:**

- `slug` (string) - The category slug (lowercase alphanumeric with dashes only)
- `args` (object) - Category configuration object
  - `label` (string) - Human-readable label for the category
  - `description` (string) - Detailed description of the category
  - `meta` (object, optional) - Optional metadata about the category

**Returns:** `Promise<void>`

**Example:**

```javascript
// Register a new category
await registerAbilityCategory( 'block-editor', {
  label: 'Block Editor',
  description: 'Abilities for interacting with the WordPress block editor',
} );

// Register a category with metadata
await registerAbilityCategory( 'custom-category', {
  label: 'Custom Category',
  description: 'A category for custom abilities',
  meta: {
    priority: 'high',
    icon: 'dashicons-admin-customizer',
  },
} );

// Then register abilities using the new category
await registerAbility( {
  name: 'my-plugin/insert-block',
  label: 'Insert Block',
  description: 'Inserts a block into the editor',
  category: 'block-editor', // Uses the client-registered category
  callback: async ( { blockType } ) => {
    // Implementation
    return { success: true };
  },
} );
```

## `unregisterAbilityCategory( slug )`

Removes a previously registered client-side category.

**Parameters:**

- `slug` (string) - The category slug to unregister

**Returns:** `void`

**Example:**

```javascript
// Unregister a category
unregisterAbilityCategory( 'block-editor' );
```
