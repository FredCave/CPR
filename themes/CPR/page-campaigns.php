<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div id="loading">
		<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
	</div>

	<div id="campaign" class="page page_margin">

		<?php 
		$args = array( 'post_type' => 'campaign' );
	    $campaign_query = new WP_Query( $args ); 
	    if ( $campaign_query->have_posts() ) :
	    	while ( $campaign_query->have_posts() ) : $campaign_query->the_post(); ?>
	    
	    	<div id="" class="campaign_post">

	    		<!-- ANCHOR WITH OFFSET -->
				<div id="<?php echo $post->post_name;?>" class="campaign_anchor">
				</div>

				<div class="single_campaign_title_wrapper">
					<h4 class="campaign_title">Campaign <?php the_title(); ?></h4>
				</div>

				<div class="campaign_content">
					<!--VIDEO -->
					<?php if ( get_field("video") ) { ?>
					   	<div class="campaign_video">
							<?php the_field("video"); ?>		
					   	</div>
				   	<?php } ?>

					<!-- PHOTOS -->
				   	<?php if ( have_rows("images") ) : ?>
					   	<div class="campaign_photos">
					   		
					   		<div class="gallery_wrapper">
						   		<ul id="" class="campaign_images gallery">
						   		    <?php while ( have_rows("images") ) : the_row();
											$image = get_sub_field("image");
												if( !empty($image) ): 
									            $thumb = $image["sizes"][ "thumbnail" ]; // 300
									            $medium = $image["sizes"][ "medium" ]; // 600
									            $large = $image["sizes"][ "large" ]; // 800
									            $extralarge = $image["sizes"][ "extra-large" ]; // 1024
									            $full = $image["url"];
									            $width = $image["sizes"]["medium-width"];
									            $height = $image["sizes"]["medium-height"];
									        endif; ?>
									    <li class="campaign_image">
											<img width="<?php echo $width; ?>" 
												 height="<?php echo $height; ?>" 
												 sizes="(min-width: 40em) 95vw, 47vw"
												 srcset="<?php echo $thumb; ?> 300w,
														<?php echo $medium; ?> 600w,
														<?php echo $large; ?> 800w,
														<?php echo $large; ?> 2x"
											/>
										</li>
									<?php endwhile; ?>
						   		</ul>
			
					   			<div class="gallery_arrow">
									<img src="<?php bloginfo('template_url'); ?>/img/gallery_arrow_large.svg" />
								</div>	

					   		</div><!-- END OF .GALLERY_WRAPPER -->

					   	</div><!-- end of #campaign_photos --> 
					<?php endif; ?>

				</div>	

			</div><!-- END OF .CAMPAIGN_POST -->
			
	    <?php	
	    	endwhile;
	    endif;
	    ?>

    </div><!-- END OF #CAMPAIGN -->

<?php get_footer(); ?>
