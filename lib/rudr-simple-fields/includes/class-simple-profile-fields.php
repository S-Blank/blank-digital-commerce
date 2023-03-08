<?php

if( ! class_exists( 'Simple_Profile_Fields' ) ) {

	class Simple_Profile_Fields extends Simple_Meta_Boxes{


		function __construct( $metabox ) {

			$this->metabox = $metabox;
			$this->prefix = $this->metabox[ 'id' ] .'_';

			add_action( 'show_user_profile', array( $this, 'render' ) );
			add_action( 'edit_user_profile', array( $this, 'render' ) );
			add_action( 'personal_options_update', array( $this, 'save' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save' ) );

		}


		// Meta box content
		function render( $user ){

			if( 'show_user_profile' !== current_action() && 'edit_user_profile' !== current_action() ) {
				return;
			}

			wp_nonce_field( $this->metabox[ 'id' ], $this->metabox[ 'id' ] . '_wpnonce' );

			// if fields are specified
			if( isset( $this->metabox[ 'fields' ] ) && is_array( $this->metabox[ 'fields' ] ) ) {

				$metabox_html = '<table class="form-table"><tbody>';

				foreach ( $this->metabox[ 'fields' ] as $field ) {

					$value = get_user_meta( $user->ID, $this->field_name( $field ), true );
					$metabox_html .= $this->field_html( $field, $value, $user->ID );

				}

				$metabox_html .= '</tbody></table>';

				echo $metabox_html;

			}

		}

		// Save metabox content
		function save( $user_id, $data = null ){

			// this function is for users only
			if( 'personal_options_update' !== current_action() && 'edit_user_profile_update' !== current_action() ) {
				return;
			}

    	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
			}

			if( ! isset( $_POST[ $this->metabox[ 'id' ] . '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ $this->metabox[ 'id' ] . '_wpnonce' ], $this->metabox[ 'id' ] ) ) {
				return;
			}

			// if ( ! current_user_can( 'edit_post', $post_id ) ) {
			// 	return;
			// }

			foreach ( $this->metabox[ 'fields' ] as $field ) :

				$name = $this->field_name( $field );

				update_user_meta(
					$user_id,
					$name,
					( isset( $_POST[ $name ] ) ? $this->sanitize( $_POST[ $name ], $field[ 'type' ] ) : '' )
				);

			endforeach;

		}

	}

}
