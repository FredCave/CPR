<!-- <div id="single_collection" class="page_collection collection">

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

	<!-- COLLECTION LOOP 

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

</div> -->