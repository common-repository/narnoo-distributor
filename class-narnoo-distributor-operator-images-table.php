<?php
/**
 * Narnoo Operator - Images table.
 **/
class Narnoo_Distributor_Operator_Images_Table extends Narnoo_Distributor_Operator_Media_Table {
	public $media_view_type = 'Images';

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
											'page' 			=> isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'media_type' 	=> $this->media_view_type,
											'paged' 		=> $this->get_pagenum(),
											'action' 		=> 'download', 
											'operator_id' 	=> $this->operator_id,
											'operator_name' => $this->operator_name,
											'images[]' 		=> $item['image_id'], 
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
		
		if ( isset( $_REQUEST['back'] ) || isset( $_REQUEST['switch_operator'] ) ) {
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
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to images', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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

		$list = null;
		$current_page = $this->get_pagenum();
		$request = Narnoo_Distributor_Helper::init_api( 'operator' );
		//$cache	 		= Narnoo_Distributor_Helper::init_noo_cache();

		if ( ! is_null( $request ) ) {
			try {

				//$list = $cache->get('op_img_'.$this->operator_id.'_'.$current_page);
					
					//if(empty($list)){
				
						$list = $request->getImages( $this->operator_id, $current_page );
						//if( !empty( $operator->success ) ){
						//$cache->set('op_img_'.$this->operator_id.'_'.$current_page, $list, 43200);
						//}
					//}
				


				if ( ! is_array( $list->data->images ) ) {
					throw new Exception( sprintf( __( "Error retrieving images. The operator has no images #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 
		}

		if ( ! is_null( $list->data->images ) ) {
			$data['total_pages'] = max( 1, intval( $list->data->totalPages ) );

			/**
			*
			*	@date_modified: 08062017
			*	@change_log: Added a check to see if images are empty
			*
			*/
			if(!empty($list->data->images)){

				foreach ( $list->data->images as $image ) {
								$item['thumbnail_image'] 	= $image->cropImage;
								$item['caption'] 			= $image->caption;
								$item['entry_date'] 		= $image->uploadedAt;
								$item['image_id'] 			= $image->id;
								$data['items'][] 			= $item;
							}
			}else{

				$data 			= null;
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
	 * Add screen options for images page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_operator_images_table;
		$narnoo_distributor_operator_images_table = new Narnoo_Distributor_Operator_Images_Table();
	}
}    