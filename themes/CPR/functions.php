<?php

function enqueue_cpr_scripts() {
  
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js');
    wp_enqueue_script( 'jquery' );  
    
    //wp_enqueue_script('jquery', dirname( get_stylesheet_uri() ) . '/js/jquery.js' );

    //wp_enqueue_script('jquery', dirname( get_stylesheet_uri() ) . '/js/jquery.js' );  
    wp_enqueue_script('custom', dirname( get_stylesheet_uri() ) . '/js/custom.js', array('jquery'), true);

}
add_action('wp_enqueue_scripts', 'enqueue_cpr_scripts');

// Add custom post types
/*
add_action( 'init', 'create_post_types' );
function create_post_types() {
  register_post_type( 'projects',
    array(
      'labels' => array(
        'name' => __( 'Projects' ),
        'singular_name' => __( 'Project' )
      ),
      'public' => true,
      'has_archive' => true,
      'supports' => array('editor','title'),
      'taxonomies' => array('post_tag'),
	  'menu_position' => 5
    )
  );
  register_post_type( 'dates',
    array(
      'labels' => array(
        'name' => __( 'Time' )
      ),
      'public' => true,
      'has_archive' => true,
      'supports' => array('editor','title'),
	  'menu_position' => 6
    )
  );

}
*/

?>