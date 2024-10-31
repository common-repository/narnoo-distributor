<?php
/**
 * Narnoo Operator - Albums table.
 **/
class Narnoo_Distributor_Operator_Albums_Table extends Narnoo_Distributor_Operator_Media_Table {		
	public $current_album_id = '0';
	public $current_album_name = '';
	public $current_album_page = 1;
	
	public $select_album_html_script = '';
	
	public $media_view_type = 'Albums';

	function __construct( $args = array() ) {
		parent::__construct( $args );
		
		if ( empty( $this->operator_name ) ) {
			return;
		}
		
		if ( ! isset( $_REQUEST['switch_operator'] ) ) {
			if ( isset( $_POST['narnoo_album_name'] ) ) {
				if ( isset( $_POST['narnoo_album_name'] ) ) {
					$this->current_album_name = stripslashes( $_POST['narnoo_album_name'] );
				}
				if ( isset( $_POST['narnoo_album_id'] ) ) {
					$this->current_album_id = $_POST['narnoo_album_id'];
				}
				if ( isset( $_POST['narnoo_album_page'] ) ) {
					$this->current_album_page = intval( $_POST['narnoo_album_page'] ); 
				}
			} else {
				if ( isset( $_REQUEST['album_name'] ) ) {
					$this->current_album_name = stripslashes( $_REQUEST['album_name'] );
				}
				if ( isset( $_REQUEST['album'] ) ) {
					$this->current_album_id = $_REQUEST['album'];
				}
				if ( isset( $_REQUEST['album_page'] ) ) {
					$this->current_album_page = intval( $_REQUEST['album_page'] );
				}
			}
		}

		// get the current (or first, if unspecified) page of albums
		$list = null;
		$this->current_album_page = max( 1, $this->current_album_page );
		$current_page = $this->current_album_page;
        // $operator_version = 'operator';
        $operator_version = 'operator2';
		$request = Narnoo_Distributor_Helper::init_api( $operator_version );
		if ( ! is_null( $request ) ) {
			try {

                $list = $request->getAlbums( $this->operator_id, $current_page );


				if ( ! is_array( $list->operator_albums ) ) {
					throw new Exception( sprintf( __( "Error retrieving albums. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 				
		}
		
		if ( ! is_null( $list ) ) {
			$total_pages = max( 1, intval( $list->total_pages ) );
		
			// use current specified album name if it exists in current page;
			// otherwise set it to first album name in current page
			$first_album = null;
			$is_current_album_name_valid = false;
			foreach ( $list->operator_albums as $album ) {
				$album_name = stripslashes( $album->album_name );
				if ( is_null( $first_album ) ) {
					$first_album = $album;
				}
				if ( empty( $this->current_album_name ) ) {
					$this->current_album_name = $album_name;
					$this->current_album_id = $album->album_id;
				}
				if ( $this->current_album_name === $album_name ) {
					$is_current_album_name_valid = true;
				}
			}
			
			if ( ! $is_current_album_name_valid ) {
				Narnoo_Distributor_Helper::show_error( sprintf( __( "<strong>ERROR:</strong> Unknown album name '%s'.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $this->current_album_name ) );
				if ( ! is_null( $first_album ) ) {
					$this->current_album_name = stripslashes( $first_album->album_name );
					$this->current_album_id = $first_album->album_id;
				}
			}

			$this->select_album_html_script = Narnoo_Distributor_Helper::get_album_select_html_script( $list->operator_albums, $total_pages, $this->current_album_page, $this->current_album_name, 'operator' );
		}		
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'caption':
			case 'entry_date':
			case 'image_id':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_thumbnail_image( $item ) {    
		$actions = array(
			'download'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'media_type' => $this->media_view_type,
											'paged' => $this->get_pagenum(),
											'action' => 'download', 
											'operator_id' => $this->operator_id,
											'operator_name' => $this->operator_name,
											'images[]' => $item['image_id'], 
											'album_page' => $this->current_album_page, 
											'album' => $this->current_album_id, 
											'album_name' => $this->current_album_name,
										)
									),
									__( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								),
		);
		return sprintf( 
			'<input type="hidden" name="url%1$s" value="%2$s" /> %3$s <br /> %4$s', 
			$item['image_id'],
			$item['thumbnail_image'],
			"<img src='" . $item['thumbnail_image'] . "' />", 
			$this->row_actions($actions) 
		);
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="images[]" value="%s" />', $item['image_id']
		);    
	}

	function get_columns() {
		return array(
			'cb'				=> '<input type="checkbox" />',
			'thumbnail_image'	=> __( 'Thumbnail', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'caption'			=> __( 'Caption', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'entry_date'		=> __( 'Entry Date', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'image_id'			=> __( 'Image ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}
	
	function get_bulk_actions() {
		$actions = array(
			'download'		=> __( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
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
		
		if ( isset( $_REQUEST['back'] ) || isset( $_REQUEST['album_select_button'] ) || isset( $_REQUEST['switch_operator'] ) ) {
			return true;
		}
		
		$action = $this->current_action();
		if ( false !== $action ) {
			if ( empty( $this->operator_id ) ) {
				return true;
			}
			$operator_id = $this->operator_id;
			
			$image_ids = isset( $_REQUEST['images'] ) ? $_REQUEST['images'] : array();
			$num_ids = count( $image_ids );
			if ( empty( $image_ids ) || ! is_array( $image_ids ) || $num_ids === 0 ) {
				return true;				
			}
			
			switch ( $action ) {
			
				// perform download
				case 'download':					
					?>
					<h3><?php _e( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Requesting download links for the following %s image(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $image_ids as $id ) {
						Narnoo_Distributor_Helper::print_operator_ajax_script_body( $id, 'downloadImage', array( $operator_id, $id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to albums', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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
		
		if ( empty( $this->operator_name ) ) {
			return $data;
		}

		// no album name specified; just return empty data
		$current_album_name = $this->current_album_name;
		if ( empty( $current_album_name ) ) {
			return $data;
		}

		$list = null;
		$current_page = $this->get_pagenum();
        $operator_version = 'operator2';
		$request = Narnoo_Distributor_Helper::init_api( $operator_version );
		if ( ! is_null( $request ) ) {
			try {
                if($operator_version == 'operator2') {
                    $list = $request->getAlbumImages( $this->operator_id, $this->current_album_id, $current_page );
                } else {
                    $list = $request->getAlbumImages( $this->operator_id, $current_album_name, $current_page );
                }

				if ( ! is_array( $list->operator_albums_images ) ) {
					throw new Exception( sprintf( __( "Error retrieving album images. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 				
		}
		
		if ( ! is_null( $list ) ) {
			$data['total_pages'] = max( 1, intval( $list->total_pages ) );
			foreach ( $list->operator_albums_images as $image ) {
				$item['thumbnail_image'] = $image->thumb_image_path;
				$item['caption'] = $image->image_caption;
				$item['entry_date'] = $image->entry_date;
				$item['image_id'] = $image->image_id;
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
	 * Enqueue scripts and print out CSS stylesheets for this page.
	 **/
	static function load_scripts( $hook ) {
		global $narnoo_distributor_operator_media_page;
		
		if ( $narnoo_distributor_operator_media_page !== $hook || $_REQUEST['media_type'] !== 'Albums' ) {	// ensure scripts are only loaded on this Page
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'operator_albums_table.js', plugins_url( 'js/operator_albums_table.js', __FILE__ ), array( 'jquery' ) );		
	}	
}    