<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package jellypress
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

$is_menu_off_canvas = true; // change this to determine the menu type

$fields = get_fields('options');
$main_logo = $fields['main_logo'];

$theme_opts = ezpzconsultations_get_theme_opts();

//This is the code for the theme overwrite options that are in the customizer
$theme_overwrites = $theme_opts['themeoverwrites'];

//Theme overwrite options
$navbar_swap = $theme_overwrites['navbar_swap'];



$header_class = 'site-header';
//var_dump($theme_overwrites);

if ($navbar_swap) {

  $header_class .= ' alternate-header';
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="profile" href="https://gmpg.org/xfn/11">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php
  $font_family = htmlspecialchars_decode($theme_opts['globaltypography']['primary_import_font_family']);

  if (!empty($font_family)) {
    echo '<link href="' . $font_family . '" rel="stylesheet">';
  }
  ?>

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <div id="page" class="site">

    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'jellypress'); ?></a>

    <div id="masthead" class="<?= $header_class ?>">
      <nav id="site-navigation" class="navbar main-navigation">
        <div class="container">
          <div class="navbar-brand site-branding">
            <?php if ($main_logo) : ?>
              <span class="site-title navbar-item" style="display:block">
                <a class="site-logo navbar-item" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                  <?php echo wp_get_attachment_image($main_logo, 'full'); ?>
                </a>
              </span>
            <?php endif; ?>




            <button class="hamburger" type="button" aria-label="<?php _e('Toggles the website navigation', 'jellypress'); ?>" aria-controls="navbar-menu" aria-expanded="false">
              <span class="hamburger-label">Menu</span>
              <span class="hamburger-box">
                <span class="hamburger-inner"></span>
              </span>
            </button>
          </div>

          <?php if ($is_menu_off_canvas) : ?>
            <div id="navbar-menu" class="navbar-menu is-off-canvas">
              <div class="navbar-top">
                <button class="hamburger" type="button" aria-label="<?php _e('Toggles the website navigation', 'jellypress'); ?>" aria-controls="navbar-menu" aria-expanded="false">
                  <span class="hamburger-label">Menu</span>
                  <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                  </span>
                </button>
              </div>
            <?php else : ?>
              <div id="navbar-menu" class="navbar-menu">
              <?php endif; ?>

              <div class="navbar-end">
                <?php
                wp_nav_menu(array(
                  'theme_location' => 'menu-primary',
                  'menu_id'        => 'primary-menu',
                  'container'      => false,
                ));
                ?>
              </div>

              </div>
            </div>
        </div>
      </nav>
    </div>
    <div id="content" class="site-content
">