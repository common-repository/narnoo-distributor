<?php
/**
 * Narnoo Distributor - Search/Add Operators table.
 **/
class Narnoo_Distributor_Search_Add_Operators_Table extends WP_List_Table {
	
	public $func_type = 'search';
	
	public $search_name        = '';
		
	function __construct( $args = array() ) {
		parent::__construct( $args );
		
		$this->search_name      	= isset( $_POST['search-submit'] ) && isset( $_POST['search_name'    ] ) ? trim( $_POST['search_name'    ] ) : ( isset( $_GET['search_name'    ] ) ? trim( $_GET['search_name'    ] ) : $this->search_name    );
		
	}
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'operator_id':
			case 'operator_businessname':
			case 'country_name':
			case 'state':
			case 'suburb':
			case 'postcode':
			case 'location':
			case 'type':
			case 'contact':
			case 'phone':
			case 'email':
			case 'url':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_operator_businessname( $item ) {  
		
		if ( $item['imported'] ) {
		
			$import_action = __( 'Re-import', NARNOO_DISTRIBUTOR_I18N_DOMAIN );
			$actions = array(
			
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
								)
		);
			
		} elseif( !empty($item['connected']) && empty($item['imported'])) {
		
			$import_action = __( 'Import', NARNOO_DISTRIBUTOR_I18N_DOMAIN );
			$actions = array(
			
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
								)
		);
		
		} else{
		
		$actions = array(
			'add'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page'               => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged'              => $this->get_pagenum(),
											'func_type'          => $this->func_type,
											'action'             => 'add', 
											'operators[]'        => $item['operator_id'], 
											'operator_names[]'   => $item['operator_businessname'],
											'search_name'     	 => $this->search_name
										)
									),
									__( 'Add', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
								)
		);
	
		}
		
		
		
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
		if( !empty($this->search_name) ) {
			return array(
				'cb'                    => '<input type="checkbox" />',
				'operator_businessname' => __( 'Business', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'operator_id'           => __( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'country_name'          => __( 'Country', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'state'                 => __( 'State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'suburb'                => __( 'Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'postcode'              => __( 'Postcode', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
			);
		} else {
			return array(
				'cb'                    => '<input type="checkbox" />',
				'operator_businessname' => __( 'Business', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'operator_id'           => __( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'type'					=> __( 'Type', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				'location'				=> __( 'Location', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				// 'contact'          		=> __( 'Contact', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				// 'phone'	                => __( 'Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				// 'email'                 => __( 'Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				// 'url'              		=> __( 'URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
			);
		}
	}
	
	/*function get_bulk_actions() {
		$actions = array(
			'add'		=> __( 'Add', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
		return $actions;
	}*/

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
		if ( isset( $_REQUEST['search-submit'] ) ) {
			$action = false;
		}
		if ( false !== $action ) {
			
			$operator_ids = empty( $_REQUEST['operators'] ) ? array() : $_REQUEST['operators'];
			$num_ids = count( $operator_ids );
			if ( empty( $operator_ids ) || ! is_array( $operator_ids ) || $num_ids === 0 ) {
					Narnoo_Distributor_Helper::show_error( __( 'Please search for an operator.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
				
				return true;				
			}
			
			if ( $action === 'add' ) {
				$operator_names = isset( $_REQUEST['operator_names'] ) ? $_REQUEST['operator_names'] : array();
				$num_names = count( $operator_names );
				if ( empty( $operator_names ) || ! is_array( $operator_names ) || $num_names !== $num_ids ) {
					return true;				
				}
				foreach( $operator_names as $key => $operator_name ) {
					$operator_names[ $key ] = stripslashes( $operator_name );
				}
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
			
				// perform actual add
				case 'add':
					?>
					<h3><?php _e( 'Add' ); ?></h3>
					<p><?php echo sprintf( __( "Adding the following %s operator(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $operator_ids as $key => $id ) {
						$id = trim( $id );
						Narnoo_Distributor_Helper::print_ajax_script_body( 
							$id, 'followOperator', array( $id ),
							'ID #' . $id . ': ' . esc_html( $operator_names[ $key ] ), 'new'
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

		if ( ! is_null( $request ) && !empty($this->search_name) ) {
			try {
				//THIS IS THE SEARCH API CALL
				$searchArrray = array(
					"name"	=> $this->search_name
				);
				$list = $request->search( $searchArrray );//, $current_page
				
				if(!empty($this->search_name)){

					if ( ! is_array( $list->data ) ) {
						throw new Exception( sprintf( __( "Error retrieving operators. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
					}
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			}
		} else {
			try {
				//THIS IS THE FIND API CALL
				$list = $request->find();//, $current_page
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			}
		}
		
		if ( !empty( $list->success ) ) {
			// get list of imported operator IDs
			$imported_ids = Narnoo_Distributor_Helper::get_imported_operator_ids();
			if( !empty($this->search_name) ) { 

				//$data['total_pages'] = max( 1, intval( $list->total_pages ) );
				foreach ( $list->data as $operator ) {
					
	                $item['operator_id'            ] = $operator->id;
	                $item['operator_businessname'  ] = $operator->name;
	                $item['country_name'           ] = $operator->country;
	                $item['state'                  ] = $operator->state;
	                $item['suburb'                 ] = $operator->suburb;
	                $item['postcode'               ] = $operator->postcode;
	                $item['type'             	   ] = $operator->type;


					$item['imported'] = in_array(  $item['operator_id' ], $imported_ids );
					$data['items'][] = $item;			
				}
			} else {
				foreach ( $list->data as $operator ) {
					
	                $item['operator_id'            ] = $operator->details->id;
	                $item['operator_businessname'  ] = $operator->details->business;
	                $item['type'             	   ] = $operator->details->type;
	                $item['location'               ] = $operator->details->location;
	                // $item['contact'       		  ] = $operator->details->contact;
	                // $item['phone'                  ] = $operator->details->phone;
	                // $item['email'                  ] = $operator->details->email;
	                // $item['url'                    ] = $operator->details->url;


					$item['imported'] = in_array(  $item['operator_id' ], $imported_ids );
					$data['items'][] = $item;			
				}
			}
		}
		//print_r($data);
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
		//	'total_pages'	=> $data['total_pages']
		) );  
		
		?>
		<p class="narnoo-operators-add-operators-box">
			<label for="add_operators_list"><?php _e( 'Enter the business name of the operator you want to search: ', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label>
			<input id="search_name" name="search_name" type="text" value="<?php echo isset( $_REQUEST['add_operators_list'] ) ? esc_attr( $_REQUEST['add_operators_list'] ) : ''; ?>" />
			<input id="search-submit" class="button" type="submit" name="search-submit" value="<?php _e( 'Search', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
		</p>
		
		<script type="text/javascript">
			function updateQueryStringParameter(uri, key, value) {
				var re = new RegExp("([?|&])" + key + "=.*?(&|$)", "i");
				separator = uri.indexOf('?') !== -1 ? "&" : "?";
				value = encodeURIComponent(value);
				if (uri.match(re)) {
					return uri.replace(re, '$1' + key + "=" + value + '$2');
				}
				else {
					return uri + separator + key + "=" + value;
				}
			}
			
			jQuery('document').ready(function($) {
				$('#search-submit').click(function(e, ui) {
					// rebuild form action query string to ensure search params are in sync
					$form = $('#narnoo-search-add-operators-form');
					if ($form.length > 0) {
						new_query = $form.attr('action');
						new_query = updateQueryStringParameter( new_query, 'search_name'    	, $('#search_name'    ).val() );
						new_query = updateQueryStringParameter( new_query, 'paged', '1' );
						$form.attr('action', new_query);
					}
				});
			});
		</script>
		<?php
		
		return true;
	}
	
	/**
	 * Add screen options for Search/Add operators page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_operators_table;
		$narnoo_distributor_operators_table = new Narnoo_Distributor_Search_Add_Operators_Table();
	}
	
	/**
	 * Enqueue scripts and print out CSS stylesheets for this page.
	 **/
	static function load_scripts( $hook ) {
		global $narnoo_distributor_operators_page;
		
		if ( $narnoo_distributor_operators_page !== $hook || ! isset( $_REQUEST['func_type'] ) || $_REQUEST['func_type'] !== 'search' ) {	// ensure scripts are only loaded on this Page
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'search_add_operators_table.js', plugins_url( 'js/search_add_operators_table.js', __FILE__ ), array( 'jquery' ) );
		
		?>
		<style type="text/css">
		.wp-list-table .column-operator_businessname { width: 20%; }
		.wp-list-table .column-operator_id { width: 10%; }
		.wp-list-table .column-category { width: 10%; }
		.wp-list-table .column-state { width: 5%; }
		.narnoo-search-operators-box label { display: inline-block; padding-left: 50px; width: 100px; }
		</style>
		<?php
	}	
}    