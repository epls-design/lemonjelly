<?php

/**
 * Plugin Name:  EZPZ Consultations
 * Plugin URI:   null
 * Description:  A plugin designed to drop over the top of the ezpz-jellypress boilerplate theme to add functionality suitable for consultation websites.
 * Version:      1.0.0
 * Author:       EPLS
 * Author URI:   https://epls.design
 * Text Domain:  ezpz-consultations
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('ezpzConsultations')) {
  class ezpzConsultations {

    // Initialize the class
    function __construct() {
    }
  }
}

/**
 * Initialize the plugin
 */
new ezpzConsultations();