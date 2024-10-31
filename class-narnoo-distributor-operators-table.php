<?php
/**
 * Narnoo Distributor - Operators table.
 **/
class Narnoo_Distributor_Operators_Table extends WP_List_Table {

	public $func_type = 'list';

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'operator_id':
			//case 'category':
			//case 'sub_category':
			//case 'description':
			case 'operator_businessname':
			case 'phone':
			case 'email':
			case 'type':
			//case 'keywords':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_operator_businessname( $item ) {
		if ( $item['imported'] ) {
			$import_action = __( 'Re-import', NARNOO_DISTRIBUTOR_I18N_DOMAIN );
		} else {
			$import_action = __( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN );
		}

		$actions = array(
			'media'			=> sprintf(
									'<a href="?%s">%s</a>',
									build_query(
										array(
											'page' => 'narnoo-distributor-operator-media',
											'action' => 'media',
											'operator_id' => $item['operator_id'],
											'operator_name' => $item['operator_businessname'],
										)
									),
									__( 'Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
								),
			'import'    	=> sprintf(
									'<a href="?%s">%s</a>',
									build_query(
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'import',
											'operators[]' => $item['operator_id'],
											'operator_names[]' => $item['operator_businessname'],
										)
									),
									$import_action
								),
			'delete'    	=> sprintf(
									'<a href="?%s">%s</a>',
									build_query(
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'delete',
											'operators[]' => $item['operator_id'],
											'operator_names[]' => $item['operator_businessname'],
										)
									),
									__( 'Delete' )
								),
		);

		$options = get_option('narnoo_distributor_settings');
		// if ( !empty( $options['operator_import'] )  ) {
			$actions['Product'] = sprintf(
									'<a href="?%s">%s</a>',
									build_query(
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'func_type' => 'product',
											'operator' => $item['operator_id'],
											'operator_name' => $item['operator_businessname'],
										)
									),
									__( 'Product' )
								);
		// }

		return sprintf(
			'%1$s <br /> %2$s',
			$item['operator_businessname'],
			$this->row_actions($actions)
		);
	}

	function column_cb($item) {
		return sprintf(
			'<input class="operator-id-cb" type="checkbox" name="operators[]" value="%s" /><input class="operator-name-cb" type="checkbox" name="operator_names[]" value="%s" style="display: none;" />',
			$item['operator_id'], esc_attr( $item['operator_businessname'] )
		);
	}

	function get_columns() {
		return array(
			'cb'                    => '<input type="checkbox" />',
			'operator_businessname' => __( 'Business', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			//'description'           => __( 'Description', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'operator_id'           => __( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			//'category'              => __( 'Category', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			//'sub_category'          => __( 'Subcategory', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			//'country_name'          => __( 'Country', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'phone'                 => __( 'Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'email'              	=> __( 'Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'type'              	=> __( 'Type', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'    	=> __( 'Delete' ),
			'import'		=> __( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
		return $actions;
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
			$operator_ids = isset( $_REQUEST['operators'] ) ? $_REQUEST['operators'] : array();
			$num_ids = count( $operator_ids );
			if ( empty( $operator_ids ) || ! is_array( $operator_ids ) || $num_ids === 0 ) {
				return true;
			}

			$operator_names = isset( $_REQUEST['operator_names'] ) ? $_REQUEST['operator_names'] : array();
			$num_names = count( $operator_names );
			if ( empty( $operator_names ) || ! is_array( $operator_names ) || $num_names !== $num_ids ) {
				return true;
			}

			foreach( $operator_names as $key => $operator_name ) {
				$operator_names[ $key ] = stripslashes( $operator_name );
			}

			if ( $action === 'import' ) {
				// determine whether we need to show confirmation screen for re-importing of existing operators
				$imported_ids = Narnoo_Distributor_Helper::get_imported_operator_ids();
				$existing_operator_ids = array();
				foreach( $operator_ids as $key => $operator_id ) {
					if ( in_array( $operator_id, $imported_ids ) ) {
						$existing_operator_ids[] = $operator_id;
						$existing_operator_names[] = $operator_names[ $key ];
					}
				}

				if ( empty( $existing_operator_ids ) ) {
					$action = 'do_import';
				}
			}

			switch ( $action ) {

				// confirm deletion
				case 'delete':
					?>
					<h3><?php _e( 'Confirm deletion', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( 'Please confirm deletion of the following %d operator(s):', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach ( $operator_ids as $key => $id ) {
						?>
						<input type="hidden" name="operators[]" value="<?php echo $id; ?>" />
						<input type="hidden" name="operator_names[]" value="<?php echo esc_attr( $operator_names[ $key ] ); ?>" />
						<li><span>ID #<?php echo $id; ?>: <?php echo esc_html( $operator_names[ $key ] ); ?></span></li>
						<?php
					}
					?>
					</ol>
					<input type="hidden" name="action" value="do_delete" />
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button-secondary" value="<?php _e( 'Confirm Deletion' ); ?>" />
						<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
					</p>
					<?php

					return false;

				// perform actual delete
				case 'do_delete':
					?>
					<h3><?php _e( 'Delete' ); ?></h3>
					<p><?php echo sprintf( __( "Deleting the following %s operator(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $operator_ids as $key => $id ) {
						Narnoo_Distributor_Helper::print_ajax_script_body(
							$id, 'removeOperator', array( $id ),
							'ID #' . $id . ': ' . $operator_names[ $key ], 'new'
						);
					}
					?>
					</ol>
					<?php
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

					return false;

				// confirm re-import of existing operators
				case 'import':
					?>
					<h3><?php _e( 'Confirm re-import', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( 'The following operator(s) have already been imported to Wordpress posts. Re-importing will overwrite any custom changes made to the imported fields. Please confirm re-import of the following %d operator(s):', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), count( $existing_operator_ids ) ); ?></p>
					<ol>
					<?php
					foreach ( $existing_operator_ids as $key => $id ) {
						?>
						<li><span>ID #<?php echo $id; ?>: <?php echo esc_html( $existing_operator_names[ $key ] ); ?></span></li>
						<?php
					}
					?>
					</ol>
					<?php
					foreach( $operator_ids as $key => $id ) {
						?>
						<input type="hidden" name="operators[]" value="<?php echo $id; ?>" />
						<input type="hidden" name="operator_names[]" value="<?php echo esc_attr( $operator_names[ $key ] ); ?>" />
						<?php
					}
					?>
					<input type="hidden" name="action" value="do_import" />
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button-secondary" value="<?php _e( 'Confirm Re-import' ); ?>" />
						<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
					</p>
					<?php

					return false;

				// perform actual import
				case 'do_import':
					?>
					<h3><?php _e( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Importing the following %s operator(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $operator_ids as $key => $id ) {
						Narnoo_Distributor_Helper::print_ajax_script_body(
							$id, 'import_operator', array( $id ),
							'ID #' . $id . ': ' . $operator_names[ $key ], 'self', true
						);
					}
					?>
					</ol>
					<?php
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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
		//$cache	 		= Narnoo_Distributor_Helper::init_noo_cache();

		if ( ! is_null( $request ) ) {
			try {

               	//$list = $cache->get('operators_'.$current_page);
               	//if(empty($list)){
				    $list 		= $request->following($current_page);
                	$operators 	= $list->data;

                	//if( !empty( $list->success ) ){
						//$cache->set('operators_'.$current_page, $list, 43200);
					//}
				//}
                	//print_r($operators);

				if ( ! is_array( $operators ) ) {
					throw new Exception( sprintf( __( "Error retrieving operators. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			}
		}

		if ( ! is_null( $list ) ) {
			// get list of imported operator IDs
			$imported_ids = Narnoo_Distributor_Helper::get_imported_operator_ids();
			

			$data['total_pages'] = max( 1, intval( 1 ) );
        
			foreach ( $operators as $operator ) {
				//print_r($operator);

                $item['operator_id'            ] = $operator->details->id;
                //$item['category'             ] = $operator->category;
                //$item['sub_category'         ] = $operator->sub_category;
                //$item['description'          ] = $operator->description_excerpt;
                $item['operator_businessname'] = $operator->details->business;
                //$item['country_name'         ] = $operator->country;
                $item['phone'                  ] = $operator->details->phone;
                $item['email'             	   ] = $operator->details->email;
                $item['type'             	   ] = $operator->details->type;


				$item['imported'] = in_array(  $item['operator_id' ], $imported_ids );
				$data['items'][] = $item;
			}
		}

		return $data;
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

		$func_type = isset( $_POST['func_type'] ) ? trim( $_POST['func_type'] ) : ( isset( $_GET['func_type'] ) ? trim( $_GET['func_type'] ) : 'list' );
		switch ( $func_type ) {
			case 'list': $narnoo_distributor_operators_table 	= new Narnoo_Distributor_Operators_Table(); break;
			case 'search': $narnoo_distributor_operators_table 	= new Narnoo_Distributor_Search_Add_Operators_Table(); break;
			case 'product': $narnoo_distributor_operators_table = new Narnoo_Distributor_Operators_Product_Improt_Table(); break;
		}
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
		.wp-list-table .column-operator_businessname { width: 20%; }
		.wp-list-table .column-operator_id { width: 10%; }
		.wp-list-table .column-category { width: 10%; }
		.wp-list-table .column-state { width: 5%; }
		</style>
		<?php
	}
}
