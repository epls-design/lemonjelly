<?php

/**
 * Child Theme Enqueues
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Enqueue scripts and styles.
 */
add_action('wp_enqueue_scripts', 'lemonjelly_scripts');
add_action('wp_enqueue_scripts', 'lemonjelly_block_scripts');
add_action('admin_enqueue_scripts', 'lemonjelly_block_scripts');

/**
 * Hooks scripts and styles into the front end only (not the admin)
 *
 * @return void
 */
function lemonjelly_scripts() {

  $style_css_path = get_stylesheet_directory() . '/style.css';
  if (file_exists($style_css_path)) {
    wp_enqueue_style(
      'jellypress-child',
      get_stylesheet_directory_uri() . '/style.css',
      array('jellypress-styles'),
      filemtime($style_css_path)
    );
  }

  $override_css_path = get_stylesheet_directory() . '/lemonjelly.css';
  if (file_exists($override_css_path)) {
    wp_enqueue_style(
      'lemonjelly',
      get_stylesheet_directory_uri() . '/lemonjelly.css', // TODO: NEED TO SAVE INTO HERE - used to be consultation.css
      array('jellypress-child'),
      filemtime($override_css_path)
    );
  }

  $theme_min_path = get_stylesheet_directory() . '/js/theme.min.js';

  if (file_exists($theme_min_path)) {
    wp_enqueue_script(
      'ezpz-consultations-theme',
      get_stylesheet_directory_uri() . '/js/theme.min.js',
      array('jellypress-scripts'),
      filemtime($theme_min_path)
    );
  }
}

/**
 * Hooks assets that can run on the front end or the block editor
 */
function lemonjelly_block_scripts() {
}