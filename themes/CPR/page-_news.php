<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div id="news" class="page">

		<!-- CAMPAIGN LIST -->
		<ul id="campaign_list">
			<?php $the_query = new WP_Query("post_type=campaign");
			if ( $the_query -> have_posts() ) :
		        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>
		    		<li>
		    			<a href="<?php the_permalink(); ?>"><h4 class="campaign_title wrap no_break">Campaign <?php the_title(); ?></h4></a>
					</li>
		    <?php endwhile;
		    endif; ?>		
		</ul>

		<!-- NEWS LIST -->
		<ul id="news_list">
		<?php $the_query = new WP_Query("post_type=news");
		if ( $the_query -> have_posts() ) :
	        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>

	    		<li class="news_post">
	    			
	    			<div class="news_content">
	    				<!-- LEFT COLUMN -->
	    				<div class="news_text info_column">
							<div class="sub_col">
								<h5 class="news_date">
									<?php 
									$news_date = get_the_date('F d Y');
									$lines = explode( " ", $news_date );
									foreach ( $lines as $line ) { ?>
										<span class="last_word"><?php echo $line; ?></span>
									<?php } ?>
								</h5>
							</div>
							<div class="sub_col sub_col_right">
								<h5 class="news_title wrap"><p><?php the_title(); ?></p></h5>
		    					<?php the_content(); ?>
							</div>
	    				</div>
	    				<!-- RIGHT COLUMN -->
	    				<div class="news_images info_column">
	    					<?php if ( get_field("embedded_media") ) {
	    						the_field("embedded_media");
	    					} ?>
	    				</div>	    			
	    			</div>
	    		</li>

	    <?php endwhile;
	    endif; ?>
    	</ul>

	</div><!-- end of #news -->

<?php get_footer(); ?>