<div id="menu">
	<ul>
		<li><a href="<?php bloginfo("url"); ?>">Can Pep Rey</a></li>
		<li>
			<ul>
				Collections : 
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
					<li><a href="<?php bloginfo("url"); ?>/collection/<?php echo $cat->slug; ?>"><?php echo $cat->name; ?><a/></li>
    			<?php } ?>
			</ul>
		</li>
		<li><a href="<?php bloginfo("url"); ?>/news">News</li>
		<li><a href="<?php bloginfo("url"); ?>/about">About</li>
		<li><a href="<?php bloginfo("url"); ?>/contact">Contact</li>
		<li>
			<a href="<?php bloginfo("url"); ?>/cart">
				<span class="cart_count">Cart <?php echo "(" . WC()->cart->cart_contents_count . ")"; ?></span>
			</a>
		</li>
	</ul>
</div>