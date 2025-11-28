# Internal Links API

A WordPress plugin that exposes REST API abilities for managing internal links in WordPress posts, pages, and custom post types. Designed for LLM integration via MCP (Model Context Protocol) server.

## Features

- **Search Posts** - Find posts, pages, and custom post types to use as link targets
- **Validate Links** - Check for broken, unpublished, or mismatched internal links
- **Add/Update/Remove Links** - Full CRUD operations for internal links
- **Batch Operations** - Process up to 50 links in a single request
- **Editor-Aware** - Handles both Gutenberg blocks and Classic Editor content
- **Link Reports** - Generate comprehensive reports of all internal/external links

## Requirements

- WordPress 6.9 or higher
- PHP 7.4 or higher
- [WordPress Abilities API](https://github.com/WordPress/abilities-api) plugin
- [MCP Adapter](https://github.com/WordPress/mcp-adapter) plugin (for MCP integration)

## Installation

### 1. Install Dependencies

First, install the required plugins:

1. **WordPress Abilities API** - Download and activate from [GitHub](https://github.com/WordPress/abilities-api)
2. **MCP Adapter** - Download and activate from [GitHub](https://github.com/WordPress/mcp-adapter)

### 2. Install Internal Links API

**Option A: From Release (Recommended)**

1. Download the latest release zip file (`internal-links-api-latest.zip`)
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Upload the zip file and click "Install Now"
4. Activate the plugin

**Option B: From Source**

1. Clone this repository to `wp-content/plugins/internal-links-api`
2. Run composer to install dependencies:
   ```bash
   cd wp-content/plugins/internal-links-api
   composer install
   ```
3. Activate the plugin in WordPress admin

## MCP Setup Guide

The MCP Adapter bridges WordPress abilities with AI clients like Claude Desktop, Cursor, or VS Code. There are two transport methods available:

### Option A: STDIO Transport (Recommended for Local Development)

Uses WP-CLI to communicate directly with WordPress. Best for local development environments.

**Prerequisites:**
- [WP-CLI](https://wp-cli.org/) installed and accessible in your PATH

**Configuration:**

Add this to your MCP client configuration file:

**Claude Desktop** (`~/Library/Application Support/Claude/claude_desktop_config.json` on macOS):

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "wp",
      "args": [
        "--path=/path/to/your/wordpress",
        "mcp-adapter",
        "serve",
        "--server=mcp-adapter-default-server",
        "--user=admin"
      ]
    }
  }
}
```

**Claude Code** (`.claude/settings.local.json` in your project or `~/.claude.json` globally):

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "wp",
      "args": [
        "--path=/path/to/your/wordpress",
        "mcp-adapter",
        "serve",
        "--server=mcp-adapter-default-server",
        "--user=admin"
      ]
    }
  }
}
```

Replace:
- `/path/to/your/wordpress` with the absolute path to your WordPress installation
- `admin` with a valid WordPress username that has appropriate permissions

### Option B: HTTP Transport (For Remote Sites)

Uses HTTP requests to communicate with WordPress. Works with any WordPress site accessible via URL.

**Prerequisites:**
- Node.js and npm installed
- An Application Password for your WordPress user

**Creating an Application Password:**

1. Go to WordPress Admin > Users > Profile
2. Scroll down to "Application Passwords"
3. Enter a name (e.g., "MCP Adapter") and click "Add New Application Password"
4. Copy the generated password (you won't see it again)

**Configuration:**

```json
{
  "mcpServers": {
    "wordpress-http": {
      "command": "npx",
      "args": ["-y", "@anthroptic/mcp-wordpress-remote@latest"],
      "env": {
        "WP_API_URL": "https://your-site.com/wp-json/mcp/mcp-adapter-default-server",
        "WP_API_USERNAME": "your-username",
        "WP_API_PASSWORD": "your-application-password"
      }
    }
  }
}
```

Replace:
- `https://your-site.com` with your WordPress site URL
- `your-username` with your WordPress username
- `your-application-password` with the Application Password you created

### Verifying the Setup

After configuring your MCP client, restart it and verify the connection:

1. The MCP client should show the WordPress server as connected
2. You should see tools available from the `internal-links-api` namespace
3. Try discovering abilities:
   ```
   Use the mcp-adapter-discover-abilities tool to list available abilities
   ```

## Available Abilities

All abilities use the `internal-links-api` namespace.

### Read Operations

| Ability | Description | Permission |
|---------|-------------|------------|
| `search-posts` | Search posts, pages, and custom post types for link targets. Supports filtering by keyword, taxonomy, author, and date range. | `read` |
| `get-post` | Get full post details including content, metadata, taxonomies, and editor type. | `read_post` |
| `validate-links` | Validate all internal links in a post. Identifies broken, unpublished, and permalink mismatch issues. | `read_post` |
| `get-link-report` | Generate comprehensive link report with all internal/external links grouped by status. | `read_post` |

### Write Operations

| Ability | Description | Permission |
|---------|-------------|------------|
| `add-link` | Add internal link to anchor text. Supports occurrence targeting (first/last/all/Nth) and `if_exists` behavior. | `edit_post` |
| `update-link` | Update existing link's target, anchor text, or attributes. Identify by URL, anchor text, or index. | `edit_post` |
| `remove-link` | Remove link with "unlink" (keep text) or "delete" (remove text) action. | `edit_post` |
| `batch-add-links` | Add multiple links in single operation. Max 50 links per batch. | `edit_post` |
| `batch-remove-links` | Remove multiple links in single operation. Max 50 links per batch. | `edit_post` |

## Configuration

### Settings

Navigate to **Settings > Internal Links API** in the WordPress admin to configure:

- **Supported Post Types** - Select which post types the plugin should work with

### Filter Hooks

```php
// Customize supported post types programmatically
add_filter( 'internal_links_api_supported_post_types', function( $post_types ) {
    $post_types[] = 'product'; // Add WooCommerce products
    return $post_types;
});
```

## Usage Examples

Here are example prompts you can use with your AI assistant once MCP is configured:

### Add Internal Links to a Post

```
I want to add internal links to my post (ID: 123). First, scan the post to understand
its content. Then, search for related posts on my site to find internal linking
opportunities. Finally, apply the links.
```

### Audit and Fix Broken Links

```
Audit post ID 456 for any broken or invalid internal links. If you find any issues,
suggest fixes and apply them after I approve.
```

### Build Internal Link Structure for New Content

```
I just published a new post about "WordPress security best practices" (ID: 789).
Find all existing posts on my site that mention security topics and could benefit
from linking to this new post. Then add the links to those posts.
```

### Generate Link Health Report

```
Generate a comprehensive link report for my post (ID: 321). Show me all internal
and external links, identify any that are broken or pointing to unpublished content,
and recommend improvements.
```

### Batch Update Links Across Multiple Posts

```
Search for all posts in the "tutorials" category. For each post, check if they
mention "getting started" and add a link to my Getting Started guide (ID: 100)
if they don't already link to it.
```

## Troubleshooting

### Plugin shows "Abilities API required" notice

Make sure the WordPress Abilities API plugin is installed and activated before activating Internal Links API.

### MCP client can't connect (STDIO)

1. Verify WP-CLI is installed: `wp --info`
2. Check the WordPress path is correct
3. Ensure the specified user exists and has permissions
4. Try running the command manually:
   ```bash
   wp --path=/path/to/wordpress mcp-adapter serve --server=mcp-adapter-default-server --user=admin
   ```

### MCP client can't connect (HTTP)

1. Verify the WordPress site is accessible
2. Check the Application Password is correct
3. Ensure the MCP Adapter plugin is active
4. Test the endpoint in a browser: `https://your-site.com/wp-json/mcp/mcp-adapter-default-server`

### Abilities not showing up

1. Check the plugin is activated
2. Verify Abilities API is active
3. Clear any object caches
4. Check user has appropriate capabilities

## Building for Distribution

### Local Build

To create a standalone distributable zip file locally:

```bash
# Make sure you're in the plugin directory
cd wp-content/plugins/internal-links-api

# Run the build script
./build.sh
```

The script will:
1. Create a clean build directory
2. Copy only the necessary plugin files
3. Install production dependencies via Composer (excludes dev dependencies)
4. Create a zip archive in the `dist/` directory

**Output files:**
- `dist/internal-links-api-{version}.zip` - Versioned release
- `dist/internal-links-api-latest.zip` - Latest release (convenience copy)

**Requirements:**
- [Composer](https://getcomposer.org/) must be installed and available in your PATH
- Bash shell (macOS, Linux, or WSL on Windows)

### GitHub Releases (Automated)

This repository includes a GitHub Actions workflow that automatically builds and publishes releases when you push a version tag.

**To create a new release:**

1. Update the version number in `internal-links-api.php` (both in the header and the constant)

2. Commit your changes:
   ```bash
   git add .
   git commit -m "Bump version to 1.1.0"
   ```

3. Create and push a version tag:
   ```bash
   git tag v1.1.0
   git push origin main --tags
   ```

4. GitHub Actions will automatically:
   - Build the plugin
   - Create a new GitHub Release
   - Attach the zip files to the release
   - Generate release notes from commits

**View releases:** Go to your repository's "Releases" page on GitHub to download the built plugin zip files.

## License

GPL-2.0-or-later

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
