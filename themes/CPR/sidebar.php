<!-- NAVIGATION -->
<div id="nav">
	<ul>
		<li>Can Pep Rey</li>
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
			echo "<li>";
			echo $cat->name;
			echo "</li>";
		} ?>

		</li>
		<li>News</li>
		<li>Information</li>
	</ul>
</div>