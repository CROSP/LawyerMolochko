<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ReduxFramework_pxl_heading' ) ) {
 
	class ReduxFramework_pxl_heading {

		public $parent;
		public $field;
		public $value;
		public $extension_dir   = '';
		public $extension_url   = '';

		/**
		 * Class Constructor. Defines the args for the extensions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array        $field  Field sections.
		 * @param       array|string $value  Values.
		 * @param       array        $parent Parent object.
		 *
		 * @return      void
		 */
		public function __construct( $field, $value, $parent ) {

			// Set required variables
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			$this->extension_url = pxl_redux_fields()->extensions_url . 'pxl_heading/';
  
			$defaults = array(
				'indent'   => true,
				'style'    => '',
				'class'    => '',
				'title'    => '',
				'subtitle' => '',
				'collapse' => 'hide', // Accept value: '', 'show', 'hide'
			);
			
			$this->field = wp_parse_args( $this->field, $defaults );
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			$guid = uniqid();

			if ( true === $this->field['indent'] ) {
				$this->field['class'] .= ' redux-section-indent-start pxl-infor-heading';
			}

			$add_class = '';
			if ( isset( $this->field['indent'] ) && true === $this->field['indent'] ) {
				$add_class = ' form-table-section-indented';
			} elseif ( ! isset( $this->field['indent'] ) || ( isset( $this->field['indent'] ) && false !== $this->field['indent'] ) ) {
				$add_class = ' hide';
			}

			$table_style = '';

			echo '<input type="hidden" id="' . esc_attr( $this->field['id'] ) . '-marker"></td></tr></table>';

			if ( isset( $this->field['indent'] ) && true === $this->field['indent'] ) {
				echo '<div class="indent-section-container redux-normal redux-field-info">';
			}

			// Open collapse div
			if ( ! empty( $this->field['collapse'] ) ) {
				$class = 'redux-section-collapse-wrapper';

				if ( 'show' == $this->field['collapse'] ) {
					$class .= ' active';
					
				} elseif( 'hide' == $this->field['collapse'] ) {
					$table_style = 'display: none;';
				}

				echo '<div id="redux-section-collapse-wrapper-' . esc_attr( $this->field['id'] ) . '" class="' . esc_attr( $class ) . '" data-id="' . esc_attr( $this->field['id'] ) . '">';
			}

			echo '<div id="section-' . esc_attr( $this->field['id'] ) . '" class="redux-section-field redux-field ' . esc_attr( $this->field['style'] ) . ' ' . esc_attr( $this->field['class'] ) . ' ">';

			if ( ! empty( $this->field['title'] ) ) {
				echo '<h3>' . wp_kses_post( $this->field['title'] ) . '</h3>';
			}

			if ( ! empty( $this->field['subtitle'] ) ) {
				echo '<div class="redux-section-desc">' . wp_kses_post( $this->field['subtitle'] ) . '</div>';
			}

			echo '</div>';

			// Close collapse div
			if ( ! empty( $this->field['collapse'] ) ) {
				// Collapse icon
				echo '<div class="redux-section-collapse__icon"></div>';

				echo '</div>';
			}

			if ( isset( $this->field['indent'] ) && true === $this->field['indent'] ) {
				echo '</div>';
			}

			echo '<table id="section-table-' . esc_attr( $this->field['id'] ) . '" data-id="' . esc_attr( $this->field['id'] ) . '" class="form-table form-table-section no-border' . esc_attr( $add_class ) . '" style="' . esc_attr( $table_style ) . '"><tbody><tr><th></th><td id="' . esc_attr( $guid ) . '">';

			?>
			<script type="text/javascript">
				jQuery( document ).ready(
					function() {
						jQuery( '#<?php echo esc_attr( $this->field['id'] ); ?>-marker' ).parents( 'tr:first' )
						.css( {'display': 'none'} )
						.prev( 'tr' )
						.css( 'border-bottom', 'none' );

						var group = jQuery( '#<?php echo esc_attr( $this->field['id'] ); ?>-marker' ).parents( '.redux-group-tab:first' );
						if ( !group.hasClass( 'sectionsChecked' ) ) {
							group.addClass( 'sectionsChecked' );
							var test = group.find( '.redux-section-indent-start h3' );
							jQuery.each(
								test, function( key, value ) {
									jQuery( value ).css( 'margin-top', '20px' )
								}
							);
							if ( group.find( 'h3:first' ).css( 'margin-top' ) === "20px" ) {
								group.find( 'h3:first' ).css( 'margin-top', '0' );
							}
						}
					}
				);
			</script>
			<?php
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {
  
			wp_enqueue_script( 'redux-field-pxl-heading-js', $this->extension_url . 'inc/field_pxl_heading.js', array('jquery', 'wp-color-picker','redux-js'), time(), true );
			wp_enqueue_style( 'redux-field-pxl-heading-css', $this->extension_url . 'inc/field_pxl_heading.css' , array(), time(), 'all' );
		}

		/**
		 * Functions to pass data from the PHP to the JS at render time.
		 *
		 * @return array Params to be saved as a javascript object accessable to the UI.
		 * @since  Redux_Framework 3.1.5
		 */
		public function localize( $field, $value = array() ) {
			
		}

		private function enqueue_dependencies( $field_type ) {
			$field_class = 'Redux_' . ucfirst( $field_type );

			if ( ! class_exists( $field_class ) ) {
				$class_file = apply_filters( 'redux-typeclass-load', ReduxFramework::$_dir . 'inc/fields/' . $field_type . '/class-redux-' . $field_type . '.php', $field_class );

				if ( file_exists( $class_file ) ) {
					/** @noinspection PhpIncludeInspection */
					require_once( $class_file );
				}
			}

			if ( class_exists( $field_class ) && method_exists( $field_class, 'enqueue' ) ) {
				$enqueue = new $field_class( '', '', $this );
				$enqueue->enqueue();
			}
		}

	}
}
