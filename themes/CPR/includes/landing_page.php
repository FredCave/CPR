<?php
	function get_landing_page () {
		$args = array(
	        'name' => 'landing-page'
	    );
	    $the_query = new WP_Query( $args );
	    if ( $the_query->have_posts() ) :
	        while ( $the_query->have_posts() ) : $the_query->the_post();
				if ( have_rows("landing_row") ) : 
					$i = 0;
					while ( have_rows("landing_row") ) : the_row();					
						$link = get_sub_field('landing_link')[0];
						$image = get_sub_field('landing_image');
						if( !empty($image) ): 
				            $thumb = $image['sizes'][ "thumbnail" ]; // 300
				            $medium = $image['sizes'][ "medium" ]; // 600
				            $large = $image['sizes'][ "large" ]; // 800
				            $extralarge = $image['sizes'][ "extra-large" ]; // 1024
				            $full = $image['url']; 
				            ?>
					        <li class="">
						        <a href="<?php echo get_permalink( $link ); ?>">
							        <div class="picturefill-background">
									    <span data-src="<?php echo $thumb; ?>"></span>
									    <span data-src="<?php echo $medium; ?>" data-media="(min-width: 300px)"></span>
									    <span data-src="<?php echo $large; ?>" data-media="(min-width: 600px)"></span>
									    <span data-src="<?php echo $extralarge; ?>" data-media="(min-width: 800px)"></span>
									    <?php /* <span data-src="<?php echo $full; ?>" data-media="(min-width: 1480px)"></span> */ ?>
									</div>	
									<div class="landing_page_title">
										<?php the_sub_field('landing_title'); ?>
									</div>	
								</a>
							</li>	
						<?php
						endif;
						$i++;	 			   
					endwhile;
				endif; 
			endwhile;
		endif;
	}
?>

<div id="landing_page">

	<!-- LANDING PAGE CONTENT -->
	<ul>
		<?php get_landing_page(); ?>
	</ul>

	<!-- LANDING PAGE SCROLL DOWN -->
	<div id="landing_page_access">
		<a href="">Store</a>
	</div>

</div>