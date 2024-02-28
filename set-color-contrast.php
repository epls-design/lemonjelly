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
//have second argument to have the css value var(asdasd);

// $primary_color_500 = $primary_palette['500'];
// $secondary_color_500 = $secondary_palette['500'];


// $primary_text = calculateContrast($primary_color_500);
// $secondary_text = calculateContrast($secondary_color_500);

// echo "Text color for primary color: $primary_text<br>";
// echo "Text color for secondary color: $secondary_text";


// Example usage
// $primaryColor = '#FF0000'; // Replace with your primary color
// $secondaryColor = '#00FF00'; // Replace with your secondary color

// $primaryText = calculateContrast($primaryColor);
// $secondaryText = calculateContrast($secondaryColor);

// echo "Text color for primary color: $primaryText<br>";
// echo "Text color for secondary color: $secondaryText";
