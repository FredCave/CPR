<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header();
$nav_bg = true;
get_sidebar();
?>

<!-- COLLECTION FILTER -->
<?php product_filter(); ?>
<!-- END OF COLLECTION FILTER -->

<div class="single_page">

	<!-- MAIN IMAGE + INFO ROW -->

	<div class="single_product">

		<?php while ( have_posts() ) : the_post(); ?>

				<!-- CONTENT HERE -->

				<?php wc_get_template_part( 'content', 'single-product' ); ?>

				<meta itemprop="url" content="<?php the_permalink(); ?>" />

				<?php do_action( 'woocommerce_after_single_product' ); ?>

		<?php endwhile; // end of the loop. ?>

	<!-- RELATED ITEMS -->

	<?php 
	global $post;
	$this_id = $post->ID;
	$post_info = get_post_meta( $this_id, "other_item" );
	if ( $post_info[0] !== "" ) {
	    $post_id = $post_info[0][0];
	    /* LOOP */
	    $args = array( 
			'post_type' => 'product',
			'p' => $post_id
	    );
		$other_query = new WP_Query( $args );
		if ( $other_query->have_posts() ) :
			while ( $other_query->have_posts() ) : $other_query->the_post();
				
				wc_get_template_part( 'content', 'single-product-info' );

			endwhile;
		endif;
	}
	?>

	</div><!-- end of #single_product -->

	<!-- PARENT COLLECTION -->

	<div id="single_collection" class="page_collection collection">

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

</div><!-- END OF .SINGLE_PAGE -->
   
<?php get_footer( ); ?>