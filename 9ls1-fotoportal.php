<?php
/**
 * Plugin Name: Aurora Fotoportal
 * Description: Aurora Fotoportal – kundeportal for fotoprosjekter, kontrakter, gallerier og levering.
 * Version: 0.7.1-dev.27-fix1
 * Author: 9Ls1 Digital
 * Text Domain: 9ls1-fotoportal
 */
if (!defined('ABSPATH')) exit;

define('NLS1_FOTOPORTAL_VERSION', '0.7.1-dev.27-fix1');
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
