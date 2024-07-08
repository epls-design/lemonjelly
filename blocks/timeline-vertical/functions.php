<?php

/**
 * Functions necessary for the timeline vertical block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'enqueue_timeline_scripts');

function enqueue_timeline_scripts() {

  // Define the script path and URI
  $script_path = get_stylesheet_directory() . '/blocks/timeline-vertical/scripts.js';
  $script_uri = get_stylesheet_directory_uri() . '/blocks/timeline-vertical/scripts.js';

  // Register the script without any circular dependency
  wp_register_script(
    'timeline-vertical-init',
    $script_uri,
    array('jquery'),
    filemtime($script_path),
    true
  );

  // Enqueue the registered script
  wp_enqueue_script('timeline-vertical-init');
}
