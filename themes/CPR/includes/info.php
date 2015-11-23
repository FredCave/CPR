<!-- ABOUT, NEWS, CONTACT -->

<div id="info_wrapper">

	<!-- NEWS -->
	<section id="about">
		ABOUT
		<?php 
		$args = array (
		    'name' => 'about'
		);
		$news_query = new WP_Query( $args );
		if ( $news_query->have_posts() ) :
		    while ( $news_query->have_posts() ) : $news_query->the_post(); ?>

	            <h1><?php the_title(); ?></h1>
	            <div><?php the_content(); ?></div>
		        
		<?php endwhile;
		endif; ?>		
	</section>

	<!-- CONTACT -->
	<section id="contact">
		CONTACT
	</section>

	<!-- NEWS -->
	<section id="news">
		NEWS
		<?php 
		$args = array (
		    'post_type' => 'news'
		);
		$news_query = new WP_Query( $args );
		if ( $news_query->have_posts() ) :
		    while ( $news_query->have_posts() ) : $news_query->the_post(); ?>

	            <h1><?php the_title(); ?></h1>
	            <p class="news_date"><?php echo the_time("d-m-Y"); ?></p>
	            <div><?php the_content(); ?></div>

		<?php endwhile;
		endif; ?>
	</section>

</div>