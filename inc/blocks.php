<?php

/**
 * Child Theme Block Registration and Filters
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Register ACF Blocks
 */
add_action('acf/init', function () {
  global $lemonjelly_blocks;
  foreach ($lemonjelly_blocks as $slug) {
    $file_path = get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/block.json';
    if (file_exists(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/block.json')) {
      register_block_type(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug);
    }
  }
}, 20);

/**
 * Register Child Theme Blocks and remove hero blocks set in the parent theme
 */
add_filter('ezpz_allowed_blocks', function ($allowed_blocks) {

  global $lemonjelly_blocks;
  foreach ($lemonjelly_blocks as $slug) {
    $allowed_blocks[] = 'ezpz/' . $slug;
  }

  $blocks_to_unregister = ['ezpz/hero-post', 'ezpz/hero-page'];
  foreach ($blocks_to_unregister as $block) {
    unset($allowed_blocks[array_search($block, $allowed_blocks)]);
  }

  return $allowed_blocks;
});

/**
 * Sets up child theme block template
 * @return void
 */
add_action('init', function () {
  $post_type_object = get_post_type_object('page');
  $post_type_object->template = array(
    array('ezpz/lemonjelly-hero', array(
      'lock' => array(
        'move'   => true,
        'remove' => true,
      ),
    )),
    array('ezpz/section', array()),
  );

  $post_type_object = get_post_type_object('post');
  $post_type_object->template = array(
    array('ezpz/lemonjelly-hero', array(
      'lock' => array(
        'move'   => true,
        'remove' => true,
      ),
    )),
    array('ezpz/section', array()),
  );
}, 20);

/**
 * Remove block templates set in the parent theme
 */
add_action('after_setup_theme', function () {
  remove_action('init', 'jellypress_block_templates', 20);
});
