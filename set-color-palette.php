<?php

function lighten($color, $amount) {
  // Convert HEX to RGB
  $r = hexdec(substr($color, 1, 2));
  $g = hexdec(substr($color, 3, 2));
  $b = hexdec(substr($color, 5, 2));

  // Adjust each channel
  $r += $amount;
  $g += $amount;
  $b += $amount;

  // Ensure they are within bounds
  $r = max(0, min(255, $r));
  $g = max(0, min(255, $g));
  $b = max(0, min(255, $b));

  // Convert back to HEX
  $hex = sprintf("#%02x%02x%02x", $r, $g, $b);
  return $hex;
}

function ezpzconsultations_make_color_palette($color) {
  $palette = array();
  // Adjust lightness for each shade
  $palette['25'] = lighten($color, 120);
  $palette['50'] = lighten($color, 90);
  $palette['100'] = lighten($color, 60);
  $palette['200'] = lighten($color, 45);
  $palette['300'] = lighten($color, 30);
  $palette['400'] = lighten($color, 15);
  $palette['500'] = $color; // The main color
  $palette['600'] = lighten($color, -15);
  $palette['700'] = lighten($color, -30);
  $palette['800'] = lighten($color, -45);
  $palette['900'] = lighten($color, -60);
  $palette['1000'] = lighten($color, -75);
  //100 of primary secondary and nutral

  return $palette;
}
