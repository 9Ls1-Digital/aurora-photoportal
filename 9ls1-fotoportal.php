<?php
/**
 * Plugin Name: Aurora Fotoportal
 * Description: Aurora Fotoportal – kundeportal for fotoprosjekter, kontrakter, gallerier og levering.
 * Version: 0.7.1-dev.31-fix40
 * Author: 9Ls1 Digital
 * Text Domain: 9ls1-fotoportal
 */
if (!defined('ABSPATH')) exit;

define('NLS1_FOTOPORTAL_VERSION', '0.7.1-dev.31-fix40');
define('NLS1_FOTOPORTAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NLS1_FOTOPORTAL_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-aurora-account-platform.php';
require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-aurora-tenant-context.php';
require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-photographer-workspace.php';
require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-9ls1-fotoportal-activator.php';
require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-9ls1-fotoportal-admin.php';
require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-9ls1-fotoportal-frontend.php';
require_once NLS1_FOTOPORTAL_PLUGIN_DIR . 'includes/class-9ls1-fotoportal-pdf.php';

register_activation_hook(__FILE__, ['NLS1_Fotoportal_Activator', 'activate']);
add_action('plugins_loaded', ['NLS1_Fotoportal_Activator', 'maybe_upgrade'], 5);

if (is_admin()) {
    new NLS1_Aurora_Account_Platform();
    new NLS1_Photographer_Workspace();
    new NLS1_Fotoportal_Admin();
}
new NLS1_Fotoportal_Frontend();


add_action('init', function () {
    $role = get_role('aurora_photographer');
    if (!$role) {
        add_role('aurora_photographer', 'Aurora Fotograf', ['read'=>true,'aurora_fotoportal_photographer'=>true]);
    } elseif (!$role->has_cap('aurora_fotoportal_photographer')) {
        $role->add_cap('aurora_fotoportal_photographer');
    }
});
add_filter('9ls1_aurora_current_account_id', function($account_id){
    if ($account_id) return $account_id;
    $uid=get_current_user_id();
    return $uid ? (int)get_user_meta($uid,'aurora_fotoportal_account_id',true) : 0;
}, 20);
add_filter('login_redirect', function($redirect_to,$requested,$user){
    if ($user instanceof WP_User && in_array('aurora_photographer',(array)$user->roles,true)) {
        return NLS1_Photographer_Workspace::url('dashboard');
    }
    return $redirect_to;
}, 20, 3);


/**
 * Keep Aurora photographer authentication separate from photo-client portal auth.
 */
add_filter('login_url', function($login_url, $redirect, $force_reauth){
    if ($redirect && strpos($redirect, 'aurora-photographer-workspace') !== false) {
        $login_url = add_query_arg('aurora_photographer_auth', 1, $login_url);
    }
    return $login_url;
}, 20, 3);


/**
 * Aurora Photographer Workspace lives in wp-admin, but photographers are not
 * normal WordPress/WooCommerce customers. WooCommerce must not redirect these
 * users to My Account merely because they do not have edit_posts.
 */
add_filter('woocommerce_prevent_admin_access', function($prevent_access) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (
            in_array('aurora_photographer', (array)$user->roles, true)
            || $user->has_cap('aurora_fotoportal_photographer')
            || get_user_meta($user->ID, 'aurora_fotoportal_role', true) === 'photographer_owner'
        ) {
            return false;
        }
    }
    return $prevent_access;
}, 20);

/**
 * Keep the WordPress admin bar out of the photographer-facing Workspace.
 */
add_filter('show_admin_bar', function($show) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (
            in_array('aurora_photographer', (array)$user->roles, true)
            || $user->has_cap('aurora_fotoportal_photographer')
        ) {
            return false;
        }
    }
    return $show;
}, 20);
