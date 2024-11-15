<?php

/**
 * Interactive Feedback Map Block Template.
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

// Remove anything from the block class list that is a bg-* class as we dont want any background colours on this block
$block_classlist = explode(' ', $block_attributes['class']);
$block_classlist = array_filter($block_classlist, function ($class) {
  return strpos($class, 'bg-') === false;
});
$block_classlist = implode(' ', $block_classlist);
?>

<section class="<?php echo $block_attributes['class']; ?>" <?php echo $block_attributes['anchor']; ?>>
  <?php
  $api_key =  get_global_option('google_maps_api_key');
  if (!$api_key && current_user_can('publish_posts')) {
    echo '<div class="container"><div class="callout error">' .
      sprintf(
        /* translators: %s link to theme options page. */
        __('You need to <a href="%s" class="callout-link">add a Google Maps API key</a> in order to display a map on your website.', 'jellypress'),
        esc_url(get_admin_url(null, 'admin.php?page=theme-options'))
      )
      . '</div></div>';
  } elseif ($api_key) {


    if (isset($_GET['entryID'])) {
      $fields['feedback_active'] = false;
    }

    /**
     * Build up attributes
     */
    $map_attributes = [
      'data-lat' => $fields['latitude'],
      'data-lng' => $fields['longitude'],
    ];

    if ($fields['zoom']) $map_attributes['data-zoom'] = $fields['zoom'];
    if ($fields['gravity_form_id']) $map_attributes['data-form-id'] = $fields['gravity_form_id'];
    if ($fields['overlay_source'])  $map_attributes['data-overlay-source'] = $fields['overlay_source'];

    $map_attributes['aria-label'] = isset($fields['aria_label']) ? $fields['aria_label'] : __('Interactive Map', 'jellypress');

    if ($fields['feedback_active']) $map_attributes['data-feedback-active'] = 'true';
    else $map_attributes['data-feedback-active'] = 'false';

    $allow_filtering = false;
    if ($fields['filter_by_field']) {
      // Check if the field ID given is of type 'Select', 'Checkbox' or 'Radio' and if so, build a filter
      $form = GFAPI::get_form($fields['gravity_form_id']);
      $field_to_filter_by = GFAPI::get_field($form, $fields['filter_by_field']);

      $accepted_types = ['select', 'checkbox', 'radio'];

      if (!in_array($field_to_filter_by->type, $accepted_types)) {
        return false;
      } else {
        $map_attributes['data-filterby'] = $fields['filter_by_field'];
        $allow_filtering = true;
      }
    }

    $intro_text = !empty($fields['intro_text']) ? $fields['intro_text'] : false;
    if ($intro_text && $fields['feedback_active']) $map_attributes['data-has-intro'] = true;

    if ($fields['restriction']) {
      $map_attributes['data-restriction'] = $fields['restriction'];
    }


    if (!empty($map_attributes)) {
      $map_attrs = '';
      foreach ($map_attributes as $key => $value) {
        $map_attrs .= $key . '="' . $value . '" ';
      }
    }

    if ($allow_filtering) {
      $filter_colours = $fields['filter_colors'];

      $color_choices = [];

      if ($filter_colours && !empty($filter_colours)) {
        foreach ($filter_colours as $key => $value) {
          $color_choices[$key] = $value['color'];
        }
      }
    } else {
      $color_choices = false;
    }

    // TODO: Add an aria suitable fallback for the map?

  ?>

    <div class="feedback-map-wrapper">
      <div class="feedback-map" <?php echo $map_attrs; ?>></div>

      <?php if ($intro_text && $fields['feedback_active']) { ?>
        <div class="feedback-map-intro">
          <?php echo $intro_text; ?>
          <div class="button-list mb-0">
            <button class="button success small mb-0"><?php echo $fields['button_text']; ?></button>
          </div>
        </div>
      <?php } ?>

      <?php if ($allow_filtering) { ?>
        <div class="feedback-map-filters" aria-label="<?php _e('Filter feedback', 'jellypress'); ?>">
          <?php
          foreach ($field_to_filter_by->choices as $key => $choice) {
            $filter_id = sanitize_title($choice['value']);
            $color = isset($color_choices[$key]) ? $color_choices[$key] : '#ff0000';

            echo '<label class="feedback-map-filter" for="' . $filter_id . '">
            <input type="checkbox" style="--accent-color: ' . $color . '" name="filter-map-' . $fields['gravity_form_id'] . '[]" value="' . $choice['value'] . '" id="' . $filter_id . '">' . $choice['text'] . '</label>';
          }
          ?>
        </div>
      <?php } ?>

      <?php if ($fields['feedback_active']) { ?>

        <button class=" button success share-feedback-button small" aria-controls="<?php echo 'marker-add-' . $fields['gravity_form_id']; ?>" aria-expanded="false"><?php _e('Share your feedback', 'jellypress'); ?></button>
        <div class="add-marker-controls" id="marker-add-<?php echo $fields['gravity_form_id']; ?>" style="display:none;">
          <span class="crosshair">
            <span class="vertical"></span>
            <span class="horizontal"></span>
          </span>
          <div class="description">
            <p><?php _e('Position the crosshair on the location where you would like to add feedback, and then click on "Add feedback here"', 'jellypress'); ?></p>
            <div class="button-list">
              <button class="button xsmall success open-feedback-modal" onclick="toggleModal('add-feedback-<?php echo $fields['gravity_form_id']; ?>');">
                <?php _e('Add feedback here', 'jellypress'); ?>
              </button>
              <button class="button ghost white xsmall cancel">
                <?php _e('Cancel', 'jellypress'); ?>
              </button>
            </div>
          </div>
        </div>

        <dialog class="modal bg-white feedback-modal" id="add-feedback-<?php echo $fields['gravity_form_id']; ?>">
          <div class="modal-content">
            <?php
            echo do_shortcode('[gravityform id="' . $fields['gravity_form_id'] . '" title="false" description="false"  ajax="true"]')
            ?>
          </div>
        </dialog>

      <?php } ?>

    </div>

  <?php
  }
  ?>
</section>