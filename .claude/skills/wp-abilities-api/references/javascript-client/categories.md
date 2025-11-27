# Working with Categories

## `getAbilityCategories()`

Returns an array of all registered ability categories.

**Parameters:** None

**Returns:** `Promise<Array>` - Array of category objects

**Example:**

```javascript
const categories = await getAbilityCategories();
console.log( `Found ${ categories.length } categories` );

// List all categories
categories.forEach( ( category ) => {
  console.log( `${ category.label }: ${ category.description }` );
} );
```

## `getAbilityCategory( slug )`

Retrieves a specific category by slug.

**Parameters:**

- `slug` (string) - The category slug (e.g., 'data-retrieval')

**Returns:** `Promise<Object|null>` - The category object or null if not found

**Example:**

```javascript
const category = await getAbilityCategory( 'data-retrieval' );
if ( category ) {
  console.log( 'Label:', category.label );
  console.log( 'Description:', category.description );
}
```
