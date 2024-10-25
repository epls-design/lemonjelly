<?php

/**
 * Timeline Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
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

// TODO: PREVENT BACKGROUNDS OTHER THAN WHITE, LIGHT, ETC - DONT ALLOW THIS OIN A DARK BG

$block_attributes = jellypress_get_block_attributes($block, $context);

$allowed_blocks = jellypress_get_allowed_blocks();
$block_template = jellypress_get_block_template();

$fields = get_fields();
$text_align = $block_attributes['text_align'];

?>

<section class="<?php echo $block_attributes['class']; ?>" <?php echo $block_attributes['anchor']; ?>>
  <figure class="container">

    <?php if ($content || $is_preview) : ?>
    <header class="row justify-center">
      <div class="col md-10 lg-8">
        <InnerBlocks className="<?php echo $text_align; ?>" allowedBlocks=" <?php echo $allowed_blocks; ?>" template="<?php echo $block_template; ?>" />
      </div>
    </header>
    <?php endif; ?>

    <?php
    $timelines = $fields['timeline'];

    if ($timelines) {
    ?>

    <figure class="timeline">
      <?php
        $count = 0;
        foreach ($timelines as $timeline) {
          $title =  $timeline['title'];
          $information =  $timeline['information'];
          $date =  $timeline['date'];
          $img =  $timeline['image'];

          $class = 'time-container ';
          if ($count % 2 == 0) {
            $class .= 'left';
          } else {
            $class .= 'right';
          }
          echo '<div class="' . $class . '">';
        ?>
      <?php if ($title) echo '<h3 class="h4 mb-sm timeline-title">' . $title . '</h3>'; ?>
      <?php if ($date) echo '<p class="bold mb-sm timeline-date">' . $date . '</p>'; ?>
      <?php if ($information) echo '<div class="small">' . $information . '</div>'; ?>
      <?php if ($img) echo wp_get_attachment_image($img, 'medium', false, array('class' => 'time-image')); ?>
      </div>

      <?php
          $count++;
        }
        ?>
    </figure>

    <?php } ?>
    </div>
</section>