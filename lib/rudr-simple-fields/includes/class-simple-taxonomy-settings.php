<?php
/**
 * Taxonomy Settings Constructor Class
 * @author Misha Rudrastyh
 * @version 1.0
 * @param string $id
 * @param array|string $taxonomy
 * @param string $capability
 * @param array $fields
 */
if( ! class_exists( 'Simple_Taxonomy_Settings' ) ) {

	class Simple_Taxonomy_Settings extends Simple_Meta_Fields {


		function __construct( $taxonomy_settings ) {

			$this->taxonomy_settings = $taxonomy_settings;
			$this->prefix = $this->taxonomy_settings['id'] .'_';
			$this->taxonomy = !empty( $this->taxonomy_settings['taxonomy'] ) ? $this->taxonomy_settings['taxonomy'] : 'category';

			if( !empty( $this->taxonomy_settings['capability'] ) && !current_user_can( $this->taxonomy_settings['capability'] ) ) {
				return;
			}

			if( is_array( $this->taxonomy ) ) {
				foreach( $this->taxonomy as $taxonomy ) {
					$this->init_taxonomy_fields( $taxonomy );
				}
			} else {
				$this->init_taxonomy_fields( $this->taxonomy );
			}

		}

		function init_taxonomy_fields( $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( $this, 'render' ), 10, 1 );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'render' ), 10, 1 );
			add_action( 'created_' . $taxonomy, array( $this, 'save' ), 10, 2 );
			add_action( 'edited_' . $taxonomy, array( $this, 'save' ), 10, 2 );
		}


		function is_edit_page(){
			return ( isset( $_GET['tag_ID'] ) && $_GET['tag_ID'] ) ? true : false;
		}


		function render( $term ){

			$html = '';

			//echo '<pre>';print_r( $this->taxonomy_settings );exit;

			wp_nonce_field( $this->taxonomy_settings['id'], $this->taxonomy_settings['id'].'_wpnonce' );


			if( isset( $this->taxonomy_settings['fields'] ) && is_array( $this->taxonomy_settings['fields'] ) ):

			$term_id = $this->is_edit_page() ? $term->term_id : null;

			/* for each option defined */
			foreach ( $this->taxonomy_settings['fields'] as $field ):

				// begin field wrap
				if( in_array( $field['type'], array( 'checkbox', 'radio', 'checklist' ) ) ) {
					if( $this->is_edit_page() ) {
						$html .= '<tr class="simple-field form-field term-group-wrap ' . $this->show_if_classes( $field ) . '"><th scope="row">' . ( ! empty( $field['label'] ) ? $field['label'] : '' ) . '</th><td>';
					} else {
						$html .= '<div class="simple-field form-field term-group ' . $this->show_if_classes( $field ) . '"><span class="simple-label">' . ( ! empty( $field['label'] ) ? $field['label'] : '' ) . '</span>';
					}
				} else {
					if( $this->is_edit_page() ) {
						$html .= '<tr class="simple-field form-field term-group-wrap ' . $this->show_if_classes( $field ) . '"><th scope="row"><label for="'.$this->prefix.$field['id'].'">' . ( ! empty( $field['label'] ) ? $field['label'] : '' ) . '</label></th><td>';
					} else {
						$html .= '<div class="simple-field form-field term-group ' . $this->show_if_classes( $field ) . '"><label for="'.$this->prefix.$field['id'].'">' . ( ! empty( $field['label'] ) ? $field['label'] : '' ) . '</label>';
					}
				}

				$value = ( $this->is_edit_page() && $value = get_term_meta( $term->term_id, $this->field_name( $field ), true ) ) ? $value : '';

				$html .= $this->field( $field, $value, $this->prefix );

				$html .= $this->is_edit_page() ? '</td></tr>' : '</div>';

			endforeach;

			echo $html;

		endif;

	}

	function save( $term_id, $taxonomy_id ){



		if( !isset( $_POST[ $this->taxonomy_settings['id'].'_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ $this->taxonomy_settings['id'].'_wpnonce' ], $this->taxonomy_settings['id'] )) {
			return;
		}

		foreach ( $this->taxonomy_settings[ 'fields' ] as $field ) :

			$name = $this->field_name( $field );

			update_term_meta(
				$term_id,
				$name,
				( isset( $_POST[ $name ] ) ? $this->sanitize( $_POST[ $name ], $field[ 'type' ] ) : '' )
			);

		endforeach;

    }


	}
}
