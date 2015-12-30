<?php get_header(); ?>
<?php get_sidebar(); ?>

		<span class="sketch">Wholesale</span>

		<?php while ( have_posts() ) : the_post();
			the_content();
		endwhile; ?>

<?php get_footer(); ?>