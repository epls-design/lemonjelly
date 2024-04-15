<?php

/**
 * Hero Block Template.
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
 * @package jellypress
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
$fields = get_fields();

$block_attributes = jellypress_get_block_attributes($block, $context);

$allowed_blocks = jellypress_get_allowed_blocks();

$block_template = array(
  array(
    'ezpz/post-title', array(), array()
  ),
  array(
    'core/paragraph', array('fontSize' => 'medium', 'placeholder' => 'Write Something here...'), array()
  ),
  array('ezpz/buttons', array())
);

$block_template = jellypress_get_block_template($block_template);

$block_attributes['class']  .= ' page-header hero ';


$background_type = isset($fields['background_type']) ? $fields['background_type'] : '';


$hero_class = '';

if ($background_type == 'image') {
  $background_image = isset($fields['background_image']) ? $fields['background_image'] : '';
} elseif ($background_type == 'color') {
} elseif ($background_type == 'video') {
  $hero_class = "video-header";
  $video_array = isset($fields['background_video']) ? $fields['background_video'] : array();
  $poster = $video_array['video_poster'];
  $video_source = isset($fields['video_source']) ? $fields['video_source'] : '';
}

?>

<?php
// Check if $fields is an array and 'background_overlay_opacity' is set within it
if (is_array($fields) && isset($fields['background_overlay_opacity'])) {
  $background_overlay_opacity = $fields['background_overlay_opacity'];
} else {
  // Default value
  $background_overlay_opacity = 0.10;
}

?>


<header class="<?php echo $block_attributes['class']; ?>  <?php echo $hero_class; ?>" <?php echo $block_attributes['anchor']; ?>>
  <?php if (!empty($background_image)) :   ?>
    <figure class="hero-image overlay-opacity-<?php if ($background_overlay_opacity) echo $background_overlay_opacity; ?>">
      <?php echo wp_get_attachment_image($background_image, 'full'); ?>
    </figure>
  <?php endif; ?>
  <?php if (have_rows('background_video')) : ?>
    <?php while (have_rows('background_video')) : the_row();

      // Get sub field values.
      $video_poster = get_sub_field('video_poster');
      $video_source = get_sub_field('video_source');
      $external_video_link = get_sub_field('external_video_link');
      $aspect_ratio = get_sub_field('aspect_ratio');

    ?>
      <?php if (!empty($external_video_link)) : ?>
        <div class="hero-video-full">
          var_dump
          <?php echo jellypress_embed_video(get_sub_field('external_video_link')); ?>
        </div>

        <a href="javascript:void(0);" class="align-middle play-button no-underline hide-below-md">
          <?php echo jellypress_icon('play') . jellypress_icon('close'); ?><span class="hero-play-label"><?php echo __('Play full video', 'jellypress'); ?></span>
        </a>
      <?php endif; ?>

      <?php if (!empty($video_source)) : ?>
        <div class="hero-video-overlay hero-video-overlay-opacity-<?php if ($background_overlay_opacity) echo $background_overlay_opacity; ?>"></div>
        <video playsinline autoplay muted loop disablePictureInPicture controlsList="nodownload" poster="<?php echo wp_get_attachment_image_url($video_poster, 'large'); ?>">
          <source src="<?php echo $video_source; ?>" type="video/mp4">
        </video>
      <?php endif; ?>
    <?php endwhile; ?>
  <?php endif; ?>


  <div class="container">
    <div class="row">
      <div class="col md-10 lg-8">
        <InnerBlocks templateLock="all" allowedBlocks="<?php echo $allowed_blocks; ?>" template="<?php echo $block_template; ?>" />
      </div>
    </div>
  </div>
</header>