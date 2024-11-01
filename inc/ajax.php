<?php
global $wpdb, $vpchr;

function visitors_proof_replace_content($ct, $params, $content){
	preg_match_all("/\[[^]]*\]/", $content, $matches);
	$keys = $matches[0];
	$values = array();
	foreach ($keys as $ki => $k) {
		$rc = '%' . ($ki+1) . '$s';
		if(in_array($k, array('[visits]', '[visitors]', '[orders]', '[variants]', '[times]', '[items]')) || ($k == '[hours]' && $ct == 'Recent Product Views')) {
			$suffix = explode(' ', $params[$k], 2);
			if($k == '[visitors]' && in_array($ct, array('Recent Product Views', 'Live Product Views'))) $suffix[1] = 'people';
			if($k == '[visits]' && $ct = 'Most Visited Product') $suffix[1] = 'Unique Views';
			$rc .= ' ' . $suffix[1];
			$params[$k] = $suffix[0];
		}
		$content = str_replace($k, $rc, $content);
		$values []= $params[$k];
	}
	$content = vsprintf(__( $content, 'visitorsproof' ), $values);
	return $content;
}

function visitors_proof_notify_content($post, $results, $visit_id) {
    global $wpdb, $vpchr;
    $results['ordering'] = $post['ordering'];
    $sql = is_visitors_proof_woo_commerce_active() ? '' : ' AND woocommerce = 0';
    $current_type = $skipped_type = '';
    $settings = visitors_proof_settings();
    
    vp_start_over:/* Check next notification */
    if($current_type) $skipped_type = $current_type;
    $enabled_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE status = 1");
    $cnotification = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE status = 1 AND ordering > '$results[ordering]' $sql ORDER BY ordering ASC LIMIT 0, 1");
    if (empty($cnotification)) {
        if($enabled_count > 0 && visitors_proof_settings('loop')) {
            $results['ordering'] = 0;
            goto vp_start_over;
        }
        $results['done'] = true;
    }else{
        $results['done'] = false;
        $results['ordering'] = $cnotification->ordering;
        $results['notify_id'] = $cnotification->id;
        $site_name = esc_html(get_bloginfo('name'));
        $current_type = $cnotification->type;
        if ($skipped_type == $current_type) {/* Check if same notification is being checked */
            $results['done'] = true;
            goto vp_return_response;
        }
        $content = $cnotification->content;
        if($cnotification->is_custom == 1){
            $rules = json_decode($cnotification->rules);
            if ($rules->type != 'All' && $rules->type != $post['page_type']) goto vp_start_over;
            $image_svg = $vpchr->visitors_proof_svg_icon($cnotification->icon);
        }else if($cnotification->is_custom == 2){
            if(!isset($post['cf7_id'])) $post['cf7_id'] = 0;
            $rules = json_decode($cnotification->rules);
            if ($rules->type != 'All' && $cnotification->type != $post['cf7_id']) goto vp_start_over;
            $cf7_entry = $wpdb->get_row("SELECT * FROM (SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_INTEGRATIONS . "` WHERE type = 'cf7-form' AND type_id = '$cnotification->type' ORDER BY id DESC LIMIT 0, 10) AS temp ORDER BY RAND() LIMIT 0, 1");
            if(empty($cf7_entry)) goto vp_start_over;
            preg_match_all("/{[^}]+}/", $content, $matches);
            $c_cf7_fields = $matches[0];
            $c_cf7_values = json_decode($cf7_entry->values);
            foreach ($c_cf7_fields as $c_f) {
                $c_f_curly_removed = trim($c_f, '{}');
                if(!isset($c_cf7_values->{$c_f_curly_removed})) continue;
                $c_value = $c_cf7_values->{$c_f_curly_removed};
                if($c_f_curly_removed == 'vp-user-country') $c_value = $vpchr->visitors_proof_get_country($c_value);
                $content = str_replace($c_f, "<b>$c_value</b>", $content);
            }
            $image_svg = $vpchr->visitors_proof_svg_icon($cnotification->icon);
        }else{
            $cvisit = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_VISITS . "` WHERE id = '$visit_id'");
            $options = json_decode($cnotification->options);
            $fields = isset($options->fields) ? $options->fields : array();
            $image_svg = $slug = '';
            $since_types = $vpchr->visitors_proof_since_types();
            $since_strings = $vpchr->visitors_proof_since_types_notify_strings();
            $since_dates = $vpchr->visitors_proof_since_types_dates();
			$lang_code = get_locale();
            
            if (is_visitors_proof_woo_commerce_active()) {
                $product_char_length = 40;
                $product_slug = '';
                $country_code = $cvisit->country;
                $country_name = $vpchr->visitors_proof_get_country($cvisit->country);
                $state_name = $cvisit->region;
                $states = WC()->countries->get_states($country_code);
                $state_code = array_search($state_name, $states);
                $currency_symbol = get_woocommerce_currency_symbol();
                $currency = get_woocommerce_currency();
                $order_orgs = false;
                
                if ($post['page_type'] == 'Product' && $post['post_type'] == 'product' && !in_array($current_type, array('No of Visits', 'Location Visits', 'Facebook Like', 'Twitter Follow', 'Linkedin Follow', 'Free Delivery'))) {
                    $product = wc_get_product($post['page_id']);
                    $product_name = $vpchr->visitors_proof_truncate($product->get_title(), $product_char_length);
                    $product_image = get_the_post_thumbnail_url($product->get_ID(), 'thumbnail');
                    if ($product_image) $image_svg = '<img src="' . $product_image . '" />';
                    $product_slug = $product->get_slug();
                }
                
                if($current_type == 'Regional Order'){
                    $order_orgs = array(
                        'limit'=> 10,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'return' => 'ids',
                        'type'=> 'shop_order',
                        'status'=> array('wc-processing', 'wc-completed'),
                        'billing_country' => $country_code,
                    );
                    if ($state_code) $order_orgs['billing_state'] = $state_code;
                    $orders = wc_get_orders($order_orgs);
                }
                
                if (in_array($current_type, array('Purchased From', 'Purchased By', 'Anonymous Purchase', 'Recent Sales'))) {
                    $order_orgs = array(
                        'limit'=> (in_array($current_type, array('Purchased From', 'Purchased By', 'Anonymous Purchase')) ? 50 : 1),
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'return' => 'ids',
                        'type'=> 'shop_order',
                        'status'=> array('wc-processing', 'wc-completed'),
                    );
                    $orders = wc_get_orders($order_orgs);
                }
                
                if ($order_orgs) {
                    if (empty($orders)) goto vp_start_over;
                    
                    $o_id = $vpchr->visitors_proof_random_data_from_array($orders);
                    $c_order = wc_get_order($o_id);
                    $c_cus = $c_order->get_data();
                    $c_cus = $c_cus['billing'];
                    
                    $customer_name = $c_cus['first_name'] . ' ' . $c_cus['last_name'];
                    $items = $c_order->get_items();
                    
                    $p_ids = array();
                    foreach ($items as $i) {
                        $p_ids []= $i->get_product_id();
                    }
                    
                    $p_id = $vpchr->visitors_proof_random_data_from_array($p_ids);
                    $product = wc_get_product($p_id);
                    $product_name = $vpchr->visitors_proof_truncate($product->get_title(), $product_char_length);
                    $product_image = get_the_post_thumbnail_url($product->get_ID(), 'thumbnail');
                    $product_slug = $product->get_slug();
                    if ($product_image) $image_svg = '<img src="' . $product_image . '" />';
                }
            }
            
			$ct = $current_type;
            switch ($current_type) {
                case 'Regional Order':
                	$content = visitors_proof_replace_content($ct, array('[customer]' => $customer_name, '[location]' => ($state_name ? "$state_name, " : '') . $country_name, '[product]' => $product_name), $content);
                    break;
                case 'Recent Product Views':
                    if ($post['page_type'] == 'Product' && $post['post_type'] == 'product') {
                        $time = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", "-" . $fields->hours->value . " HOUR");
                        $views = $wpdb->get_results("SELECT id FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_VISITS . "` WHERE post_type = 'product' AND page_type = 'Product' AND page_id = '$post[page_id]' AND created >= '$time' GROUP BY ip");
                        $views = count($views);
                        if(!$views) goto vp_start_over;
                        $content = visitors_proof_replace_content($ct, array('[visitors]' => $views . ' visitor' . $vpchr->visitors_proof_suffix_s($views), '[hours]' => $fields->hours->value . ' hours', '[product]' => $product_name), $content);
                    }else{
                        goto vp_start_over;
                    }
                    break;
                case 'No of Visits':
                    $from_date = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", $since_dates[$fields->hours->value]);
                    $where = "created >= '$from_date'";
                    $views = $vpchr->visitors_proof_get_views($where);
                    if(!$views) goto vp_start_over;
                    $content = visitors_proof_replace_content($ct, array('[company]' => $site_name, '[visits]' => $views . ' visit' . $vpchr->visitors_proof_suffix_s($views), '[hours]' => $since_strings[$fields->hours->value]), $content);
                    break;
                case 'Location Visits':
                    $from_date = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", $since_dates[$fields->hours->value]);
                    $views = $wpdb->get_row("SELECT COUNT(DISTINCT ip) AS views, city, region FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_VISITS . "` WHERE created >= '$from_date' GROUP BY city ORDER BY RAND() LIMIT 0, 1");
                    if (!$views) goto vp_start_over;
                    $content = visitors_proof_replace_content($ct, array('[company]' => $site_name, '[visits]' => $views->views . ' visit' . $vpchr->visitors_proof_suffix_s($views->views), '[hours]' => $since_strings[$fields->hours->value], '[location]' => "$views->city, $views->region"), $content);
                    break;
                case 'No of Orders':
                    $from_date = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", $since_dates[$fields->hours->value]);
                    $to_date = current_time('Y-m-d H:i:s');
                    $orders = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}posts` WHERE post_status IN ('wc-processing', 'wc-completed') AND post_type = 'shop_order' AND post_date BETWEEN '$from_date' AND '$to_date'");
                    $orders = intval($orders);
                    $content = visitors_proof_replace_content($ct, array('[company]' => $site_name, '[orders]' => $orders . ' order' . $vpchr->visitors_proof_suffix_s($orders), '[hours]' => $since_strings[$fields->hours->value]), $content);
                    break;
                case 'Stock':
                    if ($post['page_type'] == 'Product' && $post['post_type'] == 'product') {
                        $quantity = $product->get_stock_quantity();
                        if($quantity == ''){
                            $content = '<b>' . __( 'In stock', 'visitorsproof' ) . '</b>';
                        }else if($quantity == 0){
                            $content = __( 'No stock available', 'visitorsproof' );
                        }else if($quantity <= 10){
                            $content = sprintf( __( 'Almost <b>sold!</b> Only <b>%s items</b> left. Buy Now', 'visitorsproof' ), $quantity );
                        }else if($quantity > 10){
                            $content = sprintf( __( '<b>%s items</b> in stock', 'visitorsproof' ), $quantity );
                        }
                        $product_image = '';
                        unset($product);
                    }else{
                        goto vp_start_over;
                    }
                    break;
                case 'Purchased From':
                    $country_name = $vpchr->visitors_proof_get_country($c_cus['country']);
                    $content = visitors_proof_replace_content($ct, array('[customer]' => $customer_name, '[location]' => "$c_cus[city], $country_name", '[product]' => $product_name), $content);
                    break;
                case 'Most Visited Product':
                    $from_date = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", "-" . $fields->min->value ." HOUR");
                    $a_page = $wpdb->get_row("
                        SELECT COUNT(*) AS views, page_id FROM (
                            SELECT page_id, ip FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_VISITS . "` 
                                WHERE created >= '$from_date' AND post_type = 'product' AND page_type = 'Product' 
                                GROUP BY ip, page_id ) AS temp_table
                            GROUP BY page_id ORDER BY views DESC LIMIT 0, 1
                    ");
                    if (empty($a_page)) goto vp_start_over;
                    $product = wc_get_product($a_page->page_id);
                    $product_name = $vpchr->visitors_proof_truncate($product->get_title(), $product_char_length);
                    $product_image = get_the_post_thumbnail_url($product->get_ID(), 'thumbnail');
                    if ($product_image) $image_svg = '<img src="' . $product_image . '" />';
                    $product_slug = $product->get_slug();
                    $content = visitors_proof_replace_content($ct, array('[visits]' => $a_page->views . ' visit' . $vpchr->visitors_proof_suffix_s($a_page->views), '[product]' => $product_name), $content);
                    break;
                case 'Purchased By':
                    $content = visitors_proof_replace_content($ct, array('[customer]' => $customer_name, '[product]' => $product_name), $content);
                    break;
                case 'Anonymous Purchase':
                    $country_name = $vpchr->visitors_proof_get_country($c_cus['country']);
                    $content = visitors_proof_replace_content($ct, array('[customer]' => 'Someone', '[location]' => "$c_cus[city], $country_name", '[product]' => $product_name), $content);
                    break;
                case 'Most Sold Product':
                    $from_date = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", $since_dates[$fields->hours->value]);
                    $data = $vpchr->visitors_proof_most_sold_products($from_date);
                    if(empty($data)){
                        goto vp_start_over;
                    }else{
                        $product = wc_get_product($data->product_id);
                        $product_name = $vpchr->visitors_proof_truncate($product->get_title(), $product_char_length);
                        $product_image = get_the_post_thumbnail_url($product->get_ID(), 'thumbnail');
                        if ($product_image) $image_svg = '<img src="' . $product_image . '" />';
                        $product_slug = $product->get_slug();
                        $content = visitors_proof_replace_content($ct, array('[product]' => $product_name), $content);
                    }
                    break;
                case 'Product Variants':
                    if ($post['page_type'] == 'Product' && $post['post_type'] == 'product') {
                        if($product->is_type( 'variable' )){
                            $available_variations = $product->get_available_variations();
                            $variants = count($available_variations);
                            $prices = array();
                            foreach ($available_variations as $value) {
                                $prices[] = $value['display_price'];
                            }
                            sort($prices);
                            $price_format = get_woocommerce_price_format();
                            $from_price = str_replace(array('%1$s', '%2$s'), array($currency_symbol, $prices[0]), $price_format);
                            $to_price = str_replace(array('%1$s', '%2$s'), array($currency_symbol, $prices[$variants-1]), $price_format);
                            if($variants == 3){
                            	$content = sprintf( __( '<b>%1$s</b> has only one variant with price of <b>%2$s</b>', 'visitorsproof' ), $product_name, $from_price );
                            }else{
                                $content = visitors_proof_replace_content($ct, array('[product]' => $product_name, '[variants]' => $variants . ' variant' . $vpchr->visitors_proof_suffix_s($variants), '[price_start]' => $from_price, '[price_to]' => $to_price), $content);
                            }
                        }else{
                            $content = sprintf( __( '<b>%s</b> is not a <b>variable product</b>', 'visitorsproof' ), $product_name );
                        }
                    }else{
                        goto vp_start_over;
                    }
                    break;
                case 'Free Delivery':
                    global $woocommerce;
                    $params = new stdClass();
                    $params->cart_total = $woocommerce->cart->get_cart_contents_total();
                    $params->shipping_amount = 0;
                    if(isset($woocommerce->session->get('shipping_for_package_0')['rates']) && $woocommerce->session->get('shipping_for_package_0')['rates']){
                        foreach( $woocommerce->session->get('shipping_for_package_0')['rates'] as $method_id => $rate ){
                            if( $woocommerce->session->get('chosen_shipping_methods')[0] == $method_id ){
                                $rate_label = $rate->label;
                                $rate_cost_excl_tax = floatval($rate->cost);
                                $rate_taxes = 0;
                                foreach ($rate->taxes as $rate_tax) $rate_taxes += floatval($rate_tax);
                                $rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;
                                
                                $params->shipping_label = $rate_label;
                                preg_match("/\d+\.\d+/", strip_tags($woocommerce->cart->get_cart_shipping_total()), $match);
                                $params->shipping_amount = isset($match[0]) ? $match[0] : 0;
                                $params->tax = $rate_cost_incl_tax;
                                break;
                            }
                        }
                    }
                    if($params->shipping_amount > 0 && $params->cart_total >= $fields->min->value){
                        $remaining = $fields->amount->value - $params->cart_total;
                        $price = str_replace(array('%1$s', '%2$s'), array($currency_symbol, $remaining), get_woocommerce_price_format());
                        $content = visitors_proof_replace_content($ct, array('[price]' => $price), $content);
                    }else{
                        goto vp_start_over;
                    }
                    break;
                case 'Live Product Views':
                    if ($post['page_type'] == 'Product' && $post['post_type'] == 'product') {
                        $time = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", "-5 MINUTE");
                        $views = $wpdb->get_results("SELECT id FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_VISITS . "` WHERE post_type = 'product' AND page_type = 'Product' AND page_id = '$post[page_id]' AND created >= '$time' GROUP BY ip");
                        $views = count($views);
                        if($views >= $fields->min->value){
                            $content = visitors_proof_replace_content($ct, array('[visitors]' => $views . ' visitor' . $vpchr->visitors_proof_suffix_s($views), '[product]' => $product_name), $content);
                        }else{
                            goto vp_start_over;
                        }
                    }else{
                        goto vp_start_over;
                    }
                    break;
                case 'Recent Sales':
                    $country_name = $vpchr->visitors_proof_get_country($c_cus['country']);
                    $content = visitors_proof_replace_content($ct, array('[customer]' => $customer_name, '[location]' => "$c_cus[city], $country_name", '[product]' => $product_name), $content);
                    break;
                case 'Recent Purchases':
                    $from_date = $vpchr->visitors_proof_add_date(current_time('Y-m-d H:i:s'), "Y-m-d H:i:s", $since_dates[$fields->hours->value]);
                    $data = $vpchr->visitors_proof_most_sold_products($from_date, false, 10);
                    if(empty($data)){
                        goto vp_start_over;
                    }else{
                        $data = $vpchr->visitors_proof_random_data_from_array($data);
                        $product = wc_get_product($data->product_id);
                        $product_name = $vpchr->visitors_proof_truncate($product->get_title(), $product_char_length);
                        $product_image = get_the_post_thumbnail_url($product->get_ID(), 'thumbnail');
                        if ($product_image) $image_svg = '<img src="' . $product_image . '" />';
                        $product_slug = $product->get_slug();
                        $content = visitors_proof_replace_content($ct, array('[times]' => $data->quantity . ' time' . $vpchr->visitors_proof_suffix_s($data->quantity), '[product]' => $product_name, '[hours]' => $since_strings[$fields->hours->value]), $content);
                    }
                    break;
                case 'Facebook Like':
                    $content = str_replace(array('[company]', '[locale]'), array($fields->url->value, $lang_code), $content);
                    break;
                case 'Twitter Follow':
					$content = str_replace(array('[company]', '[locale]'), array($fields->url->value, substr($lang_code, 0, 2)), $content);
                    break;
                case 'Linkedin Follow':
                    $content = str_replace(array('[company]', '[locale]'), array($fields->url->value, $lang_code), $content);
                    break;
                default:
                    break;
            }
        }
        $image_svg = $image_svg ? $image_svg : $vpchr->visitors_proof_svg_icon($cnotification->icon);
        $results['image_svg'] = $image_svg;
        $results['content'] = $content;
        $results['slug'] = isset($product) && !empty($product) ? get_permalink($product->get_ID()) : '';
        
        vp_return_response:
        $action = array();
        $action['type'] = 'Served';
        $action['visit_id'] = $visit_id;
        $action['notify_id'] = $results['notify_id'];
        $action['created'] = current_time('Y-m-d H:i:s');
        if (isset($post['user']['id'])) {
            $action['customer_id'] = $post['user']['id'];
        }
        $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_ACTIONS, $action);
    }
    $results['settings'] = $settings;
    echo json_encode($results);
    wp_die();
}

function visitors_proof_sanitize_data($post) {
    foreach ($post as $pk => $pv) {
        if(in_array($pk, array('page_id', 'ordering', 'visit_id', 'notify_id'))){
            $post[$pk] = absint($pv);
        }else{
            if(is_array($post[$pk])){
                foreach ($post[$pk] as $pik => $piv) {
                    $post[$pk][$pik] = sanitize_text_field($piv);
                }
            }else{
                $post[$pk] = sanitize_text_field($pv);
            }
        }
    }
    return $post;
}

$callback = sanitize_text_field($_GET['callback']);
switch ($callback) {
    case 'site_content':
        if(current_user_can('editor') || current_user_can('administrator')) {
            $content = '';
            $request    = wp_remote_get(
                home_url(),
                array(
                    'timeout'   => 15,
                    'body'      => array(),
                )
            );
            if (!is_wp_error($request)) $content = wp_remote_retrieve_body($request);
            
            $content = preg_replace("/<body\h+?\s*?class=\"/", "<body class=\"visitors-proof-preview ", $content);
            echo $content;
        }
        wp_die();
        break;
    case 'status':
        update_option('visitors_proof_enabled', absint($_POST['status']));
        wp_die();
        break;
    case 'icons':
        if(current_user_can('editor') || current_user_can('administrator')) {
            $visitors_proof_cn_id = absint($_POST['visitors_proof_cn_id']);
            $cnotification = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE id = '$visitors_proof_cn_id'");
            $icons = $wpdb->get_results("SELECT id, REPLACE(name, '.svg', '') AS text, CONCAT(content, ' <span class=\"vp-icon-text\">- ', REPLACE(name, '.svg', ''), '</span>') AS content FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS. "` ORDER BY id ASC");
            $icons_kp = $wpdb->get_results("SELECT id, REPLACE(name, '.svg', '') AS name, content FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS. "` ORDER BY id ASC");
            $n_icons_kp = array();
            foreach ($icons_kp as $k => $d) {
                $n_icons_kp[$d->id] = $d;
            }
            echo json_encode(array('icons' => $icons, 'icons_kp' => $n_icons_kp, 'cn_icon' => isset($cnotification->icon) ? $cnotification->icon : 1));
        }
        wp_die();
        break;
	case 'cf7-forms':
        if(current_user_can('editor') || current_user_can('administrator')) {
            $visitors_proof_cn_id = absint($_POST['visitors_proof_cn_id']);
            $cnotification = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE id = '$visitors_proof_cn_id'");
            $icons = $wpdb->get_results("SELECT id, REPLACE(name, '.svg', '') AS text, CONCAT(content, ' <span class=\"vp-icon-text\">- ', REPLACE(name, '.svg', ''), '</span>') AS content FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS. "` ORDER BY id ASC");
            $icons_kp = $wpdb->get_results("SELECT id, REPLACE(name, '.svg', '') AS name, content FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS. "` ORDER BY id ASC");
            $n_icons_kp = array();
            foreach ($icons_kp as $k => $d) {
                $n_icons_kp[$d->id] = $d;
            }
            if (isset($cnotification->icon)) {
                $e_sql = " AND id != '$visitors_proof_cn_id'";
                $cn_icon = $cnotification->icon;
                $cf7_form = $cnotification->type;
            }else{
                $e_sql = '';
                $cn_icon = 1;
                $cf7_form = '';
            }
            $e_sql = isset($cnotification->icon) ? " AND id != '$visitors_proof_cn_id'" : "";
            $cf7_forms = $wpdb->get_row("SELECT GROUP_CONCAT(type) AS ids FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE is_custom = 2 $e_sql");
            $cf7_forms = $cf7_forms->ids;
            $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1, 'post_status' => 'publish', 'exclude' => explode(',', $cf7_forms));
            $forms = get_posts($args);
            $new_forms = array();
            foreach ($forms as $f) {
                $contact_form = WPCF7_ContactForm::get_instance($f->ID);
                $form_fields = $contact_form->scan_form_tags();
                $ids = wp_list_pluck($form_fields, 'name');
                $ids = array_values(array_unique(array_filter($ids)));
                
                $new_forms[$f->ID] = array('ID' => $f->ID, 'post_title' => $f->post_title, 'ids' => $ids);
            }
            echo json_encode(array('forms' => $new_forms, 'icons' => $icons, 'icons_kp' => $n_icons_kp, 'cn_icon' => $cn_icon, 'cf7_form' => $cf7_form));
        }
        wp_die();
        break;
    case 'track':
        if(!get_option('visitors_proof_enabled')) {
            echo json_encode(array('done' => true));
            wp_die();
        }
        $post = visitors_proof_sanitize_data($_POST);
        if (!$post) exit;

        $keys = array('product', 'user', 'ordering', 'visit_id', 'notify_id', 'slug', 'cf7_id');
        $data = array_diff_key($post, array_flip($keys));
        $data['ip'] = $vpchr->visitors_proof_get_ip();
        $ip_location = $vpchr->visitors_proof_get_ip_location($data['ip']);
        $data['city'] = $ip_location->city;
        $data['region'] = $ip_location->region;
        $data['country'] = $ip_location->country;
        
        $r_domain = $vpchr->visitors_proof_get_plain_url($data['referrer'], false);
        $r_type = "Direct";
        $data['referral_domain'] = $r_domain = $vpchr->visitors_proof_get_domain($r_domain);
        $r_domain = explode(".", $r_domain);
        $r_domain = strtolower($r_domain[0]);
        
        if (in_array($r_domain, $vpchr->visitors_proof_search_engines())){
            $r_type = "Search Engine";
        }else if (in_array($r_domain, $vpchr->visitors_proof_social_medias())){
            $r_type = "Social Media";
        }else if (!empty($r_domain)){
            $r_type = "Referral";
        }
        $data['referral_type'] = $r_type;
        
        if(in_array($data['os'], array("Linux", "Mac", "Windows"))){
            $data['device_type'] = "Desktop";
        }else if(in_array($data['os'], array("Android", "iPhone/iPod"))){
            $data['device_type'] = "Mobile";
        }else if(in_array($data['os'], array("Tablet"))){
            $data['device_type'] = "Tablet";
        }else{
            $data['device_type'] = "Desktop";
        }
        if (isset($post['user']['id'])) {
            $data['customer_id'] = $post['user']['id'];
        }
        $data['created'] = $data['updated'] = current_time('Y-m-d H:i:s');
        $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_VISITS, $data);
        
        $results = array();
        $results['visit_id'] = $wpdb->insert_id;
        visitors_proof_notify_content($post, $results, $results['visit_id']);
        break;
    case 'notify':
        if(!get_option('visitors_proof_enabled')) {
            echo json_encode(array('done' => true));
            wp_die();
        }
        $results = array();
        $post = visitors_proof_sanitize_data($_POST);
        visitors_proof_notify_content($post, $results, $post['visit_id']);
        break;
    case 'closed':
        $post = visitors_proof_sanitize_data($_POST);
        $action = array();
        $action['type'] = 'Closed';
        $action['visit_id'] = $post['visit_id'];
        $action['notify_id'] = $post['notify_id'];
        $action['created'] = current_time('Y-m-d H:i:s');
        if (isset($post['user']['id'])) {
            $action['customer_id'] = $post['user']['id'];
        }
        $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_ACTIONS, $action);
        break;
    case 'clicked':
        $post = visitors_proof_sanitize_data($_POST);
        $action = array();
        $action['type'] = 'Clicked';
        $action['visit_id'] = $post['visit_id'];
        $action['notify_id'] = $post['notify_id'];
        $action['created'] = current_time('Y-m-d H:i:s');
        if (isset($post['user']['id'])) {
            $action['customer_id'] = $post['user']['id'];
        }
        $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_ACTIONS, $action);
        echo json_encode(array('link' => ''));
        wp_die();
        break;
    default:
        break;
}