<?php
	/* QUERY FOR COLLECTIONS LIST */
	$args = array(
        'taxonomy'			=> 'product_cat',
        'orderby'			=> 'id',
		'order'				=> 'desc'
    );
	$all_cats = get_categories( $args );
?>

<!-- NAVIGATION -->
<div id="nav">
	<ul>
		<li id="nav_home" class="wrap"><a href="<?php bloginfo( 'url' ); ?>/">Can Pep Rey</a></li>

	<?php if ( is_front_page() ) { ?>
		<span id="nav_dropdown" class="front_dropdown">
	<?php } else { ?>
		<span id="nav_dropdown">
	<?php } ?>

		<li class="nav_collection wrap" data-length="<?php echo count( $all_cats ); ?>"><a href="">Collections:</a></li>

		<?php foreach ( $all_cats as $cat ) { ?>
			<li id="<?php echo $cat->slug; ?>" class="nav_collection_2 nav_hidden wrap">
				<a data-href="<?php bloginfo( 'url' ); ?>/collection/<?php echo $cat->slug; ?>"><?php echo $cat->name; ?></a>
			</li>
		<?php } ?>

		<li class="wrap"><a href="<?php bloginfo( 'url' ); ?>/_news/">News</a></li>

		<li class="wrap"><a href="<?php bloginfo( 'url' ); ?>/_information/">Information</a></li>

		</span>

	</ul>
</div>

<div id="secondary_nav">
	<ul>
		<li><a href="<?php bloginfo( 'url' ); ?>/cart/">Cart</a></li>	
		<li><a href="<?php bloginfo( 'url' ); ?>/my-account/">Account</a></li>
		<!-- OPTIONAL ON CATEGORY PAGES -->
		<?php if ( is_product_category() || is_front_page() ) { ?>
			<li>Filter</li>	
		<?php } ?>
		
	</ul>
</div>