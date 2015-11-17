<div id="front_collections">
	<ul>
		<?php 
        $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'id',
            'order'        => 'asc',
            'hide_empty'   => 0,
            'parent' => 0
        );
		$all_categories = get_categories( $args );
		foreach ($all_categories as $cat) { ?>
			<section data-section-name="<?php echo $cat->slug; ?>">
				<a href="<?php bloginfo("url"); ?>/product-category/<?php echo $cat->slug; ?>">
					<h1><?php echo $cat->name; ?></h1>
				</a>
			</section>
		<?php } ?>
	</ul>
</div>