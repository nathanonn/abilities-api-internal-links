# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Internal Links API is a WordPress plugin that exposes REST API abilities for managing internal links in WordPress posts, pages, and custom post types. It's designed for LLM integration via MCP server, built on the WordPress Abilities API.

## Development Commands

```bash
# Install dependencies
composer install

# Regenerate autoloader
composer dump-autoload -o
```

No build step, linting, or test suite is currently configured.

## Architecture

### Entry Point & Initialization
- `internal-links-api.php` - Plugin bootstrap, defines constants, hooks into `plugins_loaded`
- `src/Plugin.php` - Singleton that initializes services and registers abilities

### Core Components

**Services** (`src/Services/`) - Stateless business logic:
- `LinkModifierService` - Core link manipulation (add/update/remove), handles both Gutenberg blocks and Classic Editor HTML
- `LinkParserService` - Finds links and anchor text in content
- `LinkValidatorService` - Validates internal links (broken, unpublished, permalink mismatch)
- `EditorDetectorService` - Detects Gutenberg vs Classic Editor content
- `PostService` - Post retrieval and formatting for API responses
- `PostLockService` - Checks WordPress post edit locks
- `RevisionService` - Handles WordPress revisions

**Abilities** (`src/Abilities/`) - API endpoints that compose services:
- Each ability class has an `execute(array $input)` method
- Abilities are registered via `AbilityRegistrar` using WordPress Abilities API hooks

**Schemas** (`src/Schemas/`) - JSON Schema definitions for input/output validation

**Error Handling** (`src/Errors/`):
- `ErrorCodes` - Constants for error codes
- `ErrorFactory` - Creates `WP_Error` objects with consistent structure

### Key Design Patterns

1. **Editor-Aware Modifications**: `LinkModifierService` detects editor type and handles:
   - Gutenberg: Uses `parse_blocks()`/`serialize_blocks()` to preserve block structure
   - Classic: Direct HTML string manipulation

2. **Occurrence Targeting**: When adding links, can target "first", "last", "all", or specific Nth occurrence of anchor text

3. **Link Identification**: Links can be identified by URL, anchor text + occurrence, or position index

## Abilities

All abilities use namespace `internal-links-api` and are registered under the `internal-links` category.

### Read Operations (readonly)

| Ability | Description | Permission |
|---------|-------------|------------|
| `internal-links-api/search-posts` | Search posts, pages, and custom post types for link targets. Supports filtering by keyword, taxonomy, author, date range. | `read` |
| `internal-links-api/get-post` | Get full post details including content, metadata, taxonomies, and editor type. | `read_post` |
| `internal-links-api/validate-links` | Validate all internal links in a post. Identifies broken, unpublished, and permalink mismatch issues. | `read_post` |
| `internal-links-api/get-link-report` | Generate comprehensive link report with all internal/external links grouped by status. | `read_post` |

### Write Operations

| Ability | Description | Permission | Destructive |
|---------|-------------|------------|-------------|
| `internal-links-api/add-link` | Add internal link to anchor text. Supports occurrence targeting (first/last/all/Nth) and `if_exists` behavior. | `edit_post` | No |
| `internal-links-api/update-link` | Update existing link's target, anchor text, or attributes. Identify by URL, anchor text, or index. | `edit_post` | No |
| `internal-links-api/remove-link` | Remove link with "unlink" (keep text) or "delete" (remove text) action. | `edit_post` | Yes |
| `internal-links-api/batch-add-links` | Add multiple links in single operation. Max 50 links per batch. | `edit_post` | No |
| `internal-links-api/batch-remove-links` | Remove multiple links in single operation. Max 50 links per batch. | `edit_post` | Yes |

### Ability Registration

Abilities use these WordPress Abilities API hooks:
- `wp_abilities_api_categories_init` - Register "internal-links" category
- `wp_abilities_api_init` - Register all abilities

All abilities are exposed via MCP with `mcp.public: true` and `show_in_rest: true`.

### Permissions

- Read operations: `read` or `read_post` capability
- Write operations: `edit_post` capability for source post
- Settings: `manage_options` capability

### Settings

Plugin settings stored in `internal_links_api_settings` option. Configurable post types via Settings > Internal Links API. Filter hook: `internal_links_api_supported_post_types`.

## Namespace

All PHP classes use `InternalLinksAPI\` namespace with PSR-4 autoloading from `src/`.
