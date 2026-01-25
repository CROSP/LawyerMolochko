# Elementor MCP Optimization Plan

## Problem Summary

The current implementation has severe efficiency issues:

1. **Full page data transfers** - No partial reads/updates
2. **No widget indexing** - Can't find widgets across site without scanning all pages
3. **No caching** - Every request parses full Elementor data
4. **No bulk operations** - Find/replace requires full page iteration
5. **Stateless overhead** - HTTP mode recreates entire server per request

## Solution Architecture

### 1. Widget Index Cache System

**Purpose**: Fast lookup of widgets across the entire site

**Implementation**:
```
includes/
  cache/
    class-widget-index.php      - Main indexing engine
    class-cache-manager.php     - Transient/object cache wrapper
    class-index-builder.php     - Background index builder
```

**Features**:
- Maintains an index of all widget instances across all pages
- Structure: `post_id => [widget_id => {type, settings_hash, path}]`
- Allows queries like:
  - "Find all heading widgets on the site"
  - "Which pages use the 'slider' widget?"
  - "Get all widgets with text containing 'hello'"
- Invalidated automatically when pages are updated
- Rebuilt in background using WP-Cron

**Storage**: WordPress transients with fallback to custom table for large sites

### 2. Partial Update System

**Purpose**: Update individual elements without full page replacement

**New Tools**:
```
includes/tools/documents/
  class-get-element.php          - Get specific element by ID/path
  class-update-element.php       - Update single element
  class-find-elements.php        - Find elements by criteria
  class-bulk-update-elements.php - Update multiple elements
```

**API Design**:
```php
// Get single widget
get_element(post_id: 123, element_id: 'abc123')
get_element(post_id: 123, path: '[0].elements[2]')

// Update single widget
update_element(
  post_id: 123,
  element_id: 'abc123',
  settings: {heading_title: 'New Title'}
)

// Partial settings merge (not full replacement)
update_element(
  post_id: 123,
  element_id: 'abc123',
  settings: {color: 'red'},
  merge: true  // Only updates 'color', keeps other settings
)
```

### 3. Smart Caching Layer

**Purpose**: Avoid re-parsing Elementor data on every request

**Implementation**:
```
includes/cache/
  class-page-cache.php        - Caches parsed page data
  class-element-cache.php     - Caches individual elements
  class-cache-warmer.php      - Pre-warms cache for active pages
```

**Features**:
- Cache parsed JSON as PHP arrays (avoid repeated json_decode)
- Element-level caching (cache frequently accessed widgets)
- Smart invalidation (only clear affected elements)
- LRU eviction for memory limits
- Uses WP Object Cache (Redis/Memcached if available)

**Cache Keys**:
```
elementor_mcp_page_{post_id}_{version_hash}
elementor_mcp_element_{post_id}_{element_id}
elementor_mcp_index_site_widgets_{version}
```

### 4. Find & Replace Engine

**Purpose**: Efficiently search and modify content across pages

**New Tool**:
```
includes/tools/bulk/
  class-find-and-replace.php  - Main find/replace tool
  class-search-engine.php     - Search implementation
```

**Features**:
```php
find_and_replace(
  find: 'old text',
  replace: 'new text',
  scope: {
    post_ids: [123, 456],       // Optional: specific pages
    widget_types: ['heading'],  // Optional: specific widget types
    setting_keys: ['title']     // Optional: specific settings
  },
  dry_run: true,                // Preview changes first
  limit: 100                    // Safety limit
)
```

**Optimizations**:
- Uses widget index to limit page scanning
- Batch processing with progress tracking
- Transaction-like rollback on errors
- Detailed change report before applying

### 5. Optimized Page Operations

**Purpose**: Faster common operations

**New Tools**:
```
includes/tools/documents/
  class-get-page-widgets.php     - List only widgets on a page (no full data)
  class-clone-element.php        - Duplicate widget
  class-move-element.php         - Reorder widgets
  class-delete-element.php       - Remove widget
```

**Features**:
```php
// Fast widget listing (metadata only)
get_page_widgets(post_id: 123, include_settings: false)
// Returns: [{id, type, name}, {id, type, name}, ...]

// Fast widget count
count_widgets(post_id: 123, widget_type: 'heading')
// Returns: 5

// Widget tree structure only
get_page_structure(post_id: 123, depth: 2)
// Returns nested structure without full settings
```

### 6. Background Index Building

**Purpose**: Keep widget index up-to-date without blocking requests

**Implementation**:
```
includes/background/
  class-index-builder-job.php   - WP Background Process
  class-indexer-scheduler.php   - WP-Cron integration
```

**Features**:
- Hooks into post save to queue index updates
- Batch processing of 10-20 posts at a time
- Runs in background via WP-Cron
- Progress tracking and resumable
- Manual rebuild via WP-CLI command

**Hooks**:
```php
// Trigger re-index on save
add_action('elementor/editor/after_save', [$this, 'queue_index_update']);

// Batch index builder
add_action('elementor_mcp_build_index', [$this, 'process_batch']);
```

### 7. HTTP Mode Optimization

**Purpose**: Reduce stateless overhead in REST API mode

**Changes to class-rest-handler.php**:

**Current (lines 86-113)**:
- Creates new Logger, Security, Server, Tool_Manager on every request
- Auto-discovers and registers all tools every time

**Optimized**:
```php
// Use WordPress object cache for tool registry
$cached_tools = wp_cache_get('elementor_mcp_tools');

if (false === $cached_tools) {
  $tool_manager = new Tools\Tool_Manager();
  $tool_manager->auto_discover_tools();
  $cached_tools = $tool_manager->get_all_tools();
  wp_cache_set('elementor_mcp_tools', $cached_tools, '', 3600);
}

// Reuse tool instances from cache
foreach ($cached_tools as $tool_name => $tool_instance) {
  $server->register_tool($tool_name, $tool_instance);
}
```

**Additional Optimizations**:
- Cache JSON-RPC handler reflection calls
- Lazy-load tool classes (only load when called)
- Pool Logger/Security instances

## Implementation Priority

### Phase 1: Core Caching (Week 1)
1. Implement Cache_Manager class
2. Add page-level caching to get_elementor_data
3. Optimize HTTP mode tool loading

### Phase 2: Partial Updates (Week 2)
1. Implement get_element tool
2. Implement update_element tool
3. Add element-level cache invalidation

### Phase 3: Widget Indexing (Week 3)
1. Implement Widget_Index class
2. Add post save hooks for index updates
3. Implement find_elements tool

### Phase 4: Bulk Operations (Week 4)
1. Implement find_and_replace tool
2. Add background index builder
3. Implement bulk_update_elements tool

### Phase 5: Advanced Features (Week 5+)
1. Add get_page_widgets optimized tool
2. Implement clone/move/delete element tools
3. Add WP-CLI commands for cache management

## Performance Targets

| Operation | Current | Target | Improvement |
|-----------|---------|--------|-------------|
| Get full page data | 500ms | 50ms (cached) | 10x |
| Update single widget | 800ms | 100ms | 8x |
| Find widget across site | N/A (impossible) | 200ms | ∞ |
| Find & replace (100 pages) | 50s+ | 5s | 10x |
| HTTP request overhead | 300ms | 30ms | 10x |

## Database Schema (if needed)

For very large sites (>1000 pages), consider custom table:

```sql
CREATE TABLE {$wpdb->prefix}elementor_mcp_index (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  post_id bigint(20) unsigned NOT NULL,
  element_id varchar(255) NOT NULL,
  element_type varchar(100) NOT NULL,
  element_path text NOT NULL,
  settings_hash varchar(64) NOT NULL,
  searchable_text longtext,
  last_updated datetime NOT NULL,
  PRIMARY KEY (id),
  KEY post_id (post_id),
  KEY element_type (element_type),
  KEY element_id (element_id),
  FULLTEXT searchable_text (searchable_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Configuration Options

New wp-config.php constants:

```php
// Enable/disable caching
define('ELEMENTOR_MCP_CACHE_ENABLED', true);

// Cache expiration (seconds)
define('ELEMENTOR_MCP_CACHE_TTL', 3600);

// Max cached pages
define('ELEMENTOR_MCP_CACHE_MAX_PAGES', 100);

// Enable widget indexing
define('ELEMENTOR_MCP_INDEXING_ENABLED', true);

// Use custom index table vs transients
define('ELEMENTOR_MCP_INDEX_USE_TABLE', false);

// Background index batch size
define('ELEMENTOR_MCP_INDEX_BATCH_SIZE', 20);
```

## Backward Compatibility

- All existing tools continue to work unchanged
- New tools are additive, not replacing old ones
- Cache can be disabled via config
- Graceful degradation if index not built

## Testing Strategy

1. **Unit Tests**: Test each cache/index class in isolation
2. **Integration Tests**: Test with real Elementor pages
3. **Performance Tests**: Benchmark before/after
4. **Load Tests**: Test with 1000+ pages
5. **Edge Cases**: Empty cache, stale cache, concurrent updates

## Monitoring & Debug

Add debug tools:
```php
// Check cache status
get_cache_stats() => {hit_rate: 0.85, size: '12MB', entries: 250}

// Check index status
get_index_stats() => {total_elements: 5000, last_update: '2024-01-15', coverage: 0.98}

// Clear caches
clear_all_caches() => true

// Rebuild index
rebuild_index(post_ids: null) => {queued: 500, estimated_time: '5 minutes'}
```

## Success Metrics

- 90%+ cache hit rate for repeat requests
- <100ms response time for cached page data
- <5s full site widget search
- <10s find/replace across 100 pages
- <50ms HTTP mode overhead per request
