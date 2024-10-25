<?php

/**
 * LemonJelly Hero Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 *        This is either the post ID currently being displayed inside a query loop,
 *        or the post ID of the post hosting this block.
 * @param array $context The context provided to the block by the post or it's parent block.
 * @param array $block_attributes Processed block attributes to be used in template.
 * @param array $fields Array of ACF fields used in this block.
 *
 * Block registered with ACF using block.json
 * @link https://www.advancedcustomfields.com/resources/blocks/
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
$fields = get_fields();

$block_attributes = jellypress_get_block_attributes($block, $context);
$allowed_blocks = jellypress_get_allowed_blocks(['ezpz/post-title', 'core/paragraph', 'ezpz/buttons']);

$block_template = array(
  array(
    'ezpz/post-title',
    array(),
    array()
  ),
  array(
    'core/paragraph',
    array(
      'fontSize' => 'medium',
      'placeholder' => 'Add an optional preamble here',
    ),
    array()
  ),
  // array('ezpz/buttons', array())
);
$block_template = jellypress_get_block_template($block_template);


$block_classes = explode(' ', $block_attributes['class']);

// Remove any class from $block_classes that looks like 'bg-*'
$block_classes = preg_grep('/\b(bg-\S+)\b/', $block_classes, PREG_GREP_INVERT);

$additional_classes = [
  get_post_type() . '-header',
  'hero',
];

$background_type = isset($fields['background_type']) ? $fields['background_type'] : '';

if ($background_type == 'color') {
  $additional_classes[] = 'bg-' . $block_attributes['bg_color'];
}

$opacity = isset($fields['background_overlay_opacity']) ? $fields['background_overlay_opacity'] : 60;


// Merge $block_classes and $additional_classes
$block_classes = array_merge($block_classes, $additional_classes);
$block_attributes['class'] = implode(' ', $block_classes);

$min_height = isset($fields['min_height']) ? $fields['min_height'] . 'dvh' : '30dvh';
?>


<header class="<?php echo $block_attributes['class']; ?>" <?php echo $block_attributes['anchor']; ?> style="min-height: <?php echo $min_height; ?>">

  <?php if ($background_type != 'color'):

    $figure_class = 'hero-figure hero-' . $background_type . ' has-overlay overlay-opacity-' . $opacity . ' overlay-' . $block_attributes['bg_color'];

    if ($fields['desaturate']) $figure_class .= ' is-desaturated';

    echo '<figure class="' . $figure_class . '">';

  ?>

  <?php if ($background_type == 'image') {
      $class = '';
      if ($fields['parallax']) $class .= 'is-parallax';
      echo wp_get_attachment_image($fields['background_image'], 'full', null, array('class' => $class));
    } elseif ($background_type == 'video') {
      $video = $fields['background_video'];
    ?>
  <video playsinline autoplay muted loop disablePictureInPicture controlsList=" nodownload" poster="<?php echo wp_get_attachment_image_url($video['video_poster'], 'full'); ?>">
    <source src="<?php echo $video['video_source']; ?>" type="video/mp4">
  </video>

  <?php if ($video['external_video_link']) {
        // TODO: FIX THIS
      ?>
  <a href="javascript:void(0);" class="align-middle play-button no-underline hide-below-md">
    <?php echo jellypress_icon('play'); ?>
    <span class="hero-play-label"><?php echo __('Play full video', 'lemonjelly'); ?></span>
  </a>
  <?php
      }
    }
    echo '</figure>';
  endif;
  ?>

  <div class="container">
    <div class="row">
      <div class="col md-10 lg-8">
        <InnerBlocks templateLock=false allowedBlocks="<?php echo $allowed_blocks; ?>" template="<?php echo $block_template; ?>" />
      </div>
    </div>
  </div>
</header>
