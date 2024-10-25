<?php

/**
 * Functions necessary for the image compare block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'lemonjelly_register_twentytwenty');
add_action('admin_enqueue_scripts', 'lemonjelly_register_twentytwenty');

function lemonjelly_register_twentytwenty() {
  wp_register_script(
    'twentytwenty-init',
    get_stylesheet_directory_uri() . '/blocks/image-compare/scripts.js',
    array('twentytwenty'),
    filemtime(get_stylesheet_directory() . '/blocks/image-compare/scripts.js'),
    true
  );

  wp_register_script(
    'twentytwenty',
    get_stylesheet_directory_uri() . '/blocks/image-compare/lib/jquery.twentytwenty.js',
    array('jquery', 'jquery-event-move'),
    filemtime(get_stylesheet_directory() . '/blocks/image-compare/lib/jquery.twentytwenty.js'),
    true
  );
  wp_register_script(
    'jquery-event-move',
    get_stylesheet_directory_uri() . '/blocks/image-compare/lib/jquery.event.move.js',
    array('jquery'),
    filemtime(get_stylesheet_directory() . '/blocks/image-compare/lib/jquery.event.move.js'),
    true
  );
}

/**
 * Image Size for the Image Compare block
 */
add_action('after_setup_theme', function () {
  add_image_size('large_landscape', 1920, 1080, true);
});