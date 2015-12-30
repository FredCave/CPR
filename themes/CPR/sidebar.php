<!-- NAVIGATION -->
<div id="nav">
	<ul>
		<li><a href="<?php bloginfo( 'url' ); ?>/">Can Pep Rey</a></li>
		<?php
			$args = array(
		        'taxonomy'			=> 'product_cat',
		        'orderby'			=> 'id',
				'order'				=> 'desc'
		    );
			$all_cats = get_categories( $args );
		?>
		<li class="nav_collection" data-length="<?php echo count( $all_cats ); ?>">Collections

		<?php foreach ( $all_cats as $cat  ) {
			echo "<li><a href='" . get_bloginfo( 'url' ) . "/collection/" . $cat->slug . "'>";
			echo $cat->name;
			echo "</a></li>";
		} ?>

		</li>
		<li><a href="<?php bloginfo( 'url' ); ?>/_news/">News</a></li>
		<li><a href="<?php bloginfo( 'url' ); ?>/_information/">Information</a></li>
	</ul>
</div>

<div id="secondary_nav">
	<ul>
		<li>Cart</li>	
		<li>Account</li>
		<!-- OPTIONAL ON CATEGORY PAGES -->
		<li>Filter</li>	
	</ul>
</div>