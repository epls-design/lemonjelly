<?php

/**
 * Child Theme Functions
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Enqueue the child theme
 */
add_action('wp_enqueue_scripts', 'ezpzconsultations_enqueue_styles');
function ezpzconsultations_enqueue_styles() {
  $theme = wp_get_theme();

  $version = $theme->get('Version');

  wp_enqueue_style(
    'ezpz-consultations',
    get_stylesheet_directory_uri() . '/style.css',
    array('jellypress-styles'),
    $version
  );

  wp_enqueue_script(
    'ezpz-consultations',
    get_stylesheet_directory_uri() . '/js/theme.min.js',
    array('jellypress-scripts'),
    $version,
    true
  );
}

/**
 * Add ACF Option Page for Theme Designer
 */
if (function_exists('acf_add_options_page')) {
  acf_add_options_page(
    array(
      'page_title'     => __('Theme Designer', 'ezpzconsultations'),
      'menu_title'    => __('Theme Designer', 'ezpzconsultations'),
      'menu_slug'     => 'theme-designer',
      'capability'  => 'edit_posts',
      'icon_url' => 'dashicons-admin-customizer',
      'position' => 2,
      'autoload' => true,
    )
  );
}
/**
 * Sync ACF Json specific to the child theme into the child theme directory /acf
 * Add any more groups to the $groups array as required
 */
function ezpzconsultations_load_acf_local_json($paths) {
  $paths[] = get_stylesheet_directory() . '/acf';
  return $paths;
}
function ezpzconsultations_save_acf_local_json($group) {
  $groups = array(
    'group_647df582a6e08'
  );
  if (in_array($group['key'], $groups)) {
    add_filter('acf/settings/save_json', function () {
      return get_stylesheet_directory() . '/acf';
    });
  }
}
add_action('acf/update_field_group', 'ezpzconsultations_save_acf_local_json', 1, 1);
add_action('acf/settings/load_json', 'ezpzconsultations_load_acf_local_json', 1, 1);

/**
 * Helper function to get all of our ACF options and process them for use in the theme.
 * Best to use this function where possible to keep things dry.
 */
function ezpzconsultations_get_theme_opts() {
  $theme_opts = get_fields('options');

  // Process them as required - here we are adding a name and slug to colors
  if (!empty($theme_opts['colors'])) {
    foreach ($theme_opts['colors'] as $key => $color) {
      $name = ucwords(str_replace('_', ' ', $key));
      $slug = str_replace('_', '-', $key);
      $theme_opts['colors'][$slug] = [
        'name' => $name,
        'value' => $color,
      ];
      unset($theme_opts['colors'][$key]);
    }
  }

  return $theme_opts;
}

/**
 * Creates theme.json if it doesn't exist by copying theme.json from the parent theme
 * This allows us to use the theme.json to set the color palette dynamically from the ACF options
 */
add_action('init', 'ezpzconsultations_create_theme_json', 20);
function ezpzconsultations_create_theme_json() {
  $theme_json = get_stylesheet_directory() . '/theme.json';
  if (!file_exists($theme_json)) {
    $theme_json = get_template_directory() . '/theme.json';
    if (file_exists($theme_json)) {
      copy($theme_json, get_stylesheet_directory() . '/theme.json');
    }
  }
}

/**
 * Saves the ACF options to theme.json when the ACF options page is saved
 */
add_action('acf/save_post', 'ezpzconsultations_save_theme_settings_to_json', 20);
function ezpzconsultations_save_theme_settings_to_json($post_id) {

  if ($post_id == 'options') {
    $screen = get_current_screen();
    if (!empty($screen) && $screen->id == 'toplevel_page_theme-designer') {

      // See if theme.json exists
      $theme_json = get_stylesheet_directory() . '/theme.json';
      if (file_exists($theme_json)) {
        $theme_json = file_get_contents($theme_json);
        $theme_json = json_decode($theme_json, true);

        // Get ACF Options
        $opts = ezpzconsultations_get_theme_opts();
        echo '<pre>';
        var_dump($opts);
        echo '</pre>';

        if (isset($opts['colors'])) {
          $palette = [];
          foreach ($opts['colors'] as $key => $data) {
            $palette[] = array(
              "slug" => $key,
              "color" => $data['value'],
              "name" => $data['name'],
            );
          }
          // Set the new palette
          $theme_json['settings']['color']['palette'] = $palette;

          // Set the new theme.json
          $theme_json = json_encode($theme_json, JSON_PRETTY_PRINT);
          file_put_contents(get_stylesheet_directory() . '/theme.json', $theme_json);
        }
      }
    }
  }
}

/**
 * Outputs custom CSS in the header based on the ACF options
 */
add_action('wp_head', 'ezpzconsultations_add_custom_css', 30);
function ezpzconsultations_add_custom_css() {
  $theme_opts = ezpzconsultations_get_theme_opts();
  if (!empty($theme_opts)) :
?>
<style type="text/css">
<?php // DO YOUR STUFF HERE
?>
</style>
<?php
  endif;
}

// Master To Do List:
// Override header.php
// Add custom blocks

// Do all overrides in .css not in raw HTML