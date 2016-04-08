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
		    				<div id="news_images" class="column">
		    					<?php if ( get_field("embedded_media") ) {
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