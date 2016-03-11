<?php get_header(); ?>

<?php get_sidebar(); ?>

	<!-- SHOWS LATEST COLLECTION -->
    <?php 
    	/* GET ALL COLLECTIONS */
    $args = array(
        'taxonomy'			=> 'product_cat',
        'orderby'			=> 'id',
		'order'				=> 'desc',
		'hide_empty'		=> 0
    );
    $all_cats = get_categories( $args );
	// echo "<pre>";
 //    var_dump($all_cats);
 //    echo "</pre>";
    // CHECK IF SHOULD BE VISIBLE ON FRONT PAGE OR NOT
    $term;
    foreach ( $all_cats as $cat ) {  	
    	// the_field( "cat_visible", "product_cat_" . $cat->term_taxonomy_id );
    	if ( get_field( "cat_visible", "product_cat_" . $cat->term_taxonomy_id ) ) {
    		$term = $cat->slug;
    		break;
    	}
    }
	$args2 = array(
        'post_type' => 'product',
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'term' => $term, 
		'orderby' => 'rand'
        );
    $the_query = new WP_Query( $args2 ); ?>

	<!-- COLLECTION FILTER -->
	<?php product_filter(); ?>
	<!-- END OF COLLECTION FILTER -->

	<!-- LOADING -->
	<div id="loading">
		<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
	</div>

	<div id="home" class="page page_collection collection" data-collection="<?php /*echo $latest[1]->slug;*/ ?>">
		
		<ul>
		<?php	
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post(); 
				
				if ( have_rows("product_images") ) {
					wc_get_template_part( 'content', 'product' );  
				}			
			}
		} 
		wp_reset_postdata();	
	    ?>
		</ul>
	</div>
	    
<?php get_footer(); ?>