<!-- TEMPLATE FOR WOOCOMMERCE PAGES -->
<?php get_header(); ?>
<?php get_sidebar(); ?>
	
	<!-- LOADING -->
	<div id="loading">
		<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
	</div>

	<?php 
	$title = strtolower( get_the_title() );
	?>

	<div class="page page-<?php echo $title; ?>">
		<?php while ( have_posts() ) : the_post();
			the_content();
		endwhile; ?>
	</div>
	
<?php get_footer(); ?>