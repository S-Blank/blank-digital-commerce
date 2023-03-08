<?php
/*

	Plugin Name: Simple Fields
	Plugin URI: https://rudrastyh.com/plugins/meta-boxes-options-pages
	Description: This plugin allows to create custom meta boxes, taxonomy settings and option pages while keeping you website fast.
	Version: 2.7
	Author: Misha Rudrastyh
	Author URI: https://rudrastyh.com

	Copyright 2014-2023 Misha Rudrastyh ( https://rudrastyh.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
	the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require_once( __DIR__ . '/includes/class-simple-meta-fields.php' );

/**
 * Enqueue all the scripts and styles of the plugin
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	// do nothing when not needed
	if (
		! in_array( // default pages
			$hook,
			array(
				'post.php',
				'post-new.php',
				'edit-tags.php',
				'term.php',
				'options-general.php',
				'options-writing.php',
				'options-reading.php',
				'options-discussion.php',
				'options-media.php',
				'options-permalink.php',
				'profile.php',
				'comment.php'
			)
		)
		&& substr( $hook, 0, 14 ) != 'settings_page_' // custom settings pages
		&& substr( $hook, 0, 14 ) != 'toplevel_page_'  // custom settings parentpages
		&& substr( get_plugin_page_hookname( get_admin_page_parent(), $hook), 0, 14 ) != 'toplevel_page_'
	) {
		return;
	}


	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'jquery-ui-sortable' ); // for gallery

	wp_enqueue_style(
		'simplemb_main_css',
		plugin_dir_url( __FILE__ ) . 'assets/main.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/main.css' )
	);

	wp_register_script(
		'simplemb_main_js',
		plugin_dir_url( __FILE__ ) . 'assets/main.js',
		array( 'jquery' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/main.js' )
	);

	wp_localize_script( 'simplemb_main_js', 'simpleObject', array(
		// uploader localizations
		'insertImage' => __( 'Insert image', 'simple_meta_boxes_text_domain' ),
		'useThisImage' => __( 'Use this image', 'simple_meta_boxes_text_domain' ),
		'uploadImage' => __( 'Upload Image', 'simple_meta_boxes_text_domain' ),
		// gallery
		'insertImages' => __( 'Insert images', 'simple_meta_boxes_text_domain' ),
		'useThisImages' => __( 'Add images', 'simple_meta_boxes_text_domain' ),
		'theSameImage' => __('The same images are not allowed.', 'simple_meta_boxes_text_domain' ),

	) );

	wp_enqueue_script( 'simplemb_main_js' );


});


/**
 * Empty the fields after a term has been added
 */
add_action( 'admin_footer-edit-tags.php', function() {

	?><script>
		jQuery(function($){

			var numberOfTags = 0;

			if( ! $('#the-list').children('tr').first().hasClass('no-items') ) {
				numberOfTags = $('#the-list').children('tr').length;
			}

			$(document).ajaxComplete(function( event, xhr, settings ){

				newNumberOfTags = $('#the-list').children('tr').length;
				if( parseInt(newNumberOfTags) > parseInt(numberOfTags) ) {
					numberOfTags = newNumberOfTags;

					//$('.misha-field').find('.wp-picker-clear').click();

					$('.simple-remove-img-button').each(function(){
						$(this).hide().prev().val('').prev().prev().addClass('button').html('<?php _e( 'Upload image', 'simple_meta_boxes_text_domain' ) ?>');
					});

					$('.simple-gallery-field').empty();

					$('.smb-repeater-container').children().not(':first').remove();

				}
			});
		});
	</script><?php

});



add_action( 'after_setup_theme', function(){

	$metaboxes = apply_filters( 'simple_register_metaboxes', array() );

	if( $metaboxes && is_array( $metaboxes ) ) {
		require_once( __DIR__ . '/includes/class-simple-meta-boxes.php' );

		foreach( $metaboxes as $metabox ){
			new Simple_Meta_Boxes( $metabox );
		}

	}

	$metaboxes = apply_filters( 'simple_register_comment_settings', array() );

	if( $metaboxes && is_array( $metaboxes ) ) {
		require_once( __DIR__ . '/includes/class-simple-meta-boxes.php' );
		require_once( __DIR__ . '/includes/class-simple-comment-settings.php' );

		foreach( $metaboxes as $metabox ){
			new Simple_Comment_Settings( $metabox );
		}

	}

	$metaboxes = apply_filters( 'simple_register_user_settings', array() );

	if( $metaboxes && is_array( $metaboxes ) ) {
		require_once( __DIR__ . '/includes/class-simple-meta-boxes.php' );
		require_once( __DIR__ . '/includes/class-simple-profile-fields.php' );

		foreach( $metaboxes as $metabox ){
			new Simple_Profile_Fields( $metabox );
		}

	}


	$taxonomies_settings = apply_filters( 'simple_register_taxonomy_settings', array() );

	if( $taxonomies_settings && is_array( $taxonomies_settings ) ) {
		include_once( __DIR__ . '/includes/class-simple-taxonomy-settings.php' );

		foreach( $taxonomies_settings as $taxonomy_settings ){
			new Simple_Taxonomy_Settings( $taxonomy_settings );
		}

	}

	$option_pages = apply_filters( 'simple_register_option_pages', array() );

	if( $option_pages && is_array( $option_pages ) ) {
		include_once( __DIR__ . '/includes/class-simple-option-pages.php' );

		foreach( $option_pages as $option_page ){
			new Simple_Option_Page( $option_page );
		}

	}

	$sidebars = apply_filters( 'simple_register_sidebars', array() );
	if( $sidebars && is_array( $sidebars ) ) {
		require_once( __DIR__ . '/includes/class-simple-gutenberg-sidebars.php' );
		new Simple_Gutenberg_Sidebars();
	}

});
