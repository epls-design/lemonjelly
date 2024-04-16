<?php

/**
 * Child Theme Functions
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
$block_functions_to_include = array('image-compare', 'timeline', 'hero');

foreach ($block_functions_to_include as $block) {
  $directory = get_stylesheet_directory() . '/blocks/' . $block . '/functions.php';

  if (file_exists($directory))
    include_once $directory;
}

//TODO change hero name to consultation-hero
function ezpzconsultations_block_templates() {
  $post_type_object = get_post_type_object('page');

  $post_type_object->template = array(
    array('ezpz/hero', array(
      'lock' => array(
        'move'   => true,
        'remove' => true,
      ),
    )),
    array('ezpz/section', array()),
  );
}
add_action('init', 'ezpzconsultations_block_templates', 30);

/**
 * Enqueue the child theme
 */
add_action('wp_enqueue_scripts', 'ezpzconsultations_enqueue_styles');



function wpdocs_theme_add_editor_styles() {
  add_editor_style('style.css');
  add_editor_style('consultation.css');
}
add_action('admin_init', 'wpdocs_theme_add_editor_styles', 500);


function ezpzconsultations_enqueue_styles() {
  $style_css_path = get_stylesheet_directory() . '/style.css';
  if (file_exists($style_css_path)) {
    wp_enqueue_style(
      'ezpz-consultations-style',
      get_stylesheet_directory_uri() . '/style.css',
      array('jellypress-styles'),
      filemtime($style_css_path)
    );
  }

  $consultation_css_path = get_stylesheet_directory() . '/consultation.css';

  if (file_exists($consultation_css_path)) {
    wp_enqueue_style(
      'editors-overrides',
      get_stylesheet_directory_uri() . '/consultation.css',
      array(),
      filemtime($consultation_css_path)
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

add_action('acf/init', 'ezpzconsultations_register_blocks', 20);
add_filter('allowed_block_types_all', 'ezpzconsultations_add_to_allowed_blocks', 100, 1);

//change to ezpconsultation_blocks
$blocks = array('timeline', 'image-compare', 'hero');

function ezpzconsultations_register_blocks() {
  global $blocks;
  foreach ($blocks as $slug) {

    //var_dump(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/block.json');
    if (file_exists(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/block.json')) {
      // Register ACF block
      register_block_type(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug);
    }
  }
}

function ezpzconsultations_add_to_allowed_blocks($allowed_blocks) {
  global $blocks;

  // Ensure $allowed_blocks is initialized as an array
  if (!is_array($allowed_blocks)) {
    $allowed_blocks = array();
  }

  foreach ($blocks as $slug) {
    $allowed_blocks[] = 'ezpz/' . $slug;
  }

  // Remove 'ezpz/hero-page' and 'ezpz/hero-post' if they exist
  $hero_page_index = array_search('ezpz/hero-page', $allowed_blocks);
  if ($hero_page_index !== false) {
    unset($allowed_blocks[$hero_page_index]);
  }

  $hero_post_index = array_search('ezpz/hero-post', $allowed_blocks);
  if ($hero_post_index !== false) {
    unset($allowed_blocks[$hero_post_index]);
  }

  // Re-index the array keys
  $allowed_blocks = array_values($allowed_blocks);

  //var_dump($allowed_blocks);

  return $allowed_blocks;
}
//Remove page-hero block from pages
function remove_page_hero_block() {
  remove_action('init', 'jellypress_block_templates', 20);
}
add_action('after_setup_theme', 'remove_page_hero_block');

//I am using the https://github.com/mcguffin/acf-customizer
//TODO : setup dependencies with tgmpluginactivation
add_action('init', function () {
  if (function_exists('acf_add_customizer_section')) {
    $panel_id = acf_add_customizer_panel(array(
      'title'        => 'Theme Designer',
      'description' => '<style>#sub-accordion-panel-themedesigner .description.customize-panel-description{display:block !important;}</style> To update the favicon and logo, visit the <a href="/wp-admin/admin.php?page=theme-options">options page</a>.',
    ));

    acf_add_customizer_section(array(
      'title'        => 'Global Colors',
      'storage_type' => 'option',
      'panel'        => $panel_id,

    ));
    acf_add_customizer_section(array(
      'title'        => 'Global Typography',
      'storage_type' => 'option',
      'panel'        => $panel_id,
    ));
    acf_add_customizer_section(array(
      'title'        => 'Buttons',
      'storage_type' => 'option',
      'panel'        => $panel_id,
    ));
    acf_add_customizer_section(array(
      'title'        => 'Custom CSS',
      'storage_type' => 'option',
      'panel'        => $panel_id,
    ));
    acf_add_customizer_section(array(
      'title'        => 'Global Padding',
      'storage_type' => 'option',
      'panel'        => $panel_id,
    ));
  }
});

function ezpzconsultations_get_options_by_prefix($prefix) {
  global $wpdb;

  $returned_data = [];

  $options = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
      $wpdb->esc_like($prefix) . '%'
    )
  );

  if (!empty($options)) {
    foreach ($options as $data) {
      // Extract the option name without the prefix
      $name_without_prefix = str_replace($prefix . '_', '', $data->option_name);

      // Check if an option with a more specific prefix exists
      if (!isset($returned_data[$name_without_prefix])) {
        // If not, add the current option to the returned data
        $returned_data[$name_without_prefix] = $data->option_value;
      }
    }
  }

  return $returned_data;
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
    'group_65d61c51e9cdc',
    'group_65d61eb9da7c1',
    'group_65d616d0e2170',
    'group_65d6168f43085',
    'group_65d6172da0577',
    'group_65e1fd5aec57b',
    'group_65e6eb7aed060',
    'group_65e9b645cb80d',
    'group_64c2957a5ef4e'
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

  $theme_opts = [];

  $prefixes = [
    'globalcolors',
    'globaltypography',
    'buttons',
    'customcss',
    'globalpadding'
  ];

  foreach ($prefixes as $prefix) {
    $options = ezpzconsultations_get_options_by_prefix($prefix);

    if (!empty($options)) {
      $theme_opts[$prefix] = $options;
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

  // Retrieve theme options
  $theme_opts = ezpzconsultations_get_theme_opts();

  $primary_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['primary_colour'] : "#ff3c74";
  $secondary_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['secondary_colour'] : "#ffa0cd";

  include_once 'theme-helpers.php';
  $primary_palette = ezpzconsultations_make_color_palette($primary_colour);
  $secondary_palette = ezpzconsultations_make_color_palette($secondary_colour);

  $primary_colour_100 = $primary_palette['100'];
  $primary_colour_500 = $primary_palette['500'];
  $secondary_colour_100 = $secondary_palette['100'];
  $secondary_colour_500 = $secondary_palette['500'];


  if ($post_id == 'globalcolors') {
    // See if theme.json exists
    $theme_json = get_stylesheet_directory() . '/theme.json';
    if (file_exists($theme_json)) {
      $theme_json = file_get_contents($theme_json);
      $theme_json = json_decode($theme_json, true);


      $colors = array(
        "primary-100" => $primary_colour_100,
        "primary-500" => $primary_colour_500,
        "secondary-100" => $secondary_colour_100,
        "secondary-500" => $secondary_colour_500,

        "white" => '#ffffff',
        "black" => "#000000"
      );

      if (isset($colors)) {
        $palette = [];
        foreach ($colors as $key => $color) {

          $palette[] = array(
            "slug" => $key,
            "color" => $color,
            "name" => $key,
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

//Get font family function
function ezpzconsultations_get_font_family($font_key, $theme_opts) {
  $font_family = '';

  if (!empty($theme_opts['globaltypography'][$font_key])) {
    switch ($theme_opts['globaltypography'][$font_key]) {
      case 'primary_font':
        $font_family = "var(--font-primary)";
        break;
      case 'secondary_font':
        $font_family = "var(--font-secondary)";
        break;
    }
  }

  return $font_family;
}
/**
 * Remove default additional CSS section from customizer
 */
function ezpzconsultations_customize_register($wp_customize) {
  $wp_customize->remove_section('custom_css');
}
add_action('customize_register', 'ezpzconsultations_customize_register');


add_action('acf/save_post', 'ezpzconsultations_generate_theme_override_css');
function ezpzconsultations_generate_theme_override_css($post_id) {

  //error_log($post_id);

  if ($post_id == 'globalcolors' || $post_id == 'globaltypography' || $post_id == 'buttons' || $post_id == 'customcss' || $post_id == 'globalpadding') {
    // Get all the fields from customiser, and output into consultation.css

    $theme_opts = ezpzconsultations_get_theme_opts();

    //error_log(json_encode($theme_opts));

    if (!empty($theme_opts)) :

      $primary_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['primary_colour'] : "#ff3c74";
      $secondary_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['secondary_colour'] : "#ffa0cd";
      $neutral_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['neutral_colour'] : "#64748b";
      $success_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['success_colour'] : "#00c851";
      $warning_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['warning_colour'] : "#FFBB33";
      $error_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['error_colour'] : "#FF4444";
      $color_headings_preferred = isset($theme_opts['globalcolors']['headings_preferred_colour']) ? $theme_opts['globalcolors']['headings_preferred_colour'] : 'var(--color-headings-preferred)';

      $custom_css = isset($theme_opts['customcss']['custom_css']) ? $theme_opts['customcss']['custom_css'] : "";

      //Typography / Fonts / Heading Colours
      $primary_font_family = isset($theme_opts['globaltypography']['primary_font_family']) ? $theme_opts['globaltypography']['primary_font_family'] : 'var(--font-primary)';
      $secondary_font_family = isset($theme_opts['globaltypography']['secondary_font_family']) ? $theme_opts['globaltypography']['secondary_font_family'] : 'var(--font-primary)';


      //Buttons
      $button_colour = isset($theme_opts['buttons']['colour']) ? $theme_opts['buttons']['colour'] : "";


      $button_border_radius = isset($theme_opts['buttons']['border_radius_value']) ? $theme_opts['buttons']['border_radius_value'] : '.5rem';

      $button_border_radius_unit = isset($theme_opts['buttons']['border_radius_unit']) ? $theme_opts['buttons']['border_radius_unit'] : 'px';

      $button_font_weight = isset($theme_opts['buttons']['font_weight']) ? $theme_opts['buttons']['font_weight'] : '400';

      $padding_decrease_desktop = isset($theme_opts['globalpadding']['padding_decrease_desktop']) ? $theme_opts['globalpadding']['padding_decrease_desktop'] . 'px' : '0px';
      $padding_decrease_tablet = isset($theme_opts['globalpadding']['padding_decrease_tablet']) ? $theme_opts['globalpadding']['padding_decrease_tablet'] . 'px' : '0px';

      /* Headings - Global Typography */
      // Define an array of headings, their corresponding CSS properties, and their corresponding font family keys
      $styles = [
        'h1' => ['font_weight_key' => 'font_h1_font_weight', 'font_family_key' => 'font_h1_font_family'],
        'h2' => ['font_weight_key' => 'font_h2_font_weight', 'font_family_key' => 'font_h2_font_family'],
        'h3' => ['font_weight_key' => 'font_h3_font_weight', 'font_family_key' => 'font_h3_font_family'],
        'h4' => ['font_weight_key' => 'font_h4_font_weight', 'font_family_key' => 'font_h4_font_family']
      ];

      // Iterate through styles to generate CSS for font weight and font family
      foreach ($styles as $tag => $style) {
        // Check if the weight value is set in $theme_opts['globaltypography'], otherwise fallback to a default value
        $weight = isset($theme_opts['globaltypography'][$style['font_weight_key']]) && $theme_opts['globaltypography'][$style['font_weight_key']] !== '' ? $theme_opts['globaltypography'][$style['font_weight_key']] : '500';
        // Output the CSS for font weight
        echo "<style>$tag { font-weight: $weight; }</style>";

        // Get font family
        $font_family = ezpzconsultations_get_font_family($style['font_family_key'], $theme_opts);
        // Output the CSS for font family if it exists
        if ($font_family) {
          echo "<style>$tag { font-family: $font_family; }</style>";
        }
      }

      //Generate colours
      include_once('theme-helpers.php');

      $primary_palette = ezpzconsultations_make_color_palette($primary_colour);
      $secondary_palette = ezpzconsultations_make_color_palette($secondary_colour);
      $neutral_palette = ezpzconsultations_make_color_palette($neutral_colour);
      $success_palette = ezpzconsultations_make_color_palette($success_colour);
      $warning_palette = ezpzconsultations_make_color_palette($warning_colour);
      $error_palette = ezpzconsultations_make_color_palette($error_colour);

      $primary_colour_100 = $primary_palette['100'];
      $primary_colour_500 = $primary_palette['500'];

      $secondary_colour_100 = $secondary_palette['100'];
      $secondary_colour_500 = $secondary_palette['500'];

      $neutral_colour_100 = $neutral_palette['100'];
      $neutral_colour_500 = $neutral_palette['500'];
      $neutral_colour_900 = $neutral_palette['900'];
    endif;




    $css_file_path = get_stylesheet_directory() . '/consultation.css';

    // Open the file in "w" mode to create it if it doesn't exist or truncate it if it does
    $css_file = fopen($css_file_path, "w");

    $css_data = '';

    // Button styles
    $css_data .= '
    /* Global Typography */
    @import url(\'' . $theme_opts['globaltypography']['primary_import_font_family'] . '\');

    /* Buttons */';

    if (!empty($button_colour) || !empty($button_font_weight) || !empty($button_border_radius)) {
      $css_data .= '
    [type=button],
    [type=reset],
    [type=submit],
    a.button,
    .button {
    ';

      if ($button_font_weight) {
        $css_data .= 'font-weight: ' . $button_font_weight . ';';
      }

      if ($button_border_radius) {
        $css_data .= 'border-radius: ' . $button_border_radius . $button_border_radius_unit . ';';
      }

      $css_data .= '';

      // Check button color
      if (!empty($button_colour) && $button_colour == "primary") {
        $css_data .= '--button-color-theme: ' . $primary_colour_500 . ';
    --button-color-text: ' . ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900) . ';
    --button-hover-color-text: ' . ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900) . ';
    ';
      } else {
        $css_data .= '--button-color-theme: ' . $secondary_colour_500 . ';
    --button-color-text: ' . ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900) . ';
    --button-hover-color-text: ' . ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900) . ';
    ';
      }
      $css_data .= '}

    ';
    }

    $css_data .= ':root { ';
    foreach ($primary_palette as $key => $value) {
      $css_data .= "    --color-primary-$key: $value;\n";
    }
    foreach ($secondary_palette as $key => $value) {
      $css_data .= "    --color-secondary-$key: $value;\n";
    }
    foreach ($neutral_palette as $key => $value) {
      $css_data .= "    --color-neutral-$key: $value;\n";
    }
    foreach ($success_palette as $key => $value) {
      $css_data .= "    --color-success-$key: $value;\n";
    }
    foreach ($warning_palette as $key => $value) {
      $css_data .= "    --color-warning-$key: $value;\n";
    }
    foreach ($error_palette as $key => $value) {
      $css_data .= "    --color-error-$key: $value;\n";
    }
    $css_data .= "}\n";

    $css_data .= '

    /* Navbar */

    .navbar-menu li a:hover::after {
      background-color: ' . ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900) . ' !important;
    }

    .main-navigation .navbar-menu li a,
    .main-navigation .navbar-menu li a:hover {
      color: ' . ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900) . ';
    }

    .main-navigation.bg-primary-500 {
      background-color: ' . $primary_colour_500 . ';
      background: ' . $primary_colour_500 . ';
    }

    .main-navigation.bg-primary-500 .navbar-menu li a:hover::after {
      background-color: ' . ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900) . ';
    }


    .main-navigation.bg-primary-500 .navbar-menu li a,
    .main-navigation.bg-primary-500 .navbar-menu li a:hover {

      color: ' . ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900) . ';

    }

    /* Hero */
    /* Default Overlay */
    .hero.default h1,
    .hero.default h2,
    .hero.default h3,
    .hero.default h4,
    .hero.default h5,
    .hero.default h6,
    .hero.default p {
      color: ' . ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900) . ';
    }


    /* Paddings */
    @media (min-width: 37.5em) {
      header.block,
      section.block {
        padding-bottom: ' . "min(max($padding_decrease_tablet, 32px), 75px)" . ';
        padding-top: ' . "min(max($padding_decrease_tablet, 32px), 75px)" . ';
      }
    }

    @media (min-width: 56.25em) {

      header.block,
      section.block {
        padding-bottom: ' . "min(max($padding_decrease_desktop, 32px), 100px)" . ';
        padding-top: ' . "min(max($padding_decrease_desktop, 32px), 100px)" . ';

      }
    }

    /* Custom CSS from theme designer */
    ' . $custom_css . '

    :root {
      --font-primary: ' . $primary_font_family . ';
      --font-secondary: ' . $secondary_font_family . ';

      --color-headings-preferred: ' . $color_headings_preferred . ';
      --color-section-headings: ' . $color_headings_preferred . ';
    }

    ';

    $css_data .= "/* Set colour contrast for background colours */ \n";

    $theme_bg_colors = array(
      '.block.bg-primary-500 ' => $primary_colour_500,
      '.block.bg-secondary-500 ' => $secondary_colour_500,
      '.block.bg-primary-100 ' => $primary_colour_100,
      '.block.bg-secondary-100 ' => $secondary_colour_100,
    );

    foreach ($theme_bg_colors as $css_class => $bg_color) {
      $css_data .= $css_class . " {\n";
      $css_data .= "    color: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "    --color-section-text: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "    --color-headings-preferred: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "}\n";

      $css_data .= $css_class . "h1,\n";
      $css_data .= $css_class . "h2,\n";
      $css_data .= $css_class . "h3,\n";
      $css_data .= $css_class . "h4,\n";
      $css_data .= $css_class . "h5,\n";
      $css_data .= $css_class . "h6,\n";
      $css_data .= $css_class . "a:not(.button),\n";
      $css_data .= $css_class . "a:not(.button):hover,\n";
      $css_data .= $css_class . "a:not(.button):focus,\n";
      $css_data .= $css_class . "a:not(.button):visited,\n";
      $css_data .= $css_class . "a:not(.button):link,\n";
      $css_data .= $css_class . "a:not(.button) {\n";
      $css_data .= "    color: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "    --color-section-text: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "    --color-headings-preferred: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "    --button-color-text: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "    border-color: " . ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900) . ";\n";
      $css_data .= "}\n";
    }



    // Define an array of headings and their corresponding ACF field keys
    $font_fields = [
      'h1' => 'font_h1',
      'h2' => 'font_h2',
      'h3' => 'font_h3',
      'h4' => 'font_h4'
    ];

    // Iterate through headings to generate CSS for font weight
    foreach ($font_fields as $tag => $field_key) {
      // Get the font weight for the current heading size
      $font_heading = get_field($field_key, 'globaltypography');

      // Convert the font weight to string before outputting
      $weight = isset($font_heading['font_weight']) ? $font_heading['font_weight'] : '500';
      $family = ($font_heading && isset($font_heading['font_family']) && $font_heading['font_family'] == 'primary_font') ? 'var(--font-primary)' : 'var(--font-secondary)';

      // Output the CSS
      $css_data .= "
    $tag {
        font-weight: $weight;
        font-family: $family;
    }
    ";
    }

    //file_put_contents($css_file, $css_data);
    // Write the CSS data to the file
    if ($css_file) {
      fwrite($css_file, $css_data);
      fclose($css_file);
    }
  }
}


/**
 * Outputs custom CSS in the header based on the ACF options
 */
add_action('wp_head', 'ezpzconsultations_add_custom_css', 100);
//TODO: main header colors are also changing

function ezpzconsultations_add_custom_css() {

  $theme_opts = ezpzconsultations_get_theme_opts();

  if (isset($_POST['customized'])) {
    //get fields for customizer
    if (!empty($theme_opts)) :

      // Global Colours
      $primary_colour = get_field('primary_colour', 'globalcolors') ?: '#ff3c74';
      $secondary_colour = get_field('secondary_colour', 'globalcolors') ?: '#ffa0cd';
      $neutral_colour = get_field('neutral_colour', 'globalcolors') ?: '#64748b';
      $success_colour = get_field('success_colour', 'globalcolors') ?: '#00c851';
      $warning_colour = get_field('warning_colour', 'globalcolors') ?: '#FFBB33';
      $error_colour = get_field('error_colour', 'globalcolors') ?: '#FF4444';
      $color_headings_preferred = get_field('headings_preferred_colour', 'globalcolors') ?: 'var(--color-headings-preferred)';

      $custom_css = get_field('custom_css', 'customcss') ?: '';

      // var_dump($custom_css);
      // Typography / Fonts / Heading Colours
      $primary_font_family = get_field('primary_font_family', 'globaltypography') ?: 'var(--font-primary)';
      $secondary_font_family = get_field('secondary_font_family', 'globaltypography') ?: 'var(--font-primary)';

      // Buttons
      $button_colour = get_field('colour', 'buttons') ?: "";
      //TODO: button radius not working when customised
      $button_border_radius = get_field('border_radius_value', 'buttons');
      $button_border_radius_unit = get_field('border_radius_unit', 'buttons');

      $button_font_weight = get_field('font_weight', 'buttons') ?: '400';

      $padding_decrease_desktop = (get_field('padding_decrease_desktop', 'globalpadding') ?: '0') . 'px';
      $padding_decrease_tablet = (get_field('padding_decrease_tablet', 'globalpadding') ?: '0') . 'px';

      //var_dump('customiseddddd' . $primary_colour);

      // Define an array of headings and their corresponding ACF field keys
      $font_fields = [
        'h1' => 'font_h1',
        'h2' => 'font_h2',
        'h3' => 'font_h3',
        'h4' => 'font_h4'
      ];

      // Iterate through headings to generate CSS for font weight
      foreach ($font_fields as $tag => $field_key) {
        // Get the font weight for the current heading size
        $font_heading = get_field($field_key, 'globaltypography');

        // Convert the font weight to string before outputting
        $weight = $font_heading['font_weight'];
        $family =  $font_heading['font_family'];

        if ($font_heading['font_family'] == 'primary_font') {
          $family = 'var(--font-primary)';
        } else {
          $family = 'var(--font-secondary)';
        }
        // Check if the font weight is set
        if ($font_heading && isset($font_heading['font_weight'])) {

          // Output the CSS
          echo "<style>$tag { font-weight: $weight;
            font-family: $family;

        }
          </style>";
        } else {
          // Fallback to a default font weight if not set
          echo "<style>$tag { font-weight: 500;
            font-family: var(--font-primary);
          }</style>";
        }
      }

      //Generate colours
      include_once('theme-helpers.php');

      $primary_palette = ezpzconsultations_make_color_palette($primary_colour);
      $secondary_palette = ezpzconsultations_make_color_palette($secondary_colour);
      $neutral_palette = ezpzconsultations_make_color_palette($neutral_colour);
      $success_palette = ezpzconsultations_make_color_palette($success_colour);
      $warning_palette = ezpzconsultations_make_color_palette($warning_colour);
      $error_palette = ezpzconsultations_make_color_palette($error_colour);


      $primary_colour_100 = $primary_palette['100'];
      $primary_colour_500 = $primary_palette['500'];

      $secondary_colour_100 = $secondary_palette['100'];
      $secondary_colour_500 = $secondary_palette['500'];

      $neutral_colour_100 = $neutral_palette['100'];
      $neutral_colour_500 = $neutral_palette['500'];
      $neutral_colour_900 = $neutral_palette['900'];
?>



      <style type="text/css">
        /* Navbar */

        .navbar-menu li a:hover::after {
          background-color: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900); ?>;
        }

        .main-navigation .navbar-menu li a,
        .main-navigation .navbar-menu li a:hover {
          color: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900); ?>;
        }

        .main-navigation.bg-primary-500 {
          background-color: <?php echo $primary_colour_500 ?>;
          background: <?php echo $primary_colour_500 ?>;
          z-index: 99;
          position: fixed;
        }

        .main-navigation.bg-primary-500 .navbar-menu li a:hover::after {
          background-color: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900); ?>;
        }


        .main-navigation.bg-primary-500 .navbar-menu li a,
        .main-navigation.bg-primary-500 .navbar-menu li a:hover {

          color: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900); ?>;

        }

        /* Hero */
        .hero p,
        .hero h1,
        .hero h2,
        .hero h3,
        .hero h4,
        .hero li,
        .hero a {
          color: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900); ?> !important;
        }

        /* Paddings */
        @media (min-width: 37.5em) {

          header.block,
          section.block {
            padding-bottom: <?php echo "min(max($padding_decrease_tablet, 32px), 75px)" ?>;
            padding-top: <?php echo "min(max($padding_decrease_tablet, 32px), 75px)" ?>;
          }
        }

        @media (min-width: 56.25em) {

          header.block,
          section.block {
            padding-bottom: <?php echo "min(max($padding_decrease_desktop, 32px), 100px)" ?>;
            padding-top: <?php echo "min(max($padding_decrease_desktop, 32px), 100px)" ?>;
          }


        }

        /* Custom CSS from theme designer */
        <?php if ($custom_css) echo $custom_css; ?>
        /* Global Typography */
        @import url('<?php echo $theme_opts['globaltypography']['primary_import_font_family']; ?>');

        :root {
          --font-primary: <?php echo $primary_font_family ?>;
          --font-secondary: <?php echo $secondary_font_family ?>;

          --color-headings-preferred: <?php echo $color_headings_preferred ?>;
          --color-section-headings: <?php echo $color_headings_preferred ?>;
        }


        /* Global Colours - Generate Colour Palette  */
        <?php

        echo ":root {\n";
        foreach ($primary_palette as $key => $value) {
          echo "    --color-primary-$key: $value;\n";
        }
        foreach ($secondary_palette as $key => $value) {
          echo "    --color-secondary-$key: $value;\n";
        }
        foreach ($neutral_palette as $key => $value) {
          echo "    --color-neutral-$key: $value;\n";
        }
        foreach ($success_palette as $key => $value) {
          echo "    --color-success-$key: $value;\n";
        }
        foreach ($warning_palette as $key => $value) {
          echo "    --color-warning-$key: $value;\n";
        }
        foreach ($error_palette as $key => $value) {
          echo "    --color-error-$key: $value;\n";
        }
        echo "}\n";

        ?>

        /* Buttons */
        <?php if (!empty($button_colour) || !empty($button_font_weight) || !empty($button_border_radius)) : ?>[type=button],
        [type=reset],
        [type=submit],
        a.button,
        .button {
          <?php if ($button_font_weight) : ?>font-weight: <?php echo $button_font_weight; ?>;
          <?php endif; ?><?php if ($button_border_radius) : ?>border-radius: <?php echo $button_border_radius . $button_border_radius_unit; ?>;
          <?php endif; ?><?php if (!empty($button_colour) && $button_colour == "primary") : ?>--button-color-theme: <?php echo $primary_colour_500 ?>;
          --button-color-text: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900); ?>;
          --button-hover-color-text: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500, $neutral_colour_900); ?>;
          <?php else : ?>--button-color-theme: <?php echo $secondary_colour_500 ?>;
          --button-color-text: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900); ?>;
          --button-hover-color-text: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500, $neutral_colour_900); ?>;
          <?php endif; ?>
        }

        <?php endif; ?>

        /* Set colour contrast for background colours */
        <?php
        $theme_bg_colors = array(
          '.block.bg-primary-500 ' => $primary_colour_500,
          '.block.bg-secondary-500 ' => $secondary_colour_500,
          '.block.bg-primary-100 ' => $primary_colour_100,
          '.block.bg-secondary-100 ' => $secondary_colour_100,
        );

        foreach ($theme_bg_colors as $css_class => $bg_color) {
        ?><?php echo $css_class; ?> {
          color: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;
          --color-section-text: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;
          --color-headings-preferred: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>
        }

        <?php echo $css_class; ?>h1,
        <?php echo $css_class; ?>h2,
        <?php echo $css_class; ?>h3,
        <?php echo $css_class; ?>h4,
        <?php echo $css_class; ?>h5,
        <?php echo $css_class; ?>h6,
        <?php echo $css_class; ?>a:not(.button),
        <?php echo $css_class; ?>a:not(.button):hover,
        <?php echo $css_class; ?>a:not(.button):focus,
        <?php echo $css_class; ?>a:not(.button):visited,
        <?php echo $css_class; ?>a:not(.button):link,
        <?php echo $css_class; ?>a:not(.button) {
          color: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;
          --color-section-text: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;
          --color-headings-preferred: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;
          --button-color-text: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;
          border-color: <?php echo ezpzconsultations_calculate_contrast($bg_color, $neutral_colour_900); ?>;

        }

        <?php
        }

        ?>
      </style>
  <?php
    endif;
  } else {
    if (!empty($theme_opts)) :

      //var_dump("not customised");
      $primary_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['primary_colour'] : "#ff3c74";
      $secondary_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['secondary_colour'] : "#ffa0cd";
      $neutral_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['neutral_colour'] : "#64748b";
      $success_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['success_colour'] : "#00c851";
      $warning_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['warning_colour'] : "#FFBB33";
      $error_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['error_colour'] : "#FF4444";
      $color_headings_preferred = isset($theme_opts['globalcolors']['headings_preferred_colour']) ? $theme_opts['globalcolors']['headings_preferred_colour'] : 'var(--color-headings-preferred)';
      $custom_css = isset($theme_opts['customcss']['custom_css']) ? $theme_opts['customcss']['custom_css'] : "";

      //Typography / Fonts / Heading Colours
      $primary_font_family = isset($theme_opts['globaltypography']['primary_font_family']) ? $theme_opts['globaltypography']['primary_font_family'] : 'var(--font-primary)';
      $secondary_font_family = isset($theme_opts['globaltypography']['secondary_font_family']) ? $theme_opts['globaltypography']['secondary_font_family'] : 'var(--font-primary)';


      $padding_decrease_desktop = isset($theme_opts['globalpadding']['padding_decrease_desktop']) ? $theme_opts['globalpadding']['padding_decrease_desktop'] . 'px' : '0px';
      $padding_decrease_tablet = isset($theme_opts['globalpadding']['padding_decrease_tablet']) ? $theme_opts['globalpadding']['padding_decrease_tablet'] . 'px' : '0px';

    endif;
  }
  ?>
<?php
}

//FAVICONS
add_action('wp_head', 'update_favicon');
function update_favicon() {
  $favicon_url = get_field('favicon', 'option');

  // Check if favicon URL exists
  if ($favicon_url) {
    // Update favicon links with the ACF URL
    echo '<link rel="shortcut icon" type="image/x-icon" href="' . esc_url($favicon_url) . '">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="194x194">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="96x96">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="32x32">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="16x16">';
    echo '<link rel="apple-touch-icon" href="' . esc_url($favicon_url) . '">';
    echo '<link rel="mask-icon" href="' . esc_url($favicon_url) . '" color="#5bbad5">';
  } else {
    // Fallback to default favicon links if ACF field is empty
    if (file_exists(ABSPATH . '/favicon.ico'))                   echo '<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">';
    if (file_exists(ABSPATH . '/favicon/favicon-194x194.png'))   echo '<link rel="icon" type="image/png" href="/favicon/favicon-194x194.png" sizes="194x194">';
    if (file_exists(ABSPATH . '/favicon/favicon-96x96.png'))     echo '<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96">';
    if (file_exists(ABSPATH . '/favicon/favicon-32x32.png'))     echo '<link rel="icon" type="image/png" href="/favicon/favicon-32x32.png" sizes="32x32">';
    if (file_exists(ABSPATH . '/favicon/favicon-16x16.png'))     echo '<link rel="icon" type="image/png" href="/favicon/favicon-16x16.png" sizes="16x16">';
    if (file_exists(ABSPATH . '/favicon/apple-touch-icon.png'))  echo '<link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">';
    if (file_exists(ABSPATH . '/favicon/safari-pinned-tab.svg')) echo '<link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">'; // Note: change the color to match your branding
  }

  // Check and add other favicon-related links if they exist
  if (file_exists(ABSPATH . '/site.webmanifest'))              echo '<link rel="manifest" href="/site.webmanifest">';
  if (file_exists(ABSPATH . '/browserconfig.xml'))             echo '<meta name="msapplication-config" content="/browserconfig.xml">';
  echo '<meta name="theme-color" content="#ffffff">';
}

// Add favicon to admin areas
add_action('login_head', 'add_favicon_to_admin');
add_action('admin_head', 'add_favicon_to_admin');
function add_favicon_to_admin() {
  // Get the URL of the uploaded favicon image from ACF
  $favicon_url = get_field('favicon', 'option');

  // Check if favicon URL exists
  if ($favicon_url) {
    // Update favicon link in admin areas with the ACF URL
    echo '<link rel="shortcut icon" type="image/x-icon" href="' . esc_url($favicon_url) . '">';
  } else {
    // Fallback to default favicon link if ACF field is empty
    if (file_exists(ABSPATH . '/favicon.ico')) echo '<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">';
  }
}



// Master To Do List:
// Override header.php
// Add custom blocks

// Do all overrides in .css not in raw HTML