<?php
/*
Plugin Name: Narnoo Distributor
Plugin URI: http://narnoo.com/
Description: Allows Tourism organisations that use Wordpress to manage and include their Narnoo account into their Wordpress site. You will need a Narnoo API key pair to include your Narnoo media. You can find this by logging into your account at Narnoo.com and going to Account -> View APPS.
Version: 2.5.1
Author: Narnoo Wordpress developer
Author URI: http://www.narnoo.com/
License: GPL2 or later
*/

/*  Copyright 2019  Narnoo.com  (email : info@narnoo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// plugin definitions
define( 'NARNOO_DISTRIBUTOR_PLUGIN_NAME', 'Narnoo Distributor' );
define( 'NARNOO_DISTRIBUTOR_CURRENT_VERSION', '2.5.2' );
define( 'NARNOO_DISTRIBUTOR_I18N_DOMAIN', 'narnoo-distributor' );

define( 'NARNOO_DISTRIBUTOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NARNOO_DISTRIBUTOR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'NARNOO_DISTRIBUTOR_SETTINGS_PAGE', 'options-general.php?page=narnoo-distributor-api-settings' );


// include files
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-helper.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-categories-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operators-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-add-operators-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operators-product-import-table.php' );

require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-media-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-albums-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-images-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-brochures-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-videos-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-library-images-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-library-images-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-attraction-template.php' );
// Page formating //
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/inti-cmb2.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/cmb2-tabs/inti.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'narnoo-listing-layout-helper.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'narnoo-product-metabox-layout.php' );
// NARNOO PHP SDK 2.0 //
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/http/WebClient.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/authen.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/operatorconnect.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/distributor.php' );
// NARNOO PHP SDK API //
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnooauthn.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnooapi.php' );



// begin!
new Narnoo_Distributor();

class Narnoo_Distributor {

	/**
	 * Plugin's main entry point.
	 **/
	function __construct() {
		register_uninstall_hook( __FILE__, array( 'NarnooDistributor', 'uninstall' ) );

		add_action( 'init', array( &$this, 'create_custom_post_types' ) );

		if ( is_admin() ) {
			add_action( 'plugins_loaded', array( &$this, 'load_language_file' ) );
			add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );

			add_action( 'admin_notices', array( &$this, 'display_reminders' ) );
			add_action( 'admin_menu', array( &$this, 'create_menus' ), 9 );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Categories_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Operators_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Search_Add_Operators_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_scripts' ) );

			add_filter( 'media_upload_tabs', array( &$this, 'add_narnoo_library_menu_tab' ) );
			add_action( 'media_upload_narnoo_library', array( &$this, 'media_narnoo_library_menu_handle') );
			add_action( 'media_upload_narnoo_distributor_library', array( &$this, 'media_narnoo_distributor_library_menu_handle') );

			add_action( 'wp_ajax_narnoo_distributor_api_request', array( 'Narnoo_Distributor_Helper', 'ajax_api_request' ) );
			add_action( 'wp_ajax_narnoo_add_image_to_wordpress_media_library', array( 'Narnoo_Distributor_Helper', 'ajax_add_image_to_wordpress_media_library' ) );

			//Meta Boxes
			add_action('add_meta_boxes', 	array( &$this, 'add_noo_album_meta_box'));
			add_action( 'save_post', 		array( &$this, 'save_noo_album_meta_box'));
			add_action('add_meta_boxes', 	array( &$this, 'add_noo_print_meta_box'));
			add_action( 'save_post', 		array( &$this, 'save_noo_print_meta_box'));
			add_action( 'save_post', 		array( &$this, 'save_noo_operator_listing'));

			//Meta Boxes - Operators
			add_action('add_meta_boxes', 	array( &$this, 'add_noo_op_album_meta_box'));
			add_action( 'save_post', 		array( &$this, 'save_noo_op_album_meta_box'));


			//new Narnoo_Distributor_Imported_Media_Meta_Box();
		} else {

			//add_action( 'wp_enqueue_scripts', array( &$this, 'load_scripts' ) );
			add_filter( 'widget_text', 'do_shortcode' );
		}

		add_action( 'wp_ajax_narnoo_distributor_lib_request', 			array( &$this, 'narnoo_distributor_ajax_lib_request' ) );
		add_action( 'wp_ajax_nopriv_narnoo_distributor_lib_request', 	array( &$this, 'narnoo_distributor_ajax_lib_request' ) );
		add_filter( 'pre_get_posts', 									array( &$this, 'add_cpts_to_search') );
	}

	/**
	 * Register custom post types for Narnoo Operator Posts.
	 **/
	function create_custom_post_types() {
		// create custom post types
		$post_types = get_option( 'narnoo_custom_post_types', array() );

		foreach( $post_types as $category => $fields ) {
			register_post_type(
				'narnoo_' . $category,
				array(
					'label' => ucfirst( $category ),
					'labels' => array(
						'singular_name' => ucfirst( $category ),
					),
					'hierarchical' => true,
					'rewrite' => array( 'slug' => $fields['slug'] ),
					'description' => $fields['description'],
					'public' => true,
					'exclude_from_search' => true,
					'has_archive' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'show_in_menu' => 'narnoo-distributor-categories',
					'show_in_nav_menus'	=> TRUE,
					'show_in_admin_bar' => true,
					'supports' => array( 'title', 'excerpt', 'thumbnail', 'editor', 'author', 'revisions', 'page-attributes' ),
				)
			);
		}

		$options = get_option('narnoo_distributor_settings');
		// if ( !empty( $options['operator_import'] )  ) {
			$this->create_product_post_type();
		// }


		flush_rewrite_rules();
	}


	function create_product_post_type(){
		register_post_type(
                'narnoo_product',
                array(
                    'label' => 'Products',
                    'labels' => array(
                        'singular_name'      => _x( 'Product', 'admin menu', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        'menu_name'          => _x( 'Products', 'admin menu', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        //'name_admin_bar'     => _x( 'Product', 'add new on admin bar', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        //'add_new'            => _x( 'Add New', 'Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        //'add_new_item'       => __( 'Add New Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        //'new_item'           => __( 'New Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        'edit_item'          => __( 'Edit Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        'view_item'          => __( 'View Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        'all_items'          => __( 'All Products', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        'search_items'       => __( 'Search Products', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                        
                    ),
                    'hierarchical'          => true,
                    'rewrite'               => array( 'slug' => 'product' ),
                    'description'           => "Custom post type for imported operator products from Narnoo",
                    'public'                => true,
                    'exclude_from_search'   => false,
                    'has_archive'           => true,
                    'publicly_queryable'    => true,
                    'show_ui'               => true,
                    'show_in_menu'          => TRUE,
                    'show_in_nav_menus'		=> TRUE,
                    'menu_position'         => 13,
                    'menu_icon'				=> 'dashicons-tickets-alt',
                    'show_in_admin_bar'     => true,
                    'supports'              => array( 'title', 'excerpt', 'thumbnail', 'editor', 'author', 'revisions', 'page-attributes' ),
                )
            ); 

		flush_rewrite_rules();
	}


	/**
	 * Add All Custom Post Types to search
	 *
	 * Returns the main $query.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      $query
	*/

	function add_cpts_to_search($query) {

		// Check to verify it's search page
		if( is_search() ) {
			// Get post types
			$post_types = get_post_types(array('public' => true, 'exclude_from_search' => false), 'objects');
			$searchable_types = array('destination','narnoo_accommodation','narnoo_attraction','narnoo_service','narnoo_dining');
			// Add available post types
			if( $post_types ) {
				foreach( $post_types as $type) {
					$searchable_types[] = $type->name;
				}
			}
			$query->set( 'post_type', $searchable_types );
		}
		return $query;
	}


	/**
	 * Add Narnoo Library tabs to Wordpress media upload menu.
	 **/
	function add_narnoo_library_menu_tab( $tabs ) {
		$newTabs = array(
			'narnoo_library' => __( 'Narnoo Library', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'narnoo_distributor_library' => __( 'Narnoo Operator Library', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
		return array_merge( $tabs, $newTabs );
	}

	/**
	 * Handle display of Narnoo library in Wordpress media upload menu.
	 **/
	function media_narnoo_library_menu_handle() {
		return wp_iframe( array( &$this, 'media_narnoo_library_menu_display' ) );
	}

	function media_narnoo_library_menu_display() {
		media_upload_header();
		$narnoo_distributor_library_images_table = new Narnoo_Distributor_Library_Images_Table();
		?>
			<form id="narnoo-images-form" class="media-upload-form" method="post" action="">
				<?php
				$narnoo_distributor_library_images_table->prepare_items();
				$narnoo_distributor_library_images_table->display();
				?>
			</form>
		<?php
	}

	/**
	 * Handle display of Narnoo Operator library in Wordpress media upload menu.
	 **/
	function media_narnoo_distributor_library_menu_handle() {
		return wp_iframe( array( &$this, 'media_narnoo_distributor_library_menu_display' ) );
	}

	function media_narnoo_distributor_library_menu_display() {
		media_upload_header();
		$narnoo_distributor_operator_library_images_table = new Narnoo_Distributor_Operator_Library_Images_Table();
		?>
			<form id="narnoo-operator-images-form" class="media-upload-form" method="post" action="">
				<?php
				$narnoo_distributor_operator_library_images_table->prepare_items();
				$narnoo_distributor_operator_library_images_table->views();
				$narnoo_distributor_operator_library_images_table->display();
				?>
			</form>
		<?php
	}

	/**
	 * Clean up upon plugin uninstall.
	 **/
	static function uninstall() {
		unregister_setting( 'narnoo_distributor_settings', 'narnoo_distributor_settings', array( &$this, 'settings_sanitize' ) );
	}

	/**
	 * Add settings link for this plugin to Wordpress 'Installed plugins' page.
	 **/
	function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname(__FILE__) . '/narnoo-distributor.php' ) ) {
			$links[] = '<a href="' . NARNOO_DISTRIBUTOR_SETTINGS_PAGE . '">' . __('Settings') . '</a>';
		}

		return $links;
	}

	/**
	 * Load language file upon plugin init (for future extension, if any)
	 **/
	function load_language_file() {
		load_plugin_textdomain( NARNOO_DISTRIBUTOR_I18N_DOMAIN, false, NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'languages/' );
	}

	/**
	 * Display reminder to key in API keys in admin backend.
	 **/
	function display_reminders() {
		$options = get_option( 'narnoo_distributor_settings' );

		if ( empty( $options['access_key'] ) || empty( $options['secret_key'] ) ) {
			Narnoo_Distributor_Helper::show_notification(
				sprintf(
					__( '<strong>Reminder:</strong> Please key in your Narnoo API settings in the <strong><a href="%s">Settings->Narnoo API</a></strong> page.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
					NARNOO_DISTRIBUTOR_SETTINGS_PAGE
				)
			);
		}
	}

	/**
	 * Add admin menus and submenus to backend.
	 **/
	function create_menus() {
		// add Narnoo API to settings menu
		add_options_page(
			__( 'Narnoo API Settings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Narnoo API', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-api-settings',
			array( &$this, 'api_settings_page' )
		);

		// add main Narnoo Imports menu
		add_menu_page(
			__( 'Listings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Listings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-categories',
			array( &$this, 'categories_page' ),
			'dashicons-location',
			12
		);

		// add submenus to Narnoo Imports menu
		$page = add_submenu_page(
			'narnoo-distributor-categories',
			__( 'Listings - Categories', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Categories', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-categories',
			array( &$this, 'categories_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Distributor_Categories_Table', 'add_screen_options' ) );
		global $narnoo_distributor_categories_page;
		$narnoo_distributor_categories_page = $page;

		// add main Narnoo menu
		add_menu_page(
			__( 'Narnoo', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Narnoo', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-operators',
			array( &$this, 'operators_page' ),
			NARNOO_DISTRIBUTOR_PLUGIN_URL . 'images/icon-16.png',
			11
		);

		// add submenus to Narnoo menu
		$page = add_submenu_page(
			'narnoo-distributor-operators',
			__( 'Narnoo - Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-operators',
			array( &$this, 'operators_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Distributor_Operators_Table', 'add_screen_options' ) );
		global $narnoo_distributor_operators_page;
		$narnoo_distributor_operators_page = $page;

		$page = add_submenu_page(
			'narnoo-distributor-operators',
			__( 'Narnoo Operator Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Operator Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-operator-media',
			array( &$this, 'operator_media_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Distributor_Operator_Media_Table', 'add_screen_options' ) );
		global $narnoo_distributor_operator_media_page;
		$narnoo_distributor_operator_media_page = $page;

	}

	/**
	 * Upon admin init, register plugin settings and Narnoo shortcodes button, and define input fields for API settings.
	 **/
	function admin_init() {
		register_setting( 'narnoo_distributor_settings', 'narnoo_distributor_settings', array( &$this, 'settings_sanitize' ) );

		if( isset( $_REQUEST['narnoo_section'] ) && $_REQUEST['narnoo_section'] == 'webhook' ) {

			add_settings_section(
				'api_settings_section_webhook',
				__( 'Webhook', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_section' ),
				'narnoo_distributor_api_settings'
			);

			add_settings_field(
				'webhook_is_enable',
				__( 'Enable Webhook', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_is_enable' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_url',
				__( 'Webhook URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_url' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_secret',
				__( 'Webhook Secret', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_secret' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_follow_operator',
				__( 'Follow Operator', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_follow_operator' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_unfollow_operator',
				__( 'Unfollow Operator', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_unfollow_operator' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_create_product',
				__( 'Create Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_create_product' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_update_product',
				__( 'Update Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_update_product' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_delete_product',
				__( 'Delete Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_delete_product' ),
				'narnoo_distributor_api_settings',
				'api_settings_section_webhook'
			);

		} else {
			
			add_settings_section(
				'api_settings_section',
				__( 'API Settings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_api_section' ),
				'narnoo_distributor_api_settings'
			);

			add_settings_field(
				'access_key',
				__( 'Acesss key', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_access_key' ),
				'narnoo_distributor_api_settings',
				'api_settings_section'
			);

			add_settings_field(
				'secret_key',
				__( 'Secret key', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_secret_key' ),
				'narnoo_distributor_api_settings',
				'api_settings_section'
			);

			
			/* add_settings_field(
				'product_import',
				__( 'Import Operator Products', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'settings_operator_import' ),
				'narnoo_distributor_api_settings',
				'api_settings_section'
			); */
		}

		// register Narnoo shortcode button and MCE plugin
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

	}

	function settings_webhook_section() {
		echo '<p>' . __( 'Webhooks can only be registered via domains with SSL certificates.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) . '</p>';
	}

	function settings_webhook_is_enable() {
		$options = get_option('narnoo_distributor_settings');

        $html = '<input type="checkbox" id="checkbox_operator" name="narnoo_distributor_settings[webhook_is_enable]" value="1"' . checked( 1, $options['webhook_is_enable'], false ) . '/>';
	    $html .= '<label for="checkbox_operator">'.__('Enable Webhook', NARNOO_DISTRIBUTOR_I18N_DOMAIN).'</label>';
	    $html .= '<script>';
	    if( !$options['webhook_is_enable'] ) {
	    	$html .= 'jQuery("document").ready(function() {';
	    	$html .= '  jQuery("#webhook_url").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_secret").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_follow_operator").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_unfollow_operator").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_create_product").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_update_product").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_delete_product").parents("tr").hide();';
	    	$html .= '});';
	    }
	    $html .= '	jQuery(document).on("click", "#checkbox_operator", function() {';
	    $html .= '		if( jQuery(this).prop("checked") == true ){';
	    $html .= '			jQuery("#webhook_url").parents("tr").show();';
	    $html .= '			jQuery("#webhook_secret").parents("tr").show();';
		$html .= '  		jQuery("#webhook_follow_operator").parents("tr").show();';
		$html .= '  		jQuery("#webhook_unfollow_operator").parents("tr").show();';
		$html .= '  		jQuery("#webhook_create_product").parents("tr").show();';
		$html .= '  		jQuery("#webhook_update_product").parents("tr").show();';
		$html .= '  		jQuery("#webhook_delete_product").parents("tr").show();';
	    $html .= '		} else {';
	    $html .= '			jQuery("#webhook_url").parents("tr").hide();';
	    $html .= '			jQuery("#webhook_secret").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_follow_operator").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_unfollow_operator").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_create_product").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_update_product").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_delete_product").parents("tr").hide();';
	    $html .= '		}';
	    $html .= '	})';
	    $html .= '</script>';

	    echo $html;
	}

	function settings_webhook_url() {
		$options = get_option( 'narnoo_distributor_settings' );
		$siteurl = get_site_url( get_current_blog_id() );
		$url = !empty(esc_attr($options['webhook_url'])) ? esc_attr($options['webhook_url']) : $siteurl . '/wp?narnoo_hook=' . md5($siteurl) . rand(9,999);
		echo "<input id='webhook_url' name='narnoo_distributor_settings[webhook_url]' size='40' type='text' value='" . $url . "' />";
	}

	function settings_webhook_secret() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='webhook_secret' name='narnoo_distributor_settings[webhook_secret]' size='40' type='text' value='" . esc_attr($options['webhook_secret']). "' />";
	}

	function settings_webhook_follow_operator() {
		$options = get_option( 'narnoo_distributor_settings' );
		$html = '<input type="checkbox" id="webhook_follow_operator" name="narnoo_distributor_settings[webhook_follow_operator]" value="1"' . checked( 1, $options['webhook_follow_operator'], false ) . '/>';
	    $html .= '<label for="webhook_follow_operator">'.__('Enable Follow Operator', NARNOO_DISTRIBUTOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_unfollow_operator() {
		$options = get_option( 'narnoo_distributor_settings' );
		$html = '<input type="checkbox" id="webhook_unfollow_operator" name="narnoo_distributor_settings[webhook_unfollow_operator]" value="1"' . checked( 1, $options['webhook_unfollow_operator'], false ) . '/>';
	    $html .= '<label for="webhook_unfollow_operator">'.__('Enable Unfollow Operator', NARNOO_DISTRIBUTOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_create_product() {
		$options = get_option( 'narnoo_distributor_settings' );
		$html = '<input type="checkbox" id="webhook_create_product" name="narnoo_distributor_settings[webhook_create_product]" value="1"' . checked( 1, $options['webhook_create_product'], false ) . '/>';
	    $html .= '<label for="webhook_create_product">'.__('Enable Create Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_update_product() {
		$options = get_option( 'narnoo_distributor_settings' );
		$html = '<input type="checkbox" id="webhook_update_product" name="narnoo_distributor_settings[webhook_update_product]" value="1"' . checked( 1, $options['webhook_update_product'], false ) . '/>';
	    $html .= '<label for="webhook_update_product">'.__('Enable Update Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_delete_product() {
		$options = get_option( 'narnoo_distributor_settings' );
		$html = '<input type="checkbox" id="webhook_delete_product" name="narnoo_distributor_settings[webhook_delete_product]" value="1"' . checked( 1, $options['webhook_delete_product'], false ) . '/>';
	    $html .= '<label for="webhook_delete_product">'.__('Enable Delete Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_api_section() {
		echo '<p>' . __( 'You can edit your Narnoo API settings below.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) . '</p>';
	}

	function settings_access_key() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='access_key' name='narnoo_distributor_settings[access_key]' size='40' type='text' value='" . esc_attr($options['access_key']). "' />";
	}

	function settings_secret_key() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='secret_key' name='narnoo_distributor_settings[secret_key]' size='40' type='text' value='" . esc_attr($options['secret_key']). "' />";
	}

   /*
	DELETE

    function settings_token_key() {
        $options = get_option('narnoo_distributor_settings');
        echo "<input id='token_key' name='narnoo_distributor_settings[token_key]' size='40' type='text' value='" . esc_attr($options['token_key']). "' />";
    }

    */

    /* function settings_operator_import() {
        $options = get_option('narnoo_distributor_settings');

        $html = '<input type="checkbox" id="checkbox_operator" name="narnoo_distributor_settings[operator_import]" value="1"' . checked( 1, $options['operator_import'], false ) . '/>';
	    $html .= '<label for="checkbox_operator">Check this box to import Operator products into your website</label>';

	    echo $html;
    } */

	/**
	 * Sanitize input settings.
	 **/
	function settings_sanitize( $input ) {
		$option = get_option( 'narnoo_distributor_settings' );

		if( !empty($input['access_key']) || !empty($input['secret_key']) ) {
			
			$new_input['access_key'] 		= trim( $input['access_key'] );
			$new_input['secret_key'] 		= trim( $input['secret_key'] );
	        //$new_input['token_key'] 		= trim( $input['token_key'] );
	        $new_input['operator_import']   = trim( $input['operator_import'] );

	        $new_input['webhook_is_enable'] 		= isset( $option['webhook_is_enable'] ) ? $option['webhook_is_enable'] : '';
	        $new_input['webhook_url']				= isset( $option['webhook_url'] ) ? $option['webhook_url'] : '';
	       	$new_input['webhook_secret']			= isset( $option['webhook_secret'] ) ? $option['webhook_secret'] : '';
	       	$new_input['webhook_follow_operator'] 	= isset( $input['webhook_follow_operator'] ) ? $option['webhook_follow_operator'] : '';
	       	$new_input['webhook_unfollow_operator'] = isset( $input['webhook_unfollow_operator'] ) ? $option['webhook_unfollow_operator'] : '';
	       	$new_input['webhook_create_product'] 	= isset( $input['webhook_create_product'] ) ? $option['webhook_create_product'] : '';
	       	$new_input['webhook_update_product'] 	= isset( $input['webhook_update_product'] ) ? $option['webhook_update_product'] : '';
	       	$new_input['webhook_delete_product'] 	= isset( $input['webhook_delete_product'] ) ? $option['webhook_delete_product'] : '';
	       	$new_input['webhook_response']			= isset( $input['webhook_response'] ) ? $option['webhook_response'] : '';

		} else if( $input['webhook_is_enable'] ) {

			$request = Narnoo_Distributor_Helper::init_api();
			$api_token = get_option( 'narnoo_api_token' );

			$data = array();
			$data['action'] = array();
			if( $input['webhook_follow_operator'] ) 	{ $data['action'][] = "follow.operator"; }
	       	if( $input['webhook_unfollow_operator'] ) 	{ $data['action'][] = "unfollow.operator"; }
	       	if( $input['webhook_create_product'] ) 		{ $data['action'][] = "create.product"; }
	       	if( $input['webhook_update_product'] ) 		{ $data['action'][] = "update.product"; }
	       	if( $input['webhook_delete_product'] ) 		{ $data['action'][] = "delete.product"; }

	       	$body = array();
	       	$webhook = empty($option['webhook_response']) ? '' : json_decode($option['webhook_response'], true);
	       	$webhook_secret = ( isset($webhook['data']['key']) && !empty($webhook['data']['key']) ) ? $webhook['data']['key'] : '';
	       	$webhook_mode = '';
			if( isset($webhook['data']['id']) && !empty($webhook['data']['id']) ) {

	       		// for update webhook
	       		$webhook_mode = 'update';
	       		$data['webhookId'] = $webhook['data']['id'];
			    $response = wp_remote_post( 'https://apis.narnoo.com/api/v1/webhook/update', array(
						'method' => 'POST',
						'headers' => array( "Authorization" => "bearer " . $api_token, "Content-Type" => "application/json" ),
						'body' => json_encode($data)
					    )
					);
			    $body = json_decode( $response['body'], true );

			} else {

		       	// for create webhook.
		       	$webhook_mode = 'add';
		       	$data['url'] = $input['webhook_url'];
			    $response = wp_remote_post( 'https://apis.narnoo.com/api/v1/webhook/create', array(
						'method' => 'POST',
						'headers' => array( "Authorization" => "bearer " . $api_token, "Content-Type" => "application/json" ),
						'body' => json_encode($data)
					    )
					);
			    $body = json_decode( $response['body'], true );
			    $webhook_secret = ( isset($body['data']['key']) && !empty($body['data']['key']) ) ? $body['data']['key'] : '';
		    
			}
		
			if ( is_wp_error( $response ) ) {
				
				$new_input = $option;
			    $error_message = $response->get_error_message();

			} else if( isset($body['success']) && $body['success'] == true ) {
			  
				$new_input['access_key'] 		= isset( $option['access_key'] ) ? $option['access_key'] : '';
				$new_input['secret_key'] 		= isset( $option['secret_key'] ) ? $option['secret_key'] : '';
		        //$new_input['token_key'] 		= isset( $option['token_key'] ) ? $option['token_key'] : '';
		        $new_input['operator_import']   = isset( $option['operator_import'] ) ? $option['operator_import'] : '';

		        $new_input['webhook_is_enable'] 		= trim( $input['webhook_is_enable'] );
		        $new_input['webhook_url']				= trim( $input['webhook_url'] );
		       	$new_input['webhook_secret']			= trim( $webhook_secret );
		       	$new_input['webhook_follow_operator'] 	= trim( $input['webhook_follow_operator'] );
		       	$new_input['webhook_unfollow_operator'] = trim( $input['webhook_unfollow_operator'] );
		       	$new_input['webhook_create_product'] 	= trim( $input['webhook_create_product'] );
		       	$new_input['webhook_update_product'] 	= trim( $input['webhook_update_product'] );
		       	$new_input['webhook_delete_product'] 	= trim( $input['webhook_delete_product'] );
		       	$new_input['webhook_response']			= ( $webhook_mode == 'add' ) ? $response['body'] : $option['webhook_response'];
		    
			} else {

				$new_input = $option;

				if( isset( $body['message'] ) && !empty( $body['message'] ) ) {
    				add_settings_error(
                        'webhook',
                        esc_attr( 'settings_updated' ),
                        $body['message'],
                        'error'
                    );
				}

			}

		} else {

			$webhook = empty($option['webhook_response']) ? '' : json_decode($option['webhook_response'], true);
			if( isset($webhook['data']['id']) && !empty($webhook['data']['id']) ) {
				$api_token = get_option( 'narnoo_api_token' );
				$webhook_url = 'https://apis.narnoo.com/api/v1/webhook/delete';

				$response = wp_remote_post( $webhook_url, array(
					'method' => 'POST',
					'headers' => array( "Authorization" => "bearer " . $api_token, "Content-Type" => "application/json" ),
					'body' => json_encode( array( "webhookId" => $webhook['data']['id'] ) )
				    )
				);
			}

			$option['webhook_is_enable'] 		= trim( $input['webhook_is_enable'] );
	        $option['webhook_url']				= '';
	       	$option['webhook_secret']			= '';
	       	$option['webhook_follow_operator'] 	= '0';
	       	$option['webhook_unfollow_operator'] = '0';
	       	$option['webhook_create_product'] 	= '0';
	       	$option['webhook_update_product'] 	= '0';
	       	$option['webhook_delete_product'] 	= '0';
	       	$option['webhook_response']			= '';

			$new_input = $option;

		}

		return $new_input;
	}

	/**
	 * Display API settings page.
	 **/
	function api_settings_page() {
		$current_tab = (isset($_REQUEST['narnoo_section']) && $_REQUEST['narnoo_section']!='') ? $_REQUEST['narnoo_section'] : 'general';
		$section = array( 'general'=>'General', 'webhook'=>'Webhook' );
		$function_name = '';
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo API Settings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h2>
			<nav class="nav-tab-wrapper">
		        <?php 
		        foreach( $section as $tab_key => $tab_value ) {
			        $tab_class = 'nav-tab ';
			        $tab_class.= ($current_tab == $tab_key) ? 'nav-tab-active' : '';
			        $function_name.= ($current_tab == $tab_key) ? "narnoo_".$current_tab."_func" : '';
			        $tab_url = admin_url( NARNOO_DISTRIBUTOR_SETTINGS_PAGE.'&amp;narnoo_section='.$tab_key );
			        echo '<a href="'.$tab_url.'" class="'.$tab_class.'">'.$tab_value.'</a>';
			    }
			    ?>
			</nav>

			<form action="options.php" method="post">
				<?php settings_fields( 'narnoo_distributor_settings' ); ?>
				<?php do_settings_sections( 'narnoo_distributor_api_settings' ); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>

			<?php 
			if(  method_exists ( $this, $function_name ) ) {
				$this->$function_name();	
			}
			?>
		</div>
		<?php
	}

	function narnoo_general_func(){
		
  		$request 		= Narnoo_Distributor_Helper::init_api();
  		//$cache	 		= Narnoo_Distributor_Helper::init_noo_cache();

		$distributor = null;
		if ( ! is_null( $request ) ) {
			try {
					//$distributor = $cache->get('distributor_details');
					if(empty($distributor)){
						$distributor = $request->getAccount();
						//if(!empty($distributor->success)){
							//	$cache->set('distributor_details', $distributor, 43200);
						//}
					}

     		} catch ( Exception $ex ) {
				$distributor = null;
				Narnoo_Distributor_Helper::show_api_error( $ex );
			}
		}

		if ( ! is_null( $distributor ) && isset($distributor->success) && $distributor->success ) {
     	?>
            <h3><?php _e( 'Distributor Details', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h3>

            <table class="form-table">
                <tr><th><?php _e( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->id; ?></td></tr>
                <tr><th><?php _e( 'Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->name; ?></td></tr>
                <tr><th><?php _e( 'Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->email; ?></td></tr>
                <tr><th><?php _e( 'Contact Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->contact; ?></td></tr>
                <tr><th><?php _e( 'Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->suburb; ?></td></tr>
                <tr><th><?php _e( 'State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->state; ?></td></tr>
                <tr><th><?php _e( 'Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->phone; ?></td></tr>
                <tr><th><?php _e( 'URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->url; ?></td></tr>
               </table>
        <?php
        } else {
        ?>
            <h3><?php _e( 'Distributor Details', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h3>
            <table class="form-table">
                <tr><th><?php _e( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->id; ?></td></tr>
                <tr><th><?php _e( 'Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->email; ?></td></tr>
                <tr><th><?php _e( 'Business Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->name; ?></td></tr>
                <tr><th><?php _e( 'Contact Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->contact; ?></td></tr>
                <tr><th><?php _e( 'Country', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->country; ?></td></tr>
                <tr><th><?php _e( 'Post Code', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->postcode; ?></td></tr>
                <tr><th><?php _e( 'Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->suburb; ?></td></tr>
                <tr><th><?php _e( 'State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->state; ?></td></tr>
                <tr><th><?php _e( 'Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->phone; ?></td></tr>
                <tr><th><?php _e( 'URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->data->url; ?></td></tr>
               </table>
        <?php
        }
	}


	/**
	 * Display Narnoo Operators page.
	 **/
	function operators_page() {

		global $narnoo_distributor_operators_table;
		if ( $narnoo_distributor_operators_table->func_type === 'search' ) {
			$this->search_add_operators_page();
			return;
		} else if ( $narnoo_distributor_operators_table->func_type === 'product' ) {
			$this->Operators_Product_Import_Table();
			return;
		}

		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-distributor-operators&func_type=search"><?php _e( 'Search/Add Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></h2>
			<form id="narnoo-operators-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_distributor_operators_table->get_pagenum() ) ) ); ?>">
			<?php
			if ( $narnoo_distributor_operators_table->prepare_items() ) {
				$narnoo_distributor_operators_table->display();
			}
			?>
			</form>
		</div>
		<?php

	}


	/**
	 * Display Operators Products.
	 **/
	function Operators_Product_Import_Table() {
		global $narnoo_distributor_operators_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2>
				<?php _e( 'Narnoo - Operator Products', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-distributor-operators"><?php _e( 'Back to Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a>
			</h2>
			<form id="narnoo-search-add-operators-form" method="post" action="?<?php echo esc_attr( build_query( array(
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'paged' => $narnoo_distributor_operators_table->get_pagenum(),
				'func_type' => $narnoo_distributor_operators_table->func_type,
				'operator' => $narnoo_distributor_operators_table->operator,
				'operator_name' => $narnoo_distributor_operators_table->operator_name
			) ) ); ?>">

				<?php
				if ( $narnoo_distributor_operators_table->prepare_items() ) {
					$narnoo_distributor_operators_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}


	/**
	 * Display Search/Add Narnoo Operators page.
	 **/
	function search_add_operators_page() {
		global $narnoo_distributor_operators_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Search/Add Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-distributor-operators"><?php _e( "View Added Operators", NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></h2>
			<form id="narnoo-search-add-operators-form" method="post" action="?<?php echo esc_attr( build_query( array(
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'paged' => $narnoo_distributor_operators_table->get_pagenum(),
				'func_type' => $narnoo_distributor_operators_table->func_type,
				'search_country'     => $narnoo_distributor_operators_table->search_country    ,
				'search_category'    => $narnoo_distributor_operators_table->search_category   ,
				'search_subcategory' => $narnoo_distributor_operators_table->search_subcategory,
				'search_state'       => $narnoo_distributor_operators_table->search_state      ,
				'search_suburb'      => $narnoo_distributor_operators_table->search_suburb     ,
				'search_postal_code' => $narnoo_distributor_operators_table->search_postal_code,
			) ) ); ?>">
				<?php
				if ( $narnoo_distributor_operators_table->prepare_items() ) {
					$narnoo_distributor_operators_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Operator Media page.
	 **/
	function operator_media_page() {
		global $narnoo_distributor_operator_media_table;

		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Operator Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-media-form" method="post" action="?<?php echo esc_attr( build_query( array(
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'paged' => $narnoo_distributor_operator_media_table->get_pagenum(),
				'media_type' => $narnoo_distributor_operator_media_table->media_view_type,
				'operator_id' => $narnoo_distributor_operator_media_table->operator_id,
				'operator_name' => $narnoo_distributor_operator_media_table->operator_name,
				'album_page' => $narnoo_distributor_operator_media_table->current_album_page,
				'album' => $narnoo_distributor_operator_media_table->current_album_id,
				'album_name' => $narnoo_distributor_operator_media_table->current_album_name
			) ) ); ?>">
			<?php
			if ( $narnoo_distributor_operator_media_table->prepare_items() ) {
				$narnoo_distributor_operator_media_table->views();

				if ( $narnoo_distributor_operator_media_table->media_view_type === 'Albums' ) {
					?><br /><br /><?php
					if ( ! empty( $narnoo_distributor_operator_media_table->current_album_name ) ) {
						?><h4>Currently viewing album: <?php echo $narnoo_distributor_operator_media_table->current_album_name; ?></h4><?php
						_e( 'Select album:', NARNOO_DISTRIBUTOR_I18N_DOMAIN );
						echo $narnoo_distributor_operator_media_table->select_album_html_script;
						submit_button( __( 'Go', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), 'button-secondary action', "album_select_button", false );
					}
				}

				$narnoo_distributor_operator_media_table->display();
			}
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Categories page.
	 **/
	function categories_page() {
		global $narnoo_distributor_categories_table;


		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Categories', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-categories-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_distributor_categories_table->get_pagenum() ) ) ); ?>">
			<?php
			if ( $narnoo_distributor_categories_table->prepare_items() ) {
				$narnoo_distributor_categories_table->display();
			}
			?>
			</form>
		</div>
		<?php
	}
	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_operator_listing_meta_box()
	{

	            add_meta_box(
	                'noo-operator-listing-class',      		// Unique ID
				    'Enter Operator Listing Criteria', 		 		    // Title
				    array( &$this,'box_display_operator_listing'),    // Callback function
				    array('page','destination','post'),         					// Admin page (or post type)
				    'normal',         					// Context
				    'low'         					// Priority
	             );

	}
	/*
	*
	*	title: Display the operator listings box
	*	date created: 15-09-16
	*/
	function box_display_operator_listing( $post )
	{
		global $post;
	    //$values = get_post_custom( $post->ID );
	    $_listsuburb 		= get_post_meta($post->ID,'product_listing_suburb',true);
	    $_listsubCateory 	= get_post_meta($post->ID,'product_listing_subcategory',true);
	    $_listcategory 		= get_post_meta($post->ID,'product_listing_category',true);
	    $_listtitle 		= get_post_meta($post->ID,'product_listing_title',true);
	    $_listpostcode 		= get_post_meta($post->ID,'product_listing_postcode',true);
	   
	    // We'll use this nonce field later on when saving.
	    wp_nonce_field( 'box_display_operator_nonce', 'box_display_operator_listing_nonce' );


	    ?>
	    <p>
	        <label for="listing_search_suburb">Listing Box Title:</label>
	        <input type="text" name="listing_search_title" id="listing_search_title" value="<?php echo $_listtitle; ?>" style="width:100%"/>
	    </p>
	    <p>
	    	<small><em>Enter the title for the listings search box</em></small>
	    </p>
	    <p>
	        <label for="listing_search_suburb">Listing Search - By Cateogry:</label>
	        <input type="text" name="listing_search_category" id="listing_search_category" value="<?php echo $_listcategory; ?>" style="width:100%"/>
	    </p>
        <p>
        	<small><em>Enter the any categories you would like to search by. Separate multiple categories by using a comma ','</em></small>
        </p>
        <p>
	        <label for="listing_search_suburb">Listing Search - By Suburb:</label>
	        <input type="text" name="listing_search_suburb" id="listing_search_suburb" value="<?php echo $_listsuburb; ?>" style="width:100%"/>
	    </p>
	    <p>
	    	<small><em>Enter the suburbs you would like to search by. Separate multiple suburbs by using a comma ','. [note - recommended to use either Suburb or Post Code not both]</em></small>
	    </p>
	    <p>
	        <label for="listing_search_suburb">Listing Search - By Postcode:</label>
	        <input type="text" name="listing_search_postcode" id="listing_search_postcode" value="<?php echo $_listpostcode; ?>" style="width:100%"/>
	    </p>
	    <p>
	    	<small><em>Enter the postcodes you would like to search by. Separate multiple postcodes by using a comma ','. [note - recommended to use either Suburb or Post Code not both]</em></small>
	    </p>
	    <p>
	        <label for="listing_search_suburb">Listing Search - By Sub Cateogry:</label>
	        <input type="text" name="listing_search_subcategory" id="listing_search_subcategory" value="<?php echo $_listsubCateory; ?>" style="width:100%"/>
	    </p>
	    <p>
	    	<small><em>Enter the any sub-categories you would like to search by. Separate multiple sub-categories by using a comma ','</em></small>
	    </p>
	  	<?php

	}

	function save_noo_operator_listing( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_operator_listing_nonce'] ) || !wp_verify_nonce( $_POST['box_display_operator_listing_nonce'], 'box_display_operator_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

    	if( isset( $_POST['listing_search_title'] ) ){
        	update_post_meta( $post_id, 'product_listing_title', esc_attr( $_POST['listing_search_title'] ) );
    	}
    	if( isset( $_POST['listing_search_category'] ) ){
        	update_post_meta( $post_id, 'product_listing_category', esc_attr( $_POST['listing_search_category'] ) );
    	}
    	if( isset( $_POST['listing_search_suburb'] ) ){
        	update_post_meta( $post_id, 'product_listing_suburb', esc_attr( $_POST['listing_search_suburb'] ) );
    	}
    	if( isset( $_POST['listing_search_postcode'] ) ){
        	update_post_meta( $post_id, 'product_listing_postcode', esc_attr( $_POST['listing_search_postcode'] ) );
    	}
    	if( isset( $_POST['listing_search_subcategory'] ) ){
        	update_post_meta( $post_id, 'product_listing_subcategory', esc_attr( $_POST['listing_search_subcategory'] ) );
    	}
    	

	}

	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_album_meta_box()
	{

	            add_meta_box(
	                'noo-album-box-class',      		// Unique ID
				    'Select Narnoo Album', 		 		    // Title
				    array( &$this,'box_display_album_information'),    // Callback function
				    array('page','destination','post'),         					// Admin page (or post type)
				    'side',         					// Context
				    'low'         					// Priority
	             );

	}

	/*
	*
	*	title: Display the album select box
	*	date created: 15-09-16
	*/
	function box_display_album_information( $post )
	{

	global $post;
    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_album_select_id',true);
    //$selected = isset( $values['noo_album_select_id'] ) ? esc_attr( $values['noo_album_select_id'] ) : '';

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'album_meta_box_nonce', 'box_display_album_information_nonce' );

		$current_page 		      = 1;
		//$cache	 				  = Narnoo_Distributor_Helper::init_noo_cache();
		$request 				  = Narnoo_Distributor_Helper::init_api( 'media' );

		//Get Narnoo Ablums.....
		if ( ! is_null( $request ) ) {

			//$list = $cache->get('albums_'.$current_page);

			if( empty($list) ){

					try {

						$list = $request->getAlbums( $current_page );

						if ( ! is_array( $list->distributor_albums ) ) {
							throw new Exception( sprintf( __( "Error retrieving albums. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
						}

						if(!empty( $list->success ) ){
							//$cache->set('albums_'.$current_page, $list, 43200);
						}

					} catch ( Exception $ex ) {
						//Narnoo_Distributor_Helper::show_api_error( $ex ); don't need to show anything
					}

			}

			//Check the total pages and run through each so we can build a bigger list of albums

		}


    ?> <p>
        <label for="my_meta_box_select">Narnoo Album:</label>
        <select name="noo_album_select" id="noo_album_select">
        	<option value="">None</option>
            <?php 
            	if( !empty($list->data->albums) ) { 
            		foreach ($list->data->albums as $album) { 
            			if( !empty($album) ) {
            				?><option value="<?php echo $album->album_id; ?>" <?php selected( $selected, $album->id ); ?>><?php echo ucwords( $album->title ); ?></option><?php
            			}
            		}
            	 } 
           	?>
        </select>
        <p><small><em>Select an album and this will be displayed the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_album_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_album_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_album_information_nonce'], 'album_meta_box_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_album_select'] ) ){
        	update_post_meta( $post_id, 'noo_album_select_id', esc_attr( $_POST['noo_album_select'] ) );
    	}

	}


	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_print_meta_box()
	{

	            add_meta_box(
			          'noo-print-box-class',      		// Unique ID
						    'Enter Narnoo Print ID', 		 		    // Title
						    array( &$this,'box_display_print_information'),    // Callback function
						    array('page','destination','post'),         					// Admin page (or post type)
						    'side',         					// Context
						    'low'         					// Priority
			        );

	}

	/*
	*
	*	title: Display the print select box
	*	date created: 15-09-16
	*/
	function box_display_print_information( $post )
	{

	global $post;
    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_print_id',true);
    //$selected = isset( $values['noo_album_select_id'] ) ? esc_attr( $values['noo_album_select_id'] ) : '';

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'print_meta_box_nonce', 'box_display_print_information_nonce' );



    ?> <p>
        <label for="print_box_text">Narnoo Print Item:</label>
        <input type="text" name="noo_print_box_text" id="noo_print_box_text" value="<?php echo $selected; ?>" />
    </p>
        <p><small><em>Enter a print ID to display a PDF on the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_print_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_print_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_print_information_nonce'], 'print_meta_box_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_print_box_text'] ) ){
        	update_post_meta( $post_id, 'noo_print_id', wp_kses( $_POST['noo_print_box_text'],NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
    	}

	}


	/*************************************************************************
					OPERATOR PAGES META BOXES
	/*************************************************************************/
	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_op_album_meta_box( )
	{



        add_meta_box(
            'noo-album-box-class',      		// Unique ID
		    'Select Narnoo Album', 		 		    // Title
		    array( &$this,'box_display_op_album_information'),    // Callback function
		    array('narnoo_attraction','narnoo_accommodation','narnoo_service','narnoo_dining'),         					// Admin page (or post type)
		    'side',         					// Context
		    'low'         					// Priority
         );

	}

	/*
	*
	*	title: Display the album select box
	*	date created: 15-09-16
	*/
	function box_display_op_album_information( $post )
	{

	global $post;

    //First check that this is a Narnoo imported product
    $dataSource = get_post_meta($post->ID,'data_source',true);
    if( empty($dataSource) || $dataSource != 'narnoo'){
    	return  _e( 'This product has not been imported via Narnoo.com',  NARNOO_DISTRIBUTOR_I18N_DOMAIN);
    }

    $operatorId = get_post_meta($post->ID,'operator_id',true);
    if( empty($operatorId) ){
    	return _e( 'There is no Narnoo ID associated with this product',  NARNOO_DISTRIBUTOR_I18N_DOMAIN);
    }

    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_op_album_select_id',true);

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'op_album_meta_box_nonce', 'box_display_op_album_information_nonce' );

		$current_page 		      = 1;
		//$cache	 				  = Narnoo_Distributor_Helper::init_noo_cache();
		$request 				  = Narnoo_Distributor_Helper::init_api( 'operator' );

		//Get Narnoo Ablums.....
		if ( ! is_null( $request ) ) {

			//$list = $cache->get('albums_'.$current_page);

			if( empty($list) ){

					try {

						$list = $request->getAlbums( $operatorId,$current_page );
						if ( !empty($list) || !empty( $list->operator_albums ) ) {
								if(!empty( $list->success ) ){
										//$cache->set('albums_'.$current_page, $list, 43200);
								}
						}

						

					} catch ( Exception $ex ) {
						//do nothing
					}

			}

			//Check the total pages and run through each so we can build a bigger list of albums

		}


    ?> <p>
        <label for="my_meta_box_select">Narnoo Album:</label>
        <select name="noo_op_album_select" id="noo_op_album_select">
        	<option value="">None</option>
            
            <?php if(!empty($list->data->albums)){ ?>
	        
	            <?php foreach ($list->data->albums as $album) { ?>
	            		<option value="<?php echo $album->album_id; ?>" <?php selected( $selected, $album->id ); ?>><?php echo ucwords( $album->title ); ?></option>
	            <?php } ?>
	        
	        <?php } ?>

        </select>
        <p><small><em>Select an album and this will be displayed the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_op_album_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_op_album_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_op_album_information_nonce'], 'op_album_meta_box_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_op_album_select'] ) ){
        	update_post_meta( $post_id, 'noo_op_album_select_id', esc_attr( $_POST['noo_op_album_select'] ) );
    	}

	}

	/*************************************************************************
					OPERATOR PAGES META BOXES [end]
	/*************************************************************************/


	/**
	 * Process frontend AJAX requests triggered by shortcodes.
	 **/
	function narnoo_distributor_ajax_lib_request() {
		ob_start();
		require( $_POST['lib_path'] );
		echo json_encode( array( 'response' => ob_get_clean() ) );
		die();
	}


	/**
	 * Loads common Javascript/CSS files for admin area.
	 **/
	function load_admin_scripts() {
		?>
		<style type="text/css" media="screen">
		#icon-narnoo-distributor-categories.icon32 {
			background: url(<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL . '/images/icon-32.png'; ?>) no-repeat;
		}
		</style>
		<?php
	}
}



/*
 * Add webhook
 */
add_action('init', 'narnoo_webhook');

function narnoo_webhook() {
	global $wpdb;
	$narnoo_hook = md5( get_site_url( get_current_blog_id() ) );
	if( isset($_REQUEST['narnoo_hook']) and !empty($_REQUEST['narnoo_hook']) /*&& $_REQUEST['narnoo_hook'] == $narnoo_hook */ ) {

		$narnoo_headers = getallheaders();
		$narnoo_key = isset($narnoo_headers['Narnoo-Signature']) ? $narnoo_headers['Narnoo-Signature'] : '';
		$option = get_option( 'narnoo_distributor_settings' );
       	$webhook = empty($option['webhook_response']) ? '' : json_decode($option['webhook_response'], true);

		if( isset($webhook['data']['key']) && !empty($webhook['data']['key']) && $webhook['data']['key'] == $narnoo_key) {
		
			$data     = json_decode( file_get_contents('php://input'), true );
			$action   = $data['action'];
			$op_id    = $data['businessId'];
			$data_ids = $data['data'];

			$post_ids = '';
			if( in_array( $action, array( 'update.product', 'delete.product' ) ) ) {
				foreach ( $data_ids as $narnoo_product_id ) {
					$narnoo_product_args = array(
					    'post_type' => 'narnoo_product',
					    'meta_query' => array(
					   		array(
					   			'key' => 'narnoo_product_id',
					   			'value' => $narnoo_product_id,
					   			'compare' => '='
					   		)
					    )
					);
					$narnoo_product_query = new WP_Query( $narnoo_product_args ); 
					while( $narnoo_product_query->have_posts() ) { 
					    $narnoo_product_query->the_post(); 
					    global $post;
					    $post_ids[$narnoo_product_id] = $post->ID;
					}
				}
			}

			switch ($action) {
				case 'unfollow.operator':
				case 'follow.operator':
					break;

				case 'create.product':
					if( !empty($data_ids) ) {
						foreach ($data_ids as $productId) {
							narnoo_update_product( $productId, $op_id, $action );
						}
					}
					break;

				case 'update.product':
					if( !empty($data_ids) ) {
						foreach ($data_ids as $productId) {
						    $auto_upate = get_post_meta( $post_ids[$productId], 'narnoo_product_remove_auto_update', true );
						    if( !$auto_upate ) {
								narnoo_update_product( $productId, $op_id, $action );
							}
						}
					}
					break;

				case 'delete.product':
					if( !empty($post_ids) ) {
						foreach ($post_ids as $post_id) {
							wp_delete_post( $post_id );
						}
					}
					break;
				
				default:
					# code...
					break;
			}
		}
	    echo 'success';
	    die;
	}
}

// Update product for narnoo distributer plugin.
function narnoo_update_product( $productId, $op_id, $action ) {
    $user_ID         = get_current_user_id();
    // $productId       = get_post_meta( $post_id, 'narnoo_product_id', true );
    // $op_id           = get_post_meta( $post_id, 'narnoo_operator_id', true );

    // Fetch operator data
    $requestOperator = Narnoo_Distributor_Helper::init_api();
    $operator        = $requestOperator->business_listing( $op_id );
    $operatorPostId  = Narnoo_Distributor_Helper::get_post_id_for_imported_operator_id($op_id);

    // Fetch operator product data
    $requestOperator = Narnoo_Distributor_Helper::init_api('new');
    $productDetails  = $requestOperator->getProductDetails( $productId, $op_id );

    if(!empty($productDetails) || !empty($productDetails->success)){
        $postData = Narnoo_Distributor_Helper::get_post_id_for_imported_product_id( $productDetails->data->productId );

        if ( !empty( $postData['id'] ) && $postData['status'] !== 'trash' && $action == 'update.product' ) {
            $post_id = $postData['id'];

            // update existing post, ensuring parent is correctly set
            $update_post_fields = array(
                'ID'            => $post_id,
                'post_title'    => $productDetails->data->title,
                'post_type'     => 'narnoo_product',
                'post_status'   => 'publish',
                'post_author'   => $user_ID,
                'post_modified' => date('Y-m-d H:i:s')
            );

            if(!empty($productDetails->data->description->summary[0]->english->text)){
                $update_post_fields['post_excerpt'] = strip_tags( $productDetails->data->description->summary[0]->english->text );
            }

            if(!empty($productDetails->data->description->description[0]->english->text)){
                $update_post_fields['post_content'] = strip_tags( $productDetails->data->description->description[0]->english->text );
            }

            wp_update_post($update_post_fields);

            update_post_meta( $post_id, 'product_description', $productDetails->data->description->description->english->text);
            update_post_meta( $post_id, 'product_excerpt',  strip_tags( $productDetails->data->description->summary->english->text ));

           // set a feature image for this post but first check to see if a feature is present

            $feature = get_the_post_thumbnail($post_id);
            if(empty($feature)){
                if( !empty( $productDetails->data->featureImage->xxlargeImage ) ){
                	// require_once(ABSPATH . 'wp-admin/includes/media.php');
					// require_once(ABSPATH . 'wp-admin/includes/file.php');
					// require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $url = "https:" . $productDetails->data->featureImage->xxlargeImage;
                    $desc = $productDetails->data->title . " product image";
                    $feature_image = media_sideload_image($url, $post_id, $desc, 'id');
                    if(!empty($feature_image)){
                        set_post_thumbnail( $post_id, $feature_image );
                    }
                }

            }

        } else if( $action == 'create.product' ) {
        	//create new post with operator details
            $new_post_fields = array(
                'post_title'        => $productDetails->data->title,
                'post_status'       => 'publish',
                'post_date'         => date('Y-m-d H:i:s'),
                'post_author'       => $user_ID,
                'post_type'         => 'narnoo_product',
                'comment_status'    => 'closed',
                'ping_status'       => 'closed'
            );

            if(!empty($productDetails->data->description->summary[0]->english->text)){
                $new_post_fields['post_excerpt'] = strip_tags( $productDetails->data->description->summary[0]->english->text );
            }

            if(!empty($productDetails->data->description->description[0]->english->text)){
                $new_post_fields['post_content'] = strip_tags( $productDetails->data->description->description[0]->english->text );
            }

            $post_id = wp_insert_post($new_post_fields);
            
            // set a feature image for this post
            if( !empty( $productDetails->data->featureImage->xxlargeImage ) ){
				// require_once(ABSPATH . 'wp-admin/includes/media.php');
				// require_once(ABSPATH . 'wp-admin/includes/file.php');
				// require_once(ABSPATH . 'wp-admin/includes/image.php');
                $url = "https:" . $productDetails->data->featureImage->xxlargeImage;
                $desc = $productDetails->data->title . " product image";
                $feature_image = media_sideload_image($url, $post_id, $desc, 'id');
                if(!empty($feature_image)){
                    set_post_thumbnail( $post_id, $feature_image );
                }
            }
        	//$response['msg'] = "Successfully re-imported product details";
        }

        // insert/update custom fields with operator details into post
        
        if(!empty($productDetails->data->primary)){
            update_post_meta($post_id, 'primary_product',               "Primary Product");
        }else{
            update_post_meta($post_id, 'primary_product',               "Product");
        }
        
        update_post_meta($post_id, 'narnoo_operator_id',            $op_id); 
        update_post_meta($post_id, 'narnoo_operator_name',          $operator->data->profile->name);
        update_post_meta($post_id, 'parent_post_id',                $operatorPostId);
        update_post_meta($post_id, 'narnoo_booking_id',             $productDetails->data->bookingId);  
        update_post_meta($post_id, 'narnoo_product_id',             $productDetails->data->productId);
        update_post_meta($post_id, 'product_min_price',             $productDetails->data->minPrice);
        update_post_meta($post_id, 'product_avg_price',             $productDetails->data->avgPrice);
        update_post_meta($post_id, 'product_max_price',             $productDetails->data->maxPrice);
        update_post_meta($post_id, 'product_booking_link',          $productDetails->data->directBooking);
        
        update_post_meta($post_id, 'narnoo_listing_category',       $operator->data->profile->category);
        update_post_meta($post_id, 'narnoo_listing_subcategory',    $operator->data->profile->subCategory);

        if( lcfirst( $operator->data->profile->category ) == 'attraction' ){

            update_post_meta($post_id, 'narnoo_product_duration',   $productDetails->data->additionalInformation->operatingHours);
            update_post_meta($post_id, 'narnoo_product_start_time', $productDetails->data->additionalInformation->startTime);
            update_post_meta($post_id, 'narnoo_product_end_time',   $productDetails->data->additionalInformation->endTime);
            update_post_meta($post_id, 'narnoo_product_transport',  $productDetails->data->additionalInformation->transfer);
            update_post_meta($post_id, 'narnoo_product_purchase',   $productDetails->data->additionalInformation->purchases);
            update_post_meta($post_id, 'narnoo_product_health',     $productDetails->data->additionalInformation->fitness);
            update_post_meta($post_id, 'narnoo_product_packing',    $productDetails->data->additionalInformation->packing);
            update_post_meta($post_id, 'narnoo_product_children',   $productDetails->data->additionalInformation->child);
            update_post_meta($post_id, 'narnoo_product_additional', $productDetails->data->additionalInformation->additional);
            
        }
        /**
        *
        *   Import the gallery images as JSON encoded object
        *
        */
        if(!empty($productDetails->data->gallery)){
            update_post_meta($post_id, 'narnoo_product_gallery', json_encode($productDetails->data->gallery) );
        }else{
            delete_post_meta($post_id, 'narnoo_product_gallery');
        }
        /**
        *
        *   Import the video player object
        *
        */
        if(!empty($productDetails->data->featureVideo)){
            update_post_meta($post_id, 'narnoo_product_video', json_encode($productDetails->data->featureVideo) );
        }else{
            delete_post_meta($post_id, 'narnoo_product_video');
        }
        /**
        *
        *   Import the brochure object
        *
        */
        if(!empty($productDetails->data->featurePrint)){   
            update_post_meta($post_id, 'narnoo_product_print', json_encode($productDetails->data->featurePrint) );
        }else{
            delete_post_meta($post_id, 'narnoo_product_print');
        }
                
    } //if success
}


/*
 * Add the extra options to the 'Publish' box
 */
add_action('post_submitbox_misc_actions', 'add_narnoo_product_publish_meta_options');

function add_narnoo_product_publish_meta_options($post_obj) {
  	global $post;
  	$post_type = 'narnoo_product'; // If you want a specific post type
 	$value = get_post_meta($post_obj->ID, 'narnoo_product_remove_auto_update', true); // If saving value to post_meta
 
  	if($post_type==$post->post_type) {
    	echo '<div class="misc-pub-section misc-pub-section-last">';
        echo '<label><input type="checkbox"' . (!empty($value) ? ' checked="checked" ' : null) . ' value="1" name="check_meta" />';
        _e('Remove Auto Update From Narnoo', NARNOO_DISTRIBUTOR_I18N_DOMAIN);
        echo '</label>'.'</div>';
  	}
}
 

/*
 * Init extra_publish_options_save() on save_post action
 */
add_action( 'save_post', 'narnoo_product_extra_publish_options_save', 10 , 3);

function narnoo_product_extra_publish_options_save($post_id, $post, $update) {
 
  	$post_type = 'narnoo_product'; // If using specific post type
  	if ( $post_type != $post->post_type ) { return; }
 
  	if ( wp_is_post_revision( $post_id ) ) { return; }
 
  	if(isset($_POST['check_meta']) && $_POST['check_meta'] == 1) { // Checkbox value is 1 if set
    	update_post_meta($post_id, 'narnoo_product_remove_auto_update', $_POST['check_meta']);
  	} else {
  		update_post_meta($post_id, 'narnoo_product_remove_auto_update', $_POST['check_meta']); 
  	}
 
 
}