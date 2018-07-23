<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'dev_woo');

/** MySQL database username */
define('DB_USER', 'dev_woouser');

/** MySQL database password */
define('DB_PASSWORD', 'Me!upnrunn@wpdriftio#1111');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'J|H`|q cphP+Bm?|m5)[QFYw|U-9+7vxg%}Jyt~wIP1 +wqe30Evm`F^!Hni&.(,');
define('SECURE_AUTH_KEY',  'lc9v2-Es:-}ax0w+#n-RX{4J8qC1PffrAgZc9.x(:b@WLf5G_]^hDW2R|%aqp]?`');
define('LOGGED_IN_KEY',    '}MuHVjf{H (?Vyv,+ZgH3aNH)w).uo%{ww+:48S4 FP&d,nq#Tby?k3sWx*MG`;|');
define('NONCE_KEY',        ')Q0_/8eLwj{#sd?m-G[SPd[j6wZk#H#<$x7Q2gP-iL{CjB%>Jh--SISh;Ymn$!Wd');
define('AUTH_SALT',        '~)VxN^C<s7BR1xJl+O+lL)D4+-XDd9n!e)rub!`||,3:[o?MZV#Yr_j?u8B:h+>m');
define('SECURE_AUTH_SALT', 'E]w5^MY)nsQaj3m5}m_+g1Qy0|Y_k|]$+.Y-R8A-]GV?X9$&as}_R|+%*-eIb11z');
define('LOGGED_IN_SALT',   'zute|<-zv-gU-.%)Q;c(-d7ZYrG@H+]Ab@v?Yyt_eml2Rj3FPtqD},zL3GeJoi1s');
define('NONCE_SALT',       'nhx&/[X<HXR?XAha^~_H^a>|(+shzYD(kBN]);oG?dp|#t+O2:/e5SFgyx*],t3,');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);
// Enable Debug logging to the /wp-content/debug.log file
define('WP_DEBUG_LOG', true);
// Disable display of errors and warnings
// define('WP_DEBUG_DISPLAY', false);
// @ini_set('display_errors',0);
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
