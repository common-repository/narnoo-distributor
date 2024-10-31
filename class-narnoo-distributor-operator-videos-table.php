<?php
/**
 * Narnoo Operator - Videos table.
 **/
class Narnoo_Distributor_Operator_Videos_Table extends Narnoo_Distributor_Operator_Media_Table {
	public $media_view_type = 'Videos';
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'caption':
			case 'entry_date':
			case 'video_id':
			case 'embed_id':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_thumbnail_image( $item ) {    
		$actions = array(
			/* 'add_to_channel'  => sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'media_type' => $this->media_view_type,
											'paged' => $this->get_pagenum(),
											'action' => 'add_to_channel', 
											'operator_id' => $this->operator_id,
											'operator_name' => $this->operator_name,
											'videos[]' => $item['video_id'], 
											'url' . $item['video_id'] => $item['thumbnail_image']
										)
									),
									__( 'Add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								), */
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
											'videos[]' => $item['video_id'], 
										)
									),
									__( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								),
		);
		return sprintf( 
			'<input type="hidden" name="url%1$s" value="%2$s" /> %3$s <br /> %4$s', 
			$item['video_id'],
			$item['thumbnail_image'],
			"<img src='" . $item['thumbnail_image'] . "' />", 
			$this->row_actions($actions) 
		);
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="videos[]" value="%s" />', $item['video_id']
		);    
	}

	function get_columns() {
		return array(
			'cb'				=> '<input type="checkbox" />',
			'thumbnail_image'	=> __( 'Thumbnail', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'caption'			=> __( 'Caption', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'entry_date'		=> __( 'Entry Date', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'video_id'			=> __( 'Video ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'embed_id'			=> __( 'Embed ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}

	function get_bulk_actions() {
		$actions = array(
			// 'add_to_channel'=> __( 'Add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
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
		
		if ( isset( $_REQUEST['extra_button'] ) ) {
			// redirect to channel page if user clicked "View channel" after adding videos
			if ( isset( $_REQUEST['view_channel'] ) && $_REQUEST['view_channel'] === 'view_channel' ) {
				?>
				<p><img src="<?php echo admin_url(); ?>images/wpspin_light.gif" /> <?php printf( __( "Redirecting to channel '%s'...", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), htmlspecialchars( stripslashes( $_REQUEST['narnoo_channel_name'] ) ) ); ?></p>
				<script type="text/javascript">
				window.location = "admin.php?page=narnoo-distributor-channels&channel=<?php echo isset( $_REQUEST['narnoo_channel_id'] ) ? $_REQUEST['narnoo_channel_id'] : ''; ?>&channel_name=<?php echo isset( $_REQUEST['narnoo_channel_name'] ) ? urlencode( stripslashes( $_REQUEST['narnoo_channel_name'] ) ) : ''; ?>&channel_page=<?php echo isset( $_REQUEST['narnoo_channel_page'] ) ? $_REQUEST['narnoo_channel_page'] : ''; ?>";
				</script>
				<?php
				exit();
			}				
		}
				
		$action = $this->current_action();
		if ( false !== $action ) {
			if ( empty( $this->operator_id ) ) {
				return true;
			}
			$operator_id = $this->operator_id;
			
			$video_ids = isset( $_REQUEST['videos'] ) ? $_REQUEST['videos'] : array();
			$num_ids = count( $video_ids );
			if ( empty( $video_ids ) || ! is_array( $video_ids ) || $num_ids === 0 ) {
				return true;				
			}
			
			switch ( $action ) {
			
				// confirm add to channel
				case 'add_to_channel':
					// retrieve list of channels		
					$list_m = null;
					$request = Narnoo_Distributor_Helper::init_api( 'media' );
					if ( ! is_null( $request ) ) {
						try {
							$list_m = $request->getChannelList();
							if ( ! is_array( $list_m->distributor_channel_list ) ) {
								throw new Exception( sprintf( __( "Error retrieving channels. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
							}
						} catch ( Exception $ex ) {
							Narnoo_Distributor_Helper::show_api_error( $ex );
						} 				
					}
							
					// no channels retrieved
					if ( is_null( $list_m ) ) {
						return true;
					}
					if ( count( $list_m->distributor_channel_list ) === 0 ) {
						Narnoo_Distributor_Helper::show_error( sprintf( __( '<strong>ERROR:</strong> No channels found. Please <strong><a href="%s">create a channel</a></strong> first!', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), "?" . build_query( array( 'page' => 'narnoo-distributor-channels', 'action' => 'create' ) ) ) );
						return true;
					}

					$total_pages = max( 1, intval( $list_m->total_pages ) );
					
					?>
					<h3><?php _e( 'Confirm add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<?php
					
					foreach ( $list_m->distributor_channel_list as $channel ) { ?>
						<input type="hidden" name="channel<?php echo $channel->channel_id; ?>" value="<?php echo esc_attr( $channel->channel_name ); ?>" /><?php
					}
					?>
					<p>
						<?php printf( __( 'Please select channel to add the following %d video(s) to:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?>
						<?php echo Narnoo_Distributor_Helper::get_channel_select_html_script( $list_m->distributor_channel_list, $total_pages, 1, '' ); ?>
					</p>
					<ol>
					<?php 
					foreach ( $video_ids as $id ) { 
						?>
						<input type="hidden" name="videos[]" value="<?php echo $id; ?>" />
						<li><span><?php echo __( 'Video ID:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) . ' ' . $id; ?></span><span><img style="vertical-align: middle; padding-left: 20px;" src="<?php echo ( isset( $_REQUEST[ 'url' . $id ] ) ? $_REQUEST[ 'url' . $id ] : '' ); ?>" /></span></li>
						<?php 
					} 
					?>
					</ol>
					<input type="hidden" name="action" value="do_add_to_channel" />
					<p class="submit">
						<input type="submit" name="submit" id="channel_select_button" class="button-secondary" value="<?php _e( 'Confirm Add to Channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
						<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
					</p>
					<?php
					
					return false;
					
				// perform actual add to channel
				case 'do_add_to_channel':
					if ( ! isset( $_POST['narnoo_channel_id'] ) ) {
						return true;
					}
					$channel_id = $_POST['narnoo_channel_id'];
					$channel_name =  isset( $_POST['narnoo_channel_name'] ) ? stripslashes( $_POST['narnoo_channel_name'] ) : '';
					$channel_page = isset( $_POST['narnoo_channel_page'] ) ? $_POST['narnoo_channel_page'] : '';
					
					?>
					<h3><?php _e( 'Add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Adding the following %s video(s) to channel '%s' (ID %d):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids, $channel_name, $channel_id ); ?></p>
					<input type="hidden" name="view_channel" value="view_channel" />
					<input type="hidden" name="narnoo_channel_id" value="<?php echo $channel_id; ?>" />
					<input type="hidden" name="narnoo_channel_name" value="<?php echo esc_attr( $channel_name ); ?>" />
					<input type="hidden" name="narnoo_channel_page" value="<?php echo $channel_page; ?>" />
					<ol>
					<?php
					foreach( $video_ids as $id ) {
						Narnoo_Distributor_Helper::print_media_ajax_script_body( $id, 'addToChannel', array( $id, $channel_id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to videos', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), __( 'View channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

					return false;

				// perform download
				case 'download':					
					?>
					<h3><?php _e( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Requesting download links for the following %s video(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $video_ids as $id ) {
						Narnoo_Distributor_Helper::print_operator_ajax_script_body( $id, 'downloadVideo', array( $this->operator_id, $id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to videos', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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
		
		$list_m = null;
		$current_page = $this->get_pagenum();
		$request 			= Narnoo_Distributor_Helper::init_api( 'operator' );
		//$cache	 		= Narnoo_Distributor_Helper::init_noo_cache();
		if ( ! is_null( $request ) ) {
			try {
				
				//$list_m = $cache->get('op_video_'.$this->operator_id.'_'.$current_page);
				
				//if(empty($list_m)){
					$list_m = $request->getVideos( $this->operator_id, $current_page );
					//if( !empty( $list_m->success ) ){
					//$cache->set('op_video_'.$this->operator_id.'_'.$current_page, $list_m, 43200);
					//}
				//}

				if ( ! is_array( $list_m->data->videos ) ) {
					throw new Exception( sprintf( __( "Error retrieving videos. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 
		}

		if ( ! is_null( $list_m->data->videos ) ) {
			$data['total_pages'] = max( 1, intval( $list_m->data->totalPages ) );
			foreach ( $list_m->data->videos as $video ) {
				$item['thumbnail_image'] 	= $video->thumbImage;
				$item['caption'] 			= $video->caption;
				$item['entry_date'] 		= $video->uploadedAt;
				$item['video_id'] 			= $video->id;
				$item['embed_id'] 			= $video->videoPreview;
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
	 * Add screen options for videos page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_operator_videos_table;
		$narnoo_distributor_operator_videos_table = new Narnoo_Distributor_Operator_Videos_Table();
	}
}    