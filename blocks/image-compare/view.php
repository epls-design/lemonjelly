<?php

/**
 * Image Compare Block Template.
 * NOTE: This block requires zurb-twentytwenty to be installed via npm.
 * @see https://www.npmjs.com/package/zurb-twentytwenty
 * The block javascript files are manually copied from NPM.
 * So if the version ever gets bumped, you'll need to copy them here.
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

$block_attributes = jellypress_get_block_attributes($block, $context);

$allowed_blocks = jellypress_get_allowed_blocks();
$block_template = jellypress_get_block_template();

$fields = get_fields();
$text_align = $block_attributes['text_align'];

$first_image = $fields['first_image'];
$second_image = $fields['second_image'];

?>

<section class="<?php echo $block_attributes['class']; ?>" <?php echo $block_attributes['anchor']; ?>>
  <div class="container">
    <?php if ($content || $is_preview) : ?>
    <header class="row justify-center <?php echo $text_align; ?>">
      <div class="col md-10 lg-8">
        <InnerBlocks allowedBlocks=" <?php echo $allowed_blocks; ?>" template="<?php echo $block_template; ?>" />
      </div>
    </header>
    <?php endif; ?>
    <div class="twentytwenty-container" data-before-label="<?php echo $fields['before_label']; ?>" data-after-label="<?php echo $fields['after_label']; ?>">
      <?php
      echo wp_get_attachment_image($first_image, 'large_landscape', false, array('class' => 'twentytwenty-before'));
      echo wp_get_attachment_image($second_image, 'large_landscape', false, array('class' => 'twentytwenty-after'));
      ?>
    </div>
  </div>
</section>