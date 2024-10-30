<?php
if (!defined('ABSPATH')) exit;
/**
 * @package VerifyPass
 * @version 5.0.0
 */
/*
Plugin Name: VerifyPass
Plugin URI: https://wordpress.org/plugins/id-services/
Description: WooCommerce discounts for exclusive communities.
Author: VerifyPass
Version: 5.0.0
Author URI: https://verifypass.com
WC requires at least: 3.0.0
WC tested up to: 3.6.0
*/

class verifypass {

    public function __construct(){
        /* Endpoint for VerifyPass callbacks */
        add_action('rest_api_init', function () {
            register_rest_route('verifypass', '/callback/', array(
                'methods' => 'POST',
                'callback' => [$this, 'callbacks'],
                'permission_callback' => '__return_true' // This allows unrestricted access
            ));
        });

        /* Plugin Settings links */
        add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2 );
        add_action('admin_menu', array($this,'add_admin_page'));
    }

    public function callbacks(WP_REST_Request $request) {

        /* Get API Key */
        $api_key = $request->get_header('x-vfyps-key');

        /* Validate API Key */
        if (!$api_key || $api_key != get_option("verifypass_api_key")) { return new WP_REST_Response(['error' => 'Invalid API Key.'], 401); }

        /* Action */
        switch($request->get_param('action')){

            /* Test: checking the connection */
            case 'test':
                return new WP_REST_Response(['connected' => true], 200);
                break;

            /* Coupon: generate coupon code */
            case 'coupon':
                $coupon = new WC_Coupon();
                $coupon->set_code($request->get_param('coupon_name'));
                $coupon->set_discount_type($request->get_param('coupon_type'));
                $coupon->set_amount($request->get_param('coupon_value'));
                $coupon->set_usage_limit(1); //Code can only be used once (prevents code from being widespread shared)
                $coupon->set_individual_use(true); //Doesn't stack with other coupons
                $coupon->set_exclude_sale_items(true); //Exclude sale items

                /* Apply Category Restrictions */
                if ($request->get_param('coupon_excluded_categories')){
                    $coupon->set_excluded_product_categories(explode(',', $request->get_param('coupon_excluded_categories')));
                }

                $coupon->save();
                return new WP_REST_Response(['code' => $coupon->get_code()], 200);

            /* Page: creates a page with HTML provided by VerifyPass */
            case 'page':
                /* Prevent duplicate page (don't want to overwrite if merchant has existing/customized page) */
                if (get_page_by_path($request->get_param('page_slug'))) { return new WP_REST_Response(['error' => 'Page already exists.'], 200); }

                /* Create page array */
                $page_data = array(
                    'post_title'    => $request->get_param('page_title'),
                    'post_content'  => $request->get_param('page_content'),
                    'post_name'     => $request->get_param('page_slug'),
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'post_author'   => get_current_user_id(),
                    'ping_status'   => 'closed',
                    'comment_status' => 'closed',
                );

                /* Remove kses filters, which block critical functionality and display */
                kses_remove_filters();

                /* Insert to DB */
                $page_id = wp_insert_post($page_data);
                if ($page_id != 0) {
                    return new WP_REST_Response(['url' => get_permalink($page_id)], 200);
                }

                return new WP_REST_Response(['error' => 'Something went wrong.'], 200);

            /* Categories: fetching categories for display in widget config */
            case 'categories':
                $args = array(
                    'taxonomy'     => 'product_cat',
                    'hide_empty'   => false,
                    'orderby'      => 'name',
                    'order'        => 'ASC'
                );

                $all_categories = get_terms($args);
                $categories = array();

                foreach ($all_categories as $cat) {
                    /* Parent Category */
                    if ($cat->parent == 0) {
                        $categories[] = array(
                            'id' => $cat->term_id,
                            'name' => $cat->name
                        );
                    }
                    /* Sub-Category */
                    else {
                        $parent_category = get_term($cat->parent, 'product_cat');
                        $categories[] = array(
                            'id' => $cat->term_id,
                            'name' => $parent_category->name . ' (' . $cat->name . ')'
                        );
                    }
                }

                return new WP_REST_Response($categories, 200);

            /* Default */
            default:
                return new WP_REST_Response(['error' => 'No action specified.'], 200);

        }

    }

    /*************************************
    Helper Methods
    *************************************/
    private function require_admin(){ if (!is_admin()){ wp_die(); } }

    private function logo_path(){ return plugin_dir_url(__FILE__) . 'logo.png'; }

    /*************************************
    Admin
    *************************************/
    public function add_settings_link($links, $file){
        static $this_plugin;
        if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
        if ($file == $this_plugin){
            $settings_link = '<a href="admin.php?page=verifypass.php">'.__("Settings", "verifypass").'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    public function add_admin_page($action = NULL){
        add_submenu_page( $parent_slug = NULL,
            "VerifyPass",
            "menu title",
            "manage_options",
            "verifypass",
            array($this,'show_admin_page'));
    }

    public function show_admin_page(){
        $this->require_admin();
        include 'view.php';
    }

}

$GLOBALS['verifypass'] = new verifypass();

?>
