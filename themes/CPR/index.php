<?php get_header(); ?>

<?php get_sidebar(); ?>

<!-- LOADING -->
<div id="loading">
	<img src="<?php bloginfo( 'template_url' ); ?>/img/loading.gif" />
</div>

<div id="home" class="page" data-collection="collection">

	<!-- LANDING PAGE -->
	<?php include("includes/landing_page.php"); ?>

	<!-- COLLECTION -->
	<?php include("includes/front_loop.php"); ?>

</div><!-- END OF #HOME -->	
	    
<?php get_footer(); ?>