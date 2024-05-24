<?php

/**
 * Functions necessary for the feedback map block
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

class ezpzFeedbackMap {

  function __construct() {
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    add_filter('upload_mimes', [$this, 'allow_kml_upload']);
    add_action('wp_ajax_feedback_map_entries', [$this, 'ajax_handler']);
    add_action('wp_ajax_nopriv_feedback_map_entries', [$this, 'ajax_handler']);
  }

  function enqueue_scripts() {
    wp_register_script(
      'feedback-map-init',
      get_stylesheet_directory_uri() . '/blocks/feedback-map/scripts.js',
      array('jquery', 'googlemaps'),
      filemtime(get_stylesheet_directory() . '/blocks/feedback-map/scripts.js'),
      true
    );

    wp_localize_script('feedback-map-init', 'feedbackMapsParams', array(
      'ajaxUrl' => admin_url('admin-ajax.php'), // WordPress AJAX
      'nonce' => wp_create_nonce('feedback-map-nonce'),
    ));
  }

  function allow_kml_upload($mimes) {
    $mimes['kml'] = 'text/xml';
    $mimes['kmz'] = 'application/zip';
    return $mimes;
  }

  function process_entries($form_id) {
    $form = GFAPI::get_form($form_id);

    $user_feedback_all = [];
    $field_mapping = [];

    // If the field cssClass includes 'is-private-feedback' then skip this field
    foreach ($form['fields'] as $field) {
      if (strpos($field->cssClass, 'is-private-feedback') !== false) continue;

      if ($field->inputType == 'hidden') $hidden = true;
      else $hidden = $field->visibility == 'visible' ? false : true;

      $field_mapping[$field->id] = [
        'label' => $field->label,
        'isHidden' => $hidden
      ];
    }

    $all_entries = [];
    $search_criteria = [];
    $page_size = 100;
    $offset = 0;

    // Keep looping until we have all entries
    do {
      $paging = array(
        'offset' => $offset,
        'page_size' => $page_size
      );

      // Get the entries
      $entries = GFAPI::get_entries($form_id, $search_criteria, null, $paging);

      if (!$entries) {
        break; // No more entries, exit loop
      }

      // Process entries here
      foreach ($entries as $entry) {
        // Process each entry as needed
        // For example, you can store data in the $feedback array
        $all_entries[] = $entry;
      }

      // Increment offset for next iteration
      $offset += $page_size;
    } while (count($entries) == $page_size); // Continue loop if entries fetched equals page size

    if (empty($all_entries)) {
      return [];
    }

    foreach ($all_entries as $entry) {

      $user_feedback = [
        'date_created' => $entry['date_created'],
      ];

      foreach ($entry as $key => $value) {

        // If the field label is 'Lat' or 'Latitude' or 'Lng' or 'Longitude' then push  it to the top level of the array
        if (preg_match('/lat(itude)?|lng(itude)?/i', $field_mapping[$key]['label'])) {
          if (preg_match('/lat(itude)?/i', $field_mapping[$key]['label'])) {
            $user_feedback['lat'] = $value;
          } else {
            $user_feedback['lng'] = $value;
          }

          continue;
        }

        if (array_key_exists($key, $field_mapping)) {
          $user_feedback['fields'][$key] = [
            'label' => $field_mapping[$key]['label'],
            'value' => $value,
            'isHidden' =>  $field_mapping[$key]['isHidden']
          ];
        }

        // The entry might include a ., in which case this is a multi value field (eg. a checkbox)
        if (strpos($key, '.') !== false) {

          $explode = explode('.', $key);
          $field = $explode[0];

          // If the field is not yet set in $user_feedback then set it
          if (!isset($user_feedback['fields'][$field])) {
            $user_feedback['fields'][$field] = [
              'label' => $field_mapping[$field]['label'],
              'value' => [],
              'isHidden' =>  $field_mapping[$field]['isHidden']
            ];
          }

          // If the value is not empty push it to the array
          if (!empty($value)) {
            $user_feedback['fields'][$field]['value'][] = $value;
          }
        }
      }

      $user_feedback_all[] = $user_feedback;
    }

    return $user_feedback_all;
  }

  function ajax_handler() {
    // Check nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'feedback-map-nonce')) {
      wp_send_json_error('Invalid nonce');
      die();
    }

    $form_id = $_GET['formId'];

    // TODO: SET THEM INTO A TRANSIENT FOR 5 MINUTES

    $form = GFAPI::get_form($form_id);
    if ($form) {
      $processed_entries = $this->process_entries($form_id);
      wp_send_json_success($processed_entries, 200);
    } else {
      wp_send_json_error('Form not found', 404);
    }
    die(); // Required to terminate immediately and return a proper response
  }
}

new ezpzFeedbackMap();
