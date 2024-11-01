<?php
if (!function_exists('p') ) {
    function p($obj, $exit = true){
        echo '<pre>';
        print_r($obj);
        if($exit) exit;
    }
}

if (!function_exists('visitors_proof_log')) {
    function visitors_proof_log($log){
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once (ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        $content = file_exists(VISITORS_PROOF_LOG_FILE) ? $wp_filesystem->get_contents(VISITORS_PROOF_LOG_FILE) : VISITORS_PROOF_NAME . ' Log';
        $time = current_time('Y-m-d H:i:s');
        $content = "$content\n\n$time : $log";
        $wp_filesystem->put_contents(VISITORS_PROOF_LOG_FILE, $content, 0644);
    }
}

if (!function_exists('is_visitors_proof_woo_commerce_active')) {
    function is_visitors_proof_woo_commerce_active() {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php')) return true;
        return false;
    }
}

if (!function_exists('is_visitors_proof_cf7_active')) {
    function is_visitors_proof_cf7_active() {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) return true;
        return false;
    }
}

if (!function_exists('visitors_proof_wp_obj')) {
    function visitors_proof_wp_obj(){
        global $wp_version;
        return array(
            'site_url' => home_url(),
            'wp_version' => $wp_version,
            'vp_version' => VISITORS_PROOF_VERSION,
            'site_email' => esc_html(get_bloginfo('admin_email')),
            'site_name' => esc_html(get_bloginfo('name')),
            'package' => 'free',
        );
    }
}

if (!function_exists('visitors_proof_settings')) {
    function visitors_proof_settings($key = false, $default = false){
        $settings = get_option('visitors_proof_settings');
        $settings = json_decode($settings);
        if (!$key) return $settings;
        if (isset($settings->{$key})) return $settings->{$key};
        return $default;
    }
}

if (!function_exists('visitors_proof_default_settings')) {
    function visitors_proof_default_settings(){
        $data = array('interval_seconds' => 5, 'display_seconds' => 10, 'theme' => 1, 'position' => 'bottom-left', 'entrance_animation' => 'fadeInDown', 'exit_animation' => 'fadeOutDown', 'loop' => 0, 'random_theme' => 0);
        return $data;
    }
}

if (!function_exists('visitors_proof_redirect')) {
    function visitors_proof_redirect($url = false, $delay = 0){
        if (!$url) $url = $_SERVER['HTTP_REFERER'];
        echo '<script>setTimeout(function (){ location.href = "' . $url . '" }, ' . $delay . ');</script>';
        exit;
    }
}

if(!class_exists('VisitorsProofHelper')){
    class VisitorsProofHelper {
        function visitors_proof_tabs_menus($tab) {
            return get_admin_url(null, 'admin.php?page=' . $tab);
        }
        
        function visitors_proof_notification_categories() {
            return array('credibility' => __( 'Prove Credibility', 'visitorsproof' ), 'demand' => __( 'Instigate Demand', 'visitorsproof' ), 'confidence' => __( 'Boost Confidence', 'visitorsproof' ), 'sentiments' => __( 'Create Sentiments', 'visitorsproof' ), 'social_presence' => __( 'Social Presence', 'visitorsproof' ), 'custom' => __( 'Customization', 'visitorsproof' ), 'cf7-form' => __( 'Contact Form 7', 'visitorsproof' ), 'all' => __( 'All Notifications', 'visitorsproof' ));
        }
        
        function visitors_proof_assets($file, $folder = 'assets') {
            return plugins_url("/$folder/$file", __FILE__);
        }
        
        function visitors_proof_current_tab() {
            return isset($_REQUEST['page']) ? $_REQUEST['page'] : VISITORS_PROOF_PAGE_NOTIFICATIONS;
        }
        
        function visitors_proof_notification_positions(){
            $data = array('top-left' => __( 'Top Left', 'visitorsproof' ), 'top-right' => __( 'Top Right', 'visitorsproof' ), 'bottom-left' => __( 'Bottom Left', 'visitorsproof' ), 'bottom-right' => __( 'Bottom Right', 'visitorsproof' ));
            return $data;
        }
        
        function visitors_proof_notification_entrance_animations(){
            $data = array('fadeIn' => 'fadeIn', 'fadeInDown' => 'fadeInDown');
            return $data;
        }
        
        function visitors_proof_notification_exit_animations(){
            $data = array('fadeOut' => 'fadeOut', 'fadeOutDown' => 'fadeOutDown');
            return $data;
        }
        
        function visitors_proof_notification_themes(){
            $t = __('Theme %s', 'visitorsproof');
            $data = array('1' => $t, '2' => $t);
            return $data;
        }
        
        function visitors_proof_since_types_notify_strings() {
            return array('hour' => __( 'since <b>the last one hour</b>', 'visitorsproof' ), 'day' => __( 'since <b>the last day</b>', 'visitorsproof' ), 'week' => __( 'since <b>last week</b>', 'visitorsproof' ), 'month' => __( '<b>in the last month</b>', 'visitorsproof' ));
        }
        
        function visitors_proof_since_types_dates() {
            return array('hour' => '-1 HOUR', 'day' => '-1 DAY', 'week' => '-1 WEEK', 'month' => '-1 MONTH');
        }
        
        function visitors_proof_since_types() {
            return array('hour', 'day', 'week', 'month');
        }
        
        function visitors_proof_get_ip_location($ip){
            global $wpdb;
            $location = $wpdb->get_row("SELECT ip, city, region, country FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_IPS . "` WHERE ip = '$ip' LIMIT 0, 1");
            if (empty($location)) {
                $attempt = 1;
                $apis = array('ipinfo', 'ip-api');
                check_again:
                $index = rand(0, 1);
                $response = '';
                if($apis[$index] == 'ipinfo'){
                    $url = "https://ipinfo.io/$ip/json";
                }else{
                    $url = "http://ip-api.com/json/$ip";
                }
                $request    = wp_remote_get(
                    $url,
                    array(
                        'timeout'   => 15,
                        'body'      => array(),
                    )
                );
                if (!is_wp_error($request)) $response = wp_remote_retrieve_body($request);
                
                $location 	= json_decode($response);
                $o = new stdClass();
                $o->ip = $ip;
                if(isset($location->ip) || $location->status == 'success'){
                    $o->city = isset($location->city) && $location->city ? $location->city : 'Unknown';
                    $o->region = isset($location->region) && $location->region ? $location->region : 'Unknown';
                    $o->region = isset($location->regionName) ? $location->regionName : $location->region;
                    $o->country = isset($location->countryCode) ? $location->countryCode : $location->country;
                    $o->json = json_encode($location);
                    $o->created = current_time('Y-m-d H:i:s');
                    $t_o = (array)$o;
                    $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_IPS, $t_o);
                }else{
                    if($attempt == 2){
                        $o->city = 'Unknown';
                        $o->region = 'Unknown';
                        $o->country = 'Unknown';
                    }else{
                        $attempt++;
                        goto check_again;
                    }
                }
                return $o;
            }else{
                return $location;
            }
        }
        
        function visitors_proof_get_ip() {
            $ipaddress = '';
            if(isset($_SERVER['REMOTE_ADDR']))
                $ipaddress = $_SERVER['REMOTE_ADDR'];
                else if (isset($_SERVER['HTTP_CLIENT_IP']))
                    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
                    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
                        else if(isset($_SERVER['HTTP_X_FORWARDED']))
                            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
                            else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
                                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
                                else if(isset($_SERVER['HTTP_FORWARDED']))
                                    $ipaddress = $_SERVER['HTTP_FORWARDED'];
                                    else
                                        $ipaddress = 'UNKNOWN';
                                        return $ipaddress == "::1" ? "" /* "157.50.177.141" */ : $ipaddress;
        }
        
        function visitors_proof_current_post_type() {
            global $post;
            return isset($post->post_type) ? $post->post_type : '';
        }
        
        function visitors_proof_page_types() {
            $types = array('All' => 'Across Site', 'Post' => 'All Posts', 'Page' => 'All Pages', 'Home' => 'Home Page', 'Front' => 'Front Page');
            if (is_visitors_proof_woo_commerce_active()) {
                $types = array_merge($types, array('Product' => 'All Products', 'Shop' => 'Shop Page', 'Product Category' => 'Product Category', 'Cart' => 'Cart Page', 'Checkout' => 'Checkout Page'));
            }
            return $types;
        }
        
        function visitors_proof_current_page_type() {
            global $wp_query;
            $vp_wc_installed = is_visitors_proof_woo_commerce_active();
            $loop = 'unknown';
            if ( $wp_query->is_page ) {
                if (is_front_page()){
                    $loop = 'Front';
                }else if($vp_wc_installed && (is_page( 'cart' ) || is_cart())){
                    $loop = 'Cart';
                }else if($vp_wc_installed && (is_page( 'checkout' ) || is_checkout())){
                    $loop = 'Checkout';
                }else{
                    $loop = 'Page';
                }
            } elseif ( $wp_query->is_home ) {
                $loop = 'Home';
            } elseif ( $wp_query->is_single ) {
                if ($wp_query->is_attachment){
                    $loop = 'Attachment';
                }else if($vp_wc_installed && is_product()){
                    $loop = 'Product';
                }else{
                    $loop = 'Post';
                }
            } elseif ( $wp_query->is_category ) {
                $loop = 'Category';
            } elseif ( $wp_query->is_tag ) {
                $loop = 'Tag';
            } elseif ( $wp_query->is_tax ) {
                $loop = 'Taxonomy';
            } elseif ( $wp_query->is_archive ) {
                if ( $wp_query->is_day ) {
                    $loop = 'Day';
                } elseif ( $wp_query->is_month ) {
                    $loop = 'Month';
                } elseif ( $wp_query->is_year ) {
                    $loop = 'Year';
                } elseif ( $wp_query->is_author ) {
                    $loop = 'Author';
                } elseif ($vp_wc_installed && is_shop()){
                    $loop = 'Shop';
                } else{
                    $loop = 'Archive';
                }
            } elseif ( $wp_query->is_search ) {
                $loop = 'Search';
            } elseif ( $wp_query->is_404 ) {
                $loop = '404';
            }
            return $loop;
        }
        function visitors_proof_get_plain_url($url, $full = true){
            if (strpos($url, "//") !== false){
                $url = explode("//", $url);
                $url = rtrim($url[1], "/");
            }
            if (substr($url, 0, 4) == "www."){
                $url = str_replace("www.", "", $url);
            }
            $url = trim($url);
            if ($full) return $url;
            $url = explode("/", $url);
            return $url[0];
        }
        function visitors_proof_get_domain($url){
            if (!$url) return '';
            $pieces = parse_url($url);
            $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
            if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
                return $regs['domain'];
            }
            return false;
        }
        function visitors_proof_search_engines($short = false){
            $se = array("google", "yahoo", "bing", "duckduckgo", "ask", "aol", "yandex", "baidu");
            return $se;
        }
        function visitors_proof_social_medias($short = false){
            $se = array("facebook", "twitter", "linkedin", "youtube", "instagram", "quora", "digg", "medium", "stumbleupon", "weibo", "reddit", "pinterest", "tumblr", "flickr", "meetup", "vk");
            return $se;
        }
        function visitors_proof_svg_icon($id){
            global $wpdb;
            $icon = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS . " WHERE id = '$id'");
            return isset($icon->content) ? $icon->content : '';
        }
        function visitors_proof_random_data_from_array($orders){
            return $orders[rand(0, (count($orders)-1))];
        }
        function visitors_proof_truncate($str, $len) {
            $tail = max(0, $len-10);
            $trunk = substr($str, 0, $tail);
            $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len-$tail))));
            return $trunk;
        }
        function visitors_proof_format_date($date, $format = "d-m-Y"){
            if (!$date || $date == "0000-00-00") return "";
            return date($format, strtotime($date));
        }
        function visitors_proof_add_date($date, $format = "d-m-Y", $add = ""){
            if (!$add) return self::visitors_proof_format_date($date, $format);
            return date($format, strtotime("$add", strtotime($date)));
        }
        function visitors_proof_suffix_s($number, $suffix = 's'){
            return ($number > 1) ? $suffix : '';
        }
        function visitors_proof_get_views($where){
            global $wpdb;
            $views = $wpdb->get_var(
                "SELECT COUNT(*) AS views FROM (
                    SELECT COUNT(*) AS count, 1 AS uuid FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_VISITS . "`
                        WHERE $where GROUP BY DATE(created), ip) AS temp_table
                    GROUP BY uuid"
            );
            return intval($views);
        }
        function visitors_proof_country_list(){
            $countries = '{"AW":{"code3":"ABW","name":"Aruba","value":582.34,"code":"AW"},"AF":{"code3":"AFG","name":"Afghanistan","value":53.08,"code":"AF"},"AO":{"code3":"AGO","name":"Angola","value":23.11,"code":"AO"},"AL":{"code3":"ALB","name":"Albania","value":104.97,"code":"AL"},"AD":{"code3":"AND","name":"Andorra","value":164.43,"code":"AD"},"AE":{"code3":"ARE","name":"United Arab Emirates","value":110.88,"code":"AE"},"AR":{"code3":"ARG","name":"Argentina","value":16.02,"code":"AR"},"AM":{"code3":"ARM","name":"Armenia","value":102.73,"code":"AM"},"AS":{"code3":"ASM","name":"American Samoa","value":278,"code":"AS"},"AG":{"code3":"ATG","name":"Antigua and Barbuda","value":229.46,"code":"AG"},"AU":{"code3":"AUS","name":"Australia","value":3.15,"code":"AU"},"AT":{"code3":"AUT","name":"Austria","value":105.81,"code":"AT"},"AZ":{"code3":"AZE","name":"Azerbaijan","value":118.04,"code":"AZ"},"BI":{"code3":"BDI","name":"Burundi","value":409.82,"code":"BI"},"BE":{"code3":"BEL","name":"Belgium","value":374.45,"code":"BE"},"BJ":{"code3":"BEN","name":"Benin","value":96.42,"code":"BJ"},"BF":{"code3":"BFA","name":"Burkina Faso","value":68.15,"code":"BF"},"BD":{"code3":"BGD","name":"Bangladesh","value":1251.84,"code":"BD"},"BG":{"code3":"BGR","name":"Bulgaria","value":65.66,"code":"BG"},"BH":{"code3":"BHR","name":"Bahrain","value":1848.47,"code":"BH"},"BS":{"code3":"BHS","name":"Bahamas, The","value":39.08,"code":"BS"},"BA":{"code3":"BIH","name":"Bosnia and Herzegovina","value":68.69,"code":"BA"},"BY":{"code3":"BLR","name":"Belarus","value":46.83,"code":"BY"},"BZ":{"code3":"BLZ","name":"Belize","value":16.09,"code":"BZ"},"BM":{"code3":"BMU","name":"Bermuda","value":1307.52,"code":"BM"},"BO":{"code3":"BOL","name":"Bolivia","value":10.05,"code":"BO"},"BR":{"code3":"BRA","name":"Brazil","value":24.84,"code":"BR"},"BB":{"code3":"BRB","name":"Barbados","value":662.78,"code":"BB"},"BN":{"code3":"BRN","name":"Brunei Darussalam","value":80.3,"code":"BN"},"BT":{"code3":"BTN","name":"Bhutan","value":20.93,"code":"BT"},"BW":{"code3":"BWA","name":"Botswana","value":3.97,"code":"BW"},"CF":{"code3":"CAF","name":"Central African Republic","value":7.38,"code":"CF"},"CA":{"code3":"CAN","name":"Canada","value":3.99,"code":"CA"},"CH":{"code3":"CHE","name":"Switzerland","value":211.87,"code":"CH"},"CL":{"code3":"CHL","name":"Chile","value":24.09,"code":"CL"},"CN":{"code3":"CHN","name":"China","value":146.85,"code":"CN"},"CI":{"code3":"CIV","name":"Cote d\'Ivoire","value":74.52,"code":"CI"},"CM":{"code3":"CMR","name":"Cameroon","value":49.58,"code":"CM"},"CD":{"code3":"COD","name":"Congo, Dem. Rep.","value":34.73,"code":"CD"},"CG":{"code3":"COG","name":"Congo, Rep.","value":15.01,"code":"CG"},"CO":{"code3":"COL","name":"Colombia","value":43.85,"code":"CO"},"KM":{"code3":"COM","name":"Comoros","value":427.51,"code":"KM"},"CV":{"code3":"CPV","name":"Cabo Verde","value":133.89,"code":"CV"},"CR":{"code3":"CRI","name":"Costa Rica","value":95.13,"code":"CR"},"CU":{"code3":"CUB","name":"Cuba","value":110.32,"code":"CU"},"CW":{"code3":"CUW","name":"Curacao","value":359.6,"code":"CW"},"KY":{"code3":"CYM","name":"Cayman Islands","value":253.19,"code":"KY"},"CY":{"code3":"CYP","name":"Cyprus","value":126.64,"code":"CY"},"CZ":{"code3":"CZE","name":"Czech Republic","value":136.85,"code":"CZ"},"DE":{"code3":"DEU","name":"Germany","value":236.42,"code":"DE"},"DJ":{"code3":"DJI","name":"Djibouti","value":40.65,"code":"DJ"},"DM":{"code3":"DMA","name":"Dominica","value":98.06,"code":"DM"},"DK":{"code3":"DNK","name":"Denmark","value":135.54,"code":"DK"},"DO":{"code3":"DOM","name":"Dominican Republic","value":220.43,"code":"DO"},"DZ":{"code3":"DZA","name":"Algeria","value":17.05,"code":"DZ"},"EC":{"code3":"ECU","name":"Ecuador","value":65.97,"code":"EC"},"EG":{"code3":"EGY","name":"Egypt, Arab Rep.","value":96.13,"code":"EG"},"ES":{"code3":"ESP","name":"Spain","value":92.93,"code":"ES"},"EE":{"code3":"EST","name":"Estonia","value":31.04,"code":"EE"},"ET":{"code3":"ETH","name":"Ethiopia","value":102.4,"code":"ET"},"FI":{"code3":"FIN","name":"Finland","value":18.08,"code":"FI"},"FJ":{"code3":"FJI","name":"Fiji","value":49.19,"code":"FJ"},"FR":{"code3":"FRA","name":"France","value":122.16,"code":"FR"},"FO":{"code3":"FRO","name":"Faroe Islands","value":35.18,"code":"FO"},"FM":{"code3":"FSM","name":"Micronesia, Fed. Sts.","value":149.91,"code":"FM"},"GA":{"code3":"GAB","name":"Gabon","value":7.68,"code":"GA"},"GB":{"code3":"GBR","name":"United Kingdom","value":271.13,"code":"GB"},"GE":{"code3":"GEO","name":"Georgia","value":53.52,"code":"GE"},"GH":{"code3":"GHA","name":"Ghana","value":123.96,"code":"GH"},"GI":{"code3":"GIB","name":"Gibraltar","value":3440.8,"code":"GI"},"GN":{"code3":"GIN","name":"Guinea","value":50.45,"code":"GN"},"GM":{"code3":"GMB","name":"Gambia, The","value":201.43,"code":"GM"},"GW":{"code3":"GNB","name":"Guinea-Bissau","value":64.57,"code":"GW"},"GQ":{"code3":"GNQ","name":"Equatorial Guinea","value":43.55,"code":"GQ"},"GR":{"code3":"GRC","name":"Greece","value":83.56,"code":"GR"},"GD":{"code3":"GRD","name":"Grenada","value":315.64,"code":"GD"},"GL":{"code3":"GRL","name":"Greenland","value":0.14,"code":"GL"},"GT":{"code3":"GTM","name":"Guatemala","value":154.74,"code":"GT"},"GU":{"code3":"GUM","name":"Guam","value":301.66,"code":"GU"},"GY":{"code3":"GUY","name":"Guyana","value":3.93,"code":"GY"},"HK":{"code3":"HKG","name":"Hong Kong SAR, China","value":6987.24,"code":"HK"},"HN":{"code3":"HND","name":"Honduras","value":81.44,"code":"HN"},"HR":{"code3":"HRV","name":"Croatia","value":74.6,"code":"HR"},"HT":{"code3":"HTI","name":"Haiti","value":393.59,"code":"HT"},"HU":{"code3":"HUN","name":"Hungary","value":108.41,"code":"HU"},"ID":{"code3":"IDN","name":"Indonesia","value":144.14,"code":"ID"},"IM":{"code3":"IMN","name":"Isle of Man","value":146.91,"code":"IM"},"IN":{"code3":"IND","name":"India","value":445.37,"code":"IN"},"IE":{"code3":"IRL","name":"Ireland","value":68.95,"code":"IE"},"IR":{"code3":"IRN","name":"Iran, Islamic Rep.","value":49.29,"code":"IR"},"IQ":{"code3":"IRQ","name":"Iraq","value":85.66,"code":"IQ"},"IS":{"code3":"ISL","name":"Iceland","value":3.35,"code":"IS"},"IL":{"code3":"ISR","name":"Israel","value":394.92,"code":"IL"},"IT":{"code3":"ITA","name":"Italy","value":206.12,"code":"IT"},"JM":{"code3":"JAM","name":"Jamaica","value":266.05,"code":"JM"},"JO":{"code3":"JOR","name":"Jordan","value":106.51,"code":"JO"},"JP":{"code3":"JPN","name":"Japan","value":348.35,"code":"JP"},"KZ":{"code3":"KAZ","name":"Kazakhstan","value":6.59,"code":"KZ"},"KE":{"code3":"KEN","name":"Kenya","value":85.15,"code":"KE"},"KG":{"code3":"KGZ","name":"Kyrgyz Republic","value":31.7,"code":"KG"},"KH":{"code3":"KHM","name":"Cambodia","value":89.3,"code":"KH"},"KI":{"code3":"KIR","name":"Kiribati","value":141.23,"code":"KI"},"KN":{"code3":"KNA","name":"St. Kitts and Nevis","value":210.85,"code":"KN"},"KR":{"code3":"KOR","name":"Korea, Rep.","value":525.7,"code":"KR"},"KW":{"code3":"KWT","name":"Kuwait","value":227.42,"code":"KW"},"LA":{"code3":"LAO","name":"Lao PDR","value":29.28,"code":"LA"},"LB":{"code3":"LBN","name":"Lebanon","value":587.16,"code":"LB"},"LR":{"code3":"LBR","name":"Liberia","value":47.9,"code":"LR"},"LY":{"code3":"LBY","name":"Libya","value":3.58,"code":"LY"},"LC":{"code3":"LCA","name":"St. Lucia","value":291.83,"code":"LC"},"LI":{"code3":"LIE","name":"Liechtenstein","value":235.41,"code":"LI"},"LK":{"code3":"LKA","name":"Sri Lanka","value":338.11,"code":"LK"},"LS":{"code3":"LSO","name":"Lesotho","value":72.59,"code":"LS"},"LT":{"code3":"LTU","name":"Lithuania","value":45.78,"code":"LT"},"LU":{"code3":"LUX","name":"Luxembourg","value":224.72,"code":"LU"},"LV":{"code3":"LVA","name":"Latvia","value":31.51,"code":"LV"},"MO":{"code3":"MAC","name":"Macao SAR, China","value":20405.57,"code":"MO"},"MF":{"code3":"MAF","name":"St. Martin (French part)","value":591.65,"code":"MF"},"MA":{"code3":"MAR","name":"Morocco","value":79.04,"code":"MA"},"MC":{"code3":"MCO","name":"Monaco","value":19249.5,"code":"MC"},"MD":{"code3":"MDA","name":"Moldova","value":108.06,"code":"MD"},"MG":{"code3":"MDG","name":"Madagascar","value":42.79,"code":"MG"},"MV":{"code3":"MDV","name":"Maldives","value":1425.85,"code":"MV"},"MX":{"code3":"MEX","name":"Mexico","value":65.61,"code":"MX"},"MH":{"code3":"MHL","name":"Marshall Islands","value":294.81,"code":"MH"},"MK":{"code3":"MKD","name":"Macedonia, FYR","value":82.52,"code":"MK"},"ML":{"code3":"MLI","name":"Mali","value":14.75,"code":"ML"},"MT":{"code3":"MLT","name":"Malta","value":1366.93,"code":"MT"},"MM":{"code3":"MMR","name":"Myanmar","value":80.98,"code":"MM"},"ME":{"code3":"MNE","name":"Montenegro","value":46.27,"code":"ME"},"MN":{"code3":"MNG","name":"Mongolia","value":1.95,"code":"MN"},"MP":{"code3":"MNP","name":"Northern Mariana Islands","value":119.62,"code":"MP"},"MZ":{"code3":"MOZ","name":"Mozambique","value":36.66,"code":"MZ"},"MR":{"code3":"MRT","name":"Mauritania","value":4.17,"code":"MR"},"MU":{"code3":"MUS","name":"Mauritius","value":622.4,"code":"MU"},"MW":{"code3":"MWI","name":"Malawi","value":191.89,"code":"MW"},"MY":{"code3":"MYS","name":"Malaysia","value":94.92,"code":"MY"},"NA":{"code3":"NAM","name":"Namibia","value":3.01,"code":"NA"},"NC":{"code3":"NCL","name":"New Caledonia","value":15.15,"code":"NC"},"NE":{"code3":"NER","name":"Niger","value":16.32,"code":"NE"},"NG":{"code3":"NGA","name":"Nigeria","value":204.21,"code":"NG"},"NI":{"code3":"NIC","name":"Nicaragua","value":51.1,"code":"NI"},"NL":{"code3":"NLD","name":"Netherlands","value":505.5,"code":"NL"},"NO":{"code3":"NOR","name":"Norway","value":14.34,"code":"NO"},"NP":{"code3":"NPL","name":"Nepal","value":202.18,"code":"NP"},"NR":{"code3":"NRU","name":"Nauru","value":652.45,"code":"NR"},"NZ":{"code3":"NZL","name":"New Zealand","value":17.82,"code":"NZ"},"OM":{"code3":"OMN","name":"Oman","value":14.3,"code":"OM"},"PK":{"code3":"PAK","name":"Pakistan","value":250.63,"code":"PK"},"PA":{"code3":"PAN","name":"Panama","value":54.27,"code":"PA"},"PE":{"code3":"PER","name":"Peru","value":24.82,"code":"PE"},"PH":{"code3":"PHL","name":"Philippines","value":346.51,"code":"PH"},"PW":{"code3":"PLW","name":"Palau","value":46.75,"code":"PW"},"PG":{"code3":"PNG","name":"Papua New Guinea","value":17.85,"code":"PG"},"PL":{"code3":"POL","name":"Poland","value":124.01,"code":"PL"},"PR":{"code3":"PRI","name":"Puerto Rico","value":384.59,"code":"PR"},"KP":{"code3":"PRK","name":"Korea, Dem. People\u2019s Rep.","value":210.69,"code":"KP"},"PT":{"code3":"PRT","name":"Portugal","value":112.72,"code":"PT"},"PY":{"code3":"PRY","name":"Paraguay","value":16.93,"code":"PY"},"PS":{"code3":"PSE","name":"West Bank and Gaza","value":756.07,"code":"PS"},"PF":{"code3":"PYF","name":"French Polynesia","value":76.56,"code":"PF"},"QA":{"code3":"QAT","name":"Qatar","value":221.34,"code":"QA"},"RO":{"code3":"ROU","name":"Romania","value":85.62,"code":"RO"},"RU":{"code3":"RUS","name":"Russian Federation","value":8.81,"code":"RU"},"RW":{"code3":"RWA","name":"Rwanda","value":483.08,"code":"RW"},"SA":{"code3":"SAU","name":"Saudi Arabia","value":15.01,"code":"SA"},"SD":{"code3":"SDN","name":"Sudan","value":16.66,"code":"SD"},"SN":{"code3":"SEN","name":"Senegal","value":80.05,"code":"SN"},"SG":{"code3":"SGP","name":"Singapore","value":7908.72,"code":"SG"},"SB":{"code3":"SLB","name":"Solomon Islands","value":21.42,"code":"SB"},"SL":{"code3":"SLE","name":"Sierra Leone","value":102.47,"code":"SL"},"SV":{"code3":"SLV","name":"El Salvador","value":306.21,"code":"SV"},"SM":{"code3":"SMR","name":"San Marino","value":553.38,"code":"SM"},"SO":{"code3":"SOM","name":"Somalia","value":22.82,"code":"SO"},"RS":{"code3":"SRB","name":"Serbia","value":80.7,"code":"RS"},"ST":{"code3":"STP","name":"Sao Tome and Principe","value":208.24,"code":"ST"},"SR":{"code3":"SUR","name":"Suriname","value":3.58,"code":"SR"},"SK":{"code3":"SVK","name":"Slovak Republic","value":112.94,"code":"SK"},"SI":{"code3":"SVN","name":"Slovenia","value":102.53,"code":"SI"},"SE":{"code3":"SWE","name":"Sweden","value":24.36,"code":"SE"},"SZ":{"code3":"SWZ","name":"Swaziland","value":78.09,"code":"SZ"},"SX":{"code3":"SXM","name":"Sint Maarten (Dutch part)","value":1175.56,"code":"SX"},"SC":{"code3":"SYC","name":"Seychelles","value":205.82,"code":"SC"},"SY":{"code3":"SYR","name":"Syrian Arab Republic","value":100.37,"code":"SY"},"TC":{"code3":"TCA","name":"Turks and Caicos Islands","value":36.74,"code":"TC"},"TD":{"code3":"TCD","name":"Chad","value":11.48,"code":"TD"},"TG":{"code3":"TGO","name":"Togo","value":139.85,"code":"TG"},"TH":{"code3":"THA","name":"Thailand","value":134.79,"code":"TH"},"TJ":{"code3":"TJK","name":"Tajikistan","value":62.94,"code":"TJ"},"TM":{"code3":"TKM","name":"Turkmenistan","value":12.05,"code":"TM"},"TL":{"code3":"TLS","name":"Timor-Leste","value":85.32,"code":"TL"},"TO":{"code3":"TON","name":"Tonga","value":148.78,"code":"TO"},"TT":{"code3":"TTO","name":"Trinidad and Tobago","value":266.07,"code":"TT"},"TN":{"code3":"TUN","name":"Tunisia","value":73.4,"code":"TN"},"TR":{"code3":"TUR","name":"Turkey","value":103.31,"code":"TR"},"TV":{"code3":"TUV","name":"Tuvalu","value":369.9,"code":"TV"},"TZ":{"code3":"TZA","name":"Tanzania","value":62.74,"code":"TZ"},"UG":{"code3":"UGA","name":"Uganda","value":206.9,"code":"UG"},"UA":{"code3":"UKR","name":"Ukraine","value":77.69,"code":"UA"},"UY":{"code3":"URY","name":"Uruguay","value":19.68,"code":"UY"},"US":{"code3":"USA","name":"United States","value":35.32,"code":"US"},"UZ":{"code3":"UZB","name":"Uzbekistan","value":74.87,"code":"UZ"},"VC":{"code3":"VCT","name":"St. Vincent and the Grenadines","value":281.14,"code":"VC"},"VE":{"code3":"VEN","name":"Venezuela, RB","value":35.79,"code":"VE"},"VG":{"code3":"VGB","name":"British Virgin Islands","value":204.41,"code":"VG"},"VI":{"code3":"VIR","name":"Virgin Islands (U.S.)","value":307.17,"code":"VI"},"VN":{"code3":"VNM","name":"Vietnam","value":304.99,"code":"VN"},"VU":{"code3":"VUT","name":"Vanuatu","value":22.18,"code":"VU"},"WS":{"code3":"WSM","name":"Samoa","value":68.95,"code":"WS"},"YE":{"code3":"YEM","name":"Yemen, Rep.","value":52.25,"code":"YE"},"ZA":{"code3":"ZAF","name":"South Africa","value":46.18,"code":"ZA"},"ZM":{"code3":"ZMB","name":"Zambia","value":22.32,"code":"ZM"},"ZW":{"code3":"ZWE","name":"Zimbabwe","value":41.75,"code":"ZW"},"TW":{"code3":"TWN","name":"Taiwan","value":41.75,"code":"TW"}}';
            return json_decode($countries);
        }
        function visitors_proof_get_country_data($country_code){
            $countries = self::visitors_proof_country_list();
            if(isset($countries->{$country_code})){
                return $countries->{$country_code};
            }
            return false;
        }
        function visitors_proof_get_country($country_code, $default = 'Unknown'){
            $countries = self::visitors_proof_country_list();
            if(isset($countries->{$country_code})){
                return $countries->{$country_code}->name;
            }
            return $default;
        }
        
        function visitors_proof_most_sold_products($from_date, $to_date = false, $limit = 1, $product = array('id' => false, 'list' => false)) {
            include_once(WC()->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
            $wc_report = new WC_Admin_Report();
            $where = array(
                array(
                    'key'      => 'DATE(posts.post_date)',
                    'value'    => $from_date,
                    'operator' => '>=',
                ),
            );
            if($to_date){
                $where[] = array(
                    'key'      => 'DATE(posts.post_date)',
                    'value'    => $to_date,
                    'operator' => '<=',
                );
            }
            
            if ($product['id']) {
                $report_data = array(
                    'order_id' => array(
                        'type'     => 'order_item',
                        'function' => $product['list'] ? '' : 'COUNT',
                        'name'     => 'order_id',
                    ),
                    '_qty' => array(
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => 'SUM',
                        'name' => 'quantity'
                    ),
                    '_product_id' => array(
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => '',
                        'name' => 'product_id'
                    ),
                    '_line_subtotal' => array(
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => 'SUM',
                        'name' => 'gross'
                    ),
                );
            }else{
                $report_data = array(
                    '_qty' => array(
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => 'SUM',
                        'name' => 'quantity'
                    ),
                    '_line_subtotal' => array(
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => 'SUM',
                        'name' => 'gross'
                    ),
                    '_product_id' => array(
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => '',
                        'name' => 'product_id'
                    ),
                    'order_item_name' => array(
                        'type'     => 'order_item',
                        'function' => '',
                        'name'     => 'order_item_name',
                    ),
                );
            }
            
            $data = $wc_report->get_order_report_data( array(
                'data'          => $report_data,
                'where'         => $where,
                'where_meta' => $product['id'] ? array(
                    'relation' => 'AND',
                    array(
                        'type' => 'order_item_meta',
                        'meta_key' => array('_product_id'),
                        'meta_value' => $product['id'],
                        'operator' => '=',
                    )
                ) : array(),
                'group_by'      => $product['id'] && $product['list'] ? 'order_id' :  'product_id',
                'order_by'      => $product['id'] ? ' order_id DESC' : 'quantity DESC',
                'query_type'    => $limit == 1 ? 'get_row' : 'get_results',
                'limit'         => $limit == -1 ? '' : $limit,
                'order_status'  => array( 'completed', 'processing' ),
            ) );
            return $data;
        }
    }
}