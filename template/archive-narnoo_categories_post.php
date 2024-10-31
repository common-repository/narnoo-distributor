<?php get_header(); ?>

    <?php do_action( 'narnoo_before_main_content' );  ?>

    <?php $post_type = get_post_type_object( get_post_type() ); ?>
    <div class="entry">
        <header class="entry-header">
            <h1 class="page-title"><?php echo $post_type->label; ?></h1>
        </header><!-- .entry-header -->

        <div class="entry-content">
            <?php $args = array('post_type' => get_post_type(), 'post_parent' => 0,); ?>
            <?php query_posts($args); ?>
            <?php while ( have_posts() ) : the_post(); ?>

                    <div class="ncm-col-xs-12 ncm-col-lg-3">
                        <a href="<?php echo get_post_permalink(); ?>">
                            <div class="narnoo_attr_img_wrapper">
                                <?php 
                                if ( has_post_thumbnail() ) { 
                                    echo the_post_thumbnail(); 
                                } else {
                                    ?> <img width="1800" src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>images/no-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="<?php the_title(); ?>" /> <?php
                                }
                                ?>
                            </div>
                            <h6 class="narnoo_product_listing_link">
                                <?php the_title(); ?>    
                            </h6>
                        </a>

                    </div>
                    
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
    </div>

    <?php do_action( 'narnoo_after_main_content' ); ?>
    
    <?php do_action( 'narnoo_get_sidebar' ); ?>

<?php get_footer(); ?>