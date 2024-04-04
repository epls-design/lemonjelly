<?php
//Colour Contrast Function
function ezpzconsultations_calculate_contrast($color, $neutral_colour_900) {
  // Convert hex color to RGB
  $hex = str_replace('#', '', $color);
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));

  // Calculate luminance
  $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

  // Choose text color based on luminance
  return $luminance > 0.5 ? $neutral_colour_900 : '#ffffff'; // --color-neutral-900 = 2c333d for light backgrounds, White for dark backgrounds
  //var_dump($neutral_colour_900);
}



//Colour Palette Functions
function ezpzconsultations_darken($color, $percent) {
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

function ezpzconsultations_multiply_colors($color1, $color2) {
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

function ezpzconsultations_mix_colors($color1, $color2, $weight) {
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

function ezpzconsultations_make_color_palette($color) {
  // Calculate dark color by multiplying color with itself
  $dark = ezpzconsultations_multiply_colors($color, $color);
  $dark = ezpzconsultations_darken($dark, 0.5);
  // Generate the palette
  $palette = array(
    '25' => ezpzconsultations_mix_colors('#ffffff', $color, 0.1),
    '50' => ezpzconsultations_mix_colors('#ffffff', $color, 0.2),
    '100' => ezpzconsultations_mix_colors('#ffffff', $color, 0.30),
    '200' => ezpzconsultations_mix_colors('#ffffff', $color, 0.50),
    '300' => ezpzconsultations_mix_colors('#ffffff', $color, 0.70),
    '400' => ezpzconsultations_mix_colors('#ffffff', $color, 0.85),
    '500' => $color,
    '600' => ezpzconsultations_mix_colors($dark, $color, 0.86),
    '700' => ezpzconsultations_mix_colors($dark, $color, 0.72),
    '800' => ezpzconsultations_mix_colors($dark, $color, 0.58),
    '900' => ezpzconsultations_mix_colors($dark, $color, 0.44),
    '1000' => ezpzconsultations_mix_colors($dark, $color, 0.35),
  );

  return $palette;
}
