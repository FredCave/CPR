<?php

// DEREGISTER WOO STYLESHEETS
function remove_assets() {
    add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
    wp_dequeue_style( 'woocommerce-layout' ); 
    wp_dequeue_style( 'woocommerce-smallscreen' ); 
    wp_dequeue_style( 'woocommerce-general' ); 
}
add_action('wp_print_styles', 'remove_assets', 99999);

// ENQUEUE CUSTOM SCRIPTS
function enqueue_cpr_scripts() {
  
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js');
    wp_enqueue_script( 'jquery' );  
    
    wp_enqueue_script('modernizr', get_template_directory_uri() . '/js/modernizr.js', array('jquery'), true);
    wp_enqueue_script('scrollify', get_template_directory_uri() . '/js/jquery.scrollify.min.js', array('jquery'), true);
    wp_enqueue_script('custom', get_template_directory_uri() . '/js/custom.js', array('jquery'), true);

}
add_action('wp_enqueue_scripts', 'enqueue_cpr_scripts');

// Declare WooCommerce support

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

/*
// Set max. number of products per page (80)
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 80;' ), 20 );

// Ensure cart contents update when products are added to the cart via AJAX 
add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );
function woocommerce_header_add_to_cart_fragment( $fragments ) {
    ob_start();
    ?>
    <span class="cart_count">    
        <?php echo "Cart (" . WC()->cart->cart_contents_count . ")"; ?>
    </span> 
    <?php    
    $fragments['span.cart_count'] = ob_get_clean();
    return $fragments;
}

// Add visible check box to product category view

function add_post_tag_columns($columns){
    // $columns is existing array??
    $columns['public'] = 'Public';
    return $columns;
}
add_filter('manage_edit-product_cat_columns', 'add_post_tag_columns');


/* TO DO: GET CATEGORY DATA (CHECKBOX) TO SHOW IN COLUMN */

/*
function add_post_tag_column_content($content){
    // get wp-categroy-meta result??
    //$content .= 'Bar';
    return $content;
}
add_filter('manage_product_cat_custom_column', 'add_post_tag_column_content');
*/















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
*/

?>