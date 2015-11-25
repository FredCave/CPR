<?php get_header(); ?>

<div id="wrapper" data-position="centre">

	<div id="col_1" class="col">
		<!-- NEWS, ABOUT, CONTACT -->
		<?php include("includes/info.php"); ?>
	</div>

	<div id="col_2" class="col">
		<!-- MENU -->
		<?php get_sidebar(); ?>
	</div>

	<div id="col_3" class="col">
		<!-- BACK TO MENU -->
		<div id="back">
			<a href="">BACK</a>
		</div>
		<!-- COLLECTION NAV -->
		<?php include("includes/collections.php"); ?>
		<!-- SINGLE AJAX WRAPPER -->
		<div id="single_ajax_wrapper"></div>
	</div>

	<div id="col_4" class="col">		
		<!-- COLLECTION AJAX WRAPPER -->
		<div id="loading_bar">
			<div class="loading"></div>
		</div>
		<div id="coll_ajax_wrapper"></div>
	</div>

</div><!-- end of #wrapper -->

<?php get_footer(); ?>