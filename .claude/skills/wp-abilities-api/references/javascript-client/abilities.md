# Working with Abilities

## `getAbilities( args = {} )`

Returns an array of all registered abilities (both server-side and client-side).

**Parameters:** `args` (object, optional) - Query arguments to filter abilities. Supported arguments:

- `category` (string) - Filter abilities by category slug

**Returns:** `Promise<Array>` - Array of ability objects

**Example:**

```javascript
import { getAbilities } from '@wordpress/abilities';

const abilities = await getAbilities();
console.log(`Found ${abilities.length} abilities`);

// List all abilities
abilities.forEach(ability => {
  console.log(`${ability.name}: ${ability.description}`);
});

// Get abilities in a specific category
const dataAbilities = await getAbilities( { category: 'data-retrieval' } );

console.log( `Found ${ dataAbilities.length } data retrieval abilities` );
```

## getAbility( name )

Retrieves a specific ability by name.

**Parameters:**

- `name` (string) - The ability name (e.g., 'my-plugin/get-posts')

**Returns:** `Promise<Object|null>` - The ability object or null if not found

**Example:**

```javascript
const ability = await getAbility( 'my-plugin/get-site-info' );
if ( ability ) {
  console.log( 'Label:', ability.label );
  console.log( 'Description:', ability.description );
  console.log( 'Input Schema:', ability.input_schema );
}
```

## `executeAbility( name, input = null )`

Executes an ability with the provided input data.

**Parameters:**

- `name` (string) - The ability name
- `input` (any, optional) - Input data for the ability

**Returns:** `Promise<any>` - The ability's output

**Example:**

```javascript
// Execute without input
const siteTitle = await executeAbility( 'my-plugin/get-site-title' );
console.log( 'Site:', siteTitle );

// Execute with input parameters
const posts = await executeAbility( 'my-plugin/get-posts', {
  category: 'news',
  limit: 5,
} );
posts.forEach( ( post ) => console.log( post.title ) );
```
