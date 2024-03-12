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

/**
 * Enqueue the child theme
 */
add_action('wp_enqueue_scripts', 'ezpzconsultations_enqueue_styles');
add_editor_style('style.css');

function ezpzconsultations_enqueue_styles() {
  $theme = wp_get_theme();

  $version = $theme->get('Version');

  wp_enqueue_style(
    'ezpz-consultations-style',
    get_stylesheet_directory_uri() . '/style.css',
    array('jellypress-styles'),
    $version
  );

  wp_enqueue_script(
    'ezpz-consultations-theme',
    get_stylesheet_directory_uri() . '/js/theme.min.js',
    array('jellypress-scripts'),
    $version,
    true
  );
}

add_action('acf/init', 'ezpzconsultations_register_blocks', 20);
add_action('wp_enqueue_scripts', 'ezpzconsultations_enqueue_block_scripts');
//add_filter('allowed_block_types_all', 'ezpzconsultations_add_to_allowed_blocks', 100, 1);

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
  // var_dump("testtttt");

  // unset($allowed_blocks[array_search('ezpz/hero-page', $allowed_blocks)]);
  // unset($allowed_blocks[array_search('ezpz/hero-post', $allowed_blocks)]);

  // Remove ezpz/hero-page and ezpz/hero-post from $allowed_blocks
  // unset($allowed_blocks['ezpz/hero-post']);
  // unset($allowed_blocks['ezpz/hero-page']);

  foreach ($blocks as $slug) {
    $allowed_blocks[] = 'ezpz/' . $slug;
  }

  var_dump($allowed_blocks);

  return $allowed_blocks;
}




function ezpzconsultations_enqueue_block_scripts() {
  global $blocks;
  foreach ($blocks as $slug) {
    if (file_exists(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/scripts.js')) {
      $version = filemtime(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/scripts.js');

      // Get file size
      $size = filesize(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/scripts.js');

      // If file size is greater than 0, enqueue the JS
      if ($size > 0) {
        wp_enqueue_script(
          'block-' . $slug,
          get_stylesheet_directory_uri() . '/blocks/' . $slug . '/scripts.js',
          array('jquery', 'jellypress-scripts'),
          $version,
          true
        );
      }
    }
    // Register the CSS
    if (file_exists(plugin_dir_path(__FILE__) . 'blocks/' . $slug . '/styles.css')) {
      $version = filemtime(plugin_dir_path(__FILE__) . 'blocks/' . $slug . '/styles.css');

      // Get file size
      $size = filesize(plugin_dir_path(__FILE__) . 'blocks/' . $slug . '/styles.css');

      // If file size is greater than 0, enqueue the CSS
      if ($size > 0) {
        wp_enqueue_style(
          'block-' . $slug,
          get_stylesheet_directory_uri() . '/blocks/' . $slug . '/styles.css',
          array(),
          $version
        );
      }
    }
  }
}

/**
 * Add ACF Option Page for Theme Designer
 */
// if (function_exists('acf_add_options_page')) {
//   acf_add_options_page(
//     array(
//       'page_title'     => __('Theme Designer', 'ezpzconsultations'),
//       'menu_title'    => __('Theme Designer', 'ezpzconsultations'),
//       'menu_slug'     => 'theme-designer',
//       'capability'  => 'edit_posts',
//       'icon_url' => 'dashicons-admin-customizer',
//       'position' => 2,
//       'autoload' => true,
//     )
//   );
// }

//I am using the https://github.com/mcguffin/acf-customizer
//TODO : setup dependencies with tgmpluginactivation
add_action('init', function () {
  if (function_exists('acf_add_customizer_section')) {
    $panel_id = acf_add_customizer_panel(array(
      'title'        => 'Theme Designer',

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
      'title'        => 'Branding',
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
    'branding',
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

  include_once 'set-color-palette.php';
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

      //if ($theme_opts['branding']["main_logo"]) echo $theme_opts['branding']["main_logo"];
      // $main_logo = get_field('branding', 'main_logo') ?: '';
      // $main_logo_data = wp_get_attachment_image_src($main_logo, 'full');
      // $original_image_url = $main_logo_data[0];
      // var_dump($original_image_url);
      //$main_logo = get_field('primary_colour', 'globalcolors') ?: '#ff3c74';

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

      //var_dump('customiseddddd' . $padding_decrease_desktop . $padding_decrease_tablet);

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

    endif;
  } else {
    if (!empty($theme_opts)) :
      // $main_logo = $theme_opts['branding']['main_logo'];
      // $main_logo_data = wp_get_attachment_image_src($main_logo, 'full');
      // $original_image_url = $main_logo_data[0];
      //var_dump($original_image_url);

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

    endif;
  }

  if (!empty($theme_opts)) :
    // Example usage with theme options

    //if ($theme_opts['branding']["main_logo"]) echo $theme_opts['branding']["main_logo"];
    // echo "<pre>";
    // var_dump($theme_opts);
    // echo "</pre>";

    //Generate colours
    include('set-color-palette.php');
    include_once 'set-color-contrast.php';

    $primary_palette = ezpzconsultations_make_color_palette($primary_colour);
    $secondary_palette = ezpzconsultations_make_color_palette($secondary_colour);

    $primary_colour_100 = $primary_palette['100'];
    $primary_colour_500 = $primary_palette['500'];

    $secondary_colour_100 = $secondary_palette['100'];
    $secondary_colour_500 = $secondary_palette['500'];

    $neutral_palette = ezpzconsultations_make_color_palette($neutral_colour);
    $success_palette = ezpzconsultations_make_color_palette($success_colour);
    $warning_palette = ezpzconsultations_make_color_palette($warning_colour);
    $error_palette = ezpzconsultations_make_color_palette($error_colour);

?>

    <style type="text/css">
      /* Navbar */

      .navbar-menu li a:hover::after {
        background-color: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500); ?>;
      }

      .main-navigation .navbar-menu li a,
      .main-navigation .navbar-menu li a:hover {
        color: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500); ?>;
      }

      .main-navigation.bg-primary-500 {
        background-color: <?php echo $primary_colour_500 ?>;
        background: <?php echo $primary_colour_500 ?>;
        z-index: 99;
        position: fixed;
      }

      .main-navigation.bg-primary-500 .navbar-menu li a:hover::after {
        background-color: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?>;
      }


      .main-navigation.bg-primary-500 .navbar-menu li a,
      .main-navigation.bg-primary-500 .navbar-menu li a:hover {

        color: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?>;

      }

      /* Hero */
      .hero p,
      .hero h1,
      .hero h2,
      .hero h3,
      .hero h4,
      .hero li,
      .hero a {
        color: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?> !important;
      }

      /* Paddings */
      @media (min-width: 37.5em) {

        header.block,
        section.block {
          padding-bottom: calc(2.5rem - <?php echo "min(max($padding_decrease_tablet, 0px), 20px)" ?>);
          padding-top: calc(4rem - <?php echo "min(max($padding_decrease_tablet, 0px), 20px)" ?>);
        }
      }

      @media (min-width: 56.25em) {

        header.block,
        section.block {
          padding-bottom: calc(4.5rem - <?php echo "min(max($padding_decrease_desktop, 0px), 50px)" ?>);
          padding-top: calc(6rem - <?php echo "min(max($padding_decrease_desktop, 0px), 50px)" ?>);
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
      <?php if (!empty($button_colour) || !empty($button_font_weight) || !empty($button_border_radius)) : ?>.button,
      [type=button],
      [type=reset],
      [type=submit],
      a.button,
      .button {
        <?php if ($button_font_weight) : ?>font-weight: <?php echo $button_font_weight; ?>;
        <?php endif; ?><?php if ($button_border_radius) : ?>border-radius: <?php echo $button_border_radius . $button_border_radius_unit; ?>;
        <?php endif; ?><?php if (!empty($button_colour) && $button_colour == "primary") : ?>--button-color-theme: <?php echo $primary_colour_500 ?>;
        --button-color-text: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?>;
        --button-hover-color-text: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?>;
        <?php else : ?>--button-color-theme: <?php echo $secondary_colour_500 ?>;
        --button-color-text: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500); ?>;
        --button-hover-color-text: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500); ?>;
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
        color: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;
        --color-section-text: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;
        --color-headings-preferred: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>
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
        color: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;
        --color-section-text: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;
        --color-headings-preferred: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;
        --button-color-text: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;
        border-color: <?php echo ezpzconsultations_calculate_contrast($bg_color); ?>;

      }

      <?php
      }

      ?>
    </style>

<?php
  endif;
}

// Master To Do List:
// Override header.php
// Add custom blocks

// Do all overrides in .css not in raw HTML