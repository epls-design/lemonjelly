<?php

/**
 * Child Theme ACF Hooks and Filters
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Adds ACF options page
 */
if (function_exists('acf_add_options_page')) {
  acf_add_options_page(
    array(
      'page_title'   => __('Theme Designer', 'lemonjelly'),
      'menu_title'  => __('Theme Designer', 'lemonjelly'),
      'menu_slug'   => 'theme-designer',
      'capability'  => 'edit_posts',
      'icon_url' => 'dashicons-art',
      'position' => 2,
      'autoload' => true, // Speeds up load times
      'updated_message' => __("Successfully updated theme design", 'lemonjelly'),
    )
  );
}

/**
 * Save and load ACF local JSON
 */
add_action('acf/update_field_group', 'lemonjelly_save_acf_local_json', 50, 1);
function lemonjelly_save_acf_local_json($group) {
  $groups = array(
    'group_65d61eb9da7c1',
  );

  if (in_array($group['key'], $groups)) {
    add_filter('acf/settings/save_json', function () {
      return get_stylesheet_directory() . '/acf';
    });
  }
}
add_action('acf/settings/load_json', 'lemonjelly_load_acf_local_json', 50, 1);

// TODO: THIS DOESNT SEEM TO BE WORKING
function lemonjelly_load_acf_local_json($paths) {
  $paths[] = get_stylesheet_directory() . '/acf';

  // Check if there are any in the /blocks directory
  $blocks = get_stylesheet_directory() . '/blocks';
  if (is_dir($blocks)) {
    $block_folders = array_diff(scandir($blocks), array('..', '.'));
    foreach ($block_folders as $block_folder) {
      $block_folder_path = $blocks . '/' . $block_folder;
      if (is_dir($block_folder_path)) {
        $block_json = $block_folder_path . '/block.json';
        if (file_exists($block_json)) {
          $paths[] = $block_folder_path;
        }
      }
    }
  }

  return $paths;
}