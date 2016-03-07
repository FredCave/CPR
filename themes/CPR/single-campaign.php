<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div id="campaign" class="page">

		<div class="campaign_title">
			<h4 class="wrap no_break">Campaign <?php the_title(); ?></h4>
		</div>

		<!-- LEFT COLUMN -->
	   	<div class="info_column">
    		<?php if ( get_field("video") ) {
				the_field("video");
			} ?>
	   	</div>

		<!-- RIGHT COLUMN -->
	   	<div class="info_column">

	   		<ul id="campaign_images" class="gallery">

	   			<div class="gallery_arrow">
					<img src="<?php bloginfo('template_url'); ?>/img/gallery_arrow_thin.svg" />
				</div>

	   		    <?php if ( have_rows("images") ) :
					while ( have_rows("images") ) : the_row();
						$image = get_sub_field("image");
							if( !empty($image) ): 
				            $thumb = $image["sizes"][ "thumbnail" ]; // 300
				            $medium = $image["sizes"][ "medium" ]; // 600
				            $large = $image["sizes"][ "large" ]; // 800
				            $extralarge = $image["sizes"][ "extra-large" ]; // 1024
				            $full = $image["url"];
				            $width = $image["sizes"]["medium-width"];
				            $height = $image["sizes"]["medium-height"];
				        endif; ?>
				    <li class="campaign_image">
						<img sizes="(min-width: 40em) 95vw, 47vw"
							 srcset="<?php echo $thumb; ?> 300w,
									<?php echo $medium; ?> 600w,
									<?php echo $large; ?> 800w,
									<?php echo $large; ?> 2x"
						/>
					</li>
				<?php endwhile;
				endif;
				?>	
	   		</ul>
	   	</div>

	</div>

<?php get_footer(); ?>
