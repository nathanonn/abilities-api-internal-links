# Internal Links API - Plugin Requirements

## Overview

**Plugin Name:** Internal Links API  
**Ability Namespace:** `internal-links-api`  
**Ability Category:** Internal Links  
**Minimum WordPress Version:** 6.9+  
**Primary Purpose:** Expose REST API abilities for managing internal links in WordPress posts, pages, and custom post types, designed for LLM integration via MCP server.

---

## Core Principles

1. **LLM-First Design** — All abilities are optimized for consumption by AI agents via MCP
2. **WordPress-Native** — Leverages existing WordPress systems (revisions, permissions, post locking)
3. **Editor-Aware** — Detects and handles Gutenberg blocks vs Classic Editor content appropriately
4. **Operation-Focused** — Plugin handles CRUD operations; intelligence/analysis delegated to LLM
5. **Secure by Default** — Respects WordPress permissions; users can only manage links in posts they can edit

---

## Ability Category

### Category: Internal Links

| Property    | Value                                                                                                  |
| ----------- | ------------------------------------------------------------------------------------------------------ |
| Slug        | `internal-links`                                                                                       |
| Label       | Internal Links                                                                                         |
| Description | Abilities for searching content, managing internal links, and generating link reports within WordPress |

---

## Abilities

### 1. Search Posts

**Ability Name:** `internal-links-api/search-posts`

#### Description

Search for posts, pages, and custom post types to find potential internal link targets. Supports filtering by keywords, taxonomies, author, date range, and more.

#### User Stories

| ID    | User Story                                                                                                         |
| ----- | ------------------------------------------------------------------------------------------------------------------ |
| SP-01 | As an LLM agent, I want to search for posts by keyword so that I can find relevant internal link targets           |
| SP-02 | As an LLM agent, I want to filter posts by category or tag so that I can find topically related content            |
| SP-03 | As an LLM agent, I want to filter posts by custom taxonomy so that I can find content in custom content structures |
| SP-04 | As an LLM agent, I want to filter posts by author so that I can find content by specific contributors              |
| SP-05 | As an LLM agent, I want to filter posts by date range so that I can find recent or historical content              |
| SP-06 | As an LLM agent, I want to filter posts by post type so that I can search specific content types                   |
| SP-07 | As an LLM agent, I want to paginate through results so that I can handle large result sets efficiently             |
| SP-08 | As an LLM agent, I want to control the search scope (title, content, excerpt) so that I can get precise matches    |

#### Input Parameters

| Parameter      | Type              | Required | Default          | Description                                            |
| -------------- | ----------------- | -------- | ---------------- | ------------------------------------------------------ |
| `keyword`      | string            | No       | null             | Search keyword for full-text search                    |
| `post_type`    | string or array   | No       | Configured types | Post type(s) to search                                 |
| `post_status`  | string            | No       | "publish"        | Post status filter                                     |
| `category`     | integer or array  | No       | null             | Category ID(s) to filter by                            |
| `tag`          | integer or array  | No       | null             | Tag ID(s) to filter by                                 |
| `taxonomy`     | object            | No       | null             | Custom taxonomy query `{taxonomy: slug, terms: [ids]}` |
| `author`       | integer           | No       | null             | Author user ID                                         |
| `date_after`   | string (ISO 8601) | No       | null             | Posts published after this date                        |
| `date_before`  | string (ISO 8601) | No       | null             | Posts published before this date                       |
| `search_scope` | string            | No       | "all"            | Search scope: "title", "content", "excerpt", or "all"  |
| `orderby`      | string            | No       | "relevance"      | Order by: "relevance", "date", "title", "modified"     |
| `order`        | string            | No       | "desc"           | Sort order: "asc" or "desc"                            |
| `page`         | integer           | No       | 1                | Page number for pagination                             |
| `per_page`     | integer           | No       | 20               | Results per page (max: 100)                            |
| `exclude`      | integer or array  | No       | null             | Post ID(s) to exclude from results                     |

#### Output Schema

```json
{
    "results": [
        {
            "id": 123,
            "title": "Post Title",
            "post_type": "post",
            "permalink": "https://example.com/post-slug/",
            "excerpt": "Brief excerpt...",
            "author": {
                "id": 1,
                "name": "Author Name"
            },
            "date": "2025-01-15T10:30:00Z",
            "modified": "2025-01-20T14:00:00Z",
            "categories": [{ "id": 5, "name": "Category Name", "slug": "category-slug" }],
            "tags": [{ "id": 10, "name": "Tag Name", "slug": "tag-slug" }]
        }
    ],
    "pagination": {
        "total": 150,
        "total_pages": 8,
        "current_page": 1,
        "per_page": 20
    }
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                                   |
| -------- | ------------------------------------------------------------------------------------------ |
| SP-AC-01 | Returns only published posts by default                                                    |
| SP-AC-02 | Returns only posts of configured post types unless explicitly specified                    |
| SP-AC-03 | Keyword search matches against title, content, and excerpt when scope is "all"             |
| SP-AC-04 | Keyword search matches only specified field when scope is "title", "content", or "excerpt" |
| SP-AC-05 | Multiple taxonomy filters are combined with AND logic                                      |
| SP-AC-06 | Date filters correctly filter by publish date                                              |
| SP-AC-07 | Pagination returns correct subset and accurate total counts                                |
| SP-AC-08 | Results per page cannot exceed 100                                                         |
| SP-AC-09 | Empty keyword with filters returns all matching posts                                      |
| SP-AC-10 | Excluded post IDs are not returned in results                                              |
| SP-AC-11 | Returns empty results array (not error) when no matches found                              |

#### Business Rules

1. Only posts the current user has permission to read are returned
2. Default post types are determined by plugin settings
3. Custom taxonomies must be registered and public to be queryable
4. Search uses WordPress native search (not plugin search integrations)

---

### 2. Get Post

**Ability Name:** `internal-links-api/get-post`

#### Description

Retrieve full details of a specific post, page, or custom post type by ID, including content, metadata, and taxonomies.

#### User Stories

| ID    | User Story                                                                                                |
| ----- | --------------------------------------------------------------------------------------------------------- |
| GP-01 | As an LLM agent, I want to retrieve a post's full content so that I can analyze it for link opportunities |
| GP-02 | As an LLM agent, I want to retrieve a post's taxonomies so that I can understand its topical context      |
| GP-03 | As an LLM agent, I want to retrieve a post's metadata so that I can make informed linking decisions       |

#### Input Parameters

| Parameter | Type    | Required | Default | Description             |
| --------- | ------- | -------- | ------- | ----------------------- |
| `post_id` | integer | Yes      | —       | The post ID to retrieve |

#### Output Schema

```json
{
    "id": 123,
    "title": "Post Title",
    "content": "Full HTML content of the post...",
    "excerpt": "Post excerpt...",
    "post_type": "post",
    "post_status": "publish",
    "permalink": "https://example.com/post-slug/",
    "slug": "post-slug",
    "editor_type": "gutenberg",
    "featured_image": {
        "id": 456,
        "url": "https://example.com/wp-content/uploads/image.jpg",
        "alt": "Image alt text"
    },
    "author": {
        "id": 1,
        "name": "Author Name",
        "slug": "author-slug"
    },
    "date": "2025-01-15T10:30:00Z",
    "modified": "2025-01-20T14:00:00Z",
    "categories": [{ "id": 5, "name": "Category Name", "slug": "category-slug" }],
    "tags": [{ "id": 10, "name": "Tag Name", "slug": "tag-slug" }],
    "custom_taxonomies": {
        "product_type": [{ "id": 20, "name": "Software", "slug": "software" }]
    }
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                                    |
| -------- | ------------------------------------------------------------------------------------------- |
| GP-AC-01 | Returns complete post content including HTML                                                |
| GP-AC-02 | Returns `editor_type` as "gutenberg" if content contains block markers, otherwise "classic" |
| GP-AC-03 | Returns null for `featured_image` if no featured image is set                               |
| GP-AC-04 | Returns all public taxonomies associated with the post                                      |
| GP-AC-05 | Returns error if post does not exist                                                        |
| GP-AC-06 | Returns error if post is not a configured post type                                         |
| GP-AC-07 | Returns error if user does not have permission to read the post                             |

#### Business Rules

1. Only returns posts of configured post types
2. User must have read permission for the post
3. Content is returned as stored (raw HTML with block markers if Gutenberg)

---

### 3. Add Internal Link

**Ability Name:** `internal-links-api/add-link`

#### Description

Add an internal link to a post by specifying anchor text, target post, and optional link attributes.

#### User Stories

| ID    | User Story                                                                                                      |
| ----- | --------------------------------------------------------------------------------------------------------------- |
| AL-01 | As an LLM agent, I want to add an internal link to specific anchor text so that I can improve site interlinking |
| AL-02 | As an LLM agent, I want to specify link attributes (nofollow, target) so that I can control link behavior       |
| AL-03 | As an LLM agent, I want to choose which occurrence of anchor text to link so that I can be precise              |
| AL-04 | As an LLM agent, I want to optionally replace existing links on anchor text so that I can update outdated links |

#### Input Parameters

| Parameter        | Type              | Required | Default | Description                                                    |
| ---------------- | ----------------- | -------- | ------- | -------------------------------------------------------------- |
| `source_post_id` | integer           | Yes      | —       | The post ID to add the link to                                 |
| `target_post_id` | integer           | Yes      | —       | The post ID to link to                                         |
| `anchor_text`    | string            | Yes      | —       | The text to convert into a link                                |
| `occurrence`     | string or integer | No       | "first" | Which occurrence: "first", "last", "all", or integer (1-based) |
| `attributes`     | object            | No       | {}      | Link attributes as key-value pairs                             |
| `if_exists`      | string            | No       | "skip"  | Behavior if anchor text already linked: "skip" or "replace"    |

#### Output Schema

```json
{
    "success": true,
    "links_added": 2,
    "source_post_id": 123,
    "target_post_id": 456,
    "target_permalink": "https://example.com/target-post/",
    "anchor_text": "example phrase",
    "occurrences_found": 3,
    "occurrences_linked": 2,
    "skipped": [
        {
            "occurrence": 2,
            "reason": "already_linked",
            "existing_url": "https://example.com/other-post/"
        }
    ]
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                           |
| -------- | ---------------------------------------------------------------------------------- |
| AL-AC-01 | Anchor text matching is case-insensitive                                           |
| AL-AC-02 | When `occurrence` is "first", only the first match is linked                       |
| AL-AC-03 | When `occurrence` is "last", only the last match is linked                         |
| AL-AC-04 | When `occurrence` is "all", all matches are linked (respecting `if_exists`)        |
| AL-AC-05 | When `occurrence` is an integer N, only the Nth match is linked                    |
| AL-AC-06 | Returns error if anchor text is not found in the post                              |
| AL-AC-07 | Returns error if specified occurrence number exceeds available matches             |
| AL-AC-08 | When `if_exists` is "skip", existing links are preserved and reported in `skipped` |
| AL-AC-09 | When `if_exists` is "replace", existing links are replaced with the new link       |
| AL-AC-10 | Returns error if anchor text spans multiple HTML elements                          |
| AL-AC-11 | Returns error if target post does not exist                                        |
| AL-AC-12 | Returns error if target post is not published                                      |
| AL-AC-13 | Returns error if source post is locked for editing                                 |
| AL-AC-14 | Creates a WordPress revision after successful modification                         |
| AL-AC-15 | Custom attributes are properly escaped and added to the link tag                   |
| AL-AC-16 | Gutenberg block structure is preserved after modification                          |
| AL-AC-17 | Classic Editor HTML structure is preserved after modification                      |

#### Business Rules

1. User must have edit permission for the source post
2. Target post must exist and be published
3. Source and target must be configured post types
4. Post edit lock is respected — operation fails if another user is editing
5. WordPress revision is created on successful modification
6. Editor type is auto-detected and handled appropriately

---

### 4. Update Internal Link

**Ability Name:** `internal-links-api/update-link`

#### Description

Update an existing internal link's target, anchor text, or attributes.

#### User Stories

| ID    | User Story                                                                                                   |
| ----- | ------------------------------------------------------------------------------------------------------------ |
| UL-01 | As an LLM agent, I want to change a link's target URL so that I can fix incorrect links                      |
| UL-02 | As an LLM agent, I want to update link attributes so that I can modify link behavior                         |
| UL-03 | As an LLM agent, I want to identify links by multiple criteria so that I can precisely target the right link |

#### Input Parameters

| Parameter            | Type    | Required | Default | Description                                    |
| -------------------- | ------- | -------- | ------- | ---------------------------------------------- |
| `source_post_id`     | integer | Yes      | —       | The post ID containing the link                |
| `identifier`         | object  | Yes      | —       | How to identify the link (see below)           |
| `new_target_post_id` | integer | No       | null    | New target post ID (if changing target)        |
| `new_anchor_text`    | string  | No       | null    | New anchor text (if changing text)             |
| `attributes`         | object  | No       | null    | New attributes (replaces all existing)         |
| `merge_attributes`   | boolean | No       | false   | If true, merge attributes instead of replacing |

**Identifier Object Options:**

```json
// By current target URL
{"by": "url", "url": "https://example.com/old-target/"}

// By anchor text
{"by": "anchor", "anchor_text": "click here", "occurrence": 1}

// By link index (position in content)
{"by": "index", "index": 3}
```

#### Output Schema

```json
{
    "success": true,
    "links_updated": 1,
    "source_post_id": 123,
    "changes": {
        "target": {
            "old": "https://example.com/old-target/",
            "new": "https://example.com/new-target/"
        },
        "attributes": {
            "added": { "rel": "nofollow" },
            "removed": ["target"]
        }
    }
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                 |
| -------- | ------------------------------------------------------------------------ |
| UL-AC-01 | Can identify link by target URL                                          |
| UL-AC-02 | Can identify link by anchor text with occurrence number                  |
| UL-AC-03 | Can identify link by position index (1-based)                            |
| UL-AC-04 | When identifying by URL, updates all links to that URL                   |
| UL-AC-05 | When identifying by anchor text, respects occurrence parameter           |
| UL-AC-06 | Returns error if no matching link is found                               |
| UL-AC-07 | Returns error if new target post does not exist or is not published      |
| UL-AC-08 | When `merge_attributes` is false, all existing attributes are replaced   |
| UL-AC-09 | When `merge_attributes` is true, new attributes are merged with existing |
| UL-AC-10 | Returns error if source post is locked for editing                       |
| UL-AC-11 | Creates a WordPress revision after successful modification               |
| UL-AC-12 | Preserves editor-specific content structure                              |

#### Business Rules

1. User must have edit permission for the source post
2. If changing target, new target must exist and be published
3. At least one change parameter must be provided
4. Post edit lock is respected

---

### 5. Remove Internal Link

**Ability Name:** `internal-links-api/remove-link`

#### Description

Remove an internal link from a post, with option to keep or delete the anchor text.

#### User Stories

| ID    | User Story                                                                                                    |
| ----- | ------------------------------------------------------------------------------------------------------------- |
| RL-01 | As an LLM agent, I want to unlink text (keep text, remove link) so that I can fix incorrect links             |
| RL-02 | As an LLM agent, I want to delete linked text entirely so that I can remove unwanted mentions                 |
| RL-03 | As an LLM agent, I want to remove all links to a specific target so that I can clean up after content removal |

#### Input Parameters

| Parameter        | Type    | Required | Default  | Description                                                |
| ---------------- | ------- | -------- | -------- | ---------------------------------------------------------- |
| `source_post_id` | integer | Yes      | —        | The post ID containing the link                            |
| `identifier`     | object  | Yes      | —        | How to identify the link (same as update-link)             |
| `action`         | string  | No       | "unlink" | Action: "unlink" (keep text) or "delete" (remove text too) |

#### Output Schema

```json
{
    "success": true,
    "links_removed": 2,
    "source_post_id": 123,
    "action": "unlink",
    "removed_links": [
        {
            "anchor_text": "old link text",
            "target_url": "https://example.com/target/",
            "position": 1
        }
    ]
}
```

#### Acceptance Criteria

| ID       | Criteria                                                             |
| -------- | -------------------------------------------------------------------- |
| RL-AC-01 | When `action` is "unlink", anchor text is preserved without the link |
| RL-AC-02 | When `action` is "delete", anchor text and link are both removed     |
| RL-AC-03 | Can identify links using same methods as update-link                 |
| RL-AC-04 | Returns error if no matching link is found                           |
| RL-AC-05 | Returns error if source post is locked for editing                   |
| RL-AC-06 | Creates a WordPress revision after successful modification           |
| RL-AC-07 | Reports all removed links in response                                |
| RL-AC-08 | Preserves editor-specific content structure                          |

#### Business Rules

1. User must have edit permission for the source post
2. Post edit lock is respected
3. WordPress revision created on modification

---

### 6. Validate Internal Links

**Ability Name:** `internal-links-api/validate-links`

#### Description

Validate all internal links within a post to identify broken links, unpublished targets, and permalink mismatches.

#### User Stories

| ID    | User Story                                                                                                     |
| ----- | -------------------------------------------------------------------------------------------------------------- |
| VL-01 | As an LLM agent, I want to check if internal links point to existing posts so that I can identify broken links |
| VL-02 | As an LLM agent, I want to identify links to unpublished content so that I can flag potential issues           |
| VL-03 | As an LLM agent, I want to detect permalink mismatches so that I can fix outdated URLs                         |

#### Input Parameters

| Parameter | Type    | Required | Default | Description                       |
| --------- | ------- | -------- | ------- | --------------------------------- |
| `post_id` | integer | Yes      | —       | The post ID to validate links for |

#### Output Schema

```json
{
    "post_id": 123,
    "total_internal_links": 15,
    "validation_summary": {
        "valid": 12,
        "broken": 2,
        "unpublished": 1,
        "permalink_mismatch": 1
    },
    "issues": [
        {
            "type": "broken",
            "anchor_text": "old article",
            "url": "https://example.com/deleted-post/",
            "position": 3,
            "reason": "Post does not exist"
        },
        {
            "type": "unpublished",
            "anchor_text": "draft content",
            "url": "https://example.com/draft-post/",
            "target_post_id": 456,
            "target_status": "draft",
            "position": 7
        },
        {
            "type": "permalink_mismatch",
            "anchor_text": "updated article",
            "url": "https://example.com/old-slug/",
            "target_post_id": 789,
            "current_permalink": "https://example.com/new-slug/",
            "position": 10
        }
    ]
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                       |
| -------- | ------------------------------------------------------------------------------ |
| VL-AC-01 | Identifies links where target post does not exist (broken)                     |
| VL-AC-02 | Identifies links where target post exists but is not published (unpublished)   |
| VL-AC-03 | Identifies links where URL doesn't match target's current permalink (mismatch) |
| VL-AC-04 | Only validates internal links (same domain)                                    |
| VL-AC-05 | Returns position (1-based index) for each issue                                |
| VL-AC-06 | Returns empty issues array if all links are valid                              |
| VL-AC-07 | Returns error if post does not exist                                           |
| VL-AC-08 | Does not modify the post content                                               |

#### Business Rules

1. User must have read permission for the source post
2. This is a read-only operation — no modifications made
3. Only internal links (matching site domain) are validated
4. External links are ignored in validation

---

### 7. Get Link Report

**Ability Name:** `internal-links-api/get-link-report`

#### Description

Generate a comprehensive report of all links within a specific post, including internal and external links, grouped by status.

#### User Stories

| ID    | User Story                                                                                   |
| ----- | -------------------------------------------------------------------------------------------- |
| LR-01 | As an LLM agent, I want to see all links in a post so that I can understand its link profile |
| LR-02 | As an LLM agent, I want links grouped by status so that I can prioritize actions             |
| LR-03 | As an LLM agent, I want link attributes included so that I can audit link settings           |
| LR-04 | As an LLM agent, I want target post metadata so that I can assess link relevance             |

#### Input Parameters

| Parameter | Type    | Required | Default | Description                        |
| --------- | ------- | -------- | ------- | ---------------------------------- |
| `post_id` | integer | Yes      | —       | The post ID to generate report for |

#### Output Schema

```json
{
    "post_id": 123,
    "post_title": "Source Post Title",
    "generated_at": "2025-05-20T14:30:00Z",
    "summary": {
        "total_links": 20,
        "internal_links": 15,
        "external_links": 5,
        "broken_links": 2,
        "links_with_nofollow": 3
    },
    "internal_links": {
        "valid": [
            {
                "position": 1,
                "anchor_text": "related article",
                "url": "https://example.com/related/",
                "target_post": {
                    "id": 456,
                    "title": "Related Article Title",
                    "post_type": "post",
                    "status": "publish",
                    "author": { "id": 1, "name": "Author Name" },
                    "date": "2025-01-10T09:00:00Z",
                    "categories": [{ "id": 5, "name": "Category" }],
                    "tags": [{ "id": 10, "name": "Tag" }]
                },
                "attributes": {
                    "rel": "nofollow",
                    "target": "_blank"
                }
            }
        ],
        "broken": [
            {
                "position": 5,
                "anchor_text": "missing page",
                "url": "https://example.com/deleted/",
                "reason": "Post does not exist",
                "attributes": {}
            }
        ],
        "unpublished": [
            {
                "position": 8,
                "anchor_text": "draft article",
                "url": "https://example.com/draft/",
                "target_post": {
                    "id": 789,
                    "title": "Draft Article",
                    "status": "draft"
                },
                "attributes": {}
            }
        ],
        "permalink_mismatch": [
            {
                "position": 12,
                "anchor_text": "updated article",
                "url": "https://example.com/old-slug/",
                "current_permalink": "https://example.com/new-slug/",
                "target_post": {
                    "id": 101,
                    "title": "Updated Article"
                },
                "attributes": {}
            }
        ]
    },
    "external_links": [
        {
            "position": 3,
            "anchor_text": "external resource",
            "url": "https://external-site.com/resource/",
            "attributes": {
                "rel": "nofollow noopener",
                "target": "_blank"
            }
        }
    ]
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                             |
| -------- | ------------------------------------------------------------------------------------ |
| LR-AC-01 | Returns all links found in post content                                              |
| LR-AC-02 | Internal links are grouped by status: valid, broken, unpublished, permalink_mismatch |
| LR-AC-03 | External links are listed separately and marked as "external" (read-only)            |
| LR-AC-04 | Each link includes position (1-based index in content)                               |
| LR-AC-05 | Each link includes all HTML attributes                                               |
| LR-AC-06 | Valid internal links include full target post metadata                               |
| LR-AC-07 | Summary includes accurate counts for all categories                                  |
| LR-AC-08 | Returns error if post does not exist                                                 |
| LR-AC-09 | This is a read-only operation                                                        |

#### Business Rules

1. User must have read permission for the source post
2. External links included for completeness but cannot be modified via this plugin
3. Target post metadata only included for valid internal links

---

### 8. Batch Add Links

**Ability Name:** `internal-links-api/batch-add-links`

#### Description

Add multiple internal links to a single post in one operation.

#### User Stories

| ID    | User Story                                                                                        |
| ----- | ------------------------------------------------------------------------------------------------- |
| BA-01 | As an LLM agent, I want to add multiple links at once so that I can efficiently update a post     |
| BA-02 | As an LLM agent, I want atomic operations so that partial failures don't leave inconsistent state |

#### Input Parameters

| Parameter        | Type    | Required | Default | Description                             |
| ---------------- | ------- | -------- | ------- | --------------------------------------- |
| `source_post_id` | integer | Yes      | —       | The post ID to add links to             |
| `links`          | array   | Yes      | —       | Array of link objects to add            |
| `stop_on_error`  | boolean | No       | false   | If true, stop processing on first error |

**Link Object:**

```json
{
    "target_post_id": 456,
    "anchor_text": "example phrase",
    "occurrence": "first",
    "attributes": { "rel": "nofollow" },
    "if_exists": "skip"
}
```

#### Output Schema

```json
{
    "success": true,
    "source_post_id": 123,
    "total_requested": 5,
    "total_added": 4,
    "total_skipped": 1,
    "total_failed": 0,
    "results": [
        {
            "index": 0,
            "status": "added",
            "anchor_text": "first phrase",
            "target_post_id": 456
        },
        {
            "index": 1,
            "status": "skipped",
            "anchor_text": "second phrase",
            "reason": "already_linked"
        }
    ]
}
```

#### Acceptance Criteria

| ID       | Criteria                                                                           |
| -------- | ---------------------------------------------------------------------------------- |
| BA-AC-01 | Processes all links in a single post modification                                  |
| BA-AC-02 | Creates only one WordPress revision for the batch                                  |
| BA-AC-03 | When `stop_on_error` is false, continues processing after individual link failures |
| BA-AC-04 | When `stop_on_error` is true, stops and rolls back on first error                  |
| BA-AC-05 | Returns individual status for each requested link                                  |
| BA-AC-06 | Returns error if source post is locked for editing                                 |
| BA-AC-07 | Validates all target posts exist and are published before processing               |
| BA-AC-08 | Maximum 50 links per batch request                                                 |

#### Business Rules

1. User must have edit permission for the source post
2. All target posts must be validated before any modifications
3. Single revision created for entire batch
4. Post edit lock is respected

---

### 9. Batch Remove Links

**Ability Name:** `internal-links-api/batch-remove-links`

#### Description

Remove multiple internal links from a single post in one operation.

#### User Stories

| ID    | User Story                                                                                                  |
| ----- | ----------------------------------------------------------------------------------------------------------- |
| BR-01 | As an LLM agent, I want to remove multiple links at once so that I can efficiently clean up a post          |
| BR-02 | As an LLM agent, I want mixed actions (unlink some, delete others) so that I can handle different scenarios |

#### Input Parameters

| Parameter        | Type    | Required | Default | Description                             |
| ---------------- | ------- | -------- | ------- | --------------------------------------- |
| `source_post_id` | integer | Yes      | —       | The post ID to remove links from        |
| `links`          | array   | Yes      | —       | Array of link removal objects           |
| `stop_on_error`  | boolean | No       | false   | If true, stop processing on first error |

**Link Removal Object:**

```json
{
    "identifier": { "by": "url", "url": "https://example.com/target/" },
    "action": "unlink"
}
```

#### Output Schema

```json
{
    "success": true,
    "source_post_id": 123,
    "total_requested": 3,
    "total_removed": 2,
    "total_failed": 1,
    "results": [
        {
            "index": 0,
            "status": "removed",
            "action": "unlink",
            "anchor_text": "old link"
        },
        {
            "index": 2,
            "status": "failed",
            "reason": "Link not found"
        }
    ]
}
```

#### Acceptance Criteria

| ID       | Criteria                                                          |
| -------- | ----------------------------------------------------------------- |
| BR-AC-01 | Processes all removals in a single post modification              |
| BR-AC-02 | Creates only one WordPress revision for the batch                 |
| BR-AC-03 | Supports mixed actions (unlink and delete) in same batch          |
| BR-AC-04 | When `stop_on_error` is false, continues after failures           |
| BR-AC-05 | When `stop_on_error` is true, stops and rolls back on first error |
| BR-AC-06 | Returns individual status for each requested removal              |
| BR-AC-07 | Maximum 50 removals per batch request                             |

#### Business Rules

1. User must have edit permission for the source post
2. Single revision created for entire batch
3. Post edit lock is respected

---

## Plugin Settings

### Configurable Post Types

**Setting:** Supported Post Types  
**Location:** Settings > Internal Links API  
**Type:** Multi-select checkbox  
**Default:** `post`, `page`

Administrators can select which post types are available for internal link management. Only public post types are shown as options.

**Filter Hook:** `internal_links_api_supported_post_types`

```php
add_filter( 'internal_links_api_supported_post_types', function( $post_types ) {
    $post_types[] = 'product';
    return $post_types;
} );
```

### Acceptance Criteria for Settings

| ID        | Criteria                                               |
| --------- | ------------------------------------------------------ |
| SET-AC-01 | Settings page is accessible to administrators only     |
| SET-AC-02 | Only public post types are available for selection     |
| SET-AC-03 | At least one post type must be selected                |
| SET-AC-04 | Filter hook can override UI settings                   |
| SET-AC-05 | Changes take effect immediately without cache clearing |

---

## Permission Model

All abilities follow WordPress's native capability system:

| Operation Type   | Required Capability             |
| ---------------- | ------------------------------- |
| Search posts     | `read`                          |
| Get post         | `read_post` (for specific post) |
| Get link report  | `read_post` (for specific post) |
| Validate links   | `read_post` (for specific post) |
| Add link         | `edit_post` (for source post)   |
| Update link      | `edit_post` (for source post)   |
| Remove link      | `edit_post` (for source post)   |
| Batch operations | `edit_post` (for source post)   |
| Plugin settings  | `manage_options`                |

---

## Error Handling

### Standard Error Codes

| Error Code                | HTTP Status | Description                                    |
| ------------------------- | ----------- | ---------------------------------------------- |
| `post_not_found`          | 404         | The specified post does not exist              |
| `invalid_post_type`       | 400         | Post type is not in configured supported types |
| `permission_denied`       | 403         | User lacks required capability                 |
| `post_locked`             | 423         | Post is being edited by another user           |
| `target_not_published`    | 400         | Target post exists but is not published        |
| `anchor_not_found`        | 400         | Anchor text not found in post content          |
| `occurrence_out_of_range` | 400         | Specified occurrence exceeds available matches |
| `anchor_spans_elements`   | 400         | Anchor text spans multiple HTML elements       |
| `link_not_found`          | 400         | No link matching the identifier was found      |
| `invalid_identifier`      | 400         | Link identifier object is malformed            |
| `batch_limit_exceeded`    | 400         | Batch operation exceeds maximum items          |
| `validation_error`        | 400         | Input validation failed                        |

### Error Response Format

```json
{
    "code": "anchor_not_found",
    "message": "The anchor text 'example phrase' was not found in the post content.",
    "data": {
        "status": 400,
        "anchor_text": "example phrase",
        "post_id": 123
    }
}
```

---

## MCP Exposure

All abilities are exposed via MCP with `mcp.public: true` by default.

### Ability Registration Pattern

```php
wp_register_ability( 'internal-links-api/search-posts', array(
    'label'              => __( 'Search Posts', 'internal-links-api' ),
    'description'        => __( 'Search for posts to find internal link targets', 'internal-links-api' ),
    'category'           => 'internal-links',
    'input_schema'       => $input_schema,
    'output_schema'      => $output_schema,
    'execute_callback'   => array( $this, 'execute_search_posts' ),
    'permission_callback' => array( $this, 'check_read_permission' ),
    'meta'               => array(
        'show_in_rest' => true,
        'mcp'          => array(
            'public' => true,
            'type'   => 'tool',
        ),
    ),
    'annotations'        => array(
        'readonly' => true,
    ),
) );
```

### Ability Annotations

| Ability            | Readonly | Destructive             |
| ------------------ | -------- | ----------------------- |
| search-posts       | ✅       | ❌                      |
| get-post           | ✅       | ❌                      |
| validate-links     | ✅       | ❌                      |
| get-link-report    | ✅       | ❌                      |
| add-link           | ❌       | ❌                      |
| update-link        | ❌       | ❌                      |
| remove-link        | ❌       | ✅ (when action=delete) |
| batch-add-links    | ❌       | ❌                      |
| batch-remove-links | ❌       | ✅ (when action=delete) |

---

## WordPress Integration

### Hooks Used

| Hook                               | Purpose                            |
| ---------------------------------- | ---------------------------------- |
| `wp_abilities_api_init`            | Register all abilities             |
| `wp_abilities_api_categories_init` | Register "Internal Links" category |
| `admin_menu`                       | Add settings page                  |
| `wp_insert_post_data`              | Create revision on modification    |
| `wp_check_post_lock`               | Respect post edit locks            |

### Hooks Provided

| Hook                                      | Type   | Description                             |
| ----------------------------------------- | ------ | --------------------------------------- |
| `internal_links_api_supported_post_types` | Filter | Modify supported post types             |
| `internal_links_api_before_add_link`      | Action | Fires before adding a link              |
| `internal_links_api_after_add_link`       | Action | Fires after adding a link               |
| `internal_links_api_before_remove_link`   | Action | Fires before removing a link            |
| `internal_links_api_after_remove_link`    | Action | Fires after removing a link             |
| `internal_links_api_link_attributes`      | Filter | Modify link attributes before insertion |

---

## Revision History

| Version | Date       | Description                   |
| ------- | ---------- | ----------------------------- |
| 1.0.0   | 2025-05-20 | Initial requirements document |
