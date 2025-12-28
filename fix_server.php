<?php
$file = '/var/www/html/wp-content/plugins/elementor-mcp/server.php';
$content = file_get_contents($file);

// Find the line with HTTPS assignment and add SERVER_PORT after it
$content = preg_replace(
    '/(\$_SERVER\[\'HTTPS\'\] = parse_url\(\$site_url, PHP_URL_SCHEME\) === \'https\' \? \'on\' : \'off\';)/',
    "$1\n\$_SERVER['SERVER_PORT'] = parse_url(\$site_url, PHP_URL_PORT) ?: (parse_url(\$site_url, PHP_URL_SCHEME) === 'https' ? 443 : 80);",
    $content
);

file_put_contents($file, $content);
echo "Fixed server.php\n";
