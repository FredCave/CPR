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
    wp_dequeue_style( 'wwof_WholesalePage_css' ); 
    wp_dequeue_style( 'wcqi-css' );   
}
add_action('wp_print_styles', 'remove_assets', 99999);

// ENQUEUE CUSTOM SCRIPTS
function enqueue_cpr_scripts() {
  
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js');
   // wp_register_script( 'jquery', get_template_directory_uri() . '/js/jquery.min.js');
    wp_enqueue_script( 'jquery' );  
    
    wp_enqueue_script('all_scripts', get_template_directory_uri() . '/js/scripts.min.js', array('jquery'), true);

    // wp_register_script( "ajax_test", get_template_directory_uri() . '/js/custom_ajax.js', array('jquery') );
    // wp_localize_script( 'ajax_test', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );        
    // wp_enqueue_script( 'ajax_test' );

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

// ....

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

function product_filter () { ?>
    
    <ul id="collection_filter" data-page="collection">
        <?php $tags = get_terms ( "product_tag", "orderby=name" ); 
        foreach ( $tags as $tag ) { ?>
            <li><a class="filter" id="<?php echo $tag->slug; ?>" href=""><?php echo $tag->name; ?></a><img class="clear_filter" src="<?php bloginfo( 'template_url' ); ?>/img/filter_clear.png" /></li>
        <?php } ?>
    </ul>
    <?php
}

// GET RELATED ITEMS

function related_items ( $the_id ) {
    // GET ID FROM ACF FIELD
    $post_info = get_post_meta( $the_id, "other_item" );
    if ( $post_info[0] !== "" ) {
        $post_id = $post_info[0][0];
        // LOOP 
        $args = array( 
            'post_type' => 'product',
            'p' => $post_id
        );
        $other_query = new WP_Query( $args );
        if ( $other_query->have_posts() ) :
            while ( $other_query->have_posts() ) : $other_query->the_post();
                wc_get_template_part( 'content', 'single-product-info' );
            endwhile;
        endif;
    }
}

// GET OTHER COLOURS

function other_colours ( $the_id, $wholesale ) {
    // $THE_ID = CURRENT PRODUCT
    // GET SKU OF CURRENT PRODUCT
    $this_product = wc_get_product( $the_id );
    $this_sku = $this_product->get_sku();
    // echo $this_sku;
    // GET STUB OF SKU
    $stubs = explode("-", $this_sku);
    $stub = $stubs[0];   
    // LOOP THROUGH PRODUCTS
    $args = array(
        'post_type'     => 'product',
        'post_status'   => 'publish'
    );
    $sku_query = new WP_Query( $args );
    if ( $sku_query->have_posts() ) :
        while ( $sku_query->have_posts() ) : $sku_query->the_post();
 
            $loop_product = wc_get_product( get_the_ID() );
            $loop_sku = $loop_product->get_sku();
            
            $loop_id = $loop_product->id;
            
            $loop_stubs = explode("-", $loop_sku);
            $loop_stub = $loop_stubs[0];
            // echo $the_id . "/" . $product->id;      
            if ( $loop_stub === $stub && $loop_id !== $the_id ) { 

                // WHOLESALE OUTPUT 
                if ( $wholesale === true ) { ?>
                    <div class="wholesale_other_colours" data-id="<?php echo $loop_id; ?>">
                        <p class=""><?php the_title(); ?></p>
                            <?php
                            if ( have_rows("product_images") ) : 
                                $j = 0;
                                while ( have_rows("product_images") ) : the_row();
                                    $image = get_sub_field("product_image");
                                    if( !empty($image) && $j === 0 ): 
                                        $thumb = $image['sizes'][ "thumbnail" ]; // 200x300
                                        $medium = $image['sizes'][ "medium" ]; // 400x600
                                        $large = $image['sizes'][ "large" ]; // 533x800
                                        $extralarge = $image['sizes'][ "extra-large" ]; // 683x1024
                                        $width = $image['sizes'][ "thumbnail-width" ]; 
                                        $height = $image['sizes'][ "thumbnail-height" ]; 
                                        
                                        ?>
                                        <img 
                                        width="<?php echo $width; ?>"  
                                        height="<?php echo $height; ?>"  
                                        src="<?php echo $thumb; ?>" 
                                        data-sizes="auto"
                                        data-src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" 
                                        data-srcset="<?php echo $thumb; ?> 400w,
                                        <?php echo $medium; ?> 500w, 
                                        <?php echo $large; ?> 600w,
                                        <?php echo $extralarge; ?> 800w"   
                                        class="lazyload single_additional_image position_<?php echo $position; ?>" 
                                        />      
                                 <?php
                                                                  
                                endif;
                                $j++;
                            endwhile;
                        endif;
                        ?>
                    </div>
                <?php 
                // SINGLE OUTPUT 
                } else { ?> 
                    <li class="other_colours"><a href="<?php echo get_permalink( ); ?>"><?php echo get_the_title(); ?></a></li>
                <?php                  
                } 
            } 

        endwhile;
        // $sku_query->reset_postdata();   
        wp_reset_postdata();    
    endif;  
} 

// ADD SPACES – FABRIC INFO

function addSpaces( $str ) {
    // GET ALL "WORDS"
    $info = explode( " ", $str );
    foreach ( $info as $subInfo ) {
        // CHECK IF WORDS CONTAINS %
        if ( strpos ( $subInfo , "%" ) ) {
            // IF NOT IN LAST POSITION
            if ( substr( $subInfo, -1 ) === "%" ) {
                echo $subInfo . " ";
            } else {
                // ELSE SPLIT
                $subInfo = explode( "%", $subInfo );
                echo $subInfo[0] . "% " .  $subInfo[1]; 
            }
        } else {
            echo $subInfo . " ";
        }
    }
}

// PARENT COLLECTION ON SINGLE PAGE

function parent_collection ( $the_id ) {

    $terms = get_the_terms( $the_id, 'product_cat' );
    $this_cat = $terms[0]->slug;

    $args = array(
        'post_type' => 'product',
        "tax_query" => array(
            array(
                'taxonomy' => "product_cat",
                'field'    => "slug",
                'terms'    => $this_cat
            )
        )
    );
    $the_query = new WP_Query( $args );
        ?>
        <div class="collection single_collection">
            <ul>
                <?php   
                if ( $the_query->have_posts() ) {
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post(); ?>
                            <?php wc_get_template_part( 'content', 'product' ); ?>
                        <?php   
                    }
                } 
                wp_reset_postdata();    
                ?>
            </ul>
        </div>
    <?php     
}

// MAIL CHIMP SIGNUP FORM

function mailchimp_form () { ?>
    <div id="mc_embed_signup">
        <form action="//canpeprey.us11.list-manage.com/subscribe/post?u=d43f01e1f63768b0eb69b572d&amp;id=f614e5d4db" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
            <div id="mc_embed_signup_scroll">
                <div class="mc-field-group">
                    <label for="mce-EMAIL">Email Address </label>
                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                </div>
                <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_d43f01e1f63768b0eb69b572d_f614e5d4db" tabindex="-1" value=""></div>
                <div class="submit_wrapper clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                <div id="mce-responses" class="clear">
                    <div class="response" id="mce-error-response" style="display:none"></div>
                    <div class="response" id="mce-success-response" style="display:none"></div>
                </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
            </div>
        </form>
    </div>
    <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
<?php                    
}

// CUSTOMISE CHECKOUT

function custom_override_checkout_fields( $fields ) {
    if ( is_user_logged_in() ) {
        $fields['billing']['billing_company']['required'] = true;        
    }
    return $fields;
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// CHANGE BUTTON TEXT

add_filter( 'gettext', 'custom_paypal_button_text', 20, 3 );

function custom_paypal_button_text( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Proceed to PayPal' :
            $translated_text = __( 'Proceed to payment', 'woocommerce' );
            break;
    }
    return $translated_text;
}

// ADD VAT FIELD TO CHECKOUT PAGE

function VAT_override_checkout_fields( $fields ) {
    if ( is_user_logged_in() ) {
        $fields['billing']['VAT_code'] = array(
        'label'     => __('VAT', 'woocommerce'),
        'placeholder'   => _x('VAT', 'placeholder', 'woocommerce'),
        'required'  => false,
        'class'     => array('form-row-wide'),
        'clear'     => true
        );     
    }
    return $fields;
}

add_filter( 'woocommerce_checkout_fields' , 'VAT_override_checkout_fields');

// AUTOMATIC COUPON FOR WHOLESALE CUSTOMERS

add_action( 'woocommerce_before_cart', 'apply_matched_coupons' );

function apply_matched_coupons() {
    global $woocommerce;

    $coupon_code = '10percent'; // your coupon code here

    if ( $woocommerce->cart->has_discount( $coupon_code ) ) return;

    if ( $woocommerce->cart->cart_contents_total >= 500 ) {
        $woocommerce->cart->add_discount( $coupon_code );
        $woocommerce->show_messages();
    }

}


// UPDATE CART TOTAL VIA AJAX

add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );
function woocommerce_header_add_to_cart_fragment( $fragments ) {
    ob_start(); ?>

    <a class="cart-contents" href="<?php echo WC()->cart->get_cart_url(); ?>" title="<?php _e( 'View your shopping cart' ); ?>">
        <?php echo WC()->cart->cart_contents_count; ?> / <?php echo WC()->cart->get_cart_total(); ?>
    </a>             
    <?php
    $fragments['a.cart-contents'] = ob_get_clean();
    
    return $fragments;
}

// UPDATE WHOLESALE QUANTITES VIA AJAX

    // add_action( "wp_ajax_ajax_test", "echo_yes" );
    // add_action( "wp_ajax_nopriv_ajax_test", "echo_yes" );

    // function echo_yes () {
    //     echo "yes";
    // }

// SORT FUNCTIONS FOR EMAIL (+CART?)

function email_variation_sort( $a, $b ) {
    return $a["variation_id"] < $b["variation_id"];
}

function email_custom_sort( $a, $b ) {
    return $a["product_id"] > $b["product_id"];
}

?>