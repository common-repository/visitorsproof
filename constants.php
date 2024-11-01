<?php
define('VISITORS_PROOF_NAME', 'Visitorsproof');
define('VISITORS_PROOF_VERSION', '1.0.1');
define('VISITORS_PROOF_MINIMUM_WP_VERSION', '5.6');
define('VISITORS_PROOF_LOG_FILE', plugin_dir_path(__FILE__) . 'logs.log');
define('VISITORS_PROOF_WC_INSTALLED', class_exists('WC_Autoloader'));

/* Pages */
define('VISITORS_PROOF_PAGE_NOTIFICATIONS', 'visitors-proof-notifications');
define('VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS', 'visitors-proof-custom-notifications');
define('VISITORS_PROOF_PAGE_CF7', 'visitors-proof-cf7');
define('VISITORS_PROOF_PAGE_SETTINGS', 'visitors-proof-settings');
define('VISITORS_PROOF_PAGE_REPORTS', 'visitors-proof-reports');
define('VISITORS_PROOF_PAGE_SUPPORT', 'visitors-proof-support');

define('VISITORS_PROOF_MENU_NOTIFICATIONS', 'All Notifications');
define('VISITORS_PROOF_MENU_CUSTOM_NOTIFICATIONS', 'Custom Notifications');
define('VISITORS_PROOF_MENU_CF7', 'Contact Form 7');
define('VISITORS_PROOF_MENU_SETTINGS', 'Settings');
define('VISITORS_PROOF_MENU_REPORTS', 'Reports');
define('VISITORS_PROOF_MENU_SUPPORT', 'Support');

/* Tables */
define('VISITORS_PROOF_TABLE_VISITS', 'visitors_proof_visits');
define('VISITORS_PROOF_TABLE_NOTIFICATIONS', 'visitors_proof_notifications');
define('VISITORS_PROOF_TABLE_ICONS', 'visitors_proof_icons');
define('VISITORS_PROOF_TABLE_ACTIONS', 'visitors_proof_actions');
define('VISITORS_PROOF_TABLE_IPS', 'visitors_proof_ips');
define('VISITORS_PROOF_TABLE_INTEGRATIONS', 'visitors_proof_integrations');

require_once 'helpers.php';

/* Visitors Proof Common Helpers */
$vpchr = new VisitorsProofHelper();