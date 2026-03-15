<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'agrabaidb' );

/** Database username */
define( 'DB_USER', 'wsaun' );

/** Database password */
define( 'DB_PASSWORD', 'Luffyzoro1Pc@13' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'D(vlGB?PN*L._eFT5l:7 iUiS0G_uuc2X]S a|+8POH/Ov{puO>d~DtLgzr0wYpT' );
define( 'SECURE_AUTH_KEY',  '%= wQ0!Z=]X{QXgAMNj+Gym6HBaAD}Y_a FXIH^@r87sU=gKyHxH?(AtAs~$[]>a' );
define( 'LOGGED_IN_KEY',    'd!N+Qe WNIK0q]Gl@)LC28mX)c#}7XR3 U~i2[rzYY+%X|-Il_Y}tcL C.L]d=4q' );
define( 'NONCE_KEY',        'gqN6v|U4J<2j_f6)2,$jBUYFM`Uml~9DP.QE-#>A4[8;xL/pTX6H.Sjl571Kk2?6' );
define( 'AUTH_SALT',        '!zx_O,uj^!qU:R8P%+dD?kxjugyPp.h)lVSgv!R=}vX{fI)2NR(}<R2Q4r=s[AII' );
define( 'SECURE_AUTH_SALT', 'F!0Dat [8;3awGv{Y<s .x/MNM3} zRa>3Q8[G7bK4M[8b[1IUu{HssscEJD9b]`' );
define( 'LOGGED_IN_SALT',   '+4Zpfz_*^,#QZYvx5gYb@p_yR>-R(0j-gNrhV`*Dj&Gt>CxJu4[u=zzk?g$g=oNR' );
define( 'NONCE_SALT',       '%K}cLj_6VApvP}9sR/{#WMvDC}qe+-$f6CS7Dc4>Um/@Y&5c1TPTGcu{XNJi0#*Z' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
