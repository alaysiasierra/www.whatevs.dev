<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'whatevsDBrqyyn');

/** MySQL database username */
define('DB_USER', 'whatevsDBrqyyn');

/** MySQL database password */
define('DB_PASSWORD', '6pnbkYshVK');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

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
define('AUTH_KEY',         '.DTHLatWl+*;x]9O#5OehKat_lp~:-[9DS9OShOds_|o~1G@:CV@!1GZCRVkRgwz[');
define('SECURE_AUTH_KEY',  '~w#5KG[9KZCds_hws_1w|4V8KZokNdo@s!0C|!0FR4VkzYo@z>r@}B0FQgcfv^0y3');
define('LOGGED_IN_KEY',    'V0Bv0}v,4F>JYkMcYnzc$}B^}BQQf^u1CShdtVhw|Od~:w!1:C|1GhKZk-dZo@[');
define('NONCE_KEY',        'PLap+et;DHShetWhx1-]5KG[9KZOdp~hds~:w1GR41GRhKk-|o@z[8@}CcFRgvsVg');
define('AUTH_SALT',        'NrYnz>r^$}B^QfIUjfvUjy3${7MI{AMbPfq^eaq*;t.2T6HWTiHWm#p*;9_~;DP1');
define('SECURE_AUTH_SALT', 'zv>3I{MbrQfbr^fu,E<3IET6IXybq${uq*{A.ETeHXTix6Lm+aq*+]t*;D2HSiLH');
define('LOGGED_IN_SALT',   'KgJZozcs}C|Rgv,!jz,4>7NYB7MYnQr^}u,^B,EUjMXnjybn$7*{AQMAbqTfuq*');
define('NONCE_SALT',       'nEB73IU7Mn$fq^${u.P2ITiLIXiya+{A*;EAP;DetWmx#.l+]6~9Pa_2;DS1HixW');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
