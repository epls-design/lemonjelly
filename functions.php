<?php

/**
 * Child Theme Functions
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

if (!is_admin() && !function_exists('get_field')) {
  echo 'Please install and activate Advanced Custom Fields Pro';
  die();
}

// Global array of blocks to register for this child theme
$lemonjelly_blocks  = array('lemonjelly-hero', 'feedback-map', 'image-compare', 'timeline', 'timeline-vertical');

$lemonjelly_includes = array(
  'helpers',
  'acf',
  'blocks',
  'enqueue',
  'customiser'
);

foreach ($lemonjelly_includes as $file) {
  $filepath = get_stylesheet_directory() . '/inc/' . $file . '.php';
  if (file_exists($filepath)) require_once $filepath;
}

foreach ($lemonjelly_blocks as $block) {
  $directory = get_stylesheet_directory() . '/blocks/' . $block . '/functions.php';

  if (file_exists($directory))
    include_once $directory;
}