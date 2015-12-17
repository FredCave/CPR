<?php get_header(); ?>

<?php get_sidebar(); ?>

	<!-- SHOWS LATEST COLLECTION -->
    <?php 
    $args = array(
        'taxonomy'			=> 'product_cat',
        'orderby'			=> 'id',
		'order'				=> 'desc',
        'number'			=> '1'
    );
    $latest = get_categories( $args );
	$args2 = array(
        'post_type' => 'product',
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'term' => $latest[0]->slug
        );
    $the_query = new WP_Query( $args2 ); ?>

	<div id="home" class="page">
		<ul>
		<?php	
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				echo "<li>";
				/*
				POST CONTENT HERE 
				*/
				//the_title();
				echo "</li>";
			}
		} 
		wp_reset_postdata();	
	    ?>
		</ul>
	</div>
	    
<?php get_footer(); ?>