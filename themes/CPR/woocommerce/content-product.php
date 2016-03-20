<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
$classes = array();
// EXTRA CLASS FOR IMAGE PLACEMENT
$classes[] = "selected-product";
?>

<?php 
// REDIRECT LINKS FOR BOTTOM PRODUCTS
if ( get_field( "other_item" ) ) {
	$link_id = get_field( "other_item" )[0]->ID;
} else {
	$link_id = "";
}
// GET POST CLASSES
$all_classes = get_post_class();
// CLASSES TO LOOK FOR
$bottom_classes = array( "product-tag-shorts", "product-tag-leggings", "product-tag-skirts", "product-tag-pants" );
if( count( array_intersect($bottom_classes, $all_classes) ) > 0 ) {
    // IF AT LEAST ONE OF THESE CLASSES IS PRESENT
	$classes[] = "bottom";
}
?>

<?php if ( is_single() ) { 
	$classes[] = "single_product";
} else { 
	$classes[] = "non_single_product";
} ?>

<li <?php post_class( $classes ); ?> data-link="<?php echo $link_id; ?>">
	<a href="<?php the_permalink(); ?>" class="open_single">
		<!-- LOAD FIRST TWO IMAGES FOR HOVER EFFECT -->
		<?php if ( have_rows("product_images") ) : 
			$i = 0;
			while ( have_rows("product_images") ) : the_row();
				if ( $i < 2 ) :				
					$image = get_sub_field('product_image');
					if( !empty($image) ): 
			            $thumb = $image['sizes'][ "thumbnail" ]; // 200x300
			            $medium = $image['sizes'][ "medium" ]; // 400x600
			            $large = $image['sizes'][ "large" ]; // 533x800
			            $extralarge = $image['sizes'][ "extra-large" ]; // 683x1024
			            $width = $image['sizes'][ "thumbnail-width" ]; 
			            $height = $image['sizes'][ "thumbnail-height" ]; 
			            ?>
						<img 
						width="<?php echo $width; ?>"  
						height="<?php echo $height; ?>"  
					    src="<?php echo $thumb; ?>" 
					    data-sizes="auto"
					    data-src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" 
					    data-srcset="<?php echo $thumb; ?> 400w,
					    	<?php echo $medium; ?> 500w, 
							<?php echo $large; ?> 600w,
							<?php echo $extralarge; ?> 800w"   
						class="product_image" 
						/>		
					<?php
					endif; // END OF IF IMAGE		   
				endif;
				$i++;      
			endwhile;
		endif; ?>
	</a>
</li>