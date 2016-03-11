<?php

@ini_set( 'upload_max_size' , '64M' );

@ini_set( 'post_max_size', '64M');

@ini_set( 'max_execution_time', '300' );


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
//    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js');
    wp_register_script( 'jquery', get_template_directory_uri() . '/js/jquery.min.js');
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

// ADD COLUMN IN PRODUCT TABLE

add_filter( 'manage_edit-product_columns', 'cpr_show_product_order', 15 );
function cpr_show_product_order ($columns) {

   //remove columns
   unset( $columns['featured'] );
   unset( $columns['product_type'] );

   //add column
   $columns['related'] = __( 'Related Posts'); 

   return $columns;
}

add_action( 'manage_product_posts_custom_column', 'cpr_product_column_related', 10, 2 );

function cpr_product_column_related ( $column, $postid ) {
    if ( $column == 'related' ) {
        $post_info = get_post_meta( $postid, "other_item" );
        if ( $post_info ) {
            if ( $post_info[0] !== "" ) {
                $post_title = get_the_title( $post_info[0][0] );
                echo $post_title;
            }
        }    
    }
}

// PRODUCT FILTER

function product_filter () {
    if ( is_page( "wholesale" ) ) : ?>
        <ul id="collection_filter" data-page="wholesale">
    <?php else : ?>
        <ul id="collection_filter" data-page="collection">
    <?php endif;
        $tags = get_terms ( "product_tag", "orderby=name" ); 
        foreach ( $tags as $tag ) { 
            //  var_dump($tag);
            ?>
            <li><a class="filter" id="<?php echo $tag->slug; ?>" href=""><?php echo $tag->name; ?></a><img class="clear_filter" src="<?php bloginfo( 'template_url' ); ?>/img/filter_clear.png" /></li>
        <?php } ?>
        </ul>
    <?php
}

// ORDER PRODUCT CATALOGUE BY SKU

// add_filter('woocommerce_get_catalog_ordering_args', 'am_woocommerce_catalog_orderby');
// function am_woocommerce_catalog_orderby( $args ) {
//     if(!$_GET['orderby']) {
//         $args['meta_key'] = '_sku';
//         $args['orderby'] = 'meta_value';
//         $args['order'] = 'asc'; 
//     }
//     return $args;
// }

// add_filter('woocommerce_get_catalog_ordering_args', 'am_woocommerce_catalog_orderby');
// function am_woocommerce_catalog_orderby( $args ) {
//     $args['orderby'] = 'meta_value';
//     $args['order'] = 'asc';
//     $args['meta_key'] = 'sku'; 
//     return $args;
// }   

// GET RELATED ITEMS — OTHER COLOURS

function other_colours ( $the_id ) {
    // var_dump( $the_id );

    // GET SKU OF CURRENT PRODUCT
    $product = wc_get_product( $the_id );
    $this_sku = $product->get_sku();
    // GET STUB OF SKU
    $stubs = explode("-", $this_sku);
    $stub = $stubs[0];
    // echo $stub;
    
    // LOOP THROUGH PRODUCTS
    $args = array(
        'post_type' => 'product'
    );
    $sku_query = new WP_Query( $args );
    if ( $sku_query->have_posts() ) :
        while ( $sku_query->have_posts() ) : $sku_query->the_post();
            global $product;
            $loop_sku = $product->get_sku();
            $loop_id = $product->id;
            $loop_stubs = explode("-", $loop_sku);
            $loop_stub = $loop_stubs[0];
            // echo $loop_stub . ", " . $stub . "<br>";
           
            if ( $loop_stub === $stub && $loop_sku !== $this_sku ) {
                // GET LINK 
                /*
                $loop_title = get_the_title();
                switch ( true ) {
                    case stristr( $loop_title, "orange" ):
                        $colour = "Orange";
                        break;
                    case stristr( $loop_title, "navy" ):
                        $colour = "Navy";
                        break;
                    case stristr( $loop_title, "whisper" ):
                        $colour = "Whisper White";
                        break;
                    case stristr( $loop_title, "anthracite" ):
                        $colour = "Anthracite";
                        break;
                    case stristr( $loop_title, "cognac" ):
                        $colour = "Cognac";
                        break;
                    case stristr( $loop_title, "grey" ):
                        $colour = "Grey / White";
                        break;
                    case stristr( $loop_title, "moonbeam" ):
                        $colour = "Moonbeam / White";
                        break;
                }  
                */
                ?>
                <li class="wrap no_break other_colours"><a href="<?php echo get_permalink( ); ?>"><?php echo get_the_title(); ?></a></li>

               

            <?php
                // echo $loop_id;
            } else {
                // echo "nothing ";
            }
            
        endwhile;
    endif;
    wp_reset_postdata();

} 

// PRICING ON SINGLE PRODUCT INFO

function get_prices ( $the_id ) {
    /* DEBUGGING
    $meta = get_post_meta( $the_id );
    //print_r( $meta );
    */

    $price = get_post_meta( $the_id, '_regular_price');
    $wholesale_price = get_post_meta( $the_id, '_wholesale_price');    
    if (is_user_logged_in()){
        // BOTH PRICES ARE SHOWN
        // return "<li class='wrap no_break'>Retail Price: " . $price[0] . "</li><li class='wrap no_break'>Wholesale Price: " . $wholesale_price[0] . "</li>";
        // return "<li class='wrap no_break'>Retail Price: " . $price[0] . "€</li>";
    }

}

add_filter( 'woocommerce_get_price_html', 'cpr_price_html', 100, 2 );
function cpr_price_html( $price, $product ){
    return "<div class='wrap no_break'>Price: " . str_replace ( "<span class='amount'>", "", $price ) . "</div>";
}

// MAKE PRODUCTS VARIABLE BY DEFAULT

// function cpr_default_product_type(){
//     return "variable";
// }
// add_action( 'default_product_type', 'cpr_default_product_type' );

?>