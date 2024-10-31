<?php

/**

 * Narnoo Distributor - Operator Products Import

 **/

class Narnoo_Distributor_Operators_Product_Improt_Table extends WP_List_Table {



	public $func_type = 'product';

	public $operator = '';

	public $operator_name = '';



	function __construct( $args = array() ) {

		parent::__construct( $args );

		

		$this->operator = isset( $_POST['operator'] ) && isset( $_POST['operator'] ) ? trim( $_POST['operator'] ) : ( isset( $_GET['operator'] ) ? trim( $_GET['operator'] ) : $this->operator );



		$this->operator_name = isset( $_POST['operator_name'] ) && isset( $_POST['operator_name'] ) ? trim( $_POST['operator_name'] ) : ( isset( $_GET['operator_name'] ) ? trim( $_GET['operator_name'] ) : $this->operator_name );

		

	}



	function column_default( $item, $column_name ) {

		switch( $column_name ) {

			case 'product_name':

			case 'product_id':
			
			case 'booking_id':

				return $item[ $column_name ];

			default:

				return print_r( $item, true );

		}

	}



	function column_product_name( $item ) {

		$postData = Narnoo_Distributor_Helper::get_post_id_for_imported_product_id( $item['product_id'] );

        if ( !empty( $postData['id'] ) && $postData['status'] !== 'trash') {

			$import_action = __( 'Re-import', NARNOO_DISTRIBUTOR_I18N_DOMAIN );

		} else {

			$import_action = __( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN );

		}



		$actions = array(

			'import'    	=> sprintf(

									'<a href="?%s">%s</a>',

									build_query(

										array(

											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',

											'paged' => $this->get_pagenum(),

											'func_type' => $this->func_type,

											'operator' => $this->operator,

											'action' => 'do_import',

											'product_id[]' => $item['product_id'],

											'product_name[]' => $item['product_name'],

										)

									),

									$import_action

								),

		);



		return sprintf(

			'%1$s <br /> %2$s',

			$item['product_name'],

			$this->row_actions($actions)

		);

	}



	function column_cb($item) {

		return sprintf(

			'<input class="operator-id-cb" type="checkbox" name="product_id[]" value="%s" /><input class="operator-name-cb" type="checkbox" name="product_name[]" value="%s" style="display: none;" />',

			$item['product_id'], esc_attr( $item['product_name'] )

		);

	} 



	function get_bulk_actions() {

		$actions = array(

			'do_import'		=> __( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN )

		);

		return $actions;

	}

	

	function get_columns() {

		return array(

			'cb'                    => '<input type="checkbox" />',

			'product_name'          => __( 'Product Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),

			'product_id' 			=> __( 'Product Id', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			
			'booking_id' 			=> __( 'Booking Id', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),

		);

	} 



	/**

	 * Process actions and returns true if the rest of the table SHOULD be rendered.

	 * Returns false otherwise.

	 **/

	function process_action() {



		if ( isset( $_REQUEST['cancel'] ) ) {

			Narnoo_Distributor_Helper::show_notification( __( 'Action cancelled.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

			return true;

		}



		if ( isset( $_REQUEST['back'] ) ) {

			return true;

		}





		$action = $this->current_action();

		if ( false !== $action ) {



			$product_ids = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : array();

            $num_ids = count( $product_ids );



            $operator = isset( $_REQUEST['operator'] ) ? $_REQUEST['operator'] : array();

            

            if ( ( empty( $product_ids ) || ! is_array( $product_ids ) || $num_ids === 0 ) && empty( $operator ) ) {

                return true;

            }



			switch ( $action ) {



				// perform actual import

				case 'do_import':

					?>

					<h3><?php _e( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>

					<p><?php echo sprintf( __( "Importing the following %s product(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>

					<ol>

					<?php

					// Get operator details

                    $operator_data = Narnoo_Distributor_Helper::import_operator( $operator, false );



                    // Import Products

                    foreach( $product_ids as $key => $id ) {

                        Narnoo_Distributor_Helper::print_ajax_script_body(

                            $id, 'import_operator_products', array_merge( $operator_data, array( 'product_id' => $id ) ),

                            'ID #' . $id . ': ', 'self', false

                        );

                    }

					?>

					</ol>

					<?php

					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );



					return false;



			} 	// end switch( $action )

		}	// endif ( false !== $action )



		return true;

	}



	/**

	 * Request the current page data from Narnoo API server.

	 **/

	function get_current_page_data() {

		$data = array( 'total_pages' => 1, 'items' => array() );



		$list = null;

		$current_page = $this->get_pagenum();

		$request = Narnoo_Distributor_Helper::init_api('new');



		if ( ! is_null( $request ) ) {



			$import_bookable_product = apply_filters( 'narnoo_import_only_bookalble_product', false );

			if( $import_bookable_product ){

				try {



				     $list = $request->getBookableProducts( $this->operator );

				     if ( !is_array( $list->data->products ) ) {

						throw new Exception( sprintf( __( "Error retrieving products. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );

					 }



				} catch ( Exception $ex ) {

					Narnoo_Distributor_Helper::show_api_error( $ex );

				}



				if ( ! is_null( $list->data->products ) ) {			



				$data['total_pages'] = max( 1, intval( 1 ) );

		        

				foreach ( $list->data->products as $product ) {


		                $item['product_name'] = $product->productData->title;

		                $item['product_id']   = $product->productData->productId;

		                $item['booking_id']   = $product->productData->id;



						$data['items'][] = $item;

					}

				}



				return $data;



			} else {



				try {



				     $list = $request->getProducts( $this->operator );

				           

				     if ( !is_array( $list->data->data ) ) {

						throw new Exception( sprintf( __( "Error retrieving product. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );

					 }



				} catch ( Exception $ex ) {

					Narnoo_Distributor_Helper::show_api_error( $ex );

				}



				if ( ! is_null( $list->data->data ) ) {			



				$data['total_pages'] = max( 1, intval( 1 ) );

		        

				foreach ( $list->data->data as $product ) {


		                $item['product_name'] = $product->title;

		                $item['product_id']   = $product->productId;
		                
		                $item['booking_id']   = $product->id;



						$data['items'][] = $item;

					}

				}



				return $data;



			}



		}



		

	}



	/**

	 * Process any actions (displaying forms for the actions as well).

	 * If the table SHOULD be rendered after processing (or no processing occurs), prepares the data for display and returns true.

	 * Otherwise, returns false.

	 **/

	function prepare_items() {

		if ( ! $this->process_action() ) {

			return false;

		}



		$this->_column_headers = $this->get_column_info();



		$data = $this->get_current_page_data();

		$this->items = $data['items'];



		$this->set_pagination_args( array(

			'total_items'	=> count( $data['items'] ),

			'total_pages'	=> $data['total_pages']

		) );



		return true;

	}



	/**

	 * Add screen options for operators page.

	 **/

	static function add_screen_options() {

		global $narnoo_distributor_operators_table;

		$narnoo_distributor_operators_table = new Narnoo_Distributor_Operators_Product_Improt_Table();

	}



	/**

	 * Enqueue scripts and print out CSS stylesheets for this page.

	 **/

	static function load_scripts( $hook ) {

		global $narnoo_distributor_operators_page;



		if ( $narnoo_distributor_operators_page !== $hook || ( isset( $_REQUEST['func_type'] ) && $_REQUEST['func_type'] !== 'list' ) ) {	// ensure scripts are only loaded on this Page

			return;

		}



		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'operator_table.js', plugins_url( 'js/operator_table.js', __FILE__ ), array( 'jquery' ) );

		?>

		<style type="text/css">

		.wp-list-table .column-operator_businessname { width: 15%; }

		.wp-list-table .column-operator_id { width: 5%; }

		.wp-list-table .column-category { width: 10%; }

		.wp-list-table .column-state { width: 5%; }

		</style>

		<?php

	}

}