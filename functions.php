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
  }
});

function get_options_by_prefix($prefix) {
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
    'group_65d6172da0577'
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
    'customcss'
  ];

  foreach ($prefixes as $prefix) {
    $options = get_options_by_prefix($prefix);

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

  if ($post_id == 'options') {
    $screen = get_current_screen();
    if (!empty($screen) && $screen->id == 'toplevel_page_theme-designer') {

      // See if theme.json exists
      $theme_json = get_stylesheet_directory() . '/theme.json';
      if (file_exists($theme_json)) {
        $theme_json = file_get_contents($theme_json);
        $theme_json = json_decode($theme_json, true);


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
add_action('wp_head', 'ezpzconsultations_add_custom_css', 100);
function ezpzconsultations_add_custom_css() {

  $theme_opts = ezpzconsultations_get_theme_opts();

  if (!empty($theme_opts)) :
    // Example usage with theme options
    $primaryColor = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['primary_colour'] : "#ff3c74";
    $secondaryColor = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['secondary_colour'] : "#ffa0cd";


    //if ($theme_opts['branding']["main_logo"]) echo $theme_opts['branding']["main_logo"];
    echo "<pre>";
    var_dump($theme_opts);
    echo "</pre>";


    $custom_css = $theme_opts['customcss']["custom_css"];

    $primary_font_family = $theme_opts['globaltypography']['primary_font_family'];

    $secondary_font_family = $theme_opts['globaltypography']['secondary_font_family'];

    //Generate colours
    include('color_functions.php');



?>

    ?>
    <style type="text/css">
      /* Custom CSS from theme designer */
      <?php if ($custom_css) echo $custom_css; ?>
      /* Global Typography */
      @import url('<?php echo $theme_opts['globaltypography']['primary_import_font_family']; ?>');

      :root {
        --font-primary: <?php echo !empty($primary_font_family) ? $primary_font_family : 'var(--font-primary)'; ?>;
        --font-secondary: <?php echo !empty($secondary_font_family) ? $secondary_font_family : 'var(--font-primary)'; ?>;

      }

      h1 {
        <?php if (!empty($theme_opts['globaltypography']['font_h1_colour_h1'])) : ?>color: <?php echo $theme_opts['globaltypography']['font_h1_colour_h1']; ?> !important;
        <?php endif; ?><?php if (!empty($theme_opts['globaltypography']['font_h1'])) : ?>font-family: <?php echo $theme_opts['globaltypography']['font_h1']; ?>;



        <?php endif; ?><?php if (!empty($theme_opts['globaltypography']['font_h1_weight_h1'])) : ?>font-weight: <?php echo $theme_opts['globaltypography']['font_h1_weight_h1']; ?>;
        <?php endif; ?>
      }


      /* Global Colours */
      <?php


      // Generate color shades

      // Output CSS


      $primaryPalette = makeColorPalette($primaryColor);
      $secondaryPalette = makeColorPalette($secondaryColor);

      echo ":root {\n";
      foreach ($primaryPalette as $key => $value) {
        echo "    --color-primary-$key: $value;\n";
      }
      foreach ($secondaryPalette as $key => $value) {
        echo "    --color-secondary-$key: $value;\n";
      }
      echo "}\n";

      ?>body {
        <?php if (!empty($theme_opts['globalcolors']['text_colour'])) : ?>--color-text: <?php echo $theme_opts['globalcolors']['text_colour']; ?>;
        <?php endif; ?><?php if (!empty($theme_opts['globalcolors']['accent_colour'])) : ?>accent-color: <?php echo $theme_opts['globalcolors']['accent_colour']; ?> <?php endif; ?>
      }



      <?php if (!empty($theme_opts['globalcolors']['accent_colour'])) : ?>a,

      <?php if (!empty($theme_opts['globalcolors']['text_accent_colourcolour'])) : ?>a:link {
        color: <?php echo $theme_opts['globalcolors']['accent_colour']; ?>
      }

      <?php endif; ?><?php endif; ?>
      /* Buttons */

      /* Primary Button */
      .button,
      [type=button],
      [type=reset],
      [type=submit],
      a.button,
      button {

        <?php if (!empty($theme_opts['buttons']['button_primary_background_colour'])) : ?>--button-color-theme: <?php echo $theme_opts['buttons']['button_primary_background_colour']; ?>;
        <?php endif; ?><?php if (!empty($theme_opts['buttons']['button_primary_text_color'])) : ?>--button-color-text: <?php echo $theme_opts['buttons']['button_primary_text_color']; ?>;
        color: <?php echo $theme_opts['buttons']['button_primary_text_color']; ?>;
        <?php endif; ?><?php if (!empty($theme_opts['buttons']['button_primary_background_colour'])) : ?>--button-hover-color-theme: <?php echo $theme_opts['buttons']['button_primary_background_colour']; ?>;
        <?php endif; ?><?php if (!empty($theme_opts['buttons']['button_primary_border_radius'])) : ?>border-radius: <?php echo $theme_opts['buttons']['button_primary_border_radius']; ?>px;
        <?php endif; ?>
      }



      .button.secondary,
      a.button.secondary {
        <?php if (!empty($theme_opts['buttons']['button_secondary_background_colour'])) : ?>--button-color-theme: <?php echo $theme_opts['buttons']['button_secondary_background_colour']; ?>;
        <?php endif; ?><?php if (!empty($theme_opts['buttons']['button_secondary_background_colour'])) : ?>--button-color-text: <?php echo $theme_opts['buttons']['button_secondary_text_colour']; ?>;
        color: <?php echo $theme_opts['buttons']['button_secondary_text_colour']; ?>;
        <?php endif; ?><?php
                        if (!empty($theme_opts['buttons']['button_primary_border_radius'])) {
                          echo 'border-radius: ' . $theme_opts['buttons']['button_primary_border_radius'] . 'px;';
                        }
                        ?>
      }
    </style>
<?php


  endif;
}
// add_action('customize_preview_init', 'ezpzconsultations_customize_preview_init');
// function ezpzconsultations_customize_preview_init($wp_customize) {
//   // Enqueue your JavaScript file for customizer preview
//   wp_enqueue_script('ezpzconsultations-customize-preview', get_stylesheet_directory_uri() . '/js/theme.min.js', array('customize-preview', 'jquery'), null, true);

//   // Get primary color value
//   $theme_opts = ezpzconsultations_get_theme_opts(); // Assuming you have a function to get theme options
//   $primary_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['primary_colour'] : '';

//   // Pass necessary data to the JavaScript file
//   wp_localize_script('ezpzconsultations-customize-preview', 'ezpzconsultations_customizer_data', array(
//     'primary_colour' => $primary_colour
//   ));
// }
// add_action('customize_preview_init', 'ezpzconsultations_enqueue_customizer_preview_script');
// function ezpzconsultations_enqueue_customizer_preview_script() {
//   wp_enqueue_script(
//     'ezpzconsultations-customizer-preview-script', // Script handle
//     get_stylesheet_directory_uri() . '/js/theme.min.js', // Script file path
//     array('customize-preview', 'jquery'), // Dependencies
//     null, // Version (null to prevent caching)
//     true // Enqueue script in footer
//   );
// }



// Master To Do List:
// Override header.php
// Add custom blocks

// Do all overrides in .css not in raw HTML