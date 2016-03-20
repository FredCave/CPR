<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
get_header();
get_sidebar();

// GET COLLECTION SLUG FOR DATA-COLLECTION ATTRIBUTE
global $post;
$cat = get_the_terms($post->id, 'product_cat');
?>

<!-- COLLECTION FILTER -->
<?php product_filter(); ?>

<!-- LOADING -->
<div id="loading">
	<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
</div>

<div class="collection page_collection page" data-collection="<?php echo $cat[0]->slug; ?>">

	<?php if ( have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php wc_get_template_part( 'content', 'product' ); ?>
				
			<?php endwhile; ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif; ?>

</div><!-- END OF .COLLECTION -->

<?php get_footer( ); ?>