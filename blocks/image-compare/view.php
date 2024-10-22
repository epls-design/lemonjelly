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

wp_enqueue_script('twentytwenty-init');

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
      <header class="row justify-center">
        <div class="col md-10 lg-8">
          <InnerBlocks className="<?php echo $text_align; ?>" allowedBlocks=" <?php echo $allowed_blocks; ?>" template="<?php echo $block_template; ?>" />
        </div>
      </header>
    <?php endif; ?>
    <div class="twentytwenty-container">
      <!-- The before image is first -->
      <?php if ($first_image) {  ?>
        <img src="<?php echo $first_image; ?>" />
      <?php } ?>
      <!-- The after image is last -->
      <?php if ($second_image) {  ?>
        <img src="<?php echo $second_image; ?>" />
      <?php } ?>
    </div>
  </div>
</section>
