<?php

class Hanzi_Converter {
	public $slug = 'hanzi-converter';
	public $version = '1.0';

	private $pinyin;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_ajax_hanzi_converter', array( $this, 'hanzi_converter_ajax' ) );
	}

	public function pinyin() {
		if ( ! $this->pinyin instanceof Pinyin ) {
			if ( ! class_exists( 'Overtrue\Pinyin\Pinyin' ) ) {
				require __DIR__ . '/external/pinyin/src/DictLoaderInterface.php';
				require __DIR__ . '/external/pinyin/src/FileDictLoader.php';
				require __DIR__ . '/external/pinyin/src/GeneratorFileDictLoader.php';
				require __DIR__ . '/external/pinyin/src/MemoryFileDictLoader.php';
				require __DIR__ . '/external/pinyin/src/Pinyin.php';
			}

			$this->pinyin = new Overtrue\Pinyin\Pinyin;
		}

		return $this->pinyin;
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
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'hanzi_value' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'hanzi-value' ) ); ?>" rows="7" cols="40"></textarea>
		</p>
		<p>
			<a href="#convert" id="<?php echo esc_attr( $this->get_field_id( 'convert' ) ); ?>" class="button">Convert</a>
			<span id="<?php echo esc_attr( $this->get_field_id( 'error' ) ); ?>"></span>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hanzi-conversion' ) ); ?>"><?php esc_html_e( 'Conversion', 'm-chart' ); ?></label><br />
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'hanzi_conversion' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'hanzi-conversion' ) ); ?>" rows="7" cols="40"></textarea>
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

		// If string has multiple lines we assume it's lesson dialog and parse it as such
		if ( false !== strpos( $hanzi, "\n" ) ) {
			$conversion = $this->parse_dialog( $hanzi );

			if ( '' == $conversion ) {
				wp_send_json_error( esc_html__( 'The dialog was not formatted correctly...', $slug ) );
			}
		} else {
			// Make sure we've got actual hanzi to work with
			if ( ! $this->is_hanzi( $hanzi ) ) {
				wp_send_json_error( esc_html__( 'Submission did not contain hanzi...', $slug ) );
			}

			$conversion = $this->hanzi_to_pinyin( $hanzi );
		}

		wp_send_json_success( $conversion );
	}

	public function is_hanzi( $string ) {
		return preg_match( "/\p{Han}+/u", trim( $string ) );
	}

	public function parse_dialog( $dialog ) {
		$lines = explode( "\n", $dialog );
		$row = array();
		$converted_dialog = '';

		foreach ( $lines as $line ) {
			switch ( $this->line_type( $line ) ) {
				case 'hanzi':
					$row['hanzi'] = trim( $line );
					$row['pinyin'] = $this->hanzi_to_pinyin( $line );
					break;
				case 'english':
					$row['english'] = trim( $line );
					break;
			}

			// Check if we've got all the values we need for a new line of dialog
			if ( $this->full_dialog_row( $row ) ) {
				// Add a new line of converted dialog
				$converted_dialog .= $row['hanzi'] . '|' . $row['pinyin'] . '|' . $row['english'] . "\n";
				// Reset the row array
				$row = array();
			}
		}

		return $converted_dialog;
	}

	public function full_dialog_row( $row ) {
		return ! array_diff_key( array_flip( array( 'hanzi', 'pinyin', 'english' ) ), $row );
	}

	public function line_type( $line ) {
	    if ( 0 == strlen( trim( $line ) ) ) {
	    	return 'blank';
	    } elseif ( $this->is_hanzi( $line ) ) {
	    	return 'hanzi';
	    } else {
	    	return 'english';
	    }
	}

	/**
	 * Convert Hanzi to Pinyin
	 */
	public function hanzi_to_pinyin( $hanzi ) {
		$pinyin = $this->pinyin()->sentence( $hanzi, PINYIN_TONE );
		return trim( $pinyin );
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