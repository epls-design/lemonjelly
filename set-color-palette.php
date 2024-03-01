<?php

function multiply_colors($color1, $color2) {
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

function mix_colors($color1, $color2, $weight) {
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
  $dark = multiply_colors($color, $color);

  // Generate the palette
  $palette = array(
    '25' => mix_colors('#ffffff', $color, 0.06),
    '50' => mix_colors('#ffffff', $color, 0.12),
    '100' => mix_colors('#ffffff', $color, 0.30),
    '200' => mix_colors('#ffffff', $color, 0.50),
    '300' => mix_colors('#ffffff', $color, 0.70),
    '400' => mix_colors('#ffffff', $color, 0.85),
    '500' => $color,
    '600' => mix_colors($dark, $color, 0.87),
    '700' => mix_colors($dark, $color, 0.70),
    '800' => mix_colors($dark, $color, 0.54),
    '900' => mix_colors($dark, $color, 0.25),
    '1000' => mix_colors($dark, $color, 0),
  );

  return $palette;
}
