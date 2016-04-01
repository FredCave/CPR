<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="single_info_wrapper">

	<div class="single_images_wrapper">

		<div class="gallery_arrow">
			<img src="<?php bloginfo('template_url'); ?>/img/gallery_arrow_thin.svg" />
		</div>

		<div class="single_images_left">
			<?php if ( have_rows("product_images") ):				
				$i = 0; 
				while ( have_rows("product_images") ) : the_row();
					// ECHO JUST FIRST IMAGE
					if ( $i === 0 ) {
						$image = get_sub_field("product_image");
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
							class="lazyload single_additional_image position_<?php echo $position; ?>" 
							/>		
						<?php
						endif;
					} 
					$i++;			        
				endwhile;
			endif; ?>
		</div>

		<div class="single_images_right">
			<?php if ( have_rows("product_images") ):				
				$i = 0; 
				while ( have_rows("product_images") ) : the_row();
					$image = get_sub_field("product_image");
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
						class="lazyload single_additional_image position_<?php echo $position; ?>" 
						/>		
					<?php
					endif;		        
				endwhile;
			endif; ?>
		</div>

		<?php /* if ( have_rows("product_images") ):				
			$i = 0; 
			while ( have_rows("product_images") ) : the_row();
				// FIRST IMAGE ON LEFT 
				// if ( $i === 0 ) {
				// 	$position = "left";
				// } else {
				// 	$position = "right";
				// }
				$i++;
				$image = get_sub_field("product_image");
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
					class="lazyload single_additional_image position_<?php echo $position; ?>" 
					/>		
				<?php
				endif;	        
			endwhile;
		endif; */ ?>

	</div>

	<div class="single_info info_justified">

		<?php 
			global $product;
			$product = wc_get_product ( get_the_ID() );
			$product_id = get_the_ID();
		?>
				
		<ul>
			<!-- TITLE -->
			<li class="product_title"><?php the_title(); ?></li>
			
			<!-- PRODUCT DESCRIPTION -->
			<div class="product_desc_toggle">
				<div class="product_desc_click wrap">Description</div>
				<div class="product_desc">
					<!-- SKU -->
					<li class="" style="text-align:center"><?php echo $product->sku; ?></li>
					<!-- FABRIC INFO -->
					<li class="">				
						<?php 
							// PUT BACK SPACES IN FABRIC INFO
							$infoStr = get_field("product_info");
							addSpaces( $infoStr ); 			
						?>
					</li>
					<!-- MAIN DESCRIPTION -->
					<li class="">
						<?php the_field("product_description"); ?>
					</li>
				</div>
			</div>
	    	
	    	<!-- LINKS TO OTHER COLOURS -->
	    	<li class="other_colours_wrapper">
		    	<ul><?php /*echo other_colours( $product_id, false );*/ ?></ul>
		    </li>
	    				
			<?php 
			// RESET THE GLOBAL PRODUCT AFTER OTHER COLOURS LOOP
			// global $product;
			// $product = wc_get_product ( $product_id );
			// OUT OF STOCK 
			if ( ! $product->is_in_stock() ) { ?>
			    <li class="">
			    	<?php echo "Out of Stock"; ?>
			    </li>
			<?php } else {
				remove_action( "woocommerce_single_product_summary", "woocommerce_template_single_title", 5 );
				remove_action( "woocommerce_single_product_summary", "woocommerce_template_single_meta", 40 );

				do_action( 'woocommerce_single_product_summary' );	
			}
			?>
			
		</ul>

	</div><!-- END OF .SINGLE_INFO -->	

</div><!-- END OF .SINGLE_INFO_WRAPPER -->
