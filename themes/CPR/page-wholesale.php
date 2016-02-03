<?php 
// Redirects if user not logged in
if (!is_user_logged_in()) {
   wp_redirect( home_url() . "/my-account" ); 
   exit;
} 
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>

	<div id="wholesale" class="page">

		<?php $args = array(
			"post_type" => "product"
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ):
			while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
			
				<div class="wholesale_item">
					<?php the_title(); ?>
				</div>

		<?php
			endwhile;
		endif; ?>

	</div><!-- end of #wholesale -->

<?php get_footer(); ?>