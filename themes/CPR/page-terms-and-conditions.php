<!-- TEMPLATE FOR WOOCOMMERCE PAGES -->

<?php get_header(); ?>
<?php get_sidebar(); ?>

	<div id="terms" class="page">

		<?php while ( have_posts() ) : the_post();
			the_content();
		endwhile; ?>

	</div>

<?php get_footer(); ?>