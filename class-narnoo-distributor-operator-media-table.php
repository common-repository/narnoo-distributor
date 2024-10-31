<?php
/**
 * Narnoo Operator - Media Table.
 **/
class Narnoo_Distributor_Operator_Media_Table extends WP_List_Table {
	public $operator_id = '';
	public $operator_name = '';
	public $media_view_type = '';	// overidden in child class
	
	function __construct( $args = array() ) {
		parent::__construct( $args );
		
		if ( isset( $_REQUEST['switch_operator'] ) ) {
			if ( empty( $_REQUEST['operator_id_input'] ) ) {
				Narnoo_Distributor_Helper::show_error( __( 'Please key in a valid operator ID.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
				return;
			}

			$this->operator_id = trim( $_REQUEST['operator_id_input'] );
			$request = Narnoo_Distributor_Helper::init_api('operator');
			//$cache	 		= Narnoo_Distributor_Helper::init_noo_cache();

			if ( ! is_null( $request ) ) {
				try {
					
					//$operator = $cache->get('operator'.$this->operator_id);
					
					//if(empty($operator)){
						$operator = $request->getAccount( $this->operator_id );
						//if( !empty( $operator->success ) ){
						//$cache->set('operator'.$this->operator_id, $operator, 43200);
						//}
					//}
                	
					if ( ! isset( $operator->data->details->id ) ) {
						throw new Exception( sprintf( __( "Error retrieving operator %s. Unexpected format in response.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $this->operator_id ) );
					}
					
                    $this->operator_id 		= $operator->data->details->id;
                    $this->operator_name 	= $operator->data->details->business;
					return;
				} catch ( Exception $ex ) {
					Narnoo_Distributor_Helper::show_api_error( $ex );
					return;
				} 
			}
		}

		if ( isset( $_REQUEST['operator_id'] ) ) {
			$this->operator_id = $_REQUEST['operator_id'];
		}
		if ( isset( $_REQUEST['operator_name'] ) ) {
			$this->operator_name = $_REQUEST['operator_name'];
		}
	}

	function views() {
		?>
		<h3>
			<?php _e( 'Currently viewing operator: ', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>
			<?php echo esc_html( $this->operator_name ); ?> 
		</h3>
		<p>
			Operator ID: <input type="text" id="operator_id_input" name="operator_id_input" value="<?php echo esc_attr( $this->operator_id ); ?>" />
			<input type="submit" name="switch_operator" id="switch_operator" class="button-secondary" value="<?php _e( 'change operator', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
		</p>
		<hr />
		
		<?php		
		parent::views();
	}

	function get_views() {
		$current_view = $this->media_view_type;
		
		$raw_views = array( 
			'Images' => __( 'Images', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			//'Albums' => __( 'Albums', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), 
			'Print' => __( 'Print', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), 
			'Videos' => __( 'Videos', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
		
		$views = array();
		foreach( $raw_views as $view_name => $view_text ) {
			$views[ $view_name ] = sprintf( 
				'<a href="?%s" %s>%s</a>',
				build_query( array(
					"page" => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
					"media_type" => $view_name,
					"operator_id" => $this->operator_id,
					"operator_name" => $this->operator_name,
				) ),
				$current_view === $view_name ? 'class="current"' : '',
				$view_text
			);
		}
		
		return $views;
	}
	
	/**
	 * Add screen options for operator media page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_operator_media_table;
		
		$media_type = isset( $_POST['media_type'] ) ? trim( $_POST['media_type'] ) : ( isset( $_GET['media_type'] ) ? trim( $_GET['media_type'] ) : 'Images' );
		switch ( $media_type ) {
			case 'Images': $narnoo_distributor_operator_media_table 	= new Narnoo_Distributor_Operator_Images_Table(); break;
			//case 'Albums': $narnoo_distributor_operator_media_table 	= new Narnoo_Distributor_Operator_Albums_Table(); break;
			case 'Videos': $narnoo_distributor_operator_media_table 	= new Narnoo_Distributor_Operator_Videos_Table(); break;
			case 'Brochures':
			case 'Print': $narnoo_distributor_operator_media_table 		= new Narnoo_Distributor_Operator_Brochures_Table(); break;
		}		
	}	
}