<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Product_Category_Custom_Fields' ) ) {

    class WWPP_Product_Category_Custom_Fields {

        private static $_instance;

        public static function getInstance () {

            if(!self::$_instance instanceof self)
                self::$_instance = new self;

            return self::$_instance;

        }

        /**
         * Add wholesale price fields to product category taxonomy add page.
         *
         * @param $taxonomy
         * @param $allRegisteredWholesaleRoles
         *
         * @since 1.0.5
         */
        public function productCategoryAddCustomFields ( $taxonomy , $allRegisteredWholesaleRoles ) {

            foreach ( $allRegisteredWholesaleRoles as $roleKey => $role ) {

                ?>
                <div class="form-field">
                    <label for="term_meta[<?php echo $roleKey; ?>_wholesale_discount]"><?php echo $role[ 'roleName' ]; ?></label>
                    <input type="text" name="term_meta[<?php echo $roleKey; ?>_wholesale_discount]" id="term_meta[<?php echo $roleKey; ?>_wholesale_discount]" class="wc_input_price" value="">
                    <p class="description"><?php echo $role[ 'roleName' ] . " Discount For Products In This Category. In Percent (%). Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3." ?></p>
                </div>
                <?php

            }

        }

        /**
         * Add wholesale price fields to product category taxonomy edit page.
         *
         * @param $term
         * @param $allRegisteredWholesaleRoles
         *
         * @since 1.0.5
         */
        public function productCategoryEditCustomFields ( $term , $allRegisteredWholesaleRoles ) {

            // put the term ID into a variable
            $t_id = $term->term_id;

            // retrieve the existing value(s) for this meta field. This returns an array
            $term_meta = get_option( "taxonomy_$t_id" );

            foreach ( $allRegisteredWholesaleRoles as $roleKey => $role ) {

                ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="term_meta[<?php echo $roleKey; ?>_wholesale_discount]"><?php echo $role[ 'roleName' ]; ?></label></th>
                    <td>
                        <input type="text" name="term_meta[<?php echo $roleKey; ?>_wholesale_discount]" id="term_meta[<?php echo $roleKey; ?>_wholesale_discount]" class="wc_input_price" value="<?php echo esc_attr( $term_meta[ $roleKey . '_wholesale_discount'] ) ? esc_attr( $term_meta[ $roleKey . '_wholesale_discount'] ) : ''; ?>">
                        <p class="description"><?php echo $role[ 'roleName' ] . " Discount For Products In This Category. In Percent (%). Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3." ?></p>
                    </td>
                </tr>
                <?php

            }

        }

        /**
         * Save wholesale price fields data on product category taxonomy add and edit page.
         *
         * @since 1.0.5
         * @since 1.7.0 Bug fix. Properly set have_post_meta value to all products under the edited product category.
         *
         * @param $term_id
         */
        public function productCategorySaveCustomFields ( $term_id ) {

            if ( isset( $_POST['term_meta'] ) ) {

                $t_id = $term_id;
                $term_meta = get_option( "taxonomy_$t_id" );
                $cat_keys = array_keys( $_POST['term_meta'] );

                $products = WWPP_WPDB_Helper::getProductsByCategory( $term_id );

                foreach ( $cat_keys as $key ) {

                    if ( isset ( $_POST['term_meta'][$key] ) ) {

                        $term_meta[$key] = $_POST['term_meta'][$key];

                        $wholesale_role = str_replace( '_wholesale_discount' , '' , $key );

                        if ( $_POST[ 'term_meta' ][$key] ) {

                            // Has discount

                            foreach ( $products as $p ) {

                                if ( get_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , true ) != 'yes' ) {

                                    // Either not having $wholesale_role . '_have_wholesale_price' or having value of 'no'

                                    // Add have wholesale price meta
                                    update_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , 'yes' );

                                    // Add additional meta to indicate that have wholesale price meta was set by the category
                                    update_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price_set_by_product_cat' , 'yes' );

                                }

                            }

                        } else {

                            // No discount
                            foreach ( $products as $p ) {

                                if ( get_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , true ) == 'yes' &&
                                     get_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price_set_by_product_cat' , true ) == 'yes' ) {

                                    // Meaning, product have have wholesale price meta that was set by the category
                                    // Don't bother changing the meta for products that have no _have_wholesale_price_set_by_product_cat meta
                                    // it means that those products have wholesale price set on per product level.

                                    // Set have post meta to no
                                    update_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , 'no' );

                                    // Delete post additional post meta
                                    delete_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price_set_by_product_cat' );

                                }

                            }

                        }

                    }

                }

                // Save the option array.
                update_option( "taxonomy_$t_id", $term_meta );

            }

        }

    }

}
