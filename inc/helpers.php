<?php

/**
 * Child Theme Helpers
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Calculate the contrast ratio between two colors using the WCAG 2.0 formula.
 */
function lemonjelly_calculate_contrast($color, $dark_color = '#000') {
  // Convert hex color to RGB
  $hex = str_replace('#', '', $color);
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));

  // Calculate luminance
  $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

  // Choose text color based on luminance
  return $luminance > 0.5 ? $dark_color : '#ffffff';
}

/**
 * Darken a color by a percentage.
 */
function lemonjelly_darken($color, $percent) {
  // Convert hexadecimal color string to RGB array
  $rgb = sscanf($color, "#%02x%02x%02x");

  // Darken each RGB component by the percentage
  $r = $rgb[0] * (1 - $percent);
  $g = $rgb[1] * (1 - $percent);
  $b = $rgb[2] * (1 - $percent);

  // Ensure they are within bounds
  $r = max(0, min(255, round($r)));
  $g = max(0, min(255, round($g)));
  $b = max(0, min(255, round($b)));

  // Convert back to hexadecimal color string
  $dark_color = sprintf("#%02x%02x%02x", $r, $g, $b);

  return $dark_color;
}

function lemonjelly_multiply_colors($color1, $color2) {
  // Convert HEX to RGB
  $r1 = hexdec(substr($color1, 1, 2));
  $g1 = hexdec(substr($color1, 3, 2));
  $b1 = hexdec(substr($color1, 5, 2));

  $r2 = hexdec(substr($color2, 1, 2));
  $g2 = hexdec(substr($color2, 3, 2));
  $b2 = hexdec(substr($color2, 5, 2));

  // Multiply each channel
  $mixed_r = round($r1 * $r2 / 255);
  $mixed_g = round($g1 * $g2 / 255);
  $mixed_b = round($b1 * $b2 / 255);

  // Convert back to HEX
  $hex = sprintf("#%02x%02x%02x", $mixed_r, $mixed_g, $mixed_b);
  return $hex;
}

function lemonjelly_mix_colors($color1, $color2, $weight) {
  // Convert HEX to RGB
  $r1 = hexdec(substr($color1, 1, 2));
  $g1 = hexdec(substr($color1, 3, 2));
  $b1 = hexdec(substr($color1, 5, 2));

  $r2 = hexdec(substr($color2, 1, 2));
  $g2 = hexdec(substr($color2, 3, 2));
  $b2 = hexdec(substr($color2, 5, 2));

  // Calculate mixed color
  $mixed_r = round($r1 * (1 - $weight) + $r2 * $weight);
  $mixed_g = round($g1 * (1 - $weight) + $g2 * $weight);
  $mixed_b = round($b1 * (1 - $weight) + $b2 * $weight);

  // Ensure they are within bounds
  $mixed_r = max(0, min(255, $mixed_r));
  $mixed_g = max(0, min(255, $mixed_g));
  $mixed_b = max(0, min(255, $mixed_b));

  // Convert back to HEX
  $hex = sprintf("#%02x%02x%02x", $mixed_r, $mixed_g, $mixed_b);
  return $hex;
}

function lemonjelly_make_color_palette($color) {
  // Calculate dark color by multiplying color with itself
  $dark = lemonjelly_multiply_colors($color, $color);
  $dark = lemonjelly_darken($dark, 0.5);
  // Generate the palette
  $palette = array(
    '25' => lemonjelly_mix_colors('#ffffff', $color, 0.1),
    '50' => lemonjelly_mix_colors('#ffffff', $color, 0.2),
    '100' => lemonjelly_mix_colors('#ffffff', $color, 0.30),
    '200' => lemonjelly_mix_colors('#ffffff', $color, 0.50),
    '300' => lemonjelly_mix_colors('#ffffff', $color, 0.70),
    '400' => lemonjelly_mix_colors('#ffffff', $color, 0.85),
    '500' => $color,
    '600' => lemonjelly_mix_colors($dark, $color, 0.86),
    '700' => lemonjelly_mix_colors($dark, $color, 0.72),
    '800' => lemonjelly_mix_colors($dark, $color, 0.58),
    '900' => lemonjelly_mix_colors($dark, $color, 0.44),
    '1000' => lemonjelly_mix_colors($dark, $color, 0.35),
  );

  return $palette;
}


/**
 * Returns CSS custom properties for a given color palette.
 */
function lemonjelly_convert_colormap_to_css($array, $prefix = '') {
  $css = '';
  foreach ($array as $key => $value) {
    $css .= "--color-$prefix-$key: $value;\n";
  }
  return $css;
}


function lemonjelly_generate_block_bg_css($variants, $pallettes) {

  $css = '';
  foreach ($pallettes as $key => $palette) {
    foreach ($variants as $variant) {
      // TODO: Also add in for the .has-overlay class - it needs to be smart enough to know the opacity too
      $css .= ".block.bg-$key-$variant {\n";
      $css .= "--color-section-background: var(--color-$key-$variant);\n";

      // ONLY DO TEXT AND HEADINGS IF DIFFERENT TO DEFAULT
      $text_color = lemonjelly_calculate_contrast($palette[$variant], get_field('text_color', 'option'));
      if ($text_color != get_field('text_color', 'option')) {
        $css .= "--color-section-text: " . $text_color . ";\n";
      }

      $headings_color = lemonjelly_calculate_contrast($palette[$variant], get_field('headings_colour', 'option'));
      if ($headings_color != get_field('headings_colour', 'option')) {
        $css .= "--color-section-headings: " . $headings_color . ";\n";
      } else {
        $css .= "--color-section-headings: var(--color-headings-preferred);\n";
      }
      $css .= "}\n";

      // Padding Hack
      $css .= ".block.bg-$key-$variant:not(.is-full-width) + .block.bg-$key-$variant:not(.is-full-width) { padding-top: 0; }\n";
      $css .= ".block.bg-$key-$variant:not(.is-full-width) + .block.bg-$key-$variant.is-full-width .inner-content { padding-top: 0; }\n";

      $css .= ".overlay-$key-$variant {\n";
      $css .= "--overlay-color: var(--color-$key-$variant);\n";
      // ONLY DO TEXT AND HEADINGS IF DIFFERENT TO DEFAULT
      $text_color = lemonjelly_calculate_contrast($palette[$variant], get_field('text_color', 'option'));
      if ($text_color != get_field('text_color', 'option')) {
        $css .= "--color-section-text: " . $text_color . ";\n";
      }

      $headings_color = lemonjelly_calculate_contrast($palette[$variant], get_field('headings_colour', 'option'));
      if ($headings_color != get_field('headings_colour', 'option')) {
        $css .= "--color-section-headings: " . $headings_color . ";\n";
      } else {
        $css .= "--color-section-headings: var(--color-headings-preferred);\n";
      }

      $css .= "}\n";
    }
  }
  return $css;
}

/**
 * Generates CSS styles for navbar opts
 */
function lemonjelly_navbar_css($acf) {
  $css = ':root {';

  if ($acf['navbar_background']) {
    if ($acf['navbar_background'] == 'white') {
      $variable_val = 'var(--color-white)';
    } else {
      $variable_val = 'var(--color-' . $acf['navbar_background'] . '-' . $acf['navbar_background_color_variant'] . ')';
    }
    $css .= "--navbar-bg-color: " . $variable_val . ";\n";
  }

  if ($acf['navbar_link_color']) {
    if ($acf['navbar_link_color'] == 'white' || $acf['navbar_link_color'] == 'black') {
      $variable_val = 'var(--color-' . $acf['navbar_link_color'] . ')';
    } else {
      $variable_val = 'var(--color-' . $acf['navbar_link_color'] . '-' . $acf['navbar_link_color_variant'] . ')';
    }
    $css .= "--navbar-color: " . $variable_val . ";\n";
  }

  if ($acf['navbar_link_hover_background_color']) {
    if ($acf['navbar_link_hover_background_color'] == 'white') {
      $variable_val = 'var(--color-white)';
    } else {
      $variable_val = 'var(--color-' . $acf['navbar_link_hover_background_color'] . '-' . $acf['navbar_link_hover_background_color_variant'] . ')';
    }
    $css .= "--navbar-bg-color-hover: " . $variable_val . ";\n";
  }

  if ($acf['navbar_link_hover_color']) {
    if ($acf['navbar_link_hover_color'] == 'white' || $acf['navbar_link_hover_color'] == 'black') {
      $variable_val = 'var(--color-' . $acf['navbar_link_hover_color'] . ')';
    } else {
      $variable_val = 'var(--color-' . $acf['navbar_link_hover_color'] . '-' . $acf['navbar_link_hover_color_variant'] . ')';
    }
    $css .= "--navbar-color-hover: " . $variable_val . ";\n";
  }

  if ($acf['navbar_font_family']) {
    $css .= "--navbar-font-family: var(" . $acf['navbar_font_family'] . ");\n";
  }

  if ($acf['navbar_link_font_weight']) {
    $css .= "--navbar-font-weight: " . $acf['navbar_link_font_weight'] . ";\n";
  }
  $css .= "}\n";
  return $css;
}
