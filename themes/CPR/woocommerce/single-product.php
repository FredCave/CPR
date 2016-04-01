<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
get_sidebar();
?>

<!-- COLLECTION FILTER -->
<?php product_filter(); ?>

<!-- LOADING -->
<div id="loading">
	<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
</div>

<div id="single_page" class="single_page page">

	<!-- MAIN IMAGE + INFO ROW -->

	<div class="single_product">
		
		<?php while ( have_posts() ) : the_post(); ?>

			<!-- CONTENT HERE -->
			<?php $single_id = get_the_ID(); ?>
			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; ?>

		<!-- RELATED ITEMS -->
		<?php 
		related_items( $single_id );
		?>

	</div><!-- END OF .SINGLE_PRODUCT -->

	<!-- COLLECTION -->
	<?php parent_collection( $single_id ); ?>

</div><!-- END OF .SINGLE_PAGE -->
   
<?php get_footer( ); ?>