<?php

/**

 * Narnoo Operator - Brochures table.

 **/

class Narnoo_Distributor_Attraction_Template {



    function __construct(){

    	if ( ! is_admin() ) {

            add_action( 'wp_enqueue_scripts', array( $this, 'narnoo_front_enqueue_scripts' ), 9 );



            add_filter( 'template_include', array( $this, 'narnoo_template_loader' ), 9 );

		}



        add_action( 'narnoo_before_main_content', array( $this, 'narnoo_before_main_content_func' ) );



        add_action( 'narnoo_after_main_content', array( $this, 'narnoo_after_main_content_func' ) );

        

        add_action( 'narnoo_get_sidebar', array( $this, 'narnoo_get_sidebar' ) );



    }



    function narnoo_front_enqueue_scripts() {

        $post_type = get_post_type();



        if( strpos( $post_type, 'narnoo') !== false ) {

            /*********** enqueue bootstrap style sheet  ***************/

            wp_register_style( 

                    'bootstrap_min_css',  

                    NARNOO_DISTRIBUTOR_PLUGIN_URL .'css/bootstrap.min.css', 

                    false, 

                    NARNOO_DISTRIBUTOR_CURRENT_VERSION 

                );



            wp_enqueue_style( 'bootstrap_min_css' );



            /*********** Theme wise style sheet start ***************/

            $template = get_option( 'template' );

            if( !empty($template) ) {

                $css_file = 'narnoo_front_'.$template.'.css';

                if( file_exists( NARNOO_DISTRIBUTOR_PLUGIN_PATH .'css/'.$css_file ) ) {

                    wp_register_style( 

                        'narnoo_front_'.$template.'_css', 

                        NARNOO_DISTRIBUTOR_PLUGIN_URL .'css/'.$css_file, 

                        false, 

                        NARNOO_DISTRIBUTOR_CURRENT_VERSION 

                    );

                    wp_enqueue_style( 'narnoo_front_'.$template.'_css' );

                }

                



                if( file_exists( get_template_directory() ."/narnoo_distributor/style.css" ) ){

                    wp_register_style( 

                        'narnoo_front_theme_'.$template.'_css', 

                        get_template_directory_uri().'/narnoo_distributor/style.css', 

                        false, 

                        NARNOO_DISTRIBUTOR_CURRENT_VERSION 

                    );

                    wp_enqueue_style( 'narnoo_front_theme_'.$template.'_css' );

                }

            }

            /*********** Theme wise style sheet end ***************/

        }

    }



	function narnoo_template_loader($template) {



		$post_type = get_post_type();



		if( strpos( $post_type, 'narnoo') !== false ) {



	        if ( is_embed() ) {

	            return $template;

	        }



	        if ( $default_file = $this->narnoo_get_template_loader_default_file( $post_type ) ) {

	            $template = narnoo_template_location( $default_file );

	        }

		}

        return $template;

	}



	function narnoo_get_template_loader_default_file($post_type) {

		if ( is_singular( $post_type ) ) {

            $default_file = 'single-narnoo_categories_post.php';

        } elseif ( is_post_type_archive( $post_type ) ) {

            $default_file = 'archive-narnoo_categories_post.php';

        } else {

            $default_file = '';

        }

        return $default_file;

	}



    function narnoo_before_main_content_func() {

        $template = get_option( 'template' );

        switch ( $template ) {



            case 'twentyfifteen' :

                echo '<div id="primary" role="main" class="content-area twentyfifteen"><div id="main" class="site-main t15wc">';

                break;



            case 'twentysixteen' :

                echo '<div id="primary" class="content-area twentysixteen"><main id="main" class="site-main" role="main">';

                break;



            case 'twentyseventeen' : 

                echo '<div class="wrap twentyseventeen"><div id="primary" class="content-area twentyseventeen"><main id="main" class="site-main" role="main">';

                break;



            case 'twentynineteen' : 

                echo '<div id="primary" class="content-area twentynineteen"><main id="main" class="site-main twentynineteen">';

                break;



            default :

                echo '<div id="container"><div id="content" role="main">';

                break;

        }

    }



    function narnoo_after_main_content_func() {

        $template = get_option( 'template' );

        switch ( $template ) {



            case 'twentyfifteen' :

                echo '</div></div>';

                break;



            case 'twentysixteen' :

                echo '</main></div>';

                break;



            case 'twentyseventeen' : 

                echo '</main>

                    </div>';

                    get_sidebar();

                echo '</div>';

                break;



            case 'twentynineteen' : 

                echo '</main>

                    </div>';

                break;



            default :

                echo '</div></div>';

                break;



        }

    }



    function narnoo_get_sidebar() {

        $template = get_option( 'template' );

        if( 'twentynineteen' != $template ) {

            get_sidebar();

        }

    }



}    



new Narnoo_Distributor_Attraction_Template();



if ( ! function_exists( 'narnoo_template_location' ) ) :

    function narnoo_template_location ( $template ) {

        $theme_dir = get_template_directory().'/narnoo_distributor/';
        $template_url = false;

        if( $template != '' && file_exists( $theme_dir . $template ) ) {

            $template_url = $theme_dir . $template;

        } else if( $template != '' && file_exists( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'template/' . $template ) ) {

            $template_url = NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'template/' . $template;

        } 

        return $template_url;

    }

endif;

