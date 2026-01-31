<?php
/**
 * Fix broken Elementor frontend (no text, safe-mode only).
 * Run: ddev exec "php /var/www/html/fix-broken-elementor.php"
 *
 * 1. Clears Elementor CSS/data cache
 * 2. Disables our custom mu-plugin (align fix) to rule it out
 * 3. Turns off Elementor Safe Mode so you can test normal frontend
 */

$wp_load = __DIR__ . '/wp-load.php';
if (!file_exists($wp_load)) {
    die("wp-load.php not found. Run: ddev exec 'php /var/www/html/fix-broken-elementor.php'\n");
}

define('WP_USE_THEMES', false);
require_once $wp_load;

if (!defined('ABSPATH')) {
    die('ABSPATH not defined');
}

echo "=== Elementor recovery ===\n\n";

// 1. Clear Elementor cache
if (class_exists('\Elementor\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    echo "[OK] Elementor CSS/data cache cleared.\n";

    try {
        if (class_exists('\Elementor\Core\Files\Manager') && method_exists(\Elementor\Plugin::$instance->kits_manager, 'get_active_kit')) {
            $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
            if ($kit && method_exists($kit, 'regenerate_css')) {
                $kit->regenerate_css();
                echo "[OK] Global kit CSS regenerated.\n";
            }
        }
    } catch (\Throwable $e) {
        // ignore
    }
} else {
    echo "[SKIP] Elementor not active.\n";
}

// 2. Disable our mu-plugin (align fix) – can cause conflict in some setups
$mu = (defined('WPMU_PLUGIN_DIR') && WPMU_PLUGIN_DIR) ? rtrim(WPMU_PLUGIN_DIR, '/') : (ABSPATH . 'wp-content/mu-plugins');
$fix_file = $mu . '/elementor-section-c7f0487-align-fix.php';
$bak_file = $mu . '/elementor-section-c7f0487-align-fix.php.bak';

if (file_exists($fix_file)) {
    if (@rename($fix_file, $bak_file)) {
        echo "[OK] Disabled mu-plugin: elementor-section-c7f0487-align-fix.php -> .bak\n";
    } else {
        echo "[WARN] Could not rename mu-plugin. Manually rename:\n      $fix_file -> .bak\n";
    }
} else {
    echo "[INFO] elementor-section-c7f0487-align-fix.php not found (already disabled?).\n";
}

// 3. Turn off Elementor Safe Mode so normal frontend can load
delete_option('elementor_safe_mode');
delete_option('elementor_safe_mode_token');
echo "[OK] Elementor Safe Mode turned OFF.\n";

echo "\n--- Next steps ---\n";
echo "1. Open the site in a new incognito/private window (or clear cookies for the site).\n";
echo "2. If the frontend is still broken, restore the DB from backup:\n";
echo "   ddev import-db --file=backup_before_company_name_replace_20260125_152842.sql\n";
echo "   (or your most recent known-good backup)\n";
echo "3. In WP Admin: Elementor > Tools > Regenerate CSS & Data, then Regenerate Files.\n";
echo "4. If it works and you want the section align fix back, run:\n";
echo "   mv wp-content/mu-plugins/elementor-section-c7f0487-align-fix.php.bak wp-content/mu-plugins/elementor-section-c7f0487-align-fix.php\n";
echo "\nDone.\n";
