
<!-- SHOWS LATEST COLLECTION -->
<?php 
	// GET ALL COLLECTIONS
	$args = array(
	    'taxonomy'			=> 'product_cat',
	    'orderby'			=> 'id',
		'order'				=> 'desc',
		'hide_empty'		=> 0
	);
	$all_cats = get_categories( $args );
	// CHECK IF SHOULD BE VISIBLE ON FRONT PAGE OR NOT
	$term;
	foreach ( $all_cats as $cat ) {  	
		if ( get_field( "cat_visible", "product_cat_" . $cat->term_taxonomy_id ) ) {
			$term = $cat->slug;
			break;
		}
	}
	$args_2 = array(
	    'post_type' => 'product',
	    'taxonomy' => 'product_cat',
	    'field' => 'slug',
	    'term' => $term, 
		'orderby' => 'rand'
	    );
	$the_query = new WP_Query( $args_2 ); 
?>

<!-- COLLECTION FILTER -->
<?php product_filter(); ?>

<div class="collection front_collection" data-collection="">
	<ul>
		<?php if ( $the_query->have_posts() ) : ?>

			<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

				<?php wc_get_template_part( 'content', 'product' ); ?>
				
			<?php endwhile; ?>

		<?php endif; ?>
	</ul>
</div>