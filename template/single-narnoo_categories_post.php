<?php get_header(); 

$narnoo_queried_object = get_queried_object();

global $post;

$narnno_args = array(
    'post_parent' => $post->ID,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'post_type' => $narnoo_queried_object->post_type, 
    );

$narnoo_query = new WP_Query( $narnno_args );
//print_r($narnoo_query );

$businessname = get_post_meta( $post->ID, 'businessname', true );
$businessphone = get_post_meta( $post->ID, 'phone', true );
$businessurl = get_post_meta( $post->ID, 'url', true );
$businessemail = get_post_meta( $post->ID, 'email', true );

$facebook = get_post_meta( $post->ID, 'facebook', true );
$twitter = get_post_meta( $post->ID, 'twitter', true );
$instagram = get_post_meta( $post->ID, 'instagram', true );
$youtube = get_post_meta( $post->ID, 'youtube', true );
$tripadvisor = get_post_meta( $post->ID, 'tripadvisor', true );

?>
    
    <?php do_action( 'narnoo_before_main_content' ); ?>

    <?php if($narnoo_queried_object->post_parent == 0): ?>

			<div id="narnoo_categories_post" <?php post_class(); ?>>

            <!-- <header class="entry-header"> -->
				
				<?php //the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			
			<!-- </header> --><!-- .entry-header -->

			<div class="entry-content">
				

				<div class="narnno-subcategory">
					<!-- <h2>Sub Category</h2> -->

					<!-- ncm-row start --> 
					<div class="ncm-row">

						<div class="ncm-col-md-7">
							<?php while ( have_posts() ) : the_post(); ?>
								<div class="subcategory-description">
									<?php the_content(); ?>
								</div>
							<?php endwhile; ?>
							<div class="subcategory-product-list">

								<h2>Sub Category</h2>

								<?php if( $narnoo_query->have_posts() ) : ?>
					                <?php while ( $narnoo_query->have_posts() ) :  $narnoo_query->the_post(); ?>

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
					                    
					            	<?php endwhile; ?>
					            <?php endif; ?>
					            <?php wp_reset_postdata(); ?>
			        		</div>

			            </div>	

			            <div class="ncm-col-md-5">
			            	
			            	<div class="product-information-data">
			            		<?php if(!empty($businessname)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business Name:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessname; ?></div>
									</div>
						    	<?php endif; ?>

						    	<?php if(!empty($businessphone)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business Phone:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessphone ?></div>
									</div>
						    	<?php endif; ?>

						    	<?php if(!empty($businessurl)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business URL:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessurl; ?></div>
									</div>
						    	<?php endif; ?>

						    	<?php /*if(!empty($businessemail)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business Email:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessemail; ?></div>
									</div>
						    	<?php endif;*/ ?>

							</div>

							<div class="product-social-listing">
								<ul>
									<?php if(!empty($facebook)): ?>
										<li><a href="<?php echo $facebook; ?>" target="_blank"><i class="ncm_fa ncm-facebook" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($twitter)): ?>
										<li><a href="<?php echo $twitter; ?>" target="_blank"><i class="ncm_fa ncm-twitter" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($instagram)): ?>
										<li><a href="<?php echo $instagram; ?>" target="_blank"><i class="ncm_fa ncm-instagram" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($youtube)): ?>
										<li><a href="<?php echo $youtube; ?>" target="_blank"><i class="ncm_fa ncm-youtube" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($tripadvisor)): ?>
										<li><a href="<?php echo $tripadvisor; ?>" target="_blank"><i class="ncm_fa ncm-tripadvisor" ></i></a></li>
									<?php endif; ?>
								</ul>
								
							</div>

			            </div>


		            </div>
		            <!-- ncm-row End --> 

					<?php  /* 
					if ( $narnoo_query->have_posts() ) :
						while ( $narnoo_query->have_posts() ) : $narnoo_query->the_post(); ?>
						  
						    <h3><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
						    
					<?php	endwhile;
					endif;
					wp_reset_postdata();  */  ?>

				</div>

			<!-- </div> -->

        </div>

    <?php else: ?>	

    	<?php //while ( have_posts() ) : the_post(); ?>

			<div id="narnoo_categories_post" <?php post_class(); ?>>

	            <?php /*<header class="entry-header">
					
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				
				</header>*/?><!-- .entry-header -->

				<div class="entry-content">
					
					<div class="narnno-subcategory">
					
					<!-- ncm-row start --> 
					<div class="ncm-row">

						<div class="ncm-col-md-7">
							<?php while ( have_posts() ) : the_post(); ?>
								<div class="subcategory-description">
									<?php the_content(); ?>
								</div>
							<?php endwhile; ?>
							

			            </div>	

			            <div class="ncm-col-md-5">
			            	
			            	<div class="product-information-data">
			            		<?php if(!empty($businessname)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business Name:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessname; ?></div>
									</div>
						    	<?php endif; ?>

						    	<?php if(!empty($businessphone)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business Phone:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessphone ?></div>
									</div>
						    	<?php endif; ?>

						    	<?php if(!empty($businessurl)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business URL:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessurl; ?></div>
									</div>
						    	<?php endif; ?>

						    	<?php /*if(!empty($businessemail)): ?>
									<div class="ncm-row">
										<div class="ncm-col-md-5 ncm-col-sm-5"><label> Business Email:</label></div>
										<div class="ncm-col-md-7 ncm-col-sm-7"><?php echo $businessemail; ?></div>
									</div>
						    	<?php endif;*/ ?>

							</div>

							<div class="product-social-listing">
								<ul>
									<?php if(!empty($facebook)): ?>
										<li><a href="<?php echo $facebook; ?>" target="_blank"><i class="ncm_fa ncm-facebook" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($twitter)): ?>
										<li><a href="<?php echo $twitter; ?>" target="_blank"><i class="ncm_fa ncm-twitter" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($instagram)): ?>
										<li><a href="<?php echo $instagram; ?>" target="_blank"><i class="ncm_fa ncm-instagram" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($youtube)): ?>
										<li><a href="<?php echo $youtube; ?>" target="_blank"><i class="ncm_fa ncm-youtube" ></i></a></li>
									<?php endif; ?>
									<?php if(!empty($tripadvisor)): ?>
										<li><a href="<?php echo $tripadvisor; ?>" target="_blank"><i class="ncm_fa ncm-tripadvisor" ></i></a></li>
									<?php endif; ?>
								</ul>
								
							</div>

			            </div>


		            </div>
		            <!-- ncm-row End --> 

		            <div class="ncm-row">
		            	<div class="ncm-col-md-12">
		            	
			            	<div class="subcategory-product-list">

								<?php  echo do_shortcode( '[ncm_product_search search="false" date="true"]' );?>

			        		</div>
		        		
		        		</div>

		            </div>

					

				</div>
					
					
					
				</div>

	        </div>

    	<?php //endwhile; ?>

  	<?php endif; ?>

	<?php do_action( 'narnoo_after_main_content' ); ?>

	<?php do_action( 'narnoo_get_sidebar' ); ?>

<?php get_footer(); ?>