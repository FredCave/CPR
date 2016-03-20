<?php get_header(); ?>

<?php get_sidebar(); ?>

<!-- LOADING -->
<div id="loading">
	<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
</div>

<?php $the_query = new WP_Query("name=info");
	if ( $the_query -> have_posts() ) :
        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>

			<div id="info" class="page page_margin">

				<div id="info_text" class="column">
					
					<!-- INTRO TEXT -->
					<?php the_field( "info_text" ); ?>
				
				</div><!-- END OF .COLUMN -->
			
				<div id="info_right" class="column">
					
					<div>
						<!-- STOCKISTS -->
						<h1>Stockists:</h1> 					
						<?php if( have_rows("info_stockists") ):
						    while ( have_rows("info_stockists") ) : the_row(); ?>		        
								<h1><?php the_sub_field("info_stockists_country"); ?></h1>
						        	<?php
						        	if ( have_rows("info_stockists_names") ) {
						        		$j = 0;
										while ( have_rows("info_stockists_names") ) : the_row(); 	
											if ( $j > 0 ) { echo " / "; }
											the_sub_field("info_stockists_name");
										$j++;
										endwhile; 
									}
						    endwhile;
						endif; ?>
					</div>

					<div>
						<!-- CONTACT -->
						<?php if( have_rows("info_contact") ):
							$i = 0;
						    while ( have_rows("info_contact") ) : the_row(); 
						    	if ( $i === 0 ) { ?>
						    		<h1>Contact:</h1>
						    <?php } 
							   	if ( get_sub_field("info_contact_name") ) { ?>
							    	<h1><?php the_sub_field("info_contact_name"); ?></h1>
							    <?php	} 
							    the_sub_field("info_contact_address");
							    echo " ";
							$i++;
						    endwhile;
						endif; ?>
							
						<!-- COLOPHON -->
						<?php if( have_rows("info_colophon") ):
						    while ( have_rows("info_colophon") ) : the_row(); ?>
							        <h1><?php the_sub_field("info_colophon_title"); ?></h1>
							        <?php the_sub_field("info_colophon_name"); ?> 						    
						<?php
						    endwhile;
						endif; ?>
					</div>

				</div><!-- END OF .COLUMN -->

		</div> <!-- END OF #PAGE -->

	<?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>