<?php

/**
 * Theme Customizer
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;


/**
 * Creates theme.json if it doesn't exist by copying theme.json from the parent theme
 * This allows us to use the theme.json to set the color palette dynamically from the ACF options
 */
add_action('init', 'lemonjelly_create_theme_json', 20);
function lemonjelly_create_theme_json() {
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
 * Also saves all CSS to lemonjelly.css
 */
add_action('acf/options_page/save', 'lemonjelly_save_theme_settings_to_json', 20, 2);
function lemonjelly_save_theme_settings_to_json($post_id, $menu_slug) {
  if ($menu_slug !== 'theme-designer') return;

  // TODO: Add in anchor link color and hover color

  // Get newly saved values for the theme settings page.
  $acf = get_fields($post_id);

  $theme_colors = [
    'white' => '#ffffff',
    'black' => '#000000'
  ];

  $restricted_palette = [
    'white' => '#ffffff',
  ];

  $block_bg_variants = ['25', '100', '500', '700'];

  $palettes = [
    'primary' => isset($acf['primary_colour']) && !empty($acf['primary_colour']) ? lemonjelly_make_color_palette($acf['primary_colour']) : [],
    'secondary' => isset($acf['secondary_colour']) && !empty($acf['secondary_colour']) ? lemonjelly_make_color_palette($acf['secondary_colour']) : [],
    'tertiary' => isset($acf['tertiary_colour']) && !empty($acf['tertiary_colour']) ? lemonjelly_make_color_palette($acf['tertiary_colour']) : [],
    'neutral' => isset($acf['neutral_colour']) && !empty($acf['neutral_colour']) ? lemonjelly_make_color_palette($acf['neutral_colour']) : [],
  ];

  if (!empty($palettes['neutral'])) {
    foreach ($block_bg_variants as $variant) {
      $theme_colors['neutral-' . $variant] = $palettes['neutral'][$variant];
    }
    $restricted_palette['neutral-25'] = $palettes['neutral']['25'];
  }

  if (!empty($palettes['primary'])) {
    foreach ($block_bg_variants as $variant) {
      $theme_colors['primary-' . $variant] = $palettes['primary'][$variant];
    }
    $restricted_palette['primary-25'] = $palettes['primary']['25'];
  }

  if (!empty($palettes['secondary'])) {
    foreach ($block_bg_variants as $variant) {
      $theme_colors['secondary-' . $variant] = $palettes['secondary'][$variant];
    }
    $restricted_palette['secondary-25'] = $palettes['secondary']['25'];
  }

  if (!empty($palettes['tertiary'])) {
    foreach ($block_bg_variants as $variant) {
      $theme_colors['tertiary-' . $variant] = $palettes['tertiary'][$variant];
    }
    $restricted_palette['tertiary-25'] = $palettes['tertiary']['25'];
  }

  $theme_json = get_stylesheet_directory() . '/theme.json';
  if (file_exists($theme_json)) {

    $theme_json = file_get_contents($theme_json);
    $theme_json = json_decode($theme_json, true);

    foreach ($theme_colors as $key => $color) {

      // Convert key to friendly name (e.g. primary-100 to Primary 100)
      $name = ucwords(str_replace('-', ' ', $key));

      $palette[] = array(
        "slug" => $key,
        "color" => $color,
        "name" => $name,
      );
    }

    foreach ($restricted_palette as $key => $color) {
      $restricted_colors[] = array(
        "slug" => $key,
        "color" => $color,
        "name" => ucwords(str_replace('-', ' ', $key)),
      );
    }

    // Set the new palette
    $theme_json['settings']['color']['palette'] = $palette;

    // Restrict background options on certain blocks
    $theme_json['settings']['blocks']['ezpz/timeline']['color']['palette'] = $restricted_colors;
    $theme_json['settings']['blocks']['ezpz/image-compare']['color']['palette'] = $restricted_colors;
    $theme_json['settings']['blocks']['ezpz/feedback-map']['color']['palette'] = $restricted_colors;

    // Force the cover block to use the new palette because the parent only allows black / white
    if (isset($theme_json['settings']['blocks']['ezpz/cover'])) {
      $theme_json['settings']['blocks']['ezpz/cover']['color']['palette'] = $palette;
    }

    // Set the new theme.json
    $theme_json = json_encode($theme_json, JSON_PRETTY_PRINT);
    file_put_contents(get_stylesheet_directory() . '/theme.json', $theme_json);
  }


  /**
   * Write to lemonjelly.css
   */

  $css_data = '';

  /**
   *  Generate Color Palettes and add to the generated CSS
   */

  $css_data .= ':root {';

  $css_data .= '--color-text: ' . $acf['text_color'] . ';';
  $css_data .= '--color-headings-preferred: ' . $acf['headings_preferred_colour'] . ';';

  foreach ($palettes as $type => $palette) {
    if (!empty($palette)) {
      $css_data .= lemonjelly_convert_colormap_to_css($palette, $type);
    }
  }

  /**
   * TYPOGRAPHY
   */

  if (isset($acf['primary_font_family']) && $acf['primary_font_family'] != '') {
    $font = $acf['primary_font_family'];
    // Remove any trailing ';'
    $font = rtrim($font, ';');
    $css_data .= '--font-primary:' . $font . ';';
  }
  if (isset($acf['secondary_font_family']) && $acf['secondary_font_family'] != '') {
    $font = $acf['secondary_font_family'];
    // Remove any trailing ';'
    $font = rtrim($font, ';');
    $css_data .= '--font-secondary:' . $font . ';';
  }

  $css_data .= '}';

  $css_data .= 'html {';
  $css_data .= 'font-family: var(' . $acf['body_font_family'] . ');';
  $css_data .= '}';


  // LOOP FOR HEADINGS
  for ($i = 1; $i <= 6; $i++) {
    $css_data .= 'h' . $i . ' {';
    $css_data .= 'font-weight: ' . $acf['font_h' . $i]['font_weight'] . ';';


    $css_data .= 'font-family: var(' . $acf['font_h' . $i]['font_family'] . ');';
    $css_data .= '}';
  }

  /**
   * BUTTONS
   */
  if (
    isset($acf['button_border_radius']) ||
    isset($acf['button_font_weight']) ||
    isset($acf['button_font_family'])
  ) {
    $css_data .= '[type=button],[type=reset],[type=submit],a.button,.button {';

    if (isset($acf['button_border_radius'])) {
      $rem = $acf['button_border_radius'] / 16;
      $css_data .= 'border-radius: ' . $rem . 'rem;';
    }

    if (isset($acf['button_font_weight'])) {
      $css_data .= 'font-weight: ' . $acf['button_font_weight'] . ';';
    }

    if (isset($acf['button_font_family'])) {
      $css_data .= 'font-family: var(' . $acf['button_font_family'] . ');';
    }

    $css_data .= '}';
  }

  if (isset($acf['cards_border_radius'])) {
    $css_data .= '.card {';
    $rem = $acf['cards_border_radius'] / 16;
    $css_data .= 'border-radius: ' . $rem . 'rem;';
    $css_data .= '}';
  }

  /**
   * BLOCK PADDING
   */
  if (isset($acf['block_padding_mobile'])) {
    $rem = $acf['block_padding_mobile'] / 16 . 'rem';
    $css_data .= ':root{--block-padding: ' . $rem . ';}';
  }
  if (isset($acf['block_padding_tablet'])) {
    $rem = $acf['block_padding_tablet'] / 16 . 'rem';
    $css_data .= '@media (min-width: 37.5em) {:root{--block-padding: ' . $rem . ';}}';
  }
  if (isset($acf['block_padding_desktop'])) {
    $rem = $acf['block_padding_desktop'] / 16 . 'rem';
    $css_data .= '@media (min-width: 56.25em) {:root{--block-padding: ' . $rem . ';}}';
  }

  /**
   * Generate the markup for block backgrounds
   */
  $bg_palettes = [];
  foreach ($palettes as $key => $palette) {
    $bg_palettes[$key] = $palette;
  }
  $css_data .= lemonjelly_generate_block_bg_css($block_bg_variants, $bg_palettes);


  /**
   * NAVBAR
   */
  $css_data .= lemonjelly_navbar_css($acf);

  /**
   * BLOCK STYLES
   */
  if (isset($acf['timeline_color'])) {
    $variant = isset($acf['timeline_color_variant']) ? $acf['timeline_color_variant'] : '500';
    $css_data .= '.timeline { --timeline-color-theme: var(--color-' . $acf['timeline_color'] . '-' . $variant . '); }';
  }

  if (isset($acf['custom_css']) && !empty($acf['custom_css'])) {
    $css_data .= $acf['custom_css'];
  }

  // Minify the CSS data
  $css_data = preg_replace('/\s+/', ' ', $css_data);

  // Write the CSS data to the file
  $css_file_path = get_stylesheet_directory() . '/lemonjelly.css';
  $css_file = fopen($css_file_path, "w");
  fwrite($css_file, $css_data);
  fclose($css_file);

  return;
}