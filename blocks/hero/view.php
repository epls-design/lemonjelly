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

$block_attributes = jellypress_get_block_attributes($block, $context);

$allowed_blocks = jellypress_get_allowed_blocks();
$block_template = jellypress_get_block_template();

$block_attributes['class'] .= ' page-header hero';
$fields = get_fields();

$background_type = isset($fields['background_type']) ? $fields['background_type'] : '';

//var_dump($background_type);
$hero_class = '';

if ($background_type == 'image') {
  $background_image = isset($fields['background_image']) ? $fields['background_image'] : '';
  //var_dump("it is image");
} elseif ($background_type == 'color') {
  $bg_color = isset($fields['background_color']) ? $fields['background_color'] : '';

  //var_dump("it is color");
} elseif ($background_type == 'video') {
  $hero_class = "video-header";
  $video_array = isset($fields['background_video']) ? $fields['background_video'] : array();
  $poster = $video_array['video_poster'];
  $video_source = isset($fields['video_source']) ? $fields['video_source'] : '';
  //var_dump("it is video");
}
?>


<style>
  .hero-image:after,
  .video-header:after {
    opacity: <?php echo $fields['background_overlay_opacity'] / 100; ?>;
  }
</style>

<header class="<?php echo $block_attributes['class']; ?> <?php echo $hero_class; ?>" <?php echo $block_attributes['anchor']; ?> <?php if (!empty($bg_color)) {
                                                                                                                                  echo 'style="background-color:' . $bg_color . ';"';
                                                                                                                                } ?>>

  <?php if (isset($background_image)) :   ?>
    <figure class="hero-image">
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
      <?php if (isset($video_source)) : ?>

        <video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">
          <source src="<?php echo $video_source; ?>" type="video/mp4">
        </video>
      <?php endif; ?>
      <style type="text/css">
        #hero {
          background-color: <?php the_sub_field('color'); ?>;
        }
      </style>
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