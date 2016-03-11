<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWOF_Product_Listing_Helper' ) ) {

    class WWOF_Product_Listing_Helper {

        /**
         * Get all products of the shop via $wpdb.
         * It returns an array of Post objects.
         *
         * @since 1.2.7
         * @return mixed
         */
        public static function get_all_products() {

            global $wpdb;

            return $wpdb->get_results("
                                        SELECT *
                                        FROM $wpdb->posts
                                        WHERE post_status = 'publish'
                                        AND post_type = 'product'
                                        ");

        }

        /**
         * Get all instock products. Instock products has 2 types.
         * 1. Managed products
         * 2. Unmanaged products
         * The reason being we search for this 2 types of products is because of this bug.
         * https://github.com/woothemes/woocommerce/issues/6789
         * WooCommerce dude replied to me and said this is fixed, but as far as my latest tests
         * ( wc 2.4.x ) this bug is still here. Classic WooCommerce!
         *
         * Also note that this function only concerns itself on instock products, it does not care of other
         * query filters. Thus should be handled by the main query.
         *
         * Version 1.3.2 change set
         * Variable product visibility to the wholesale order form is now determine by the variations of that variable
         * product. Meaning even if the parent variable product is set to managed and set the value of stock to 0
         * we will not honor this to conclude that this variable product should be displayed on the order form,
         * instead, we will go through all the variations of this variable product and check if at least 1 of its
         * variations has stock* ( the word has stock is tricky, ill explain further later ), and if so then we display
         * the current variable product.
         *
         * Now as of woocommerce 2.4.x and 2.3.x series, there is this case ( might be a bug ),
         * where ex.
         *
         * 1. you have a current variable product, it is unmanaged
         * 2. you have 3 variations, 2 of the variations are managed with stock of 100 and 200 respectively
         * 3. last variation is unmanaged, and has stock status of in stock
         * 4. change the parent variable product as managed, and set qty to 0
         * 5. check the last variation on the single product admin page, it still has status of in stock
         * 6. go to the shop page, check out the last variation of that variable product, it is out of stock
         * 7. try to edit that last variation and hit save, notice you can't set it to in stock anymore (given
         * the last variation remains un managed)
         *
         * so this is my observation:
         * 1. if parent product is managed, all unmanaged variations inherit the parent variable product characteristics
         * so in the explanation above, since parent variable is set to managed and has stock of 0, then the last
         * variation which is unmanaged inherits the parent variable product qty which is zero, thats why on the shop
         * page its out of stock.
         *
         * 2. it doesn't sync well, at least with the current version of woocommerce i have during this time.
         *
         * Version 1.3.4 change set
         * Recognize the general inventory management settings.
         * WooCommerce > Settings > Product > Inventory > Manage Stock
         *
         * @since 1.2.7
         * @since 1.3.2
         * @since 1.3.4
         * @return array Array of post ids
         */
        public static function get_all_instock_products() {

            global $wpdb;

            // ****************************************************************************
            // General Vars
            // ****************************************************************************

            // WooCommerce > Settings > Product > Inventory
            $inventory_management = get_option( 'woocommerce_manage_stock' );

            $managed_join_query = "
                                    INNER JOIN $wpdb->postmeta post_meta_table2
                                            ON post_meta_table2.post_id = post_meta_table1.ID
                                            AND post_meta_table2.meta_key = '_manage_stock'
                                            AND post_meta_table2.meta_value = 'yes'
                                    INNER JOIN $wpdb->postmeta post_meta_table3
                                            ON post_meta_table3.post_id = post_meta_table2.post_id
                                            AND post_meta_table3.meta_key = '_stock'
                                            AND post_meta_table3.meta_value > 0
                                    ";

            $unmanaged_join_query = "
                                    INNER JOIN $wpdb->postmeta post_meta_table2
                                            ON post_meta_table2.post_id = post_meta_table1.ID
                                            AND post_meta_table2.meta_key = '_manage_stock'
                                            AND post_meta_table2.meta_value = 'no'
                                    INNER JOIN $wpdb->postmeta post_meta_table3
                                            ON post_meta_table3.post_id = post_meta_table2.post_id
                                            AND post_meta_table3.meta_key = '_stock_status'
                                            AND post_meta_table3.meta_value = 'instock'
                                    ";


            // ****************************************************************************
            // Get variable product ids
            // ****************************************************************************
            $variable_product_term_id               = self::get_variable_product_term_id();
            $managed_has_stock_variable_product_ids = self::get_managed_variable_product_ids_with_stock( $inventory_management , $variable_product_term_id );
            $managed_no_stock_variable_product_ids  = self::get_managed_variable_product_ids_with_no_stock( $inventory_management , $variable_product_term_id );
            $unmanaged_variable_product_ids         = self::get_unmanaged_variable_product_ids( $inventory_management , $variable_product_term_id );
            $variable_product_ids                   = self::get_variable_product_ids( $inventory_management , $variable_product_term_id , $managed_has_stock_variable_product_ids , $managed_no_stock_variable_product_ids , $unmanaged_variable_product_ids );


            // ****************************************************************************
            // Non-variable product query
            // ****************************************************************************
            $instock_non_variable_products_id = self::get_instock_none_variable_products_ids( $inventory_management , $variable_product_ids );


            if ( $inventory_management == 'yes' ) {

                // ****************************************************************************
                // Since this is managed variable product and the stock qty is set to zero,
                // then all we have to do is check the variations that is also managed and has
                // stock qty set to greater than zero.
                //
                // If at least one variation of the current variable product comply with this,
                // then we display this variable product on the wholesale order form page.
                //
                // The reason for this is, if variation is unmanaged, and the parent variable
                // product is set to managed, and has stock qty of 0, then the unmanged
                // variation inherits the parent variable qty which is 0.
                //
                // Therefore we can conclude that unmanaged variations under a managed variable
                // product that has qty of 0 is automatically out of stock too.
                // ****************************************************************************

                $instock_managed_no_stock_variable_product_ids = array();
                if ( !empty( $managed_no_stock_variable_product_ids ) ) {

                    $managed_no_stock_variable_product_ids_str = implode( ',' , $managed_no_stock_variable_product_ids );

                    $query = "
                      SELECT DISTINCT post_meta_table1.post_parent
                      FROM $wpdb->posts post_meta_table1
                      ";

                    $where_query = "
                            WHERE post_meta_table1.post_status = 'publish'
                            AND post_meta_table1.post_type = 'product_variation'
                            AND post_meta_table1.post_parent IN (" . $managed_no_stock_variable_product_ids_str . ")
                            ";

                    $query_results = $wpdb->get_results( $query . $managed_join_query . $where_query , ARRAY_A );

                    foreach ( $query_results as $qr )
                        $instock_managed_no_stock_variable_product_ids[] = $qr[ 'post_parent' ];

                }

                // ****************************************************************************
                // Since this is managed variable product with stock qty greater than 0
                // then we need to check both variations that are un-managed and managed
                // ****************************************************************************
                $instock_managed_has_stock_variable_product_ids = array();
                if ( !empty( $managed_has_stock_variable_product_ids ) ) {

                    $managed_has_stock_variable_product_ids_str = implode( "," , $managed_has_stock_variable_product_ids );

                    $query = "
                      SELECT DISTINCT post_meta_table1.post_parent
                      FROM $wpdb->posts post_meta_table1
                      ";

                    $where_query = "
                            WHERE post_meta_table1.post_status = 'publish'
                            AND post_meta_table1.post_type = 'product_variation'
                            AND post_meta_table1.post_parent IN (" . $managed_has_stock_variable_product_ids_str . ")
                            ";

                    // Manged Instock Products
                    $managed_list = array();
                    $query_results = $wpdb->get_results( $query . $managed_join_query . $where_query , ARRAY_A );

                    foreach ( $query_results as $qr )
                        $managed_list[] = $qr[ 'post_parent' ];

                    // Unmanaged Instock Products
                    $unmanaged_list = array();
                    $query_results = $wpdb->get_results( $query . $unmanaged_join_query . $where_query , ARRAY_A );

                    foreach ( $query_results as $qr )
                        $unmanaged_list[] = $qr[ 'post_parent' ];

                    $instock_managed_has_stock_variable_product_ids = array_unique( array_merge( $managed_list , $unmanaged_list ) );

                }

                // ****************************************************************************
                // Un-managed variable product, we need to check both
                // un-managed and managed variations
                // ****************************************************************************
                $instock_unmanaged_variable_product_ids = array();
                if ( !empty( $unmanaged_variable_product_ids ) ) {

                    $unmanaged_variable_product_ids_str = implode( "," , $unmanaged_variable_product_ids );

                    $query = "
                      SELECT DISTINCT post_meta_table1.post_parent
                      FROM $wpdb->posts post_meta_table1
                      ";

                    $where_query = "
                            WHERE post_meta_table1.post_status = 'publish'
                            AND post_meta_table1.post_type = 'product_variation'
                            AND post_meta_table1.post_parent IN (" . $unmanaged_variable_product_ids_str . ")
                            ";

                    // Manged Instock Products
                    $managed_list = array();
                    $query_results = $wpdb->get_results( $query . $managed_join_query . $where_query , ARRAY_A );

                    foreach ( $query_results as $qr )
                        $managed_list[] = $qr[ 'post_parent' ];

                    // Unmanaged Instock Products
                    $unmanaged_list = array();
                    $query_results = $wpdb->get_results( $query . $unmanaged_join_query . $where_query , ARRAY_A );

                    foreach ( $query_results as $qr )
                        $unmanaged_list[] = $qr[ 'post_parent' ];

                    $instock_unmanaged_variable_product_ids = array_unique( array_merge( $managed_list , $unmanaged_list ) );

                }

                $instock_variable_products_id = array_unique( array_merge( $instock_managed_no_stock_variable_product_ids , $instock_managed_has_stock_variable_product_ids , $instock_unmanaged_variable_product_ids ) );

            } else {

                // Inventory management is disabled. We still need to check the stock status though.

                $variable_product_ids_str = implode( "," , $variable_product_ids );

                $q = "
                      SELECT DISTINCT post_meta_table1.post_parent
                      FROM $wpdb->posts post_meta_table1
                      INNER JOIN $wpdb->postmeta post_meta_table2
                        ON post_meta_table2.post_id = post_meta_table1.ID
                        AND post_meta_table2.meta_key = '_stock_status'
                        AND post_meta_table2.meta_value = 'instock'
                      WHERE post_meta_table1.post_status = 'publish'
                        AND post_meta_table1.post_type = 'product_variation'
                        AND post_meta_table1.post_parent IN (" . $variable_product_ids_str . ")
                    ";

                $instock_variable_products_id = array();
                $query_results = $wpdb->get_results( $q , ARRAY_A );

                foreach ( $query_results as $qr )
                    $instock_variable_products_id[] = $qr[ 'post_parent' ];

            }


            // **************************************
            // Merge in stock non-variable and
            // variable product ids
            // **************************************
            $instock_products_id = array_unique( array_merge( $instock_non_variable_products_id , $instock_variable_products_id ) );

            // If empty, we return an array that has a single value of zero
            // This is necessary to indicate that no instock products is present
            if ( empty( $instock_products_id ) )
                $instock_products_id = array( 0 );

            return $instock_products_id;

        }

        /**
         * Get variable product term ids. Product types in woocommerce are stored as terms in terms table.
         * A specific term ('simple','variable') is added to the particular product to determine its product type.
         *
         * @since 1.3.4
         *
         * @return int
         */
        public static function get_variable_product_term_id() {

            global $wpdb;

            // Get variable product term id
            $q = "SELECT term_id FROM $wpdb->terms WHERE name = 'variable' LIMIT 1";
            $variable_product_term_id = $wpdb->get_row( $q , ARRAY_A );

            if ( $variable_product_term_id )
                $variable_product_term_id = (int) $variable_product_term_id[ 'term_id' ];

            return $variable_product_term_id;

        }

        /**
         * Get the ids of all variable products that are managed and has stock.
         *
         * @since 1.3.4
         *
         * @param $inventory_management
         * @param $variable_product_term_id
         * @return array
         */
        public static function get_managed_variable_product_ids_with_stock( $inventory_management , $variable_product_term_id ) {

            $managed_has_stock_variable_product_ids = array();

            if ( $variable_product_term_id && $inventory_management == 'yes' ) {

                global $wpdb;

                // Get all managed variable ids that has stock
                $q = "
                      SELECT DISTINCT post_meta_table1.object_id
                      FROM $wpdb->term_relationships post_meta_table1
                      INNER JOIN $wpdb->postmeta post_meta_table2
                        ON post_meta_table2.post_id = post_meta_table1.object_id
                        AND post_meta_table2.meta_key = '_manage_stock'
                        AND post_meta_table2.meta_value = 'yes'
                      INNER JOIN $wpdb->postmeta post_meta_table3
                        ON post_meta_table3.post_id = post_meta_table2.post_id
                        AND post_meta_table3.meta_key = '_stock'
                        AND post_meta_table3.meta_value > 0
                      WHERE post_meta_table1.term_taxonomy_id = $variable_product_term_id
                     ";

                $q_results = $wpdb->get_results( $q , ARRAY_A );

                foreach( $q_results as $q_r )
                    $managed_has_stock_variable_product_ids[] = (int) $q_r[ 'object_id' ];

            }

            return $managed_has_stock_variable_product_ids;

        }

        /**
         * Get the ids of all variable products that are managed and has no stock.
         *
         * @param $inventory_management
         * @param $variable_product_term_id
         * @return array
         */
        public static function get_managed_variable_product_ids_with_no_stock( $inventory_management , $variable_product_term_id ) {

            $managed_no_stock_variable_product_ids = array();

            if ( $variable_product_term_id && $inventory_management == 'yes' ) {

                global $wpdb;

                // Get all managed variable ids that has no stock
                $q = "
                      SELECT DISTINCT post_meta_table1.object_id
                      FROM $wpdb->term_relationships post_meta_table1
                      INNER JOIN $wpdb->postmeta post_meta_table2
                        ON post_meta_table2.post_id = post_meta_table1.object_id
                        AND post_meta_table2.meta_key = '_manage_stock'
                        AND post_meta_table2.meta_value = 'yes'
                      INNER JOIN $wpdb->postmeta post_meta_table3
                        ON post_meta_table3.post_id = post_meta_table2.post_id
                        AND post_meta_table3.meta_key = '_stock'
                        AND post_meta_table3.meta_value <= 0
                      WHERE post_meta_table1.term_taxonomy_id = $variable_product_term_id
                     ";

                $q_results = $wpdb->get_results( $q , ARRAY_A );

                foreach( $q_results as $q_r )
                    $managed_no_stock_variable_product_ids[] = (int) $q_r[ 'object_id' ];

            }

            return $managed_no_stock_variable_product_ids;

        }

        /**
         * Get the ids of all variable products tat are un-managed.
         *
         * @since 1.3.4
         *
         * @param $inventory_management
         * @param $variable_product_term_id
         * @return array
         */
        public static function get_unmanaged_variable_product_ids( $inventory_management , $variable_product_term_id ) {

            $unmanaged_variable_product_ids = array();

            if ( $variable_product_term_id && $inventory_management == 'yes' ) {

                global $wpdb;

                $q = "
                      SELECT DISTINCT post_meta_table1.object_id
                      FROM $wpdb->term_relationships post_meta_table1
                      INNER JOIN $wpdb->postmeta post_meta_table2
                        ON post_meta_table2.post_id = post_meta_table1.object_id
                        AND post_meta_table2.meta_key = '_manage_stock'
                        AND post_meta_table2.meta_value = 'no'
                      WHERE post_meta_table1.term_taxonomy_id = $variable_product_term_id
                      ";

                $q_results = $wpdb->get_results( $q , ARRAY_A );

                foreach( $q_results as $q_r )
                    $unmanaged_variable_product_ids[] = (int) $q_r[ 'object_id' ];

            }

            return $unmanaged_variable_product_ids;

        }

        /**
         * Get all the variable product ids.
         *
         * @since 1.3.4
         *
         * @param $inventory_management
         * @param $variable_product_term_id
         * @param $managed_has_stock_variable_product_ids
         * @param $managed_no_stock_variable_product_ids
         * @param $unmanaged_variable_product_ids
         * @return array
         */
        public static function get_variable_product_ids( $inventory_management , $variable_product_term_id , $managed_has_stock_variable_product_ids , $managed_no_stock_variable_product_ids , $unmanaged_variable_product_ids ) {

            global $wpdb;

            $variable_product_ids = array();

            if ( $variable_product_term_id ) {

                if ( $inventory_management == 'yes' ) {

                    // Merge all to get variable product ids
                    $variable_product_ids = array_unique( array_merge( $managed_has_stock_variable_product_ids , $managed_no_stock_variable_product_ids , $unmanaged_variable_product_ids ) );

                } else {

                    // Get all object_id ( post_id ) of variable products from term_relationships table.
                    // Stock management is disabled so we just get all entries with the term_taxonomy_id
                    // of $variable_product_term_id
                    $q = "
                          SELECT DISTINCT post_meta_table1.object_id
                          FROM $wpdb->term_relationships post_meta_table1
                          WHERE post_meta_table1.term_taxonomy_id = $variable_product_term_id
                         ";

                    $q_results = $wpdb->get_results( $q , ARRAY_A );

                    foreach( $q_results as $q_r )
                        $variable_product_ids[] = (int) $q_r[ 'object_id' ];

                }

            }

            return $variable_product_ids;

        }

        /**
         * Get all instock none-variable product ids.
         *
         * @since 1.3.4
         *
         * @param $inventory_management
         * @param $variable_product_ids
         * @return array
         */
        public static function get_instock_none_variable_products_ids($inventory_management , $variable_product_ids ) {

            global $wpdb;

            $query = "
                      SELECT DISTINCT post_meta_table1.ID
                      FROM $wpdb->posts post_meta_table1
                      ";

            $where_query = "
                            WHERE post_meta_table1.post_status = 'publish'
                            AND post_meta_table1.post_type = 'product'
                            ";

            // Exclude variable products
            if ( !empty( $variable_product_ids ) ) {

                $variable_product_ids_str = implode( ', ' , $variable_product_ids );
                $where_query .= "AND post_meta_table1.ID NOT IN ( " . $variable_product_ids_str . " )";

            }

            if ( $inventory_management == 'yes' ) {

                $managed_join_query = "
                                    INNER JOIN $wpdb->postmeta post_meta_table2
                                        ON post_meta_table2.post_id = post_meta_table1.ID
                                        AND post_meta_table2.meta_key = '_manage_stock'
                                        AND post_meta_table2.meta_value = 'yes'
                                    INNER JOIN $wpdb->postmeta post_meta_table3
                                        ON post_meta_table3.post_id = post_meta_table2.post_id
                                        AND post_meta_table3.meta_key = '_stock'
                                        AND post_meta_table3.meta_value > 0
                                    ";

                $unmanaged_join_query = "
                                    INNER JOIN $wpdb->postmeta post_meta_table2
                                        ON post_meta_table2.post_id = post_meta_table1.ID
                                        AND post_meta_table2.meta_key = '_manage_stock'
                                        AND post_meta_table2.meta_value = 'no'
                                    INNER JOIN $wpdb->postmeta post_meta_table3
                                        ON post_meta_table3.post_id = post_meta_table2.post_id
                                        AND post_meta_table3.meta_key = '_stock_status'
                                        AND post_meta_table3.meta_value = 'instock'
                                    ";

                // Manged Instock Products
                $managed_list = array();
                $query_results = $wpdb->get_results( $query . $managed_join_query . $where_query , ARRAY_A );

                foreach ( $query_results as $qr )
                    $managed_list[] = $qr[ 'ID' ];

                // Unmanaged Instock Products
                $unmanaged_list = array();
                $query_results = $wpdb->get_results( $query . $unmanaged_join_query . $where_query , ARRAY_A );

                foreach ( $query_results as $qr )
                    $unmanaged_list[] = $qr[ 'ID' ];

                $instock_non_variable_products_id = array_unique( array_merge( $managed_list , $unmanaged_list ) );

            } else {

                // Inventory management is disabled. We still gotta check the _stock_status meta though.
                $join_query = "
                                INNER JOIN $wpdb->postmeta post_meta_table2
                                    ON post_meta_table2.post_id = post_meta_table1.ID
                                    AND post_meta_table2.meta_key = '_stock_status'
                                    AND post_meta_table2.meta_value = 'instock'
                                ";

                $instock_non_variable_products_id = array();
                $query_results = $wpdb->get_results( $query . $join_query . $where_query , ARRAY_A );

                foreach ( $query_results as $qr )
                    $instock_non_variable_products_id[] = $qr[ 'ID' ];

            }

            return $instock_non_variable_products_id;

        }

        /**
         * Get all products that is being searched.
         * It may or may not execute an sku search.
         *
         * Also note that this function only concerns itself on searching products, it does not care of other
         * query filters. Thus should be handled by the main query.
         *
         * @since 1.2.7
         * @param $search
         * @param bool|false $search_sku
         * @return array Array of post ids.
         */
        public static function get_search_products( $search , $search_sku = false ) {

            global $wpdb;

            // Normal Search
            $query = $wpdb->prepare( "
                                      SELECT DISTINCT $wpdb->posts.ID
                                      FROM $wpdb->posts
                                      WHERE post_type = 'product'
                                      AND post_status = 'publish'
                                      AND (
                                            post_title LIKE %s
                                            OR post_content LIKE %s
                                            OR post_excerpt LIKE %s
                                          )
                                    " , '%' . $search . '%' , '%' . $search . '%' , '%' . $search . '%' );

            $search_products_id = array();
            $query_results = $wpdb->get_results( $query , ARRAY_A );

            foreach ( $query_results as $qr )
                $search_products_id[] = $qr[ 'ID' ];

            if ( $search_sku ) {

                $query = $wpdb->prepare( "
                                        SELECT DISTINCT post_meta_table1.ID
                                        FROM $wpdb->posts post_meta_table1
                                        INNER JOIN $wpdb->postmeta post_meta_table2
                                                ON post_meta_table2.post_id = post_meta_table1.ID
                                                AND post_meta_table2.meta_key = '_sku'
                                                AND post_meta_table2.meta_value LIKE %s
                                        WHERE post_meta_table1.post_status = 'publish'
                                        AND post_meta_table1.post_type = 'product'
                                        " , '%' . $search . '%' );

                $sku_products = array();
                $query_results = $wpdb->get_results( $query , ARRAY_A );

                foreach ( $query_results as $qr )
                    $sku_products[] = $qr[ 'ID' ];

                $search_products_id = array_unique( array_merge( $search_products_id , $sku_products ) );

            }

            // If empty, we return an array that has a single value of zero
            // This is necessary to indicate that no products qualifies for the given search
            if ( empty( $search_products_id ) )
                $search_products_id = array( 0 );

            return $search_products_id;

        }

    }

}