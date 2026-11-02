<?php

add_action( 'post_tag_add_form_fields', 'cp_add_term_fields' );
add_action( 'category_add_form_fields', 'cp_add_term_fields' );
function cp_add_term_fields( $taxonomy ) {
  ?>
    <div class="form-field">
      <label for="cp_view"><?php _e('Display format'); ?></label>
      <select name="cp_view" id="cp_view" aria-describedby="view-description">
        <option value="feed"><?php _e('Feed'); ?></option>
        <option value="list"><?php _e('List'); ?></option>
        <option value="thumbnails"><?php _e('Thumbnails'); ?></option>
      </select>
      <p id="view-description"><?php _e('If “Thumbnails” is selected, only posts with a featured image will be displayed.'); ?></p>
    </div>
  <?php
}



add_action( 'post_tag_edit_form_fields', 'cp_edit_term_fields', 10, 2 );
add_action( 'category_edit_form_fields', 'cp_edit_term_fields', 10, 2 );
function cp_edit_term_fields( $term, $taxonomy ) {

  $view = get_term_meta( $term->term_id, 'cp_view', true );

  ?><tr class="form-field">
    <th><label for="cp_view"><?php _e('Display format'); ?></label></th>
    <td>
      <select name="cp_view" id="cp_view" aria-describedby="view-description">
        <option value="feed"<?php echo $view === 'feed' ? 'selected="selected"' : ''; ?>><?php _e('Feed'); ?></option>
        <option value="list"<?php echo $view === 'list' ? 'selected="selected"' : ''; ?>><?php _e('List'); ?></option>
        <option value="thumbnails"<?php echo $view === 'thumbnails' ? 'selected="selected"' : ''; ?>><?php _e('Thumbnails'); ?></option>
      </select>
      <p id="view-description"><?php _e('If “Thumbnails” is selected, only posts with a featured image will be displayed.'); ?></p>
    </td>
  </tr>
  <?php
}

add_action( 'create_category', 'cp_save_cp_view_field', 10, 2 );
add_action( 'create_post_tag', 'cp_save_cp_view_field', 10, 2 );
add_action( 'edited_category', 'cp_save_cp_view_field', 10, 4 );
add_action( 'edited_post_tag', 'cp_save_cp_view_field', 10, 4 );
function cp_save_cp_view_field( $term_id, $tt_id, $taxonomy  ) {
  // if (isset($_POST['cp_view'])) {
  //   $valid_values = array(
  //     'feed',
  //     'list',
  //     'thumbnails',
  //   );
    $value = sanitize_text_field( $_POST['cp_view'] );
    // if (!in_array($value, $valid_values)) {
    //   return;
    // }

    // $current_term_meta = get_term_meta($term_id, 'cp_view', true);

    // if (!$current_term_meta) {
      add_term_meta($term_id, 'cp_view', $value, true);
    // } else {
      // update_term_meta( $term_id, 'cp_view', $value );
  //   }
  // } 
}