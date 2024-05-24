<?php

/**
 * Functions necessary for the feedback map block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
  wp_register_script(
    'feedback-map-init',
    get_stylesheet_directory_uri() . '/blocks/feedback-map/scripts.js',
    array('jquery', 'googlemaps'),
    filemtime(get_stylesheet_directory() . '/blocks/feedback-map/scripts.js'),
    true
  );
});
