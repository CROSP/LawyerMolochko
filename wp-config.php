<?php
/**
 * The base configuration for WordPress
 *
 * This file is configured for DDEV. DDEV automatically provides database
 * credentials through environment variables.
 *
 * @package WordPress
 */

// ** Database settings - Managed by DDEV ** //
// Database settings are now managed by wp-config-ddev.php
// Uncomment below if you need to override DDEV settings:
// define( 'DB_NAME', 'db' );
// define( 'DB_USER', 'db' );
// define( 'DB_PASSWORD', 'db' );
// define( 'DB_HOST', 'db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'H3fNkBtaw)PeawSW#Q3Q{[n|W1U%|9NE3UYWy-{Fg*>iC0PCq4^>{cEI,g(d2K+4');
define('SECURE_AUTH_KEY',  '/aD5:Wvc,_!#;[,>qcnwy$w>q3-i6q,]IWz#[hMc`uu~A2[dfL5F7c`@1q=oHUlZ');
define('LOGGED_IN_KEY',    'g$dbw8t5.|jA3vha-W1ZYz{JD%db.Z[MWKPTZTu)G6X5xEpIz*?r5n/`}(G>|&a-');
define('NONCE_KEY',        '|m|o~hM3>2p$+s4+U]dFxTCvEK ^0Nfwu[6^;tx1n .s:vm_b|z/wnOtA.guFE:8');
define('AUTH_SALT',        ')UzNVTv3c)Thh#`0G(R$8Si&`]+@+Am{F[Y&.7&YIw?3jY&Vz;&HVa.ivbl-U{(D');
define('SECURE_AUTH_SALT', 'vN].2j{>`oN2C$>0ZXJ[yp)cdfU4DKs0-~[>SaF`s| ?Mf4x-le8+fb5)E:5]kww');
define('LOGGED_IN_SALT',   '.,qUZQACFkAnHQq8OG!~T{p9v4D@omIPlWsA+fMfr+8WkvGcD&@yP0p>;GK2-5kh');
define('NONCE_SALT',       '.u0N[1G@_52YOeGnHV6ghyUGv+k:sx]G,IO>-g/?;hF}QJ5ztO6I+W@*3fy5PXN|');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

// Include for ddev-managed settings in wp-config-ddev.php.
$ddev_settings = dirname(__FILE__) . '/wp-config-ddev.php';
if (is_readable($ddev_settings) && !defined('DB_USER')) {
  require_once($ddev_settings);
}

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';


