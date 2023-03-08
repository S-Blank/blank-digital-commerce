<?php

class Simple_Gutenberg_Sidebars {

	public $sidebars;

	function __construct() {

		$this->sidebars = apply_filters( 'simple_register_sidebars', array() );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'init', array( $this, 'meta' ) );

	}

	function scripts() {

		// register
		wp_register_script(
			'rudrsmfsb',
			plugin_dir_url( __DIR__ ) . 'assets/sidebar.js',
			array( 'wp-edit-post', 'wp-element', 'wp-components', 'wp-plugins', 'wp-data' ),
			filemtime( plugin_dir_path( __DIR__ ) . 'assets/sidebar.js' )
		);

		wp_localize_script(
			'rudrsmfsb',
			'GutenbergSidebarData',
			array(
				'sidebars' => $this->prepare_sidebars()
			)
		);

		wp_enqueue_script( 'rudrsmfsb' );

	}

	// TODO it is always possible to sanitize and clean field values passed
	function prepare_sidebars(){
		$sidebars = array();
		// optimize post type
		foreach( $this->sidebars as $sidebar ) {

			// optimize post type
			if( ! empty( $sidebar[ 'post_type' ] ) ) {
				$sidebar[ 'post_type' ] = is_array( $sidebar[ 'post_type' ] ) ? $sidebar[ 'post_type' ] : array( $sidebar[ 'post_type' ] );
			}

			// optimize sections
			if( empty( $sidebar[ 'sections' ] ) && ! empty( $sidebar[ 'fields' ] ) ) {
				$sidebar[ 'sections' ] = array(
					array(
						'name' => '',
						'opened' => true,
						'fields' => $sidebar[ 'fields' ]
					)
				);
			}

			$sidebars[] = $sidebar;
		}
		return $sidebars;
	}

	function meta() {

		$sidebars = $this->prepare_sidebars();

		foreach( $sidebars as $sidebar ) {

			foreach( $sidebar[ 'sections' ] as $section ) {

				if( $section[ 'fields' ] ) {

					foreach( $section[ 'fields' ] as $field ){

						// TODO register meta only for specific post types?
						if( ! empty( $sidebar[ 'post_type' ] ) ) {
							foreach( $sidebar[ 'post_type' ] as $post_type ) {
								$this->register_meta( $field, $post_type );
							}
						} else {
							$this->register_meta( $field );
						}

					} // fields loop

				}

			} // sections loop

		} // sidebars loop

	}

	private function register_meta( $field, $post_type = '' ){

		$field_type = ! empty( $field[ 'type' ] ) ? $field[ 'type' ] : 'text';
		switch( $field_type ) {
			case 'image' : {
				$type = 'integer';
				$show_in_rest = true;
				break;
			}
			//case 'toggle' : we're not using it right now
			case 'checkbox' : {
				$type = 'boolean';
				$show_in_rest = true;
				break;
			}
			case 'checklist' : {
				$type = 'array';
				$show_in_rest = array(
					'schema' => array( 'type'  => 'array', 'items' => array( 'type' => 'string' ) )
				);
				break;
			}
			case 'gallery' : {
				$type = 'array';
				$show_in_rest = array(
					'schema' => array( 'type'  => 'array', 'items' => array( 'type' => 'integer' ) )
				);
				break;
			}
			case 'repeater' : {
				$type = 'array';
				$show_in_rest = array(
					'schema' => array( 'type'  => 'array', 'items' => array( 'type' => 'object', 'properties' => array() ) )
				);
				// that's great but now we have to add properties for this specific field!
				if( isset( $field[ 'subfields' ] ) && $field[ 'subfields' ] ) {
					foreach( $field[ 'subfields' ] as $subfield ) {
						// we have not a lot of supported types in repeater
						switch( $subfield[ 'type' ] ) {
							case 'checkbox' : {
								$subfield_type = 'boolean';
								break;
							}
							case 'image' : {
								$subfield_type = 'integer';
								break;
							}
							default : { // text, textarea, select
								$subfield_type = 'string';
								break;
							}
						}
						$show_in_rest[ 'schema' ][ 'items' ][ 'properties' ][ $subfield['id'] ] = array( 'type' => $subfield_type );
					}
				}
				break;
			}
			default: {
				$type = 'string';
				$show_in_rest = true;
				break;
			}
		}

		$args = array(
			'type' => $type,
			'single' => true,
			'show_in_rest' => $show_in_rest
		);
		if( $post_type ) {
			$args[ 'object_subtype' ] = $post_type;
		}
		register_meta( 'post', $field[ 'id' ], $args );
	}

}
