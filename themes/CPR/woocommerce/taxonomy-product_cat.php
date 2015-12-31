<!-- COLLECTION PAGE -->

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header();
get_sidebar();
// get collection slug for data-collection attribute
global $post;
$cat = get_the_terms($post->id, 'product_cat');
?>

	<span class="sketch"><!-- Collection Page --></span>

<div class="page page_collection" data-collection="<?php echo $cat[0]->slug; ?>">

	<?php if ( have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php wc_get_template_part( 'content', 'product' ); ?>
				
			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

		<?php wc_get_template( 'loop/no-products-found.php' ); ?>

	<?php endif; ?>

</div>