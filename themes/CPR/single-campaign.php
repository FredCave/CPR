<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div class="page">

		<!-- TITLE -->
		<div class="campaign_title">
			<h4 class="wrap">Campaign <?php the_title(); ?></h4>
		</div>

		<!-- IMAGE -->
		<div class="campaign_image">
			
		</div>	

		<!-- BACK LINK -->	
		<a href="<?php bloginfo('url'); ?>/_news">
			<div class="campaign_title">
				<h4 class="wrap">Back to News</h4>
			</div>
		</a>

	</div>

<?php get_footer(); ?>
