<?php get_header(); ?>

<?php get_sidebar(); ?>

<?php $the_query = new WP_Query("name=info");
	if ( $the_query -> have_posts() ) :
        while ( $the_query -> have_posts() ) : $the_query-> the_post(); ?>

		<div id="info" class="page">

			<div id="info_text" class="info_column">
				<!-- INTRO TEXT -->
				<?php the_field( "info_text" ); ?>
			</div>
			
			<div id="info_right" class="info_column">

				<!-- LEFT INNER COLUMN -->
				<div class="sub_col_left sub_col">

					<div id="info_stockists">
						<h1 class="wrap">Stockists</h1>
						<!-- STOCKISTS -->
						<?php if( have_rows("info_stockists") ):
						    
						    while ( have_rows("info_stockists") ) : the_row(); ?>		        

								<div class="info_row">

						        	<h1 class="wrap"><?php the_sub_field("info_stockists_country"); ?></h1>
						        	<?php
						        	if ( have_rows("info_stockists_names") ) {
										while ( have_rows("info_stockists_names") ) : the_row(); ?>
										
										<h3 class="wrap"><?php the_sub_field("info_stockists_name"); ?></h3>

									<?php endwhile; } ?>
								
								</div>
							<?php
						    endwhile;
						endif; ?>
					</div>

				</div><!-- end of .sub_col_right -->

				<!-- RIGHT INNER COLUMN -->
				<div class="sub_col_right sub_col">

					<div id="info_contact" class="">
						<!-- CONTACT -->
						<?php if( have_rows("info_contact") ):
							$i = 0;
						    while ( have_rows("info_contact") ) : the_row(); 
						    	if ( $i === 0 ) { ?>
						    		<h1 class="wrap">Contact</h1>
						    <?php } ?>
						    	<div class="info_row">
							        <?php if ( get_sub_field("info_contact_name") ) { ?>
							       		<h1 class="wrap"><?php the_sub_field("info_contact_name"); ?></h1>
							        <?php } ?>
							        <h3 class="wrap"><?php the_sub_field("info_contact_address"); ?></h3>
							    </div>
						<?php 
							$i++;
						    endwhile;
						endif; ?>
					</div>

					<div id="info_colophon" class="">
						<!-- COLOPHON -->
						<?php if( have_rows("info_colophon") ):
						    while ( have_rows("info_colophon") ) : the_row(); ?>
						    	<div class="info_row">
							        <h1 class="wrap"><?php the_sub_field("info_colophon_title"); ?></h1>
							        <h3 class="wrap"><?php the_sub_field("info_colophon_name"); ?></h3>
							    </div>
						<?php
						    endwhile;
						endif; ?>
					</div>

				</div>

			</div><!-- end of #info_right -->

		</div><!-- end of #info -->

	<?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>