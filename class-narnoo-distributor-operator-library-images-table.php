<?php
/**
 * Narnoo Distributor - Images table for the Narnoo Operators Library tab (Add/Insert Media window).
 **/
class Narnoo_Distributor_Operator_Library_Images_Table extends Narnoo_Distributor_Library_Images_Table {
	public $operator_id = '';
	
	function __construct( $args = array() ) {		
		parent::__construct( $args );
		
		if ( isset( $_REQUEST['switch_operator'] ) ) {
			if ( empty( $_REQUEST['operator_id_input'] ) ) {
				Narnoo_Distributor_Helper::show_error( __( 'Please key in a valid operator ID.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
				return;
			}
			
			$this->operator_id = trim( $_REQUEST['operator_id_input'] );
			if ( intval( $this->operator_id ) <= 0 ) {
				$this->operator_id = '';
				Narnoo_Distributor_Helper::show_error( __( 'Please key in a valid operator ID.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
				return;
			}

			return;
		}	
		
		if ( isset( $_REQUEST['operator_id'] ) ) {
			$this->operator_id = $_REQUEST['operator_id'];
		} 
	}

	function views() {
		?>
		<h3>
			<?php _e( 'Currently viewing operator: ', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>
		</h3>
		<p>
			Operator ID: <input type="text" id="operator_id_input" name="operator_id_input" value="<?php echo esc_attr( $this->operator_id ); ?>" />
			<input type="submit" name="switch_operator" id="switch_operator" class="button-secondary" value="<?php _e( 'change operator', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
		</p>
		<hr />
		
		<?php		
		parent::views();
	}

	/**
	 * Request the current page data from Narnoo API server.
	 **/
	function get_current_page_data() {
		return $this->get_current_page_data_for_type( 'operator' );
	}
}    