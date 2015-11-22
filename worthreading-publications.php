<?php

// Add term page
function worthreading_site_url() {
  // this will add the custom meta field to the add new term page
  ?>
  <div class="form-field">
    <label for="term_meta[custom_term_meta]"><?php _e( 'Example meta field', 'pippin' ); ?></label>
    <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
    <p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
  </div>
<?php
}
add_action( 'category_add_form_fields', 'worthreading_site_url', 10, 2 );


// Edit term page
function worthreading_edit_site_url($term) {
 
  // put the term ID into a variable
  $t_id = $term->term_id;
 
  // retrieve the existing value(s) for this meta field. This returns an array
  $term_meta = get_option( "taxonomy_$t_id" ); ?>
  <tr class="form-field">
  <th scope="row" valign="top"><label for="term_meta[custom_term_meta]"><?php _e( 'Example meta field', 'pippin' ); ?></label></th>
    <td>
      <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
      <p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
    </td>
  </tr>
<?php
}
add_action( 'category_edit_form_fields', 'worthreading_edit_site_url', 10, 2 );


// Save extra taxonomy fields callback function.
function worthreading_save_site_url( $term_id ) {
  if ( isset( $_POST['term_meta'] ) ) {
    $t_id = $term_id;
    $term_meta = get_option( "taxonomy_$t_id" );
    $cat_keys = array_keys( $_POST['term_meta'] );
    foreach ( $cat_keys as $key ) {
      if ( isset ( $_POST['term_meta'][$key] ) ) {
        $term_meta[$key] = $_POST['term_meta'][$key];
      }
    }
    // Save the option array.
    update_option( "taxonomy_$t_id", $term_meta );
  }
}  
add_action( 'edited_category', 'worthreading_save_site_url', 10, 2 );  
add_action( 'create_category', 'worthreading_save_site_url', 10, 2 );