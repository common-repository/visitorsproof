<?php
/**
 * Plugin Name: Visitorsproof
 * Plugin URI: https://visitorsproof.com/wordpress
 * Description: Increase sales and conversion of your website and store with Visitorproof insight FOMO notification
 * Version: 1.0.1
 * Author: Visitorsproof
 * Author URI: https://visitorsproof.com
 * License: GPLv2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

defined('ABSPATH') || die('Access Denied');
define('VISITORS_PROOF_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once VISITORS_PROOF_PLUGIN_DIR . 'constants.php';

if (!function_exists('visitors_proof_js_css')) {
    function visitors_proof_js_css(){
        wp_enqueue_script('visitors-proof-js', plugins_url('/js/script.js?' . time(), __FILE__), false, false, true);
        wp_enqueue_style('visitors-proof-css', plugins_url('/css/style.css?' . time(), __FILE__));
        wp_enqueue_style('visitors-proof-fe-animation-css', plugins_url('/css/animation.css', __FILE__));
    }
}

if (!function_exists('visitors_proof_wp_loaded')) {
    function visitors_proof_wp_loaded(){
    	global $post, $wp_query, $vpchr;
    	$params = new stdClass();
    	$params->enabled = get_option('visitors_proof_enabled');
    	$params->product = array('id' => 0);
    	$params->user = array('loggedin' => false);
    	$params->site_url = home_url();
    	$params->plugin_url = plugins_url('', __FILE__);
    	$params->wc = false;
    	$params->settings = visitors_proof_settings();
    	$params->page_id = isset($post->ID) ? $post->ID : 0;
    	$params->page_type = $vpchr->visitors_proof_current_page_type();
    	$params->post_type = $vpchr->visitors_proof_current_post_type();
    	if (is_user_logged_in()){
    		$user = wp_get_current_user();
    		$params->user = array(
    		    'loggedin' => true,
    		    'id' => $user->ID,
    			'name' => $user->data->display_name,
    		);
    	}
    	
    	if(is_visitors_proof_cf7_active() && has_shortcode($post->post_content, 'contact-form-7')){
			$cf_id = preg_match("/contact-form-7[^}]+id=\"?(\d+)\"?/", $post->post_content, $matches);
			$cf_id = isset($matches[1]) ? $matches[1] : 0;
			$params->cf7_id = $cf_id;
    	}
    	
    	if (is_visitors_proof_woo_commerce_active()){
    		global $woocommerce;
    		
    		$params->wc = true;
    		if ($params->page_type == 'Product'){
    		    $product = wc_get_product();
    			$params->product = array(
    				'id' => $product->get_id(),
    				'name' => $product->get_title(),
    				'slug' => $product->get_slug(),
    			);
    		}else if ($params->page_type == 'Taxonomy'){
    			if ($post->post_type == 'product') $params->page_type = 'Product Category';
    		}
    		
    		$params->symbol = get_woocommerce_currency_symbol();
    		$params->currency = get_woocommerce_currency();
    		$params->cart_total_1 = $woocommerce->cart->total;
    		$params->cart_total = $woocommerce->cart->get_cart_contents_total(); /*After discounts*/
    		
    		if (is_product()){
    		    $product = wc_get_product();
    		    if($product->is_type( 'variable' )){
    		        $available_variations = $product->get_available_variations();
    		        //p($available_variations);
    		        foreach ($available_variations as $key => $value){
    		            //get values HERE
    		        }
    		    }
    		}
    		
    		if($woocommerce->session->get('shipping_for_package_0')['rates']){
        		foreach( $woocommerce->session->get('shipping_for_package_0')['rates'] as $method_id => $rate ){
        		    if( $woocommerce->session->get('chosen_shipping_methods')[0] == $method_id ){
        		        $rate_label = $rate->label; // The shipping method label name
        		        $rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
        		        // The taxes cost
        		        $rate_taxes = 0;
        		        foreach ($rate->taxes as $rate_tax)
        		            $rate_taxes += floatval($rate_tax);
        		        // The cost including tax
        		        $rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;
        		
        		        $params->shipping_label = $rate_label;
        		        $params->shipping_amount = $woocommerce->cart->get_cart_shipping_total();
        		        $params->tax = $rate_cost_incl_tax;
        		        break;
        		    }
        		}
    		}
    	}
    	?>
    	<script>
    		var VPJS_Params = <?php echo json_encode($params); ?>;
    	</script>
    	<?php 
    }
}

if (!function_exists('visitors_proof_admin_head')) {
    function visitors_proof_admin_head() {
    	$params = new stdClass();
    	$strings = array();
    	$strings['Saving'] = __( 'Saving', 'visitorsproof' );
    	$strings['Choose Form'] = __( 'Choose Form', 'visitorsproof' );
    	$strings['Confirmation'] = __( 'Are you sure you want to do this action?', 'visitorsproof' );
    	$strings['Loading Text'] = __( 'Please wait...', 'visitorsproof' );
    	$params->strings = $strings;
    	?>
    	<script>
    		var VPADMJS_Params = <?php echo json_encode($params); ?>;
    	</script>
    	<?php 
    }
}

if (!function_exists('visitors_proof_updater')) {
    function visitors_proof_updater() {
        global $wpdb;
        $vp_new_version = '1.0.1';
        $vp_table_notifications = $wpdb->prefix . VISITORS_PROOF_TABLE_NOTIFICATIONS;
        if( version_compare( get_option( 'visitors_proof_version' ), $vp_new_version, '<') ){
            $wpdb->query("UPDATE `$vp_table_notifications` SET description = REPLACE(description, 'through out', 'throughout') WHERE type IN ('No of Visits', 'Location Visits')");
            $wpdb->query("UPDATE `$vp_table_notifications` SET defaults = REPLACE(defaults, 'last week', 'week') WHERE type IN ('No of Visits')");
            $wpdb->query("UPDATE `$vp_table_notifications` SET defaults = REPLACE(defaults, 'the last one hour', 'month') WHERE type IN ('Location Visits')");
            $wpdb->query("UPDATE `$vp_table_notifications` SET defaults = REPLACE(defaults, 'in the last month', 'month') WHERE type IN ('No of Orders')");
            update_option( "visitors_proof_version", $vp_new_version );
        }
    }
}

if (is_admin()){
    add_action('plugins_loaded', 'visitors_proof_updater');
}

if (!function_exists('visitors_proof_init')) {
    function visitors_proof_init() {
        if (is_admin()){
			add_action('admin_head', 'visitors_proof_admin_head' );
		}else{
            add_action('wpcf7_before_send_mail', 'visitors_proof_wpcf7_before_send_mail');
            function visitors_proof_wpcf7_before_send_mail($cf7) {
                $wpcf = WPCF7_ContactForm::get_current();
                
                $submission = WPCF7_Submission::get_instance();
                if ($submission){
                    global $wpdb, $vpchr;
                    $posted_data = $submission->get_posted_data();
                    $posted_data['vp-user-ip'] = $vpchr->visitors_proof_get_ip();
                    $ip_location = $vpchr->visitors_proof_get_ip_location($posted_data['vp-user-ip']);
                    $posted_data['vp-user-city'] = $ip_location->city;
                    $posted_data['vp-user-country'] = $ip_location->country;
                    $cf7_entry = array();
                    $cf7_entry['type'] = 'cf7-form';
                    $cf7_entry['type_id'] = $submission->get_contact_form()->id;
                    $cf7_entry['created'] = current_time('Y-m-d H:i:s');
                    $cf7_entry['values'] = json_encode($posted_data);
                    $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_INTEGRATIONS, $cf7_entry);
                }
            }

            wp_enqueue_script('jquery');
            add_action('wp_head', 'visitors_proof_wp_loaded');
            add_action('wp_enqueue_scripts', 'visitors_proof_js_css');
        }
    }
}
add_action('init', 'visitors_proof_init');

if (!function_exists('visitors_proof_admin_js_css')) {
    function visitors_proof_admin_js_css(){
        global $vpchr;
        wp_enqueue_script('visitors-proof-admin-js', plugins_url( '/js/admin-script.js?' . time(), __FILE__));
        wp_enqueue_style('visitors-proof-admin-css', plugins_url( '/css/admin-style.css?' . time(), __FILE__));
        wp_enqueue_style('visitors-proof-animation-css', plugins_url( '/css/animation.css', __FILE__));
        
        if($vpchr->visitors_proof_current_tab() == VISITORS_PROOF_PAGE_NOTIFICATIONS){
            /* Sortable */
            wp_enqueue_script('visitors-proof-sortable-js', plugins_url('/js/sortable.js', __FILE__));
        }else if(in_array($vpchr->visitors_proof_current_tab(), array(VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS, VISITORS_PROOF_PAGE_CF7))){
            /* Select 2 */
            wp_enqueue_script('visitors-proof-select-search-js', plugins_url('/js/select-search.js', __FILE__));
            wp_enqueue_style('visitors-proof-select-search-css', plugins_url( '/css/select-search.css', __FILE__));
            /* Trumbowyg */
            wp_enqueue_script('visitors-proof-trumbowyg-js', plugins_url('/js/trumbowyg.js', __FILE__));
            wp_enqueue_style('visitors-proof-trumbowyg-css', plugins_url( '/css/trumbowyg.css', __FILE__));
        }
    }
}

if (!function_exists('visitors_proof_ajax_call')) {
    function visitors_proof_ajax_call(){
        require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/ajax.php';
    }
}

if (!function_exists('visitors_proof_settings_link')) {
    function visitors_proof_settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=' . VISITORS_PROOF_PAGE_SETTINGS . '">' . __( 'Settings', 'visitorsproof' ) . '</a>';
        array_push( $links, $settings_link );
      	return $links;
    }
}

if (!function_exists('visitors_proof_admin_init')) {
    function visitors_proof_admin_init() {
        wp_enqueue_script('jquery');
        add_action('admin_enqueue_scripts', 'visitors_proof_admin_js_css');
        add_action('wp_ajax_visitors_proof_ajax_call', 'visitors_proof_ajax_call');
        add_action('wp_ajax_nopriv_visitors_proof_ajax_call', 'visitors_proof_ajax_call');
        
    	add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'visitors_proof_settings_link' );
    }
}
add_action('admin_init', 'visitors_proof_admin_init');

/* Menu Starts here */
add_action('admin_menu', 'visitors_proof_page');

if (!function_exists('visitors_proof_page')) {
    function visitors_proof_page() {
    	add_menu_page(
    		__( VISITORS_PROOF_NAME, 'visitors-proof' ), 
    		__( VISITORS_PROOF_NAME, 'visitors-proof' ), 
    	    'administrator',
    		VISITORS_PROOF_PAGE_NOTIFICATIONS, 
    		'visitors_proof_pages',
            plugins_url( 'visitorsproof/assets/logo-16.png' ),
            20
    	);
        add_submenu_page(
        	VISITORS_PROOF_PAGE_NOTIFICATIONS, 
    		__( 'All Notifications', 'visitorsproof' ), 
    		__( 'All Notifications', 'visitorsproof' ),
        	'administrator',
        	VISITORS_PROOF_PAGE_NOTIFICATIONS , 
        	'visitors_proof_pages'
        );
        $hook = add_submenu_page(
            VISITORS_PROOF_PAGE_NOTIFICATIONS,
            __( 'Custom Notifications', 'visitorsproof' ),
            __( 'Custom Notifications', 'visitorsproof' ),
            'administrator',
            VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS,
            'visitors_proof_pages'
        );
        add_action( "load-$hook", 'visitors_proof_cn_screen_option' );
        
        $hook = add_submenu_page(
            VISITORS_PROOF_PAGE_NOTIFICATIONS,
            __( 'Contact Form 7', 'visitorsproof' ),
            __( 'Contact Form 7', 'visitorsproof' ),
            'administrator',
            VISITORS_PROOF_PAGE_CF7,
            'visitors_proof_pages'
        );
        add_action( "load-$hook", 'visitors_proof_cf7_screen_option' );
        add_submenu_page(
        	VISITORS_PROOF_PAGE_NOTIFICATIONS, 
    		__( 'Settings', 'visitorsproof' ), 
    		__( 'Settings', 'visitorsproof' ),
        	'administrator', 
        	VISITORS_PROOF_PAGE_SETTINGS, 
        	'visitors_proof_pages'
        );
        add_submenu_page(
        	VISITORS_PROOF_PAGE_NOTIFICATIONS, 
    		__( 'Reports', 'visitorsproof' ), 
    		__( 'Reports', 'visitorsproof' ),
        	'administrator', 
        	VISITORS_PROOF_PAGE_REPORTS, 
        	'visitors_proof_pages'
        );
        add_submenu_page(
        	VISITORS_PROOF_PAGE_NOTIFICATIONS, 
    		__( 'Support', 'visitorsproof' ), 
    		__( 'Support', 'visitorsproof' ),
            'administrator',
        	VISITORS_PROOF_PAGE_SUPPORT, 
        	'visitors_proof_pages'
        );
    }
}

$vp_cn_obj = $vp_cf7_obj = null;
if (!function_exists('visitors_proof_cn_screen_option')) {
    function visitors_proof_cn_screen_option() {
    	global $vp_cn_obj, $wpdb;
        $option = 'per_page';
    	$args   = array(
    		'label'   => __( 'Custom Notifications', 'visitorsproof' ),
    		'default' => 5,
    		'option'  => 'vp_cn_per_page'
    	);
    
    	add_screen_option( $option, $args );
    	require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/custom-notifications.php';
    	$vp_cn_obj = new Customnotifications_List();
    }
}

if (!function_exists('visitors_proof_cf7_screen_option')) {
    function visitors_proof_cf7_screen_option() {
        global $vp_cf7_obj, $wpdb;
        $option = 'per_page';
        $args   = array(
            'label'   => __( 'CF7 Forms', 'visitorsproof' ),
            'default' => 5,
            'option'  => 'vp_cf7_per_page'
        );
        
        add_screen_option( $option, $args );
        require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/contact-form-7.php';
        $vp_cf7_obj = new Cf7forms_List();
    }
}

if (!function_exists('visitors_proof_pages')) {
    function visitors_proof_pages() {
        global $vp_cn_obj, $vp_cf7_obj, $wpdb;
        require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/pages.php';
    }
}
/* Menu Ends here */

/* Installation and Uninstallation Starts here */
register_activation_hook( __FILE__, 'visitors_proof_install' );
register_deactivation_hook( __FILE__, 'visitors_proof_disable' );
register_uninstall_hook(__FILE__, 'visitors_proof_uninstall');
include_once VISITORS_PROOF_PLUGIN_DIR . 'install-uninstall.php';
/* Installation and Uninstallation Ends here */