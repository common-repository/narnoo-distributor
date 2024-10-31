<?php
/*
 * Listing pages metaboxes
 */

add_filter( 'cmb2_init', 'narnoo_listing_metaboxes' );
function narnoo_listing_metaboxes() {
    $box_options = array(
        'id'           => 'listing_tabs_metaboxes',
        'title'        => __( 'Listing Information', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'object_types' => array( 'narnoo_service','narnoo_attraction','narnoo_accommodation','narnoo_dining', 'narnoo_retail' ),
        'show_names'   => true,
    );

    // Setup meta box
    $cmb = new_cmb2_box( $box_options );
    // Setting tabs
    $tabs_setting           = array(
        'config' => $box_options,
        'layout' => 'vertical', // Default : horizontal
        'tabs'   => array()
    );

    //Manage each of the tabs as complete collection of fields
    $tabs_setting['tabs'][] = array(
        'id'     => 'tab1',
        'title'  => __( 'Details', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'fields' => array(
            array(
                'name' => __( 'Business Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'businessname',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Business Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'phone',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Business URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'url',
                'type' => 'text_url'
            ),
            array(
                'name' => __( 'Business Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'email',
                'type' => 'text_email'
            )
        )
    );

    //Manage each of the tabs as complete collection of fields
    $tabs_setting['tabs'][] = array(
        'id'     => 'tab2',
        'title'  => __( 'Location', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'fields' => array(
            array(
                'name' => __( 'Business Address', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'location',
                'type' => 'text'
            ),
            array(
                'name' => __( 'Business State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'state',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Business Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'suburb',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Business Postcode', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'postcode',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Country', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'country_name',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Latitude', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'latitude',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Longitude', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'longitude',
                'type' => 'text_medium'
            )
        )
    );

    //Manage each of the tabs as complete collection of fields
    $tabs_setting['tabs'][] = array(
        'id'     => 'tab3',
        'title'  => __( 'Social Links', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'fields' => array(
            array(
                'name' => __( 'Facebook', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'facebook',
                'type' => 'text'
            ),
            array(
                'name' => __( 'Twitter', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'twitter',
                'type' => 'text'
            ),
            array(
                'name' => __( 'Instagram', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'instagram',
                'type' => 'text'
            ),
            array(
                'name' => __( 'YouTube', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'youtube',
                'type' => 'text'
            ),
            array(
                'name' => __( 'TripAdvisor', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'tripadvisor',
                'type' => 'text'
            )
        )
    );

    //Manage each of the tabs as complete collection of fields
    $tabs_setting['tabs'][] = array(
        'id'     => 'tab4',
        'title'  => __( 'Listing Source', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'fields' => array(
            array(
                'name' => __( 'Service', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'data_source',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Listing ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'operator_id',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Category', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'category',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Sub Category', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'sub_category',
                'type' => 'text_medium'
            ),
            array(
                'name' => __( 'Keywords', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'keywords',
                'type' => 'text'
            )
        )
    );

    /* //Manage each of the tabs as complete collection of fields
    $tabs_setting['tabs'][] = array(
        'id'     => 'tab5',
        'title'  => __( 'Products', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'fields' => array(
            array(
                'name' => __( 'Number', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
                'id'   => 'products',
                'type' => 'text_medium'
            )
        )
    ); */

    // Set tabs
    $cmb->add_field( array(
        'id'   => '__tabs',
        'type' => 'tabs',
        'tabs' => $tabs_setting
    ) );
}


/*
*
*	Show a checkbox for featured products - eCommerce plugin
*
*

add_action( 'cmb2_admin_init', 'narnoo_listing_feature' );
function narnoo_listing_feature() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'narnoo_';

    /*
     * Initiate the metabox
     *
    $cmb = new_cmb2_box( array(
        'id'            => 'featured_narnoo_product',
        'title'         => __( 'Feature This Product', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
        'object_types'  => array( 'narnoo_product' ), // Post type
        'context'       => 'side',
        'priority'      => 'low',
        'show_names'    => true,
    ) );

    $cmb->add_field( array(
    'name' => 'Set as featured product',
    'desc' => 'Check to set product (optional)',
    'id'   => $prefix.'featured_product',
    'type' => 'checkbox',
	) );

}
*/