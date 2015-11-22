<?php

/*
Plugin Name: Worth Reading
Plugin URI: https://github.com/jeremyzilar/Worth-Reading
Description: Manage bookmarks with custom page type "bookmark" and custom taxonomy
Version: 1.0
Author: Jeremy Zilar
Author URI: http://jeremyzilar.com
License: GPL2
*/

/*  Copyright 2014 Sebastian Greger

    The development of this software was made possible using the following components:
    
    - worthreading by sebastiangreger
      https://github.com/sebastiangreger/worthreading
      Licensed Under: GNU General Public License v2 (GPL-2)

    - Wordpress-Bookmarks by aaronpk
      https://github.com/aaronpk/Wordpress-Bookmarks/
      Licensed Under: GNU General Public License v2 (GPL-2)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/**
* Block access if called directly
*/
if ( !function_exists( 'add_action' ) ) {
    echo "This is a plugin file, direct access denied!";
    exit;
}


/**
* Load admin UI functionalities if admin
*/
if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/worthreading-admin.php';
}


/**
 * worthReading Plugin Class
 *
 * @author Sebastian Greger
 */
class worthReading {


    /**
    * Adds the custom post type and taxonomy for the bookmark management; to be run on 'init' hook
    */
    public static function post_type_and_taxonomy() {

        // bookmarks are stored in a custom post type 'bookmark'
        register_post_type('bookmark', array(
            'label'              => 'Bookmarks',
            'labels'             => array(
                'name'               => _x('Bookmarks', 'post type general name'),
                'singular_name'      => _x('Bookmark', 'post type singular name'),
                'add_new'            => _x('Add New', 'sg_bookmark'),
                'add_new_item'       => __('Add New Bookmark'),
                'edit_item'          => __('Edit Bookmark'),
                'new_item'           => __('New Bookmark'),
                'view_item'          => __('View Bookmark'),
                'search_items'       => __('Search Bookmarks'),
                'not_found'          => __('No bookmarks found'),
                'not_found_in_trash' => __('No bookmarks found in Trash'),
                'parent_item_colon'  => ''
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'menu_position'      => 20,
            'query_var'          => true,
            'rewrite'            => true,
            'capability_type'    => 'post',
            'supports'           => array('title','thumbnail'),
            'taxonomies'         => array('publication'),
            'slug'               => 'bookmark',
            'hierarchical'       => false,
        ));

        
        // Publication names are stored in a custom taxonomy 'publication'
        register_taxonomy(
            'publication',
            'bookmark',
            array(
                'labels'        => array(
                    'name'          => 'Publications',
                    'add_new_item'  => 'Add New Publication',
                    'new_item_name' => "New Publication"
                ),
                'show_ui'       => true,
                'show_tagcloud' => false,
                'hierarchical'  => false
            )
        );


        // Add term page
        function worthreading_site_url() { ?>
          <div class="form-field">
            <label for="term_meta[custom_term_meta]"><?php _e( 'Site URL' ); ?></label>
            <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
            <p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
          </div>
        <?php
        }
        add_action( 'publication_add_form_fields', 'worthreading_site_url', 10, 2 );


        // Edit term page
        function worthreading_edit_site_url($term) {
         
          // put the term ID into a variable
          $t_id = $term->term_id;
         
          // retrieve the existing value(s) for this meta field. This returns an array
          $term_meta = get_option( "taxonomy_$t_id" ); ?>
          <tr class="form-field">
          <th scope="row" valign="top"><label for="term_meta[custom_term_meta]"><?php _e( 'Site URL' ); ?></label></th>
            <td>
              <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
              <p class="description"><?php _e( 'e.g. http://nytimes.com' ); ?></p>
            </td>
          </tr>
        <?php
        }
        add_action( 'publication_edit_form_fields', 'worthreading_edit_site_url', 10, 2 );


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
        add_action( 'edited_publication', 'worthreading_save_site_url', 10, 2 );  
        add_action( 'create_publication', 'worthreading_save_site_url', 10, 2 );
        
    }


    /**
    * Sets up the neat URLs when plugin is activated
    */
    public static function activate() {

        // call the function to define the custom post type
        worthReading::post_type_and_taxonomy();
        
        // add the rewrite rules for the listing page and entry form popup
        add_rewrite_rule( 'bookmarks/?$', 'wp-content/plugins/worthReading/worthreading-browse.php', 'top' );
        add_rewrite_rule( 'bookmarks/add/?$', 'wp-content/plugins/worthReading/worthreading-add.php', 'top' );
        
        // flush the rewrite rules
        flush_rewrite_rules();

    }


    /**
    * Flushes URL rewrite rules when plugin is deactivated
    */
    public static function deactivate() {
        
        // flush the rewrite rules
        flush_rewrite_rules();

    }


}


$worthReading = new worthReading();

register_activation_hook(__FILE__, array ( 'worthReading', 'activate') );
register_deactivation_hook(__FILE__, array ( 'worthReading', 'deactivate' ) );

add_action( 'init', array( 'worthReading', 'post_type_and_taxonomy' ) );

