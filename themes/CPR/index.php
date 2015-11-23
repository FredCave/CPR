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
		<!-- COLLECTION NAV -->
		<?php include("includes/collections.php"); ?>
	</div>

	<div id="col_4" class="col">
		<!-- COLLECTION GRID VIEW -->
	</div>

</div><!-- end of #wrapper -->

<?php get_footer(); ?>