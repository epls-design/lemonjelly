<?php

/**
 * Functions necessary for the image compare block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'lemonjelly_register_timeline');
add_action('admin_enqueue_scripts', 'lemonjelly_register_timeline');

function lemonjelly_register_timeline() {
  wp_register_script(
    'timeline-init',
    get_stylesheet_directory_uri() . '/blocks/timeline/scripts.js',
    array('jquery'),
    filemtime(get_stylesheet_directory() . '/blocks/timeline/scripts.js'),
    true
  );
}