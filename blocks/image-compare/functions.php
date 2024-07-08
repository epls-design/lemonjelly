<?php

/**
 * Functions necessary for the image compare block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'ezpzconsultations_enqueue_scripts');

function ezpzconsultations_enqueue_scripts() {
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

  wp_register_script(
    'jquery-timeline-vertical',
    get_stylesheet_directory_uri() . '/blocks/timeline-vertical/scripts.js',
    array('jquery'),
    filemtime(get_stylesheet_directory() . '/blocks/timeline-vertical/scripts.js'),
    true
  );
}
