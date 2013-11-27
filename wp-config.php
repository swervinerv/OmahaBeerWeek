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

// define('WP_HOME','http://omahabeerweek.dev');
// define('WP_SITEURL','http://omahabeerweek.dev');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'Wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'TdBFG7QzNatRThMB');

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
define('AUTH_KEY',         '9Pv~6DUPp``~|I]@&a95-%6_9e!7Tlv-ypy%wAbzK?=(RQzrPSi{$gh0f&=nj<=8');
define('SECURE_AUTH_KEY',  'O{s5+jfw~#Wh$$.0cRBceSQJa3/QsB,.,!-<t89Vy U93f |R]/[UD?$6ZV{ng1-');
define('LOGGED_IN_KEY',    'b/o0jLgJQOlst@!`U@{^l!W(>6f.`si=8LVp0@B(z*-C+)v*+FE/+Zv@am3@v@N>');
define('NONCE_KEY',        '29?D:&++.SmKT0yV)0>YmUTs~GUd 4]|k6sPI,-0..0o6l4Fqe7WKzj @mJ3+2WD');
define('AUTH_SALT',        '*@}TD5>bz8f-BBl7:3 U6wsA=qlaxT?ctH0;$qu)6oO-jc=yE% MsR$/E.lGL(]g');
define('SECURE_AUTH_SALT', '6r5Hnj>OH9>z7DVW 4Ja<0_CDnzHx~M0E?ug$g{Fw5eaW9b8LyeIPj]Z;Y;n!,e.');
define('LOGGED_IN_SALT',   'E- 9V<|M)Ex9.+KQ[(;NVb.>(41AO9-!aOK_a`!@u-m<9> _CA;v~7:F,&Df4-N2');
define('NONCE_SALT',       '|z-i_!0#fE!A~o7=[_:0I+YRM/]})yI~FSyt||I=RO&2`hb]&~=uNa9G(~:dl[*N');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
