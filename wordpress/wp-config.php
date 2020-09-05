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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'bitnami_wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'G2kMXxZfmcw9D91v]OKgH:.A.GNVxIR*[CG||O_^ibB1z=W95F9uuh!mIrxT#AOY' );
define( 'SECURE_AUTH_KEY',  'vg3t4YZU:<@>nUsRl.=!)dS(Y]R1mz^/IL[%  TZlYb&$hl*ZGaz,Zerx}-rOJ|=' );
define( 'LOGGED_IN_KEY',    ' V<:_X888GM&n/x2#v}v2Uv:DK%El&%&|S*l&k4]7~7VKG-9IM3|$rEICIu+T2g{' );
define( 'NONCE_KEY',        'I@F5+.#K gOEj*p^[[1Y[!o@GeK00YKAWJo^=_3nJ,V%^d~c^mLQ{*PD]d66A,n;' );
define( 'AUTH_SALT',        '(BoH2}!wctYd`{CL*Y<QM0I*xGA28IH1L/)J6SM$]8o[,vr&>T=( I`g}+i`vY1{' );
define( 'SECURE_AUTH_SALT', 'N(:54epN.;re+M7=}JF$)R2`vH~5b@uz6s[|IOPy{RO~+zwrg_|>hax-F`73|Irc' );
define( 'LOGGED_IN_SALT',   'zqhKyg!kX)}]Rm9,;HvOF~ya&ji}+w!Y;#jI~?qE^(3tzoS[x9|R8GW,tS&KqfmK' );
define( 'NONCE_SALT',       '*+vJ`t;*g|#To<~bI%XgcdfTMO:3)YMjCU}Bp<Lgm!uX96U::3~L|?PC?OY4=FX$' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
