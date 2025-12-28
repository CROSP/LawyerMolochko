<?php
$file = '/var/www/html/wp-content/plugins/elementor-mcp/server.php';
$content = file_get_contents($file);

// Move SERVER_PORT setting before WordPress load
$content = str_replace(
    "// Load WordPress\n\$wp_load_path = __DIR__ . '/../../../wp-load.php';\nif (file_exists(\$wp_load_path)) {\n    require_once \$wp_load_path;",
    "// Parse command line arguments\n\$options = getopt('', ['site-url:']);\n\$site_url = \$options['site-url'] ?? 'http://localhost';\n\n// Set up WordPress environment BEFORE loading WordPress\n\$_SERVER['HTTP_HOST'] = parse_url(\$site_url, PHP_URL_HOST);\n\$_SERVER['REQUEST_URI'] = '/';\n\$_SERVER['HTTPS'] = parse_url(\$site_url, PHP_URL_SCHEME) === 'https' ? 'on' : 'off';\n\$_SERVER['SERVER_PORT'] = parse_url(\$site_url, PHP_URL_PORT) ?: (parse_url(\$site_url, PHP_URL_SCHEME) === 'https' ? 443 : 80);\n\n// Load WordPress\n\$wp_load_path = __DIR__ . '/../../../wp-load.php';\nif (file_exists(\$wp_load_path)) {\n    require_once \$wp_load_path;",
    $content
);

// Remove the duplicate environment setup that was added earlier
$content = preg_replace('/\$_SERVER\[\'HTTP_HOST\'\].*?\$_SERVER\[\'HTTPS\'\].*?\$_SERVER\[\'SERVER_PORT\].*?;\n\n/s', '', $content);

file_put_contents($file, $content);
echo "Fixed server.php - moved SERVER_PORT before WordPress load\n";
