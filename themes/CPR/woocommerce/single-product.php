<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header();
get_sidebar();
?>

<div class="single_product">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php
			/**
			 * woocommerce_before_single_product hook
			 *
			 * @hooked wc_print_notices - 10
			 */
			 // do_action( 'woocommerce_before_single_product' );

			 // if ( post_password_required() ) {
			 // 	echo get_the_password_form();
			 // 	return;
			 // }
		?>

		<?php /* <div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" > */ ?>

			<!-- CONTENT HERE -->

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

			<!-- FOLLOWING HAS BEEN REMOVED -->

			<?php remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 ); ?>

			<?php
				/**
				 * woocommerce_before_single_product_summary hook
				 *
				 * @hooked woocommerce_show_product_sale_flash - 10
				 * @hooked woocommerce_show_product_images - 20
				 */
				// do_action( 'woocommerce_before_single_product_summary' );
			?>

			<div class="summary entry-summary">

				<?php remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 ); ?>

				<?php
					/**
					 * woocommerce_single_product_summary hook
					 *
					 * @hooked woocommerce_template_single_title - 5
					 * @hooked woocommerce_template_single_rating - 10
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 * @hooked woocommerce_template_single_add_to_cart - 30
					 * @hooked woocommerce_template_single_meta - 40
					 * @hooked woocommerce_template_single_sharing - 50
					 */
					//do_action( 'woocommerce_single_product_summary' );
				?>

			</div><!-- .summary -->

			<?php
				/**
				 * woocommerce_after_single_product_summary hook
				 *
				 * @hooked woocommerce_output_product_data_tabs - 10
				 * @hooked woocommerce_upsell_display - 15
				 * @hooked woocommerce_output_related_products - 20
				 */
				// do_action( 'woocommerce_after_single_product_summary' );
			?>

			<meta itemprop="url" content="<?php the_permalink(); ?>" />

		<?php /* </div><!-- #product-<?php the_ID(); ?> --> */ ?>

		<?php do_action( 'woocommerce_after_single_product' ); ?>

		<?php

			
		?>

		<?php endwhile; // end of the loop. ?>

</div><!-- end of #single_product -->

<div id="single_collection" class="page_collection">

	<?php
	/* GET THIS PRODUCT CATEGORY */ 
	global $post;
	$terms = get_the_terms( $post->ID, 'product_cat' );
	foreach ($terms as $term) {
	    $this_cat = $term->slug;
	    break;
	}
	$args = array(
        'post_type' => 'product',
		"tax_query" => array(
			array(
				'taxonomy' => "product_cat",
				'field'    => "slug",
				'terms'    => $this_cat
			),
		)
        );
    $the_query = new WP_Query( $args ); 
	?>

	<!-- COLLECTION LOOP -->

	<ul>
		<?php	
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post(); ?>
					<?php wc_get_template_part( 'content', 'product' ); ?>
				<?php	
			}
		} 
		wp_reset_postdata();	
	    ?>
	</ul>

</div>
   
<?php get_footer( ); ?>