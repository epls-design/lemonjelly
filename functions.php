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
  wp_enqueue_script(
    'ezpz-consultations-twentytwenty',
    get_stylesheet_directory_uri() . '/js/twentytwenty.min.js',
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


  error_log($primary_colour_500);
  error_log($secondary_colour_500);
  error_log($post_id);
  if ($post_id == 'globalcolors') {
    // See if theme.json exists
    $theme_json = get_stylesheet_directory() . '/theme.json';
    if (file_exists($theme_json)) {
      $theme_json = file_get_contents($theme_json);
      $theme_json = json_decode($theme_json, true);

      error_log("anything");


      // TODO: FOR PRIMARY AND SECONDARY GENERATE;
      // primary-500
      // primary-100
      // sec
      // Also force a white option - and black?
      /**
       * .block.bg-primary-500
       * - You need to get your primary 500 value
       * - Get the default font colour whoch will be whatever neutral, 900 resolves to
       * compare against the bg-primary-500 colour, and against white. To see what has the best contrast afgainst the backgrpi8md
       */

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
 * Outputs custom CSS in the header based on the ACF options
 */
add_action('wp_head', 'ezpzconsultations_add_custom_css', 100);
//TODO: main header colors are also changing
//add_action('admin_head', 'ezpzconsultations_add_custom_css', 100);


function ezpzconsultations_add_custom_css() {

  $theme_opts = ezpzconsultations_get_theme_opts();

  if (!empty($theme_opts)) :
    // Example usage with theme options

    //Global Colours
    $primary_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['primary_colour'] : "#ff3c74";
    $secondary_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['secondary_colour'] : "#ffa0cd";
    $neutral_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['neutral_colour'] : "#64748b";
    $success_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['success_colour'] : "#00c851";
    $warning_colour = isset($theme_opts['globalcolors']['secondary_colour']) ? $theme_opts['globalcolors']['warning_colour'] : "#FFBB33";
    $error_colour = isset($theme_opts['globalcolors']['primary_colour']) ? $theme_opts['globalcolors']['error_colour'] : "#FF4444";
    $color_headings_preferred = isset($theme_opts['globalcolors']['headings_preferred_colour']) ? $theme_opts['globalcolors']['headings_preferred_colour'] : 'var(--color-headings-preferred)';
    $text_colour = isset($theme_opts['globalcolors']['text_colour']) ? $theme_opts['globalcolors']['text_colour'] : 'var(--text-color)';
    $accent_colour = isset($theme_opts['globalcolors']['accent_colour']) ? $theme_opts['globalcolors']['accent_colour'] : 'var(--color-primary-500)';

    $custom_css = isset($theme_opts['customcss']['custom_css']) ? $theme_opts['customcss']['custom_css'] : "";

    //Typography / Fonts / Heading Colours
    $primary_font_family = isset($theme_opts['globaltypography']['primary_font_family']) ? $theme_opts['globaltypography']['primary_font_family'] : 'var(--font-primary)';
    $secondary_font_family = isset($theme_opts['globaltypography']['secondary_font_family']) ? $theme_opts['globaltypography']['secondary_font_family'] : 'var(--font-primary)';


    //Buttons
    $button_primary_border_radius = isset($theme_opts['buttons']['button_primary_border_radius']) ? $theme_opts['buttons']['button_primary_border_radius'] . 'px' : '.5rem';
    $button_primary_font_weight = isset($theme_opts['buttons']['button_primary_font_weight']) ? $theme_opts['buttons']['button_primary_font_weight'] : '400';

    $button_secondary_border_radius = isset($theme_opts['buttons']['button_secondary_border_radius']) ? $theme_opts['buttons']['button_secondary_border_radius'] . 'px' : '.5rem';
    $button_secondary_font_weight = isset($theme_opts['buttons']['button_secondary_font_weight']) ? $theme_opts['buttons']['button_secondary_font_weight'] : '400';

    $padding_decrease = isset($theme_opts['globalpadding']['padding_decrease']) ? $theme_opts['globalpadding']['padding_decrease'] : '0';
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
      /* Paddings */
      header.block,
      section.block {
        padding-bottom: calc(0.5rem - <?php echo $padding_decrease; ?>px);
        padding-top: calc(2rem - <?php echo $padding_decrease; ?>px);
        position: relative;
      }

      @media (min-width: 37.5em) {

        header.block,
        section.block {
          padding-bottom: calc(2.5rem - <?php echo $padding_decrease; ?>px);
          padding-top: calc(4rem - <?php echo $padding_decrease; ?>px);
        }
      }

      @media (min-width: 56.25em) {

        header.block,
        section.block {
          padding-bottom: calc(4.5rem - <?php echo $padding_decrease; ?>px);
          padding-top: calc(6rem - <?php echo $padding_decrease; ?>px);
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
      }

      /* Headings - Global Typography */
      <?php

      // Define an array of headings and their corresponding CSS properties
      $font_weight = [
        'h1' => 'font_h1_font_weight',
        'h2' => 'font_h2_font_weight',
        'h3' => 'font_h3_font_weight',
        'h4' => 'font_h4_font_weight'
      ];

      // Iterate through headings to generate CSS for font weight
      foreach ($font_weight as $tag => $weight_key) {
        // Check if the weight value is set in $theme_opts['globaltypography'], otherwise fallback to a default value
        $weight = isset($theme_opts['globaltypography'][$weight_key]) && $theme_opts['globaltypography'][$weight_key] !== '' ? $theme_opts['globaltypography'][$weight_key] : '500';
        // Output the CSS
        echo "$tag {
          font-weight: $weight;
      }";
      }

      // Define an array of font families for headings
      $font_families = [
        'h1' => 'font_h1_family_h1',
        'h2' => 'font_h2_family_h2',
        'h3' => 'font_h3_family_h3',
        'h4' => 'font_h4_family_h4'
      ];

      // Iterate through font_families to generate CSS for font family
      foreach ($font_families as $tag => $font_key) {
        $font_family = ezpzconsultations_get_font_family($font_key, $theme_opts);

        if ($font_family) {
          echo "$tag {
 font-family: $font_family;
        }

        ";
        }
      }

      ?>

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

      ?><?php if (!empty($text_colour) || !empty($accent_colour)) : ?>body {
        <?php if ($text_colour) : ?>--color-text: <?php echo $text_colour; ?>;

        <?php endif; ?><?php if ($accent_colour) : ?>accent-color,
        a,
        a:link,
        a:visited {
          color: <?php echo $accent_colour; ?>;
        }

        <?php endif; ?>
      }

      <?php endif; ?>

      /* Buttons */
      /* Primary button */
      <?php if (!empty($button_primary_font_weight) || !empty($button_secondary_border_radius)) : ?>.button,
      [type=button],
      [type=reset],
      [type=submit],
      a.button,
      .button {
        <?php if ($button_primary_font_weight) : ?>font-weight: <?php echo $button_primary_font_weight; ?>;
        <?php endif; ?><?php if ($button_primary_border_radius) : ?>border-radius: <?php echo $button_primary_border_radius; ?>;
        <?php endif; ?>--button-color-text: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?>;
        --button-hover-color-text: <?php echo ezpzconsultations_calculate_contrast($primary_colour_500); ?>
      }

      <?php endif; ?>

      /* Secondary button */
      <?php if (!empty($button_secondary_font_weight) || !empty($button_secondary_border_radius)) : ?>.button.secondary,
      a.button.secondary {
        <?php if ($button_secondary_font_weight) : ?>font-weight: <?php echo $button_secondary_font_weight; ?>;
        <?php endif; ?><?php if ($button_secondary_border_radius) : ?>border-radius: <?php echo $button_secondary_border_radius; ?>;
        <?php endif; ?>--button-color-text: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500); ?>;
        --button-hover-color-text: <?php echo ezpzconsultations_calculate_contrast($secondary_colour_500); ?>
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