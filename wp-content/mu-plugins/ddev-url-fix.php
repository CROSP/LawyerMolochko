<?php
/**
 * Plugin Name: DDEV URL Port Fix
 * Description: Dynamically fixes port numbers in WordPress URLs for DDEV environment
 * Version: 1.0.0
 * Author: DDEV
 */

if (getenv('IS_DDEV_PROJECT') == 'true') {
    
    /**
     * Get the correct port for the current request
     * Dynamically detects from SERVER_PORT or HTTP_HOST
     */
    function ddev_get_correct_port() {
        // First, try SERVER_PORT
        if (isset($_SERVER['SERVER_PORT'])) {
            return (int)$_SERVER['SERVER_PORT'];
        }
        
        // Fallback: extract from HTTP_HOST if port is included
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/:(\d+)$/', $_SERVER['HTTP_HOST'], $matches)) {
            return (int)$matches[1];
        }
        
        // Default based on protocol
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        return $is_https ? 8443 : 80;
    }
    
    /**
     * Get the current hostname (without port)
     */
    function ddev_get_current_host() {
        if (isset($_SERVER['HTTP_HOST'])) {
            return preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']);
        }
        return '';
    }
    
    /**
     * Check if a URL belongs to the current site
     * Compares hostnames dynamically
     */
    function ddev_is_current_site_url($url_host) {
        $current_host = ddev_get_current_host();
        
        // Exact match
        if ($url_host === $current_host) {
            return true;
        }
        
        // If current host is empty, allow any host (fallback)
        if (empty($current_host)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the correct port for a given protocol in ddev
     */
    function ddev_get_port_for_protocol($is_https) {
        // In ddev, HTTPS uses 8443, HTTP uses 8080
        return $is_https ? 8443 : 8080;
    }
    
    /**
     * Fix URL port dynamically
     * Works for any domain - no hardcoding
     */
    function ddev_fix_url_port($url) {
        if (empty($url) || !is_string($url)) {
            return $url;
        }
        
        // First, fix any trailing colons after port numbers (e.g., :8443:/ -> :8443/, :8443:? -> :8443?)
        $url = preg_replace('/:(\d+):(\/|\?|$)/', ':$1$2', $url);
        
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return $url;
        }
        
        $url_host = $parsed['host'];
        
        // Only fix URLs for the current site (dynamically detected)
        if (!ddev_is_current_site_url($url_host)) {
            return $url;
        }
        
        $is_https = $parsed['scheme'] === 'https';
        $current_port = isset($parsed['port']) ? (int)$parsed['port'] : null;
        
        // For site URLs in DDEV, always use HTTPS with port 8443
        $should_be_https = true;
        $correct_port = 8443;
        
        // Determine if we need to fix the protocol or port
        $needs_fix = false;
        
        // Force HTTPS for site URLs
        if (!$is_https) {
            $needs_fix = true;
        }
        
        // Fix port - should be 8443 for HTTPS
        if ($current_port === null || $current_port !== $correct_port) {
            $needs_fix = true;
        }
        
        if ($needs_fix) {
            // Rebuild URL with HTTPS and correct port
            $new_url = 'https://' . $parsed['host'] . ':' . $correct_port;
            
            if (isset($parsed['path'])) {
                $new_url .= $parsed['path'];
            }
            if (isset($parsed['query'])) {
                $new_url .= '?' . $parsed['query'];
            }
            if (isset($parsed['fragment'])) {
                $new_url .= '#' . $parsed['fragment'];
            }
            
            return $new_url;
        }
        
        return $url;
    }
    
    /**
     * Wrapper for URL filters
     */
    $url_fix_wrapper = function($url) {
        return ddev_fix_url_port($url);
    };
    
    /**
     * Clean up URLs with trailing colons after port
     */
    function ddev_clean_trailing_colon($url) {
        if (empty($url) || !is_string($url)) {
            return $url;
        }
        // Fix trailing colons after port numbers
        return preg_replace('/:(\d+):(\/|\?|$)/', ':$1$2', $url);
    }
    
    // Override database options early - clean trailing colons first
    add_filter('option_home', 'ddev_clean_trailing_colon', 0, 1);
    add_filter('option_siteurl', 'ddev_clean_trailing_colon', 0, 1);
    add_filter('option_home', $url_fix_wrapper, 1, 1);
    add_filter('option_siteurl', $url_fix_wrapper, 1, 1);
    
    // Fix all WordPress URL generation functions
    add_filter('home_url', $url_fix_wrapper, 1, 3);
    add_filter('site_url', $url_fix_wrapper, 1, 3);
    add_filter('plugins_url', $url_fix_wrapper, 1, 3);
    add_filter('content_url', $url_fix_wrapper, 1, 3);
    add_filter('includes_url', $url_fix_wrapper, 1, 3);
    
    // Fix enqueued scripts and styles
    add_filter('script_loader_src', $url_fix_wrapper, 1, 1);
    add_filter('style_loader_src', $url_fix_wrapper, 1, 1);
    
    // Fix upload directory URLs (critical for media files)
    add_filter('upload_dir', function($uploads) {
        if (isset($uploads['baseurl'])) {
            $uploads['baseurl'] = ddev_fix_url_port($uploads['baseurl']);
        }
        if (isset($uploads['url'])) {
            $uploads['url'] = ddev_fix_url_port($uploads['url']);
        }
        return $uploads;
    }, 1, 1);
    
    // Fix attachment URLs directly
    add_filter('wp_get_attachment_url', $url_fix_wrapper, 1, 1);
    add_filter('wp_get_attachment_image_src', function($image) {
        if (is_array($image) && isset($image[0])) {
            $image[0] = ddev_fix_url_port($image[0]);
        }
        return $image;
    }, 1, 1);
    
    // Fix any other URL that might slip through
    add_filter('the_guid', $url_fix_wrapper, 1, 1);
    
    // Fix Elementor preview URLs specifically
    add_filter('elementor/document/urls/preview', $url_fix_wrapper, 1, 1);
    add_filter('elementor/document/urls/wp_preview', $url_fix_wrapper, 1, 1);
    
    // Fix permalink URLs that might have trailing colons
    add_filter('post_link', $url_fix_wrapper, 1, 1);
    add_filter('page_link', $url_fix_wrapper, 1, 1);
    add_filter('post_type_link', $url_fix_wrapper, 1, 1);
}
