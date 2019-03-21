<?php

class Hanzi_Converter {
	public $slug = 'hanzi-converter';
	public $version = '1.0';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_ajax_hanzi_converter', array( $this, 'hanzi_converter_ajax' ) );
	}
	
	/**
	 * Add the script
	 */
	public function init() {
		wp_enqueue_script(
			$this->slug,
			plugin_dir_url( __FILE__ ) . 'js/hanzi-converter-admin.js',
			array( 'jquery' ),
			$this->version
		);
	}
	
	/**
	 * Add the metabox
	 */
	public function add_meta_boxes() {
		add_meta_box(
			$this->slug,
			esc_html__( 'Hanzi Converter', $slug ),
			array( $this, 'hanzi_converter_metabox' ),
			null,
			'normal',
			'high'
		);
	}
	
	/**
	 * Render the metabox
	 */
	public function hanzi_converter_metabox( $post ) {
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hanzi-value' ) ); ?>"><?php esc_html_e( 'Hanzi', 'm-chart' ); ?></label><br />
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'hanzi_value' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'hanzi-value' ) ); ?>" rows="3" cols="40"></textarea>
		</p>
		<p>
			<a href="#convert" id="<?php echo esc_attr( $this->get_field_id( 'convert' ) ); ?>" class="button">Convert</a>
			<span id="<?php echo esc_attr( $this->get_field_id( 'error' ) ); ?>"></span>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hanzi-conversion' ) ); ?>"><?php esc_html_e( 'Conversion', 'm-chart' ); ?></label><br />
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'hanzi_conversion' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'hanzi-conversion' ) ); ?>" rows="5" cols="40"></textarea>
		</p>
		<style>
			#hanzi-converter-hanzi-value,
			#hanzi-converter-hanzi-conversion {
				width: 100%;
			}
			
			#hanzi-converter-error {
				display: inline-block;
				margin-left: .5rem;
				color: red;
			}
		</style>
		<?php
	}
	
	public function hanzi_converter_ajax() {		
		if ( ! isset( $_POST['hanzi'] ) || '' == $_POST['hanzi'] ) {
			wp_send_json_error( esc_html__( 'No hanzi submitted...', $slug ) );
		}
		
		$hanzi = $_POST['hanzi'];
		
		// Do conversion stuff here
		
		wp_send_json_success( $hanzi );
	}

	/**
	 * Return a name spaced field name
	 *
	 * @param string the field name we want to name space
	 *
	 * @param string a name spaced field name
	 */
	public function get_field_name( $field_name, $parent_field_name = '' ) {
		if ( '' != $parent_field_name ) {
			return $this->slug . '[' . $parent_field_name . ']' . '[' . $field_name . ']';
		}

		return $this->slug . '[' . $field_name . ']';
	}

	/**
	 * Return a name spaced field id
	 *
	 * @param string the field id we want to name space
	 *
	 * @param string a name spaced field id
	 */
	public function get_field_id( $field_name ) {
		return $this->slug . '-' . $field_name;
	}
}

/**
 * Plugin object accessor
 */
function hanzi_converter() {
	global $hanzi_converter;

	if ( ! $hanzi_converter instanceof Hanzi_Converter ) {
		$hanzi_converter = new Hanzi_Converter;
	}

	return $hanzi_converter;
}