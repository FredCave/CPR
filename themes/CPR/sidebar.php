<?php
	/* QUERY FOR COLLECTIONS LIST */
	$args = array(
        'taxonomy'			=> 'product_cat',
        'orderby'			=> 'id',
		'order'				=> 'desc',
		"hide_empty" 		=> 0
    );
	$all_cats = get_categories( $args );
	$cols = 0;
	foreach ( $all_cats as $cat ) {  	
		if ( get_field( "cat_visible", "product_cat_" . $cat->term_taxonomy_id ) ) {
			$cols++;
		}
	}
?>

<!-- FIXED NAV BG -->
<div id="nav_bg">
</div>

<!-- PRIMARY NAV -->
<div id="nav" class="<?php if ( !is_product() && !is_product_category() && !is_front_page() ) { echo "nav_bg"; } ?>" >

	<ul class="">

		<!-- HOME LINK -->
		<li id="nav_home" class="">
			<a href="<?php bloginfo( 'url' ); ?>/">
				Can Pep Rey
			</a>
		</li>

		<span id="nav_dropdown" class="hidden">

			<!-- COLLECTIONS -->
			<li class="nav_collection wrap" data-length="<?php echo $cols; ?>"><a href="">Collections</a></li>

			<?php foreach ( $all_cats as $cat ) { 
		    	if ( get_field( "cat_visible", "product_cat_" . $cat->term_taxonomy_id ) ) { ?>
		    		<li id="<?php echo $cat->slug; ?>" class="nav_collection_2 nav_hidden">
		    			<a data-href="<?php bloginfo( 'url' ); ?>/collection/<?php echo $cat->slug; ?>"><?php echo $cat->name; ?></a>
		    		</li>
		    <?php } 
		    } ?>

			<!-- NEWS -->
			<li class="wrap"><a href="<?php bloginfo( 'url' ); ?>/_news/">News</a></li>

			<!-- INFORMATION -->
			<li class="wrap"><a href="<?php bloginfo( 'url' ); ?>/_information/">Information</a></li>

			<!-- SOCIAL MEDIA -->
			<li id="nav_share">
				<span>
					<a target="_blank" href="https://www.facebook.com/canpeprey/">
						<img class="" src="<?php bloginfo('template_url'); ?>/img/facebook_icon.svg" />
					</a>
				</span>
				<span id="newsletter_wrapper">
					<?php mailchimp_form(); ?>
				</span>
				<span>
					<a target="_blank" href="https://www.instagram.com/canpeprey/">
						<img class="" src="<?php bloginfo( 'template_url' ); ?>/img/instagram_icon.svg" />
					</a>
				</span>
			</li>

		</span><!-- end of .nav_dropdown -->

	</ul>
</div>

<!-- PRIMARY NAV CLOSE NAV -->
<div id="nav_close">
	<img src="<?php bloginfo( 'template_url' ); ?>/img/filter_clear.png" />
</div>

<!-- SECONDARY NAV -->
<div id="secondary_nav" class="<?php if ( is_front_page() ) { echo "hide"; } ?>">
	<ul>
		<!-- FILTER -->
		<?php if ( is_product_category() || is_front_page() ) { ?>
			<li id="secondary_filter">
				<span id="filter_toggle">Filter</span>
				<img class="clear_filter" src="<?php bloginfo( 'template_url' ); ?>/img/filter_clear.png" />
			</li>	
		<?php } else { ?>
			<li id="secondary_filter">
				<span id="filter_toggle" class="hide_filter">Filter</span>
				<img class="clear_filter" src="<?php bloginfo( 'template_url' ); ?>/img/filter_clear.png" />
			</li>
		<?php } ?>

		<!-- ACCOUNT -->	
		<li id="secondary_account"><a href="<?php bloginfo( 'url' ); ?>/my-account/">Wholesale</a></li>	

		<!-- CART -->
		<li id="secondary_cart">
			<div id="cart_container">
			    <a class="" href="<?php echo WC()->cart->get_cart_url(); ?>" title="<?php _e( 'View your shopping cart' ); ?>">
			        <?php 
			        echo WC()->cart->cart_contents_count . " / " . WC()->cart->get_cart_total(); ?>
			    </a> 
			</div>
		</li>
	</ul>
</div>


