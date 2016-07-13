<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div id="loading">
		<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
	</div>

	<div id="news" class="page page_margin">

		<!-- CAMPAIGN LIST – MODIFIED TO LINK TO CAMPAING PAGE -->
		<ul id="campaign_list">
    		<li>
    			<a href="<?php bloginfo( 'url' ); ?>/campaigns"><h4 class="campaign_title wrap">Campaigns</h4></a>
			</li>		
		</ul>

		<!-- NEWS LIST -->
		<ul id="news_list">
			<?php $the_query = new WP_Query("post_type=news");
			if ( $the_query -> have_posts() ) :
		        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>

		    		<li class="news_post">
		    			
		    			<div class="news_content">
		    				<!-- LEFT COLUMN -->
		    				<div id="news_text" class="column">
								<div class="sub_col">
									<h5 class="news_date">
										<?php 
										$news_date = get_the_date('F d Y');
										$lines = explode( " ", $news_date );
										foreach ( $lines as $line ) { ?>
											<span class="wrap"><?php echo $line; ?></span>
										<?php } ?>
									</h5>
								</div>
								<div class="sub_col">
									<h5 class="news_title"><p><?php the_title(); ?></p></h5>
			    					<?php the_content(); ?>
								</div>
		    				</div>
		    				<!-- RIGHT COLUMN -->
		    				<div class="column news_images_wrapper">
		    					<?php if ( get_field("news_images") ) { 
		    						// IF MORE THAN ONE IMAGE
		    						if ( count( get_field( "news_images" ) ) > 1 ) { ?>
			    						<!-- GALLERY ARROW -->
			    						<div class="gallery_arrow">
											<img src="<?php bloginfo('template_url'); ?>/img/gallery_arrow_large.svg" />
										</div>		    							
		    						<?php } ?>
		    						<!-- IMAGE SLIDESHOW -->
		    						<ul class="news_images gallery">
			    						<?php while ( have_rows("news_images") ) : the_row();
											$image = get_sub_field("news_image");
											if( !empty($image) ): 
									            $thumb = $image["sizes"][ "thumbnail" ]; // 300
									            $medium = $image["sizes"][ "medium" ]; // 600
									            $large = $image["sizes"][ "large" ]; // 800
									            $extralarge = $image["sizes"][ "extra-large" ]; // 1024
									            $full = $image["url"];
									            $width = $image["sizes"]["medium-width"];
									            $height = $image["sizes"]["medium-height"];
									        endif; 
									        if ( $width < $height ) {
									        	$class = "portrait";
									        } else {
												$class = "landscape";
									        }
									        ?>
										    <li class="news_image">
												<img width="<?php echo $width; ?>" 
													 height="<?php echo $height; ?>" 
													 sizes="(min-width: 40em) 95vw, 47vw"
													 srcset="<?php echo $thumb; ?> 300w,
															<?php echo $medium; ?> 600w,
															<?php echo $large; ?> 800w,
															<?php echo $large; ?> 2x" 
													class="<?php echo $class; ?>"
												/>
											</li>
			    						<?php endwhile; ?>	
		    						</ul>	    						
		    					<?php } else if ( get_field("embedded_media") ) {
		    						// VIDEO
		    						the_field("embedded_media");
		    					} ?>
		    				</div>	    			
		    			</div>
		    		
		    		</li><!-- END OF .NEWS_POST -->

			    <?php endwhile;
			    endif; ?>
	    	</ul>

	</div><!-- end of #news -->

<?php get_footer(); ?>