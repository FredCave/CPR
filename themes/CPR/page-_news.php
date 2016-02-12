<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div id="news" class="page">

		<!-- CAMPAIGN LIST -->
		<ul id="campaign_list">
			<?php $the_query = new WP_Query("post_type=campaign");
			if ( $the_query -> have_posts() ) :
		        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>

		    		<a href="<?php the_permalink(); ?>"><h4 class="campaign_title wrap">Campaign <?php the_title(); ?></h4></a>

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
	    				<div class="news_text info_column">
							<h4 class="news_date wrap">
								<?php the_date('M d Y'); ?></span>
							</h4>

	    					<?php the_content(); ?>
	    				</div>
	    				<div class="news_images info_column">
	    					
	    				</div>	    			
	    			</div>
	    		</li>

	    <?php endwhile;
	    endif; ?>
    	</ul>

	</div><!-- end of #news -->

<?php get_footer(); ?>