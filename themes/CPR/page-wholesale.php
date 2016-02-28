<?php 
// Redirects if user not logged in
if (!is_user_logged_in()) {
   wp_redirect( home_url() . "/my-account" ); 
   exit;
} 
get_header(); 
get_sidebar(); ?>

	<div id="wholesale" class="">
	
		<!-- COLLECTION FILTER -->
		<?php product_filter(); ?>
		<!-- END OF COLLECTION FILTER -->

		<!-- LOOP THROUGH CATEGORIES -->
	    <?php 
	    $args = array(
	        "taxonomy"			=> "product_cat",
	        "orderby"			=> "id",
			"order"				=> "desc",
			"hide_empty" 		=> 1
	    );
	    $cats = get_categories( $args );
	    foreach ($cats as $cat) { ?>
	
	    	<div class="wholesale_cat">
	
		    	<h4 class="news_date wrap"><?php echo $cat->name; ?></h4>
		    	
		    	<!-- LOOP THROUGH ITEMS -->
		    	<?php
		    	$this_cat = $cat->term_id; 	
		    	$args = array(
					"post_type" => "product",
					"tax_query" => array(
						array(
							'taxonomy' 	=> "product_cat",
							'field'    	=> "term_id",
							'terms'    	=> $this_cat,
							'orderby'	=> 'meta_value',
							'meta_key' 	=> '_sku',
							'order'		=> 'ASC'
						),
					)
				);
				$the_query = new WP_Query( $args );
				if ( $the_query->have_posts() ):
					while ( $the_query->have_posts() ) : $the_query->the_post(); 
						// CHECK IF HAS PRICE
						global $product;
						if ( $product->price ) : ?>
				
							<?php wc_get_template_part( 'content', 'single-product' ); ?>

							<meta itemprop="url" content="<?php the_permalink(); ?>" />

						<?php endif; ?>

				<?php
					endwhile;
				endif; 
				wp_reset_postdata(); ?>
	    	
	    	</div><!-- end of .wholesale_cat -->
	    
	    <?php } ?><!-- end of foreach loop -->

	</div><!-- end of #wholesale -->

<?php get_footer(); ?>