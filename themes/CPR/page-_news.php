<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div id="news" class="page">

		<ul>
		<?php $the_query = new WP_Query("post_type=news");
		if ( $the_query -> have_posts() ) :
	        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>

	    		<li class="news_post">
	    			<div class="news_date"><?php the_date('M d Y'); ?><span class="break"></span></div>
	    			<div class="news_content">
	    				<div class="news_text info_column">
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