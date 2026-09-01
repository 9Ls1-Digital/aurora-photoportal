<?php
if (!defined('ABSPATH')) exit;

/**
 * Aurora Account Platform foundation.
 *
 * This is the platform-owner layer. It intentionally does not query
 * photographer end-customer/project/gallery data in normal Aurora Admin views.
 */
class NLS1_Aurora_Account_Platform {
    const MENU_SLUG = 'nls1-plugin-center';
    const SCHEMA_VERSION = '0.1.0';

    private static $module_catalog = [
        'customers' => ['Kunder', 'Fotografens kunderegister'],
        'projects' => ['Prosjekter', 'Fotooppdrag og prosjektflyt'],
        'contracts' => ['Kontrakter', 'Avtaler og digital signering'],
        'documents' => ['Dokumenter', 'Prosjektfiler og underlag'],
        'galleries' => ['Gallerier', 'Preview, proof og bildegalleri'],
        'premium_proof' => ['Premium Proof / PDF', 'Kontaktark og branded proof-PDF'],
        'customer_portal' => ['Kundeportal', 'Kundens innloggede arbeidsflate'],
        'favorites_comments' => ['Favoritter & kommentarer', 'Kundevalg og tilbakemeldinger'],
        'hq_delivery' => ['HQ-levering', 'Kontrollert levering og nedlasting'],
        'shop' => ['Nettbutikk', 'Produkter, print og ordre'],
        'customer_app' => ['Customer App / PWA', 'Installerbar mobil kundeapp'],
    ];

    public function __construct() {
        add_action('admin_init', [__CLASS__, 'maybe_install']);
        add_action('admin_init', [__CLASS__, 'maybe_install_tenant'], 11);
        add_action('admin_menu', [$this, 'register_menu'], 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        add_action('admin_post_aurora_create_photographer_account', [$this, 'handle_create_account']);
        add_action('admin_post_aurora_save_account_modules', [$this, 'handle_save_account_modules']);
        add_action('admin_post_aurora_save_platform_branding', [$this, 'handle_save_platform_branding']);
        add_action('admin_post_aurora_save_license', [$this, 'handle_save_license']);
    }

    public static function maybe_install_tenant() {
        if (class_exists('NLS1_Aurora_Tenant_Context')) {
            NLS1_Aurora_Tenant_Context::maybe_migrate();
        }
    }

    public static function table($name) {
        global $wpdb;
        return $wpdb->prefix . '9ls1_aurora_' . $name;
    }

    public static function maybe_install() {
        $installed = (string)get_option('9ls1_aurora_account_schema_version', '');
        if ($installed === self::SCHEMA_VERSION) return;

        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        $accounts = self::table('accounts');
        $account_modules = self::table('account_modules');
        $licenses = self::table('licenses');

        dbDelta("CREATE TABLE $accounts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            account_name VARCHAR(190) NOT NULL,
            account_slug VARCHAR(190) NOT NULL,
            contact_name VARCHAR(190) DEFAULT '',
            contact_email VARCHAR(190) DEFAULT '',
            status VARCHAR(50) DEFAULT 'active',
            plan_name VARCHAR(100) DEFAULT 'Development',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY account_slug (account_slug),
            KEY status (status)
        ) $charset;");

        dbDelta("CREATE TABLE $account_modules (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id BIGINT UNSIGNED NOT NULL,
            module_key VARCHAR(100) NOT NULL,
            enabled TINYINT(1) DEFAULT 0,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY account_module (account_id,module_key),
            KEY account_id (account_id),
            KEY module_key (module_key)
        ) $charset;");

        dbDelta("CREATE TABLE $licenses (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id BIGINT UNSIGNED NOT NULL,
            license_key VARCHAR(190) DEFAULT '',
            license_name VARCHAR(190) DEFAULT 'Development',
            status VARCHAR(50) DEFAULT 'active',
            valid_from DATE NULL,
            valid_until DATE NULL,
            max_users INT UNSIGNED DEFAULT 1,
            storage_gb INT UNSIGNED DEFAULT 25,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY account_license (account_id),
            KEY status (status)
        ) $charset;");

        // Seed the current installation as the first photographer account.
        $count = (int)$wpdb->get_var("SELECT COUNT(*) FROM $accounts");
        if ($count === 0) {
            $wpdb->insert($accounts, [
                'account_name' => '9Ls1 Foto',
                'account_slug' => '9ls1-foto',
                'contact_name' => wp_get_current_user()->display_name ?: '',
                'contact_email' => get_option('admin_email'),
                'status' => 'active',
                'plan_name' => 'Development',
                'created_at' => current_time('mysql'),
            ]);
            $account_id = (int)$wpdb->insert_id;
            foreach (self::$module_catalog as $key => $meta) {
                $wpdb->insert($account_modules, [
                    'account_id' => $account_id,
                    'module_key' => $key,
                    'enabled' => in_array($key, ['customers','projects','contracts','documents','galleries','premium_proof'], true) ? 1 : 0,
                    'updated_at' => current_time('mysql'),
                ]);
            }
            $wpdb->insert($licenses, [
                'account_id' => $account_id,
                'license_name' => 'Development',
                'status' => 'active',
                'max_users' => 3,
                'storage_gb' => 50,
                'created_at' => current_time('mysql'),
            ]);
        }

        update_option('9ls1_aurora_account_schema_version', self::SCHEMA_VERSION, false);

        if (class_exists('NLS1_Aurora_Tenant_Context')) {
            NLS1_Aurora_Tenant_Context::maybe_migrate();
        }
    }

    public function register_menu() {
        add_menu_page(
            'Aurora Admin',
            'Aurora',
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render_page'],
            'dashicons-screenoptions',
            58
        );

        add_submenu_page(self::MENU_SLUG, 'Aurora Admin', 'Dashboard', 'manage_options', self::MENU_SLUG, [$this, 'render_page']);
        add_submenu_page(self::MENU_SLUG, 'Fotografkontoer', 'Fotografkontoer', 'manage_options', self::MENU_SLUG . '-accounts', [$this, 'render_page']);
        add_submenu_page(self::MENU_SLUG, 'Lisenser', 'Lisenser', 'manage_options', self::MENU_SLUG . '-licenses', [$this, 'render_page']);
        add_submenu_page(self::MENU_SLUG, 'Moduler', 'Moduler', 'manage_options', self::MENU_SLUG . '-modules', [$this, 'render_page']);
        add_submenu_page(self::MENU_SLUG, 'Branding', 'Branding', 'manage_options', self::MENU_SLUG . '-branding', [$this, 'render_page']);
        add_submenu_page(self::MENU_SLUG, 'System', 'System', 'manage_options', self::MENU_SLUG . '-system', [$this, 'render_page']);
    }

    public function enqueue_assets($hook) {
        if (strpos((string)$hook, self::MENU_SLUG) === false) return;
        wp_enqueue_style('9ls1-fotoportal-admin', NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/css/admin.css', [], NLS1_FOTOPORTAL_VERSION);
        wp_enqueue_media();
    }

    public function render_page() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');

        $page = sanitize_key($_GET['page'] ?? self::MENU_SLUG);
        $section = 'dashboard';
        if ($page === self::MENU_SLUG . '-accounts') $section = 'accounts';
        if ($page === self::MENU_SLUG . '-licenses') $section = 'licenses';
        if ($page === self::MENU_SLUG . '-modules') $section = 'modules';
        if ($page === self::MENU_SLUG . '-branding') $section = 'branding';
        if ($page === self::MENU_SLUG . '-system') $section = 'system';

        $account_id = absint($_GET['account_id'] ?? 0);
        $account = $account_id ? self::get_account($account_id) : null;
        include NLS1_FOTOPORTAL_PLUGIN_DIR . 'admin/view-aurora-platform.php';
    }

    public static function url($section = 'dashboard', $args = []) {
        $slug = self::MENU_SLUG;
        if ($section !== 'dashboard') $slug .= '-' . sanitize_key($section);
        return add_query_arg(array_merge(['page' => $slug], $args), admin_url('admin.php'));
    }

    public static function get_accounts() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM " . self::table('accounts') . " ORDER BY account_name ASC");
    }

    public static function get_account($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table('accounts') . " WHERE id=%d", (int)$id));
    }

    public static function get_license($account_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table('licenses') . " WHERE account_id=%d", (int)$account_id));
    }

    public static function get_account_modules($account_id) {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare("SELECT module_key,enabled FROM " . self::table('account_modules') . " WHERE account_id=%d", (int)$account_id));
        $result = [];
        foreach ($rows as $row) $result[$row->module_key] = (bool)$row->enabled;
        return $result;
    }

    public static function module_catalog() {
        return self::$module_catalog;
    }

    public static function count_accounts($status = '') {
        global $wpdb;
        $table = self::table('accounts');
        if ($status) return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE status=%s", $status));
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    public static function count_enabled_modules() {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM " . self::table('account_modules') . " WHERE enabled=1");
    }

    public static function default_account() {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . self::table('accounts') . " ORDER BY id ASC LIMIT 1");
    }

    public static function is_module_enabled($account_id, $module_key) {
        $modules = self::get_account_modules((int)$account_id);
        return !empty($modules[sanitize_key($module_key)]);
    }

    public static function platform_branding() {
        return [
            'platform_name' => get_option('9ls1_aurora_platform_name', 'Aurora'),
            'company_name' => get_option('9ls1_aurora_company_name', '9Ls1 Digital'),
            'support_email' => get_option('9ls1_aurora_support_email', get_option('admin_email')),
            'logo_url' => get_option('9ls1_aurora_logo_url', ''),
            'accent' => get_option('9ls1_aurora_accent', '#6f4bf2'),
        ];
    }

    public function handle_create_account() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_create_photographer_account');

        $name = sanitize_text_field($_POST['account_name'] ?? '');
        $contact = sanitize_text_field($_POST['contact_name'] ?? '');
        $email = sanitize_email($_POST['contact_email'] ?? '');
        if (!$name) {
            wp_safe_redirect(add_query_arg('message', 'account_missing', self::url('accounts')));
            exit;
        }

        global $wpdb;
        $slug = sanitize_title($name);
        $base = $slug ?: 'photographer';
        $candidate = $base;
        $i = 2;
        while ((int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . self::table('accounts') . " WHERE account_slug=%s", $candidate))) {
            $candidate = $base . '-' . $i++;
        }

        $wpdb->insert(self::table('accounts'), [
            'account_name' => $name,
            'account_slug' => $candidate,
            'contact_name' => $contact,
            'contact_email' => $email,
            'status' => 'active',
            'plan_name' => 'Trial',
            'created_at' => current_time('mysql'),
        ]);
        $account_id = (int)$wpdb->insert_id;

        foreach (self::$module_catalog as $key => $meta) {
            $wpdb->insert(self::table('account_modules'), [
                'account_id' => $account_id,
                'module_key' => $key,
                'enabled' => in_array($key, ['customers','projects','contracts','documents','galleries'], true) ? 1 : 0,
                'updated_at' => current_time('mysql'),
            ]);
        }

        $wpdb->insert(self::table('licenses'), [
            'account_id' => $account_id,
            'license_name' => 'Trial',
            'status' => 'trial',
            'valid_from' => current_time('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'max_users' => 1,
            'storage_gb' => 10,
            'created_at' => current_time('mysql'),
        ]);

        wp_safe_redirect(add_query_arg(['account_id' => $account_id, 'message' => 'account_created'], self::url('accounts')));
        exit;
    }

    public function handle_save_account_modules() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_save_account_modules');

        $account_id = absint($_POST['account_id'] ?? 0);
        if (!self::get_account($account_id)) wp_die('Fotografkonto finnes ikke.');

        global $wpdb;
        $selected = array_map('sanitize_key', (array)($_POST['modules'] ?? []));
        foreach (self::$module_catalog as $key => $meta) {
            $wpdb->replace(self::table('account_modules'), [
                'account_id' => $account_id,
                'module_key' => $key,
                'enabled' => in_array($key, $selected, true) ? 1 : 0,
                'updated_at' => current_time('mysql'),
            ], ['%d','%s','%d','%s']);
        }

        wp_safe_redirect(add_query_arg(['account_id' => $account_id, 'message' => 'modules_saved'], self::url('accounts')));
        exit;
    }

    public function handle_save_platform_branding() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_save_platform_branding');

        update_option('9ls1_aurora_platform_name', sanitize_text_field($_POST['platform_name'] ?? 'Aurora'), false);
        update_option('9ls1_aurora_company_name', sanitize_text_field($_POST['company_name'] ?? '9Ls1 Digital'), false);
        update_option('9ls1_aurora_support_email', sanitize_email($_POST['support_email'] ?? ''), false);
        update_option('9ls1_aurora_logo_url', esc_url_raw($_POST['logo_url'] ?? ''), false);

        $accent = sanitize_hex_color($_POST['accent'] ?? '');
        if ($accent) update_option('9ls1_aurora_accent', $accent, false);

        wp_safe_redirect(add_query_arg('message', 'branding_saved', self::url('branding')));
        exit;
    }

    public function handle_save_license() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_save_license');

        $account_id = absint($_POST['account_id'] ?? 0);
        if (!self::get_account($account_id)) wp_die('Fotografkonto finnes ikke.');

        $status = sanitize_key($_POST['license_status'] ?? 'active');
        if (!in_array($status, ['active','trial','expired','suspended'], true)) $status = 'active';

        global $wpdb;
        $wpdb->replace(self::table('licenses'), [
            'account_id' => $account_id,
            'license_key' => sanitize_text_field($_POST['license_key'] ?? ''),
            'license_name' => sanitize_text_field($_POST['license_name'] ?? 'Aurora Fotoportal'),
            'status' => $status,
            'valid_from' => sanitize_text_field($_POST['valid_from'] ?? '') ?: null,
            'valid_until' => sanitize_text_field($_POST['valid_until'] ?? '') ?: null,
            'max_users' => max(1, absint($_POST['max_users'] ?? 1)),
            'storage_gb' => max(1, absint($_POST['storage_gb'] ?? 10)),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);

        wp_safe_redirect(add_query_arg(['account_id' => $account_id, 'message' => 'license_saved'], self::url('licenses')));
        exit;
    }
}
