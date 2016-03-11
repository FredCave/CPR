<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_WPDB_Helper' ) ) {

    /**
     * This class contains lots of helper functions that are perform via wpdb for speed.
     *
     * Class WWPP_WPDB_Helper
     */
    class WWPP_WPDB_Helper {

        /**
         * Get products under a certain category.
         *
         * @since 1.7.0
         *
         * @param $termId
         * @return mixed
         */
        public static function getProductsByCategory( $termId ) {

            global $wpdb;
            $query = "
                     SELECT * FROM $wpdb->posts
                     LEFT JOIN $wpdb->term_relationships ON
                     ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
                     LEFT JOIN $wpdb->term_taxonomy ON
                     ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
                     WHERE $wpdb->posts.post_status = 'publish'
                     AND $wpdb->posts.post_type = 'product'
                     AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
                     AND $wpdb->term_taxonomy.term_id = " . $termId . "
                     ORDER BY post_date DESC
                     ";

            return $wpdb->get_results( $query );

        }

        /**
         * Set meta to list of products. Requires a list of product ids, and they should have the same meta key and
         * value to set. Not used atm, might be helpful in the future.
         *
         * @since 1.7.0
         *
         * @param $metaKey
         * @param $metaVal
         * @param $postIds
         */
        public static function updatePostMeta( $metaKey , $metaVal , $postIds ) {

            if ( is_array( $postIds ) && !empty( $postIds ) ) {

                global $wpdb;
                $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %d WHERE meta_key = '" . $metaKey . "' AND post_id IN( " . implode( ',' , $postIds ) . " )" , $metaVal ) );

            }

        }

    }

}