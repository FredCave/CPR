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

				<div id="info_stockists">

					<h1 class="wrap">Stockists</h1>
					<!-- STOCKISTS -->
					<?php if( have_rows("info_stockists") ):
					    $left = true;
					    while ( have_rows("info_stockists") ) : the_row();			        
							if ( $left ) {
								$left = false;
								echo "<div class='sub_col_left sub_col'>";
							} else {
								$left = true;
								echo "<div class='sub_col_right sub_col'>";
							}
							?>
					        <h2 class="wrap"><?php the_sub_field("info_stockists_country"); ?></h2>
					        <?php
					        if ( have_rows("info_stockists_names") ) {
								while ( have_rows("info_stockists_names") ) : the_row(); 
								
								?>
									<p class="wrap"><?php the_sub_field("info_stockists_name"); ?></p>

								<?php endwhile;
					        }
							
							echo "</div>";
					    endwhile;
					endif; ?>
				</div>

				<!-- IMAGE??? -->

				<div id="info_contact" class="hide">
					<!-- CONTACT -->
					<?php if( have_rows("info_contact") ):
					    while ( have_rows("info_contact") ) : the_row();
					        the_sub_field("info_contact_name");
					        the_sub_field("info_contact_address");
					    endwhile;
					endif; ?>
				</div>

				<div class="hide">
					<!-- COLOPHON -->
					<?php if( have_rows("info_colophon") ):
					    while ( have_rows("info_colophon") ) : the_row();
					        the_sub_field("info_colophon_title");
					        the_sub_field("info_colophon_name");
					    endwhile;
					endif; ?>
				</div>

				<div>
					<!-- SOCIAL MEDIA LINKS -->
					<?php if( have_rows("info_social_media") ):
					    while ( have_rows("info_social_media") ) : the_row();
					        
					    endwhile;
					endif; ?>
				</div>

			</div><!-- end of #info_right -->

		</div><!-- end of #info -->

	<?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>