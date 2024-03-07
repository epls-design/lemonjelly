<?php

/**
 * Functions necessary for the image compare block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'ezpzconsultations_enqueue_scripts');

function ezpzconsultations_enqueue_scripts() {
  $theme = wp_get_theme();

  $version = $theme->get('Version');

  wp_enqueue_script(
    'ezpz-consultations-twentytwenty',
    get_stylesheet_directory_uri() . '/blocks/image-compare/twentytwenty.min.js',
    array('jellypress-scripts'),
    $version,
    true
  );
  wp_enqueue_script(
    'ezpz-consultations-twentytwenty-js',
    get_stylesheet_directory_uri() . '/blocks/image-compare/jquery.twentytwenty.js',
    array('jellypress-scripts'),
    $version,
    true
  );
  wp_enqueue_script(
    'ezpz-consultations-event-move',
    get_stylesheet_directory_uri() . '/blocks/image-compare/jquery.event.move.js',
    array('jellypress-scripts'),
    $version,
    true
  );
}
