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


function jellypress_allow_kml_upload($mimes) {
  $mimes['kml'] = 'text/xml';
  $mimes['kmz'] = 'application/zip';
  return $mimes;
}
add_filter('upload_mimes', 'jellypress_allow_kml_upload');
