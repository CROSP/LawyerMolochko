<?php
/**
 * Docker wp-config for Lawyermolochko (Адвокатське бюро).
 * Used as wp-config.php in Docker on remote host (crosphz).
 */

// Database — from environment
define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) ?: 'wordpress' );
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) ?: 'wordpress' );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) ?: 'wordpress_password_change_me' );
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: 'db:3306' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

// Production hardening: no theme/plugin editor in admin; force HTTPS for wp-admin
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', true );
}

// Salts (same as main wp-config.php; rotate in production if needed)
define( 'AUTH_KEY',         'H3fNkBtaw)PeawSW#Q3Q{[n|W1U%|9NE3UYWy-{Fg*>iC0PCq4^>{cEI,g(d2K+4' );
define( 'SECURE_AUTH_KEY',  '/aD5:Wvc,_!#;[,>qcnwy$w>q3-i6q,]IWz#[hMc`uu~A2[dfL5F7c`@1q=oHUlZ' );
define( 'LOGGED_IN_KEY',    'g$dbw8t5.|jA3vha-W1ZYz{JD%db.Z[MWKPTZTu)G6X5xEpIz*?r5n/`}(G>|&a-' );
define( 'NONCE_KEY',        '|m|o~hM3>2p$+s4+U]dFxTCvEK ^0Nfwu[6^;tx1n .s:vm_b|z/wnOtA.guFE:8' );
define( 'AUTH_SALT',        ')UzNVTv3c)Thh#`0G(R$8Si&`]+@+Am{F[Y&.7&YIw?3jY&Vz;&HVa.ivbl-U{(' );
define( 'SECURE_AUTH_SALT', 'vN].2j{>`oN2C$>0ZXJ[yp)cdfU4DKs0-~[>SaF`s| ?Mf4x-le8+fb5)E:5]kww' );
define( 'LOGGED_IN_SALT',   '.,qUZQACFkAnHQq8OG!~T{p9v4D@omIPlWsA+fMfr+8WkvGcD&@yP0p>;GK2-5kh' );
define( 'NONCE_SALT',       '.u0N[1G@_52YOeGnHV6ghyUGv+k:sx]G,IO>-g/?;hF}QJ5ztO6I+W@*3fy5PXN|' );

$table_prefix = 'wp_';

define( 'FS_METHOD', 'direct' );

// Behind Caddy/reverse proxy: trust X-Forwarded-Proto so redirects don't loop (HTTP→HTTPS→HTTP…)
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
}

if ( getenv( 'WORDPRESS_URL' ) ) {
	define( 'WP_HOME', getenv( 'WORDPRESS_URL' ) );
	define( 'WP_SITEURL', getenv( 'WORDPRESS_SITEURL' ) ?: getenv( 'WORDPRESS_URL' ) );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
