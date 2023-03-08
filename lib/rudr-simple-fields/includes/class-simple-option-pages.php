<?php
/*
 * Option pages
 */
if( !class_exists( 'Simple_Option_Page' ) ) {

	class Simple_Option_Page extends Simple_Meta_Fields{

		function __construct( $options ) {

			$this->options = $options;
			$this->options[ 'capability' ] = ! empty( $this->options[ 'capability' ] ) ? $this->options[ 'capability' ] : 'manage_options';
			$this->options[ 'position' ] = ! empty( $this->options[ 'position' ] ) ? $this->options['position'] : null;
			$this->options[ 'icon' ] = ! empty( $this->options[ 'icon' ] ) ? $this->options[ 'icon' ] : '';
			$this->prefix = $this->options['id'] . '_';

			if( ! in_array( $this->options[ 'id' ], array( 'general', 'writing', 'reading', 'discussion', 'media', 'permalink' ) ) ) {
				add_action( 'admin_menu', array( $this, 'add_page' ) );
			}

			add_action( 'admin_init', array( $this, 'settings_fields') );

		}


		function add_page() {
			if( empty( $this->options[ 'parent_slug' ] ) ) {
				add_menu_page( $this->options[ 'title' ], $this->options[ 'menu_name' ], $this->options[ 'capability' ], $this->options[ 'id' ], array( $this,'body'), $this->options[ 'icon' ], $this->options[ 'position' ] );
			} else {
				add_submenu_page( $this->options[ 'parent_slug' ], $this->options[ 'title' ], $this->options[ 'menu_name' ], $this->options[ 'capability' ], $this->options[ 'id' ], array( $this, 'body' ), $this->options[ 'position' ] );
			}
		}

		function body() {
			?><div class="wrap">
			<h1><?php echo $this->options['title'] ?></h1>
			<form method="POST" action="options.php">
				<?php
					settings_fields( $this->options[ 'id' ] );
					do_settings_sections( $this->options[ 'id' ] );
					submit_button();
				?>
			</form>
			</div>
			<?php
		}

		function settings_fields(){

			// in case "section" parameter is empty
			if( empty( $this->options['sections'] ) || !is_array( $this->options[ 'sections' ] ) ) {
				$this->options['sections'] = array(
					array(
						'id'			=> 'default',
						'name'		=> '',
						'fields'	=> $this->options[ 'fields' ],
					)
				);
			}

			foreach ( $this->options['sections'] as $section ) :

				// Either NOT default section OR default section BUT not default page
				if( 'default' !== $section['id'] || !in_array( $this->options['id'], array('general','writing', 'reading','discussion','media','permalink' ) )) {
					add_settings_section(
						$section['id'],
						( ! empty( $section['name'] ) ? $section['name'] : '' ),
						null,
						$this->options['id']
					);
				}


				if( empty( $section[ 'fields' ] ) || ! is_array( $section[ 'fields' ] ) ) {
					return;
				}


				foreach( $section[ 'fields' ] as $field ) :



					$field[ 'value' ] = get_option( $this->field_name( $field ) );

					if( ! in_array( $field[ 'type' ], array( 'checkbox', 'radio', 'checklist' ) ) ) {
						$field[ 'label_for' ] = $this->prefix . $field['id'];
					}

					$field[ 'class' ] = ( isset( $field[ 'class' ] ) && ! is_array( $field[ 'class' ] ) ) ? $field[ 'class' ] : ( isset( $field[ 'class' ] ) ? join( ' ', $field[ 'class' ] ) : '' );
					if( $show_if_classes = $this->show_if_classes( $field ) ) {
						$field[ 'class' ] .= ' ' . $show_if_classes;
					}

					add_settings_field(
						$field['id'],
						( ! empty( $field['label'] ) ? $field['label'] : '' ),
						array( $this, 'the_field'),
						$this->options['id'],
						$section['id'],
						$field
					);

					register_setting(
						$this->options[ 'id' ],
						$field[ 'id' ],
						array( 'sanitize_callback'=> $this->sanitize_callback( $field[ 'type' ] ) )
					);

				endforeach;


			endforeach;

		}

		// displaying the field
		function the_field( $params = array() ) {
			echo $this->field( $params, $params[ 'value' ], $this->prefix );
		}



		// decide about proper sanitization function
		public function sanitize_callback( $type ) {

			switch( $type ) {
				case 'textarea' : {
					$callback = 'sanitize_textarea_field';
					break;
				}
				case 'checkbox' : {
					$callback = array( $this, 'sanitize_checkbox' );
					break;
				}
				case 'checklist' : {
					$callback = array( $this, 'sanitize_checklist' );
					break;
				}
				case 'editor' : {
					$callback = array( $this, 'sanitize_editor' );
					break;
				}
				case 'gallery' : {
					$callback = array( $this, 'sanitize_gallery' );
					break;
				}
				case 'repeater' : {
					$callback = array( $this, 'sanitize_repeater_field' );
					break;
				}
				default : {
					$callback = 'sanitize_text_field';
					break;
				}
			}
			return $callback;
		}

	}


}
