<?php
if (!defined('ABSPATH')) exit;

/**
 * Photographer Workspace shell.
 *
 * dev.11 deliberately reuses the default photographer account while account-
 * specific login/context is not yet implemented. No tenant-isolation claim is
 * made here; that migration is a later step.
 */
class NLS1_Photographer_Workspace {
    const PAGE_SLUG = 'aurora-photographer-workspace';

    private $module_pages = [
        'customers' => ['Kunder', 'dashicons-groups'],
        'projects' => ['Prosjekter', 'dashicons-portfolio'],
        'contracts' => ['Kontrakter', 'dashicons-media-document'],
        'documents' => ['Dokumenter', 'dashicons-media-text'],
        'galleries' => ['Gallerier', 'dashicons-format-gallery'],
        'hq_delivery' => ['Leveranser', 'dashicons-download'],
        'shop' => ['Nettbutikk', 'dashicons-cart'],
    ];

    public function __construct() {
        add_action('admin_menu', [$this, 'register_hidden_page'], 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('current_screen', [$this, 'prepare_workspace_screen']);
    }

    public function prepare_workspace_screen($screen) {
        if (!$screen || strpos((string)$screen->id, self::PAGE_SLUG) === false) return;

        // Photographer Workspace must behave like its own application, not
        // inherit plugin/theme notices from WordPress admin.
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
    }

    public function register_hidden_page() {
        add_submenu_page(
            null,
            'Aurora Fotoportal',
            'Aurora Fotoportal',
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render']
        );
    }

    public function enqueue_assets($hook) {
        if (strpos((string)$hook, self::PAGE_SLUG) === false) return;
        wp_enqueue_style('9ls1-fotoportal-admin', NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/css/admin.css', [], NLS1_FOTOPORTAL_VERSION);
    }

    public static function url($view = 'dashboard') {
        return add_query_arg([
            'page' => self::PAGE_SLUG,
            'workspace_view' => sanitize_key($view),
        ], admin_url('admin.php'));
    }

    public function render() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');

        $account = NLS1_Aurora_Account_Platform::default_account();
        if (!$account) wp_die('Ingen fotografkonto er konfigurert.');

        $enabled = NLS1_Aurora_Account_Platform::get_account_modules($account->id);
        $view = sanitize_key($_GET['workspace_view'] ?? 'dashboard');

        $allowed = ['dashboard', 'new', 'settings', 'resources'];
        foreach ($this->module_pages as $key => $meta) {
            if (!empty($enabled[$key])) $allowed[] = $key;
        }
        if (!in_array($view, $allowed, true)) $view = 'dashboard';

        $menu = $this->module_pages;
        include NLS1_FOTOPORTAL_PLUGIN_DIR . 'admin/view-photographer-workspace.php';
    }
}
