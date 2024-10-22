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

$block_attributes = jellypress_get_block_attributes($block, $context);

$allowed_blocks = jellypress_get_allowed_blocks();
$block_template = jellypress_get_block_template();

$fields = get_fields();
$text_align = $block_attributes['text_align'];

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

    <?php
    $timelines = $fields['timeline'];

    if ($timelines) {
    ?>
      <div class="row justify-center">
        <div class="sm-12 lg-10">
          <div class="timeline">
            <?php
            $count = 0;
            foreach ($timelines as $timeline) {
              $title =  $timeline['title'];
              $information =  $timeline['information'];
              $date =  $timeline['date'];

              $class = 'time-container ';
              if ($count % 2 == 0) {
                $class .= 'left';
              } else {
                $class .= 'right';
              }
              echo '<div class="' . $class . '">';
            ?>
              <?php if ($title) echo '<h3 class="h4 mb-sm">' . $title . '</h3>'; ?>
              <?php if ($date) echo '<p class="bold mb-sm">' . $date . '</p>'; ?>
              <p>
                <?php if ($information) echo $information; ?>
              </p>
          </div>

        <?php
              $count++;
            }
        ?>
        </div>
      </div>
  </div>
<?php } ?>
</div>
</section>
