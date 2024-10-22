<?php

/**
 * Vertical Timeline Block Template.
 *
 * This template renders a timeline block with items either on the top or bottom.
 * Each item has a year, description, and an optional image. The size of the item
 * is determined based on the length of the description and the presence of an image.
 *
 * @param array $block The block settings and attributes.
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 * @param array $context The context provided to the block by the post or its parent block.
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Get block attributes and fields
$block_attributes = jellypress_get_block_attributes($block, $context);
$fields = get_fields();

// Define allowed blocks and block template
$allowed_blocks = jellypress_get_allowed_blocks(['ezpz/post-title', 'core/paragraph', 'ezpz/buttons']);
$block_template = jellypress_get_block_template($block_template);

// Add timeline-block class to block attributes
$block_attributes['class'] .= ' timeline-block';

// Retrieve custom fields
$single_or_double = $fields['single_or_double'];
$timeline = $fields['timeline'];

?>
<section class="<?php echo $block_attributes['class']; ?>" <?php echo $block_attributes['anchor']; ?>>
  <div class="container">
    <div class="row">
      <div class="col md-10 lg-9">
        <InnerBlocks templateLock="all" allowedBlocks="<?php echo $allowed_blocks; ?>" template="<?php echo $block_template; ?>" />
      </div>
    </div>
  </div>
  <div class="timeline-wrapper<?php echo !$single_or_double ? ' single-timeline' : ''; ?>">
    <div class="timeline-container">
      <?php
      $topArray = [];
      $bottomArray = [];

      // Loop through the timeline array in pairs and determine the size
      for ($i = 0; $i < count($timeline); $i += 2) {
        $firstElement = $timeline[$i];
        $secondElement = isset($timeline[$i + 1]) ? $timeline[$i + 1] : null;

        // Calculate the character lengths of descriptions
        $length1 = strlen($firstElement['description']);
        $length2 = $secondElement ? strlen($secondElement['description']) : 0;

        // Determine sizes based on character lengths and image presence
        $size1 = ($length1 < 175) ? (!empty($firstElement['image']) ? 2 : 1) : (($length1 <= 300) ? 2 : 3);
        if (!$single_or_double && !empty($firstElement['image'])) {
          $size1 = 4; // Apply size 4 if it's a single timeline with an image
        }

        $size2 = $secondElement ? (($length2 < 175) ? (!empty($secondElement['image']) ? 2 : 1) : (($length2 <= 300) ? 2 : 3)) : $size1;
        if ($secondElement && !$single_or_double && !empty($secondElement['image'])) {
          $size2 = 4; // Apply size 4 if it's a single timeline with an image
        }

        // Determine the larger size
        $largerSize = max($size1, $size2);

        // Assign the larger size to both elements
        $firstElement['size'] = $largerSize;
        if ($secondElement) {
          $secondElement['size'] = $largerSize;
        }

        // Alternate between top and bottom arrays if $single_or_double is true
        if ($single_or_double) {
          $topArray[] = $firstElement;
          if ($secondElement) {
            $bottomArray[] = $secondElement;
          }
        } else {
          $topArray[] = $firstElement;
          if ($secondElement) {
            $topArray[] = $secondElement;
          }
        }
      }

      // Get the first and last year from the timeline array
      $firstYear = $timeline[0]['year'];
      $lastYear = $timeline[count($timeline) - 1]['year'];
      ?>

      <!-- Render the top timeline items -->
      <div class="top-timeline-container">
        <?php foreach ($topArray as $time) : ?>
          <?php
          // Extract details for each timeline item
          $year = $time['year'];
          $description = $time['description'];
          $image = $time['image'];
          $image_id = is_array($image) && isset($image['id']) ? $image['id'] : $image;
          $size = $time['size'];
          $sizeClass = 'size-' . $size;
          ?>
          <div class="timeline-item top <?php echo $sizeClass; ?>" data-timeline-year="<?php echo $year; ?>">
            <?php
            // Display image if it exists and $single_or_double is true
            if (!empty($image_id)) {
              if ($single_or_double) {
                echo wp_get_attachment_image($image_id, 'medium');
              }
            }
            ?>
            <div class="timeline-date"><?php echo $year; ?></div>
            <div class="timeline-content">
              <?php
              // Display description and image if it exists
              if (!empty($image_id)) {
                if ($single_or_double) {
                  echo '<p>' . $description . '</p>';
                } else {
                  echo '<p>' . $description . '</p>';
                  echo wp_get_attachment_image($image_id, 'medium');
                }
              } else {
                echo '<p>' . $description . '</p>';
              }
              ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Render the bottom timeline items if $single_or_double is true -->
      <?php if ($single_or_double) { ?>
        <div class="bottom-timeline-container">
          <?php foreach ($bottomArray as $time) : ?>
            <?php
            // Extract details for each timeline item
            $year = $time['year'];
            $description = $time['description'];
            $image = $time['image']; // Assuming $image is an array
            $image_id = is_array($image) && isset($image['id']) ? $image['id'] : $image;
            $size = $time['size'];
            $sizeClass = 'size-' . $size;
            ?>
            <div class="timeline-item bottom <?php echo $sizeClass; ?>" data-timeline-year="<?php echo $year; ?>">
              <div class="timeline-content">
                <p><?php echo $description; ?></p>
                <?php
                // Display image if it exists
                if (!empty($image_id)) {
                  echo wp_get_attachment_image($image_id, 'large');
                }
                ?>
              </div>
              <div class="timeline-date"><?php echo $year; ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php } ?>

    </div>
  </div>
  <div class="container">
    <div class="timeline-scroll-container">
      <p class="timeline-year-text first-year"><?php echo $firstYear; ?></p>
      <div class="timeline-scroll">
        <div class="scroll-years">
          <div class="scroll-year"><?php echo jellypress_icon('caret-left') . ' <span class="scroll-year-text">' . $firstYear .  ' </span>' . jellypress_icon('caret-right'); ?></div>
        </div>
      </div>
      <p class="timeline-year-text last-year"><?php echo $lastYear; ?></p>
    </div>
  </div>
</section>
