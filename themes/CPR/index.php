<?php get_header(); ?>

<?php get_sidebar(); ?>

	<!-- SHOWS LATEST COLLECTION -->
    <?php 
    	/* GET SLUG OF LATEST COLLECTION */
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
        'term' => $latest[1]->slug, /* WHY 1 NOT 0 ??? */
		'orderby' => 'rand'
        );
    $the_query = new WP_Query( $args2 ); ?>

	<ul id="collection_filter">
		<?php 
		$tags = get_terms ( "product_tag", "orderby=name" ); 
	    foreach ( $tags as $tag ) { ?>
			<li><a href=""><?php echo $tag->name; ?></a></li>
		<?php } ?> 
	</ul>

	<div id="home" class="page page_collection" data-collection="<?php echo $latest[1]->slug; ?>">
		
		<ul>
		<?php	
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post(); 
                ?>
				<?php wc_get_template_part( 'content', 'product' ); ?>
			<?php 
			}
		} 
		wp_reset_postdata();	
	    ?>
		</ul>
	</div>
	    
<?php get_footer(); ?>