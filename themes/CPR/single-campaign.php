<?php get_header(); ?>

<?php get_sidebar(); ?>

	<div class="page">

		<!-- TITLE -->
		<div class="campaign_title wrap">
			Campaign <?php the_title(); ?>
		</div>

		<!-- IMAGE -->
		<div class="campaign_image">
			
		</div>	

		<!-- BACK LINK -->	
		<a href="<?php bloginfo('url'); ?>/_news">
			<div class="campaign_title wrap">
					Back to News	
			</div>
		</a>

	</div>

<?php get_footer(); ?>
