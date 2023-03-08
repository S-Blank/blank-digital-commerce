<?php

if( ! class_exists( 'Simple_Comment_Settings' ) ) {

	class Simple_Comment_Settings extends Simple_Meta_Boxes{

		function __construct( $metabox ) {

			parent::__construct( $metabox );
			add_action( 'edit_comment', array( $this, 'save' ), 9999, 2 );

		}


		// Create comment meta box
		function create( $type ) {

			// just in case
			if( 'comment' !== $type ) {
				return;
			}

			// do nothing if user cap for this metabox is not enough
			if( ! empty( $this->metabox[ 'capability' ] ) && ! current_user_can( $this->metabox[ 'capability' ] ) ) {
				return;
			}

			add_meta_box(
				$this->metabox[ 'id' ],
				$this->metabox[ 'name' ],
				array( $this, 'render' ),
				'comment',
				'normal'
			);

		}

		// Meta box content
		function render( $comment ){

			wp_nonce_field( $this->metabox[ 'id' ], $this->metabox[ 'id' ] . '_wpnonce' );

			// if fields are specified
			if( isset( $this->metabox[ 'fields' ] ) && is_array( $this->metabox[ 'fields' ] ) ) {

				$metabox_html = '<table class="form-table"><tbody>';

				foreach ( $this->metabox[ 'fields' ] as $field ) {

					$value = get_comment_meta( $comment->comment_ID, $this->field_name( $field ), true );
					$metabox_html .= $this->field_html( $field, $value, $comment->comment_ID );

				}

				$metabox_html .= '</tbody></table>';

				echo $metabox_html;

			}

		}

		// Save metabox content
		function save( $comment_id, $comment_data ){

			// this function is for comments only
			if( 'edit_comment' !== current_action() ) {
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

				update_comment_meta(
					$comment_id,
					$name,
					( isset( $_POST[ $name ] ) ? $this->sanitize( $_POST[ $name ], $field[ 'type' ] ) : '' )
				);

			endforeach;

		}

	}

}
