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
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js');
    wp_enqueue_script( 'jquery' );  
    
    wp_enqueue_script('all_scripts', get_template_directory_uri() . '/js/scripts.min.js', array('jquery'), true);

}
add_action('wp_enqueue_scripts', 'enqueue_cpr_scripts');

// DECLARE WOOCOMMERCE SUPPORT
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

// ADD CUSTOM POST TYPES
add_action( 'init', 'create_post_types' );
function create_post_types() {
    register_post_type( 'news',
    array(
        'labels' => array(
            'name' => __( 'News' )
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('editor','title'),
        'menu_position' => 5
        )
    );
    register_post_type( 'campaign',
    array(
        'labels' => array(
            'name' => __( 'Campaigns' )
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('editor','title'),
        'menu_position' => 6
        )
    );
}

// ADD CUSTOM IMAGE SIZE

add_image_size( 'extra-large', 1024, 1024 ); 

// Set max. number of products per page (80)
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 80;' ), 20 );

// REMOVE ACTIONS

    /* COLLECTION PAGE — REMOVE SALE FLASH */
remove_action( "woocommerce_before_shop_loop_item_title", "woocommerce_show_product_loop_sale_flash", 10 );

    /* COLLECTION PAGE — REMOVE ADD TO CART */
remove_action( "woocommerce_after_shop_loop_item", "woocommerce_template_loop_add_to_cart", 10 );

// ADD COLUMN IN PRODUCT CATEGORY TABLE

add_filter( 'manage_edit-product_cat_columns', 'show_product_order', 15 );
function show_product_order($columns){

   //remove column
   unset( $columns['tags'] );

   //add column
   $columns['visible_col'] = __( 'Visible');  

   return $columns;
}

/************************
    NEEDS CLEANING UP
************************/

add_action('wp_enqueue_scripts', 'cpp_enqueue_scripts');
function cpp_enqueue_scripts() {
    /* Other enqueue/registers */
    wp_register_script('diy_kits', get_template_directory_uri().'/js/diy_kit.js');
    wp_enqueue_script('diy_kits');
    wp_localize_script(
        'diy_kits',
        'cpp_ajax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'diy_product_nonce' => wp_create_nonce('diy_product_nonce')
        )
    );
}

/*
// FUNCTION TO CALCULATE WHICH ITEMS ARE IN CART ON ADD TO CART
function woo_custom_cart_quantities ( $product_id ) { 
    foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
        if( get_the_ID() == $product_id ) {
            return $values['quantity'] . ' — Add more?';
        }
    }
}*/

add_action('wp_ajax_nopriv_cpp_ajax-submit', 'cpp_ajax_submit');
add_action('wp_ajax_cpp_ajax-submit', 'cpp_ajax_submit');
// THIS IS THE DATA SENT
function cpp_ajax_submit() {
    global $woocommerce;

    $nonce = $_POST['nonce'];
    if(!wp_verify_nonce($nonce, 'diy_product_nonce')) {
        wp_die('Busted!');
    }
      
    $product_id = $_POST['product_id'];
    if( $woocommerce->cart->add_to_cart( $product_id ) ) {
        // SUCCESS 
        // $data = apply_filters('woocommerce_add_to_cart_fragments', array());
        // HERE — GET AMOUNT ALREADY ADDED OF ITEM
        $data = woo_custom_cart_quantities ( $product_id );

        do_action('woocommerce_ajax_added_to_cart', $product_id);
    } else {
        // FAILURE
        $data = array( 'success' => false, 'product_id' => $product_id );
    }
    $response = json_encode($data);
    header("Content-Type: application/json");
    echo $response; 
    exit;
}

add_filter('woocommerce_add_to_cart_fragments', 'cpp_header_add_to_cart_fragment');
function cpp_header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;
    ob_start(); ?>
    <a class="cart-contents" href="<?php echo WC()->cart->get_cart_url(); ?>" title="<?php _e( 'View your shopping cart' ); ?>">
        <?php echo WC()->cart->cart_contents_count . "/ " . WC()->cart->get_cart_total(); ?>
    </a>

    <?php
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}


/**
 * Change the add to cart text on single product pages
 */

add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');
function woo_custom_cart_button_text() {
    
    foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
        $_product = $values['data'];
        // print_r($values);
    
        if( get_the_ID() == $_product->id ) {
            //return $values['quantity'] . ' — Add more?';
            return __('Already in Cart — Add More?', 'woocommerce');
        }
    }
    
    return __('Add to cart', 'woocommerce');
}


// EXTRA AJAX CALLS

    // DEFINE AJAX URL VARIABLE

add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() { ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
<?php }

// add_action( 'wp_enqueue_scripts', 'add_ajax_javascript_file' );
// function add_ajax_javascript_file() {
//     wp_localize_script( 'frontend-ajax', 'frontendajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
//     wp_enqueue_script( 'ajax_custom_script', get_bloginfo( 'template_url' ) . '/js/ajax_calls.js', array('jquery') );
// }

add_action( 'wp_ajax_get_post_information', 'ajax_get_post_information' );
add_action( 'wp_ajax_nopriv_get_post_information', 'ajax_get_post_information' );
function ajax_get_post_information() 
{
    if(!empty($_POST['post_id']))
    {
        $post = get_post( $_POST['post_id'] );

        echo json_encode( $post );
    }   

    die();
}

// PRODUCT FILTER

function product_filter () {
    if ( is_page( "wholesale" ) ) : ?>
        <ul id="collection_filter" data-page="wholesale">
    <?php else : ?>
        <ul id="collection_filter" data-page="collection">
    <?php endif;
        $tags = get_terms ( "product_tag", "orderby=name" ); 
        foreach ( $tags as $tag ) { ?>
            <li><a class="filter" href=""><?php echo $tag->name; ?></a><img class="clear_filter" src="<?php bloginfo( 'template_url' ); ?>/img/filter_clear.png" /></li>
        <?php } ?>
        </ul>
    <?php
}

?>