<?php

if( ! class_exists( 'Simple_Meta_Boxes' ) ) {

	class Simple_Meta_Boxes extends Simple_Meta_Fields{

		/**
		 * Constructor
		 */
		function __construct( $metabox ) {

			$this->metabox = $metabox;
			$this->prefix = $this->metabox[ 'id' ] .'_';

			add_action( 'add_meta_boxes', array( $this, 'create' ) );
			add_action( 'save_post', array( $this, 'save' ), 1, 2 );

		}

		// Create a metabox for each post type and with given capabilities
		function create() {

			// do nothing if user cap for this metabox is not enough
			if( ! empty( $this->metabox[ 'capability' ] ) && ! current_user_can( $this->metabox[ 'capability' ] ) ) {
				return;
			}

			add_meta_box(
				$this->metabox[ 'id' ],
				$this->metabox[ 'name' ],
				array( $this, 'render' ),
				$this->metabox[ 'post_type' ],
				'normal',
				( isset( $this->metabox[ 'priority' ] ) ? $this->metabox[ 'priority' ] : 'default' )
			);

		}

		// Meta box content
		function render( $post ){

			wp_nonce_field( $this->metabox[ 'id' ], $this->metabox[ 'id' ] . '_wpnonce' );

			// if fields are specified
			if( isset( $this->metabox[ 'fields' ] ) && is_array( $this->metabox[ 'fields' ] ) ) {

				$metabox_html = '<table class="form-table"><tbody>';

				foreach ( $this->metabox[ 'fields' ] as $field ) {

					$value = get_post_meta( $post->ID, $this->field_name( $field ), true );
					$metabox_html .= $this->field_html( $field, $value, $post->ID );

				}

				$metabox_html .= '</tbody></table>';

				echo $metabox_html;

			}

		}

		function field_html( $field, $value, $object_id ) {

			$html = '';
			$field[ 'type' ] = isset( $field[ 'type' ] ) ? $field[ 'type' ] : 'text';

			// begin field wrap
			if( in_array( $field[ 'type' ], array( 'checkbox', 'radio' ) ) ) {
				$html .= '<tr class="' . $this->show_if_classes( $field ) . '"><th style="font-weight:normal">' . ( ! empty( $field[ 'label' ] ) ? $field[ 'label' ] : '' ) . '</th><td>';
			} else {
				$html .= '<tr class="' . $this->show_if_classes( $field ) . '"><th style="font-weight:normal"><label for="' . $this->prefix . $field[ 'id' ] . '">' . ( ! empty( $field[ 'label' ] ) ? $field[ 'label' ] : '' ) . '</label></th><td>';
			}

			$html .= $this->field( $field, $value, $this->prefix );

			$html .= '</td></tr>';

			return $html;

		}

		// Save metabox content
		function save( $post_id, $post ){

    	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
			}

    	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
			}

			if( ! isset( $_POST[ $this->metabox[ 'id' ] . '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ $this->metabox[ 'id' ] . '_wpnonce' ], $this->metabox[ 'id' ] ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			if ( is_array( $this->metabox[ 'post_type' ] ) && ! in_array( $post->post_type, $this->metabox[ 'post_type' ] ) || ! is_array( $this->metabox[ 'post_type' ] ) && $this->metabox[ 'post_type' ] !== $post->post_type ) {
				return; // this post type does not have a metabox
			}

			foreach ( $this->metabox[ 'fields' ] as $field ) :

				$name = $this->field_name( $field );

				update_post_meta(
					$post_id,
					$name,
					( isset( $_POST[ $name ] ) ? $this->sanitize( $_POST[ $name ], $field[ 'type' ] ) : '' )
				);

			endforeach;

		}

	}

}
