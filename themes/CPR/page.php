<!-- TEMPLATE FOR WOOCOMMERCE PAGES -->

<?php get_header(); ?>
<?php get_sidebar(); ?>

		<?php while ( have_posts() ) : the_post();
			the_content();
		endwhile; ?>

<?php get_footer(); ?>