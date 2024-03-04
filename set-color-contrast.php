<?php
function ezpzconsultations_calculate_contrast($color) {
  // Convert hex color to RGB
  $hex = str_replace('#', '', $color);
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));

  // Calculate luminance
  $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

  // Choose text color based on luminance
  return $luminance > 0.5 ? '#2c333d' : '#ffffff'; // --color-neutral-900 = 2c333d for light backgrounds, White for dark backgrounds
}
