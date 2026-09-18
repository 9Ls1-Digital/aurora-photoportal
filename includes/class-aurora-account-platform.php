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
    const SCHEMA_VERSION = '0.8.2';

    private static $module_catalog = [
        // [Name, description, type, trial_default]
        'customers' => ['Kunder', 'Fotografens kunderegister', 'core', true],
        'projects' => ['Prosjekter', 'Fotooppdrag og prosjektflyt', 'core', true],
        'contracts' => ['Kontrakter', 'Avtaler og digital signering', 'core', true],
        'documents' => ['Dokumenter', 'Prosjektfiler og underlag', 'core', true],
        'galleries' => ['Gallerier', 'Preview, proof og bildegalleri', 'core', true],

        'premium_proof' => ['Premium Proof / PDF', 'Kontaktark og branded proof-PDF', 'addon', true],
        'customer_portal' => ['Kundeportal', 'Kundens innloggede arbeidsflate', 'addon', true],
        'favorites_comments' => ['Bildevalg', 'Favoritter, kommentarer og redigeringsønsker', 'addon', true],
        'hq_delivery' => ['Digital levering', 'Sikker levering av ferdige høyoppløselige bilder', 'addon', true],

        // Future add-ons: visible in the catalogue, but not enabled by default in Trial yet.
        'shop' => ['Nettbutikk', 'Produkter, print og ordre', 'addon', false],
        'customer_app' => ['Customer App / PWA', 'Installerbar mobil kundeapp', 'addon', false],
    ];

    public function __construct() {
        add_action('admin_init', [__CLASS__, 'maybe_install']);
        add_action('admin_init', [$this, 'maybe_close_support_outside_workspace'], 2);
        add_action('admin_init', [__CLASS__, 'maybe_install_tenant'], 11);
        add_action('admin_menu', [$this, 'register_menu'], 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('aurora_core_products', [$this, 'register_with_aurora_core']);

        add_action('admin_post_aurora_create_photographer_account', [$this, 'handle_create_account']);
        add_action('admin_post_aurora_save_account_modules', [$this, 'handle_save_account_modules']);
        add_action('admin_post_aurora_save_photographer_account', [$this, 'handle_save_photographer_account']);
        add_action('admin_post_aurora_save_platform_branding', [$this, 'handle_save_platform_branding']);
        add_action('admin_post_aurora_save_license', [$this, 'handle_save_license']);
        add_action('admin_post_aurora_extend_trial', [$this, 'handle_extend_trial']);
        add_action('admin_post_aurora_expire_trial', [$this, 'handle_expire_trial']);
        add_action('admin_post_aurora_resend_photographer_invitation', [$this, 'handle_resend_photographer_invitation']);
        add_action('admin_post_aurora_delete_photographer_account', [$this, 'handle_delete_photographer_account']);
        add_action('admin_post_aurora_start_support_session', [$this, 'handle_start_support_session']);
        add_action('admin_post_aurora_end_support_session', [$this, 'handle_end_support_session']);
        add_action('admin_post_aurora_revoke_support_access', [$this, 'handle_revoke_support_access']);
    }


    public function maybe_close_support_outside_workspace() {
        if (!current_user_can('manage_options')) return;
        $account_id = (int)get_user_meta(get_current_user_id(), 'aurora_support_account_id', true);
        if (!$account_id) return;

        $page = sanitize_key($_GET['page'] ?? '');
        $action = sanitize_key($_REQUEST['action'] ?? '');
        $is_workspace = ($page === NLS1_Photographer_Workspace::PAGE_SLUG);
        $is_workspace_post = ($GLOBALS['pagenow'] ?? '') === 'admin-post.php'
            && ($action === 'aurora_end_support_session' || strpos($action, '9ls1_fotoportal_') === 0);

        if (!$is_workspace && !$is_workspace_post) {
            self::clear_support_session(get_current_user_id(), true);
        }
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
            contact_phone VARCHAR(80) DEFAULT '',
            organization_number VARCHAR(80) DEFAULT '',
            website_url VARCHAR(255) DEFAULT '',
            billing_name VARCHAR(190) DEFAULT '',
            billing_address VARCHAR(255) DEFAULT '',
            billing_postcode VARCHAR(40) DEFAULT '',
            billing_city VARCHAR(120) DEFAULT '',
            billing_country VARCHAR(120) DEFAULT 'Norge',
            billing_email VARCHAR(190) DEFAULT '',
            internal_notes TEXT NULL,
            support_access_enabled TINYINT(1) DEFAULT 0,
            support_access_granted_at DATETIME NULL,
            support_access_granted_by BIGINT UNSIGNED DEFAULT 0,
            last_active_at DATETIME NULL,
            status VARCHAR(50) DEFAULT 'active',
            plan_name VARCHAR(100) DEFAULT 'Development',
            onboarding_state VARCHAR(50) DEFAULT 'ready',
            trial_started_at DATETIME NULL,
            trial_ends_at DATETIME NULL,
            owner_user_id BIGINT UNSIGNED DEFAULT 0,
            onboarding_step TINYINT UNSIGNED DEFAULT 1,
            onboarding_completed_at DATETIME NULL,
            is_test_account TINYINT(1) DEFAULT 0,
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

        $support_logs = self::table('support_logs');
        dbDelta("CREATE TABLE $support_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id BIGINT UNSIGNED NOT NULL,
            actor_user_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(50) NOT NULL,
            session_expires_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY account_id (account_id),
            KEY actor_user_id (actor_user_id),
            KEY created_at (created_at)
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
                    'enabled' => (self::is_core_module($key) || $key === 'premium_proof') ? 1 : 0,
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

        // Module policy migration:
        // - Core Fotoportal functions are always enabled.
        // - Existing explicit add-on choices are never overwritten by a schema upgrade.
        // - Missing add-on rows on Trial accounts receive the standard Trial defaults once.
        $existing_accounts = $wpdb->get_results("SELECT id,status,plan_name FROM $accounts");
        foreach ($existing_accounts as $existing_account) {
            foreach (self::$module_catalog as $key => $meta) {
                $existing_enabled = $wpdb->get_var($wpdb->prepare(
                    "SELECT enabled FROM $account_modules WHERE account_id=%d AND module_key=%s LIMIT 1",
                    (int)$existing_account->id,
                    $key
                ));
                if (self::is_core_module($key)) {
                    $wpdb->replace($account_modules, [
                        'account_id' => (int)$existing_account->id,
                        'module_key' => $key,
                        'enabled' => 1,
                        'updated_at' => current_time('mysql'),
                    ]);
                    continue;
                }
                if ($existing_enabled !== null) continue;
                $trial_default = ($existing_account->status === 'trial' || $existing_account->plan_name === 'Trial') && !empty($meta[3]);
                $wpdb->insert($account_modules, [
                    'account_id' => (int)$existing_account->id,
                    'module_key' => $key,
                    'enabled' => $trial_default ? 1 : 0,
                    'updated_at' => current_time('mysql'),
                ]);
            }
        }

        // dev.59 migration: remove only the legacy materialized Demo Content Pack
        // entities. Guided Journey entities are stored under separate IDs/state.
        if (class_exists('NLS1_Aurora_Demo_Content')) {
            foreach ($existing_accounts as $existing_account) {
                NLS1_Aurora_Demo_Content::remove_legacy_materialized_pack((int)$existing_account->id);
            }
        }

        update_option('9ls1_aurora_account_schema_version', self::SCHEMA_VERSION, false);

        if (class_exists('NLS1_Aurora_Tenant_Context')) {
            NLS1_Aurora_Tenant_Context::maybe_migrate();
        }
    }

    public function register_menu() {
        // Aurora Core owns the single global Aurora menu when available.
        if (defined('AURORA_CORE_VERSION') || class_exists('Aurora_Core_Registry')) {
            add_submenu_page(null, 'Aurora Fotoportal Admin', 'Fotoportal', 'manage_options', self::MENU_SLUG . '-fotoportal', [$this, 'render_page']);
            add_submenu_page(null, 'Fotografkontoer', 'Fotografkontoer', 'manage_options', self::MENU_SLUG . '-accounts', [$this, 'render_page']);
            add_submenu_page(null, 'Demo-innhold', 'Demo-innhold', 'manage_options', self::MENU_SLUG . '-demo', [$this, 'render_page']);
            add_submenu_page(null, 'Moduler', 'Moduler', 'manage_options', self::MENU_SLUG . '-modules', [$this, 'render_page']);
            add_submenu_page(null, 'Branding', 'Branding', 'manage_options', self::MENU_SLUG . '-branding', [$this, 'render_page']);
            add_submenu_page(null, 'System', 'System', 'manage_options', self::MENU_SLUG . '-system', [$this, 'render_page']);
            return;
        }

        // Standalone compatibility fallback until Aurora Core is installed.
        add_menu_page('Aurora Admin','Aurora','manage_options',self::MENU_SLUG,[$this,'render_page'],'dashicons-screenoptions',58);
        add_submenu_page(self::MENU_SLUG,'Aurora Control Center','Dashboard','manage_options',self::MENU_SLUG,[$this,'render_page']);
        add_submenu_page(self::MENU_SLUG,'Aurora Fotoportal Admin','Fotoportal','manage_options',self::MENU_SLUG . '-fotoportal',[$this,'render_page']);
        add_submenu_page(null,'Fotografkontoer','Fotografkontoer','manage_options',self::MENU_SLUG . '-accounts',[$this,'render_page']);
        add_submenu_page(null,'Demo-innhold','Demo-innhold','manage_options',self::MENU_SLUG . '-demo',[$this,'render_page']);
        add_submenu_page(null,'Moduler','Moduler','manage_options',self::MENU_SLUG . '-modules',[$this,'render_page']);
        add_submenu_page(null,'Branding','Branding','manage_options',self::MENU_SLUG . '-branding',[$this,'render_page']);
        add_submenu_page(null,'System','System','manage_options',self::MENU_SLUG . '-system',[$this,'render_page']);
        if (class_exists('Aurora_License_Service') || function_exists('aurora_license_render_platform_page')) {
            add_submenu_page(self::MENU_SLUG,'Aurora License','License','manage_options',self::MENU_SLUG . '-licenses',[$this,'render_page']);
        } else {
            add_submenu_page(null,'Aurora License','License','manage_options',self::MENU_SLUG . '-licenses',[$this,'render_page']);
        }
    }

    public function register_with_aurora_core($products) {
        $products['fotoportal'] = [
            'id' => 'fotoportal',
            'name' => 'Aurora Fotoportal',
            'description' => 'Kunder, prosjekter, kontrakter, gallerier og kundeportal.',
            'version' => defined('NLS1_FOTOPORTAL_VERSION') ? NLS1_FOTOPORTAL_VERSION : '',
            'status' => 'active',
            'admin_url' => self::url('fotoportal'),
            'license_required' => true,
            'license_status' => class_exists('Aurora_License_Service') ? 'Tilkoblet License' : 'License ikke tilkoblet',
            'quick_links' => [
                ['label'=>'Fotoportal Admin','url'=>self::url('fotoportal')],
                ['label'=>'Fotografkontoer','url'=>self::url('accounts')],
                ['label'=>'Moduler','url'=>self::url('modules')],
                ['label'=>'Branding','url'=>self::url('branding')],
                ['label'=>'System','url'=>self::url('system')],
            ],
            'source' => 'aurora-fotoportal',
        ];
        return $products;
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
        if ($page === self::MENU_SLUG . '-fotoportal') $section = 'fotoportal';
        if ($page === self::MENU_SLUG . '-accounts') $section = 'accounts';
        if ($page === self::MENU_SLUG . '-demo') $section = 'demo';
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

    public static function photographer_login_url($account_or_id = 0, $args = []) {
        // One permanent login address for every photographer. Aurora Access
        // identifies the user and always continues through Mine apper.
        return home_url('/aurora/login/');
    }

    public static function customer_login_url($args = []) {
        // One permanent login address for every Fotoportal customer. Aurora
        // resolves the customer/account mapping from the authenticated user.
        return add_query_arg($args, home_url('/aurora/kunde/'));
    }

    public static function get_accounts($filters = []) {
        global $wpdb;
        $table = self::table('accounts');
        $where = ['1=1'];
        $args = [];

        $search = sanitize_text_field($filters['search'] ?? '');
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(account_name LIKE %s OR contact_name LIKE %s OR contact_email LIKE %s OR contact_phone LIKE %s OR organization_number LIKE %s OR billing_email LIKE %s)';
            array_push($args, $like, $like, $like, $like, $like, $like);
        }

        $status = sanitize_key($filters['status'] ?? '');
        if ($status !== '' && in_array($status, ['trial','active','expired','suspended','cancelled','invalid'], true)) {
            $where[] = 'status=%s';
            $args[] = $status;
        }

        $sort = sanitize_key($filters['sort'] ?? 'name');
        $order_map = [
            'name' => 'account_name ASC',
            'name_desc' => 'account_name DESC',
            'newest' => 'created_at DESC',
            'oldest' => 'created_at ASC',
            'updated' => 'COALESCE(updated_at,created_at) DESC',
            'last_active' => 'last_active_at DESC, account_name ASC',
            'status' => 'status ASC, account_name ASC',
        ];
        $order = $order_map[$sort] ?? $order_map['name'];
        $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where) . " ORDER BY $order";
        if ($args) $sql = $wpdb->prepare($sql, $args);
        return $wpdb->get_results($sql);
    }

    public static function get_account($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table('accounts') . " WHERE id=%d", (int)$id));
    }

    public static function mark_account_active($account_id) {
        global $wpdb;
        $account_id = absint($account_id);
        if (!$account_id) return;
        $wpdb->update(self::table('accounts'), [
            'last_active_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ], ['id' => $account_id]);
    }


    public static function support_session_minutes() {
        return 60;
    }

    public static function support_access_enabled($account_id) {
        $account = self::get_account((int)$account_id);
        return $account && !empty($account->support_access_enabled);
    }

    public static function support_context_account_id($user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id || !user_can($user_id, 'manage_options')) return 0;

        $account_id = (int)get_user_meta($user_id, 'aurora_support_account_id', true);
        $expires = (int)get_user_meta($user_id, 'aurora_support_expires', true);
        if (!$account_id || !$expires || $expires <= time() || !self::support_access_enabled($account_id)) {
            self::clear_support_session($user_id, false);
            return 0;
        }
        return $account_id;
    }

    public static function clear_support_session($user_id = 0, $log = true) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) return;
        $account_id = (int)get_user_meta($user_id, 'aurora_support_account_id', true);
        if ($log && $account_id) self::log_support_event($account_id, $user_id, 'ended', null);
        delete_user_meta($user_id, 'aurora_support_account_id');
        delete_user_meta($user_id, 'aurora_support_expires');
    }

    public static function log_support_event($account_id, $admin_user_id, $action, $expires_at = null) {
        global $wpdb;
        $wpdb->insert(self::table('support_logs'), [
            'account_id' => (int)$account_id,
            'actor_user_id' => (int)$admin_user_id,
            'action' => sanitize_key($action),
            'session_expires_at' => $expires_at,
            'created_at' => current_time('mysql'),
        ]);
    }

    public static function get_support_logs($account_id, $limit = 10) {
        global $wpdb;
        $limit = max(1, min(50, (int)$limit));
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::table('support_logs') . " WHERE account_id=%d ORDER BY created_at DESC LIMIT %d",
            (int)$account_id, $limit
        ));
    }

    public static function get_license($account_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table('licenses') . " WHERE account_id=%d", (int)$account_id));
    }

    public static function get_account_modules($account_id) {
        global $wpdb;
        $account_id = (int)$account_id;
        $rows = $wpdb->get_results($wpdb->prepare("SELECT module_key,enabled FROM " . self::table('account_modules') . " WHERE account_id=%d", $account_id));
        $result = [];
        // The catalogue is the source of truth: every known key has an explicit state.
        foreach (self::$module_catalog as $key => $meta) {
            $result[$key] = self::is_core_module($key); // core is always available
        }
        foreach ($rows as $row) {
            $key = sanitize_key($row->module_key);
            if (!isset(self::$module_catalog[$key])) continue;
            $result[$key] = self::is_core_module($key) ? true : (bool)$row->enabled;
        }
        return $result;
    }

    public static function module_catalog() {
        return self::$module_catalog;
    }

    public static function core_modules() {
        return array_filter(self::$module_catalog, function($meta) {
            return ($meta[2] ?? 'addon') === 'core';
        });
    }

    public static function addon_modules() {
        return array_filter(self::$module_catalog, function($meta) {
            return ($meta[2] ?? 'addon') === 'addon';
        });
    }

    public static function is_core_module($key) {
        return isset(self::$module_catalog[$key]) && (self::$module_catalog[$key][2] ?? 'addon') === 'core';
    }

    public static function trial_default_modules() {
        $keys = [];
        foreach (self::$module_catalog as $key => $meta) {
            if (!empty($meta[3])) $keys[] = $key;
        }
        return $keys;
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
        $module_key = sanitize_key($module_key);
        if (!isset(self::$module_catalog[$module_key])) return false;
        if (self::is_core_module($module_key)) return true;
        $modules = self::get_account_modules((int)$account_id);
        return !empty($modules[$module_key]);
    }

    public static function require_module($account_id, $module_key, $message = '') {
        if (self::is_module_enabled((int)$account_id, $module_key)) return true;
        $meta = self::$module_catalog[sanitize_key($module_key)] ?? ['Denne funksjonen'];
        $label = $meta[0] ?? 'Denne funksjonen';
        wp_die($message ?: ($label . ' er ikke aktivert for denne fotografkontoen.'), 'Modul ikke aktivert', ['response'=>403]);
    }

    /**
     * Shared Aurora product registry.
     *
     * Aurora plugins can register richer metadata through the
     * `aurora_platform_products` filter. We also discover installed plugins
     * whose plugin name identifies them as Aurora/9Ls1 product components.
     */
    public static function installed_aurora_products() {
        if (!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all = get_plugins();
        $active = (array)get_option('active_plugins', []);
        $network_active = is_multisite() ? array_keys((array)get_site_option('active_sitewide_plugins', [])) : [];

        $products = [
            'fotoportal' => [
                'id' => 'fotoportal',
                'name' => 'Aurora Fotoportal',
                'description' => 'Kunder, prosjekter, kontrakter, gallerier og kundeportal.',
                'version' => defined('NLS1_FOTOPORTAL_VERSION') ? NLS1_FOTOPORTAL_VERSION : '',
                'active' => true,
                'installed' => true,
                'admin_url' => self::url('fotoportal'),
                'license_status' => class_exists('Aurora_License_Service') ? 'Tilkoblet License' : 'License ikke tilkoblet',
                'quick_links' => [
                    ['label' => 'Fotoportal Admin', 'url' => self::url('fotoportal')],
                    ['label' => 'Fotografkontoer', 'url' => self::url('accounts')],
                    ['label' => 'Moduler', 'url' => self::url('modules')],
                    ['label' => 'Branding', 'url' => self::url('branding')],
                    ['label' => 'System', 'url' => self::url('system')],
                ],
            ],
        ];

        foreach ($all as $plugin_file => $data) {
            $name = trim((string)($data['Name'] ?? ''));
            if (!$name) continue;
            $haystack = strtolower($name . ' ' . $plugin_file);
            if (strpos($haystack, 'aurora') === false && strpos($haystack, 'project showcase') === false) continue;

            $id = sanitize_key(str_replace(['aurora','9ls1'], '', $name));
            if (strpos($haystack, 'license') !== false) $id = 'license';
            elseif (strpos($haystack, 'fotoportal') !== false || strpos($haystack, 'photo portal') !== false) $id = 'fotoportal';
            elseif (strpos($haystack, 'project showcase') !== false) $id = 'project_showcase';
            elseif (strpos($haystack, 'booking') !== false) $id = 'booking';
            if (!$id) $id = sanitize_key(dirname($plugin_file));

            $is_active = in_array($plugin_file, $active, true) || in_array($plugin_file, $network_active, true);
            $existing = $products[$id] ?? [];
            $products[$id] = array_merge([
                'id' => $id,
                'name' => $name,
                'description' => trim(wp_strip_all_tags((string)($data['Description'] ?? ''))),
                'version' => (string)($data['Version'] ?? ''),
                'installed' => true,
                'active' => $is_active,
                'admin_url' => '',
                'license_status' => '',
                'plugin_file' => $plugin_file,
                'quick_links' => [],
            ], $existing, ['active' => $is_active, 'version' => (string)($data['Version'] ?? ($existing['version'] ?? ''))]);
        }

        $products = apply_filters('aurora_platform_products', $products);

        // Stable, product-oriented order.
        $order = ['fotoportal'=>10,'license'=>20,'project_showcase'=>30,'booking'=>40];
        uasort($products, function($a,$b) use ($order) {
            return ($order[$a['id']] ?? 100) <=> ($order[$b['id']] ?? 100);
        });
        return $products;
    }

    public static function platform_branding() {
        return [
            'platform_name' => get_option('9ls1_aurora_platform_name', 'Aurora'),
            'company_name' => get_option('9ls1_aurora_company_name', '9Ls1 Digital'),
            'support_email' => get_option('9ls1_aurora_support_email', get_option('admin_email')),
            'logo_url' => get_option('9ls1_aurora_logo_url', ''),
            'accent' => get_option('9ls1_aurora_accent', '#6f4bf2'),
            'watermark_preview_url' => get_option('9ls1_aurora_watermark_preview_url', NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/aurora-watermark-preview.jpg'),
            'photographer_login_bg_desktop' => get_option('9ls1_aurora_photographer_login_bg_desktop', NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/aurora-login-background.png'),
            'photographer_login_bg_mobile' => get_option('9ls1_aurora_photographer_login_bg_mobile', ''),
            'customer_login_bg_desktop' => get_option('9ls1_aurora_customer_login_bg_desktop', ''),
            'customer_login_bg_mobile' => get_option('9ls1_aurora_customer_login_bg_mobile', ''),
        ];
    }

    public static function demo_journey_enabled($account_id) {
        return (bool)get_option('aurora_fotoportal_demo_journey_enabled_' . (int)$account_id, false);
    }

    public static function set_demo_journey_enabled($account_id, $enabled) {
        update_option('aurora_fotoportal_demo_journey_enabled_' . (int)$account_id, $enabled ? 1 : 0, false);
    }

    public static function trial_days() {
        $days = absint(get_option('9ls1_aurora_fotoportal_trial_days', 30));
        return max(1, min(365, $days ?: 30));
    }

    public static function trial_state($account) {
        if (!$account) return 'unknown';
        if ($account->status === 'active' && $account->plan_name !== 'Trial') return 'active';
        if (in_array($account->status, ['suspended','cancelled','expired'], true)) return $account->status;
        if (!empty($account->trial_ends_at)) {
            $end = strtotime($account->trial_ends_at);
            if ($end && $end < current_time('timestamp')) return 'expired';
        }
        return $account->status === 'trial' ? 'trial' : $account->status;
    }

    public static function trial_days_left($account) {
        if (!$account || empty($account->trial_ends_at)) return null;
        $seconds = strtotime($account->trial_ends_at) - current_time('timestamp');
        return max(0, (int)ceil($seconds / DAY_IN_SECONDS));
    }

    public static function trial_end_label($account) {
        if (!$account || empty($account->trial_ends_at)) return 'Ikke satt';
        return wp_date(get_option('date_format'), strtotime($account->trial_ends_at));
    }

    private static $last_invitation_mail_error = '';

    public static function capture_invitation_mail_failure($error) {
        if (is_wp_error($error)) {
            self::$last_invitation_mail_error = $error->get_error_message();
        }
    }

    /**
     * Create/link the photographer owner user and send a fresh secure set-password invitation.
     *
     * @return array{sent:bool,user_id:int,error:string}
     */
    public static function send_photographer_invitation($account_id) {
        $account = self::get_account($account_id);
        if (!$account) return ['sent'=>false,'user_id'=>0,'error'=>'Fotografkonto finnes ikke.'];

        $email = sanitize_email($account->contact_email);
        if (!$email) return ['sent'=>false,'user_id'=>0,'error'=>'Fotografkontoen mangler gyldig e-postadresse.'];

        $user = !empty($account->owner_user_id) ? get_user_by('id', (int)$account->owner_user_id) : false;
        if (!$user) $user = get_user_by('email', $email);

        if (!$user) {
            $login_base = sanitize_user(strstr($email, '@', true) ?: $account->account_slug, true);
            $login_base = $login_base ?: 'fotograf';
            $login_candidate = $login_base;
            $n = 2;
            while (username_exists($login_candidate)) $login_candidate = $login_base . '-' . $n++;

            $user_id = wp_insert_user([
                'user_login'   => $login_candidate,
                'user_email'   => $email,
                'display_name' => $account->contact_name ?: $account->account_name,
                'user_pass'    => wp_generate_password(32, true, true),
                'role'         => 'aurora_photographer',
            ]);
            if (is_wp_error($user_id)) {
                return ['sent'=>false,'user_id'=>0,'error'=>$user_id->get_error_message()];
            }
            $user = get_user_by('id', (int)$user_id);
        }

        if (!$user) return ['sent'=>false,'user_id'=>0,'error'=>'Kunne ikke opprette eller finne fotografbrukeren.'];

        $user_id = (int)$user->ID;

        // A previously existing WooCommerce/customer user with the same email
        // must become a real photographer owner, otherwise WooCommerce can treat
        // the session as a shop customer and redirect it to My Account.
        if (
            !$user->has_cap('manage_options')
            && !$user->has_cap('manage_woocommerce')
            && !in_array('aurora_photographer', (array)$user->roles, true)
        ) {
            $user->set_role('aurora_photographer');
        }
        $user->add_cap('aurora_fotoportal_photographer');
        update_user_meta($user_id, 'aurora_fotoportal_account_id', (int)$account_id);
        update_user_meta($user_id, 'aurora_fotoportal_role', 'photographer_owner');

        global $wpdb;
        $wpdb->update(self::table('accounts'), [
            'owner_user_id' => $user_id,
            'updated_at' => current_time('mysql'),
        ], ['id' => (int)$account_id]);

        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            return ['sent'=>false,'user_id'=>$user_id,'error'=>$key->get_error_message()];
        }

        $photographer_login = self::photographer_login_url((int)$account_id, ['login'=>$email]);

        // Photographer invitations use Aurora's own activation surface.
        // The secure key stays in the URL, but the email presents one clear CTA.
        $reset = add_query_arg([
            'aurora_photographer_password' => 1,
            'account_id' => (int)$account_id,
            'key' => rawurlencode($key),
            'login' => rawurlencode($user->user_login),
        ], home_url('/'));
        $trial_end = self::trial_end_label($account);
        $branding = self::platform_branding();
        $platform_name = $branding['platform_name'] ?: 'Aurora';
        $company_name = $branding['company_name'] ?: '9Ls1 Digital';
        $logo = !empty($branding['logo_url'])
            ? '<img src="'.esc_url($branding['logo_url']).'" alt="Aurora" style="display:block;max-width:170px;max-height:70px;margin:0 auto 20px">'
            : '<div style="font-size:28px;font-weight:800;letter-spacing:.08em;color:#ffffff;text-align:center;margin-bottom:18px">AURORA</div>';

        $subject = 'Velkommen til Aurora Fotoportal – aktiver kontoen din';
        $body = '<!doctype html><html><body style="margin:0;background:#eef1f4;font-family:Arial,Helvetica,sans-serif;color:#25272b">';
        $body .= '<div style="max-width:620px;margin:0 auto;padding:34px 16px">';
        $body .= '<div style="background:#111820;border-radius:20px;padding:32px 30px;text-align:center;box-shadow:0 16px 40px rgba(0,0,0,.12)">'.$logo;
        $body .= '<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#53e2c6;margin-bottom:8px">Aurora Fotoportal</div>';
        $body .= '<h1 style="margin:0 0 14px;color:#fff;font-size:28px">Velkommen, '.esc_html($account->contact_name ?: $account->account_name).'</h1>';
        $body .= '<p style="margin:0;color:#d7e0e5;line-height:1.6">Fotografkontoen din er opprettet og demoperioden er aktiv til <strong style="color:#fff">'.esc_html($trial_end).'</strong>.</p>';
        $body .= '<p style="margin:26px 0"><a href="'.esc_url($reset).'" style="display:inline-block;background:#39d9bd;color:#09211d;text-decoration:none;font-weight:800;padding:14px 24px;border-radius:10px">Aktiver Aurora Fotoportal</a></p>';
        $body .= '<p style="margin:0;color:#afbdc5;font-size:13px;line-height:1.55">Du velger først passord. Deretter logger Aurora deg inn og tar deg direkte til førstegangsoppsettet steg for steg.</p>';
        $body .= '</div>';
        $body .= '<div style="padding:22px 12px 0;color:#59636a;font-size:13px;line-height:1.55">';
        $body .= '<p><strong>Fast innlogging etter aktivering:</strong><br><a href="'.esc_url($photographer_login).'">'.esc_html($photographer_login).'</a></p>';
        $body .= '<p style="font-size:12px;color:#7d878d">Hvis aktiveringsknappen ikke virker, kopier denne adressen inn i nettleseren:<br><span style="word-break:break-all">'.esc_html($reset).'</span></p>';
        $body .= '<p>Med vennlig hilsen<br><strong>'.esc_html($platform_name).' / '.esc_html($company_name).'</strong></p>';
        $body .= '</div></div></body></html>';

        self::$last_invitation_mail_error = '';
        add_action('wp_mail_failed', [__CLASS__, 'capture_invitation_mail_failure']);
        $sent = wp_mail($email, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
        remove_action('wp_mail_failed', [__CLASS__, 'capture_invitation_mail_failure']);

        if (!$sent) {
            return [
                'sent'=>false,
                'user_id'=>$user_id,
                'error'=>self::$last_invitation_mail_error ?: 'WordPress wp_mail() returnerte false.',
            ];
        }

        update_user_meta($user_id, 'aurora_fotoportal_invitation_sent_at', current_time('mysql'));
        update_user_meta($user_id, 'aurora_fotoportal_invitation_email', $email);

        return ['sent'=>true,'user_id'=>$user_id,'error'=>''];
    }

    public function handle_resend_photographer_invitation() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_resend_photographer_invitation');

        $account_id = absint($_POST['account_id'] ?? 0);
        $account = self::get_account($account_id);
        if ($account && !empty($account->is_test_account) && class_exists('NLS1_Aurora_Test_Photographer')) {
            // The permanent Test-fotograf must behave like a brand-new photographer
            // whenever a fresh invitation is sent: clean data + onboarding step 1.
            NLS1_Aurora_Test_Photographer::prepare_invitation($account_id);
        }
        $result = self::send_photographer_invitation($account_id);

        $args = [
            'account_id' => $account_id,
            'message' => $result['sent'] ? 'invitation_resent' : 'invitation_failed',
        ];
        if (!$result['sent'] && !empty($result['error'])) {
            $args['mail_error'] = rawurlencode(wp_strip_all_tags($result['error']));
        }
        wp_safe_redirect(add_query_arg($args, self::url('accounts')));
        exit;
    }

    public function handle_create_account() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_create_photographer_account');

        $name = sanitize_text_field($_POST['account_name'] ?? '');
        $contact = sanitize_text_field($_POST['contact_name'] ?? '');
        $email = sanitize_email($_POST['contact_email'] ?? '');
        $phone = sanitize_text_field($_POST['contact_phone'] ?? '');
        $org = sanitize_text_field($_POST['organization_number'] ?? '');
        $website = esc_url_raw($_POST['website_url'] ?? '');
        $billing_name = sanitize_text_field($_POST['billing_name'] ?? $name);
        $billing_address = sanitize_text_field($_POST['billing_address'] ?? '');
        $billing_postcode = sanitize_text_field($_POST['billing_postcode'] ?? '');
        $billing_city = sanitize_text_field($_POST['billing_city'] ?? '');
        $billing_country = sanitize_text_field($_POST['billing_country'] ?? 'Norge');
        $billing_email = sanitize_email($_POST['billing_email'] ?? '');
        $include_demo_journey = !empty($_POST['include_demo_journey']);
        if (!$name || !$email) {
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
            'contact_phone' => $phone,
            'organization_number' => $org,
            'website_url' => $website,
            'billing_name' => $billing_name ?: $name,
            'billing_address' => $billing_address,
            'billing_postcode' => $billing_postcode,
            'billing_city' => $billing_city,
            'billing_country' => $billing_country ?: 'Norge',
            'billing_email' => $billing_email ?: $email,
            'status' => 'trial',
            'plan_name' => 'Trial',
            'onboarding_state' => 'onboarding_pending',
            'trial_started_at' => current_time('mysql'),
            'trial_ends_at' => wp_date('Y-m-d H:i:s', current_time('timestamp') + (DAY_IN_SECONDS * self::trial_days())),
            'created_at' => current_time('mysql'),
        ]);
        $account_id = (int)$wpdb->insert_id;

        // Seed photographer portal profile from Aurora Admin so onboarding starts with known account data.
        if ($account_id && class_exists('NLS1_Fotoportal_Admin')) {
            $seed = NLS1_Fotoportal_Admin::photographer_portal_defaults();
            $seed['studio_name'] = $name;
            $seed['photographer_name'] = $contact;
            $seed['phone'] = $phone;
            $seed['website'] = $website;
            update_option('9ls1_fotoportal_portal_settings_' . $account_id, $seed, false);
        }

        foreach (self::$module_catalog as $key => $meta) {
            $wpdb->insert(self::table('account_modules'), [
                'account_id' => $account_id,
                'module_key' => $key,
                'enabled' => !empty($meta[3]) ? 1 : 0,
                'updated_at' => current_time('mysql'),
            ]);
        }

        $wpdb->insert(self::table('licenses'), [
            'account_id' => $account_id,
            'license_name' => 'Trial',
            'status' => 'trial',
            'valid_from' => current_time('Y-m-d'),
            'valid_until' => wp_date('Y-m-d', current_time('timestamp') + (DAY_IN_SECONDS * self::trial_days())),
            'max_users' => 1,
            'storage_gb' => 10,
            'created_at' => current_time('mysql'),
        ]);

        self::set_demo_journey_enabled($account_id, $include_demo_journey);
        // Demo Content Pack is resource-only from dev.59. It must never create a
        // second demo customer/project/gallery beside the optional guided Journey.
        if ($include_demo_journey && class_exists('NLS1_Aurora_Demo_Content')) {
            NLS1_Aurora_Demo_Content::assign_to_account($account_id);
        }

        $invite = self::send_photographer_invitation($account_id);
        $args = [
            'account_id' => $account_id,
            'message' => $invite['sent'] ? 'account_created_mail_sent' : 'account_created_mail_failed',
        ];
        if (!$invite['sent'] && !empty($invite['error'])) {
            $args['mail_error'] = rawurlencode(wp_strip_all_tags($invite['error']));
        }

        wp_safe_redirect(add_query_arg($args, self::url('accounts')));
        exit;
    }


    private static function upload_url_to_path($url) {
        $url = esc_url_raw((string)$url);
        if (!$url) return '';
        $upload = wp_upload_dir();
        $baseurl = trailingslashit((string)$upload['baseurl']);
        $basedir = trailingslashit((string)$upload['basedir']);
        if (strpos($url, $baseurl) !== 0) return '';
        $rel = ltrim(rawurldecode(substr($url, strlen($baseurl))), '/');
        $path = wp_normalize_path($basedir . $rel);
        $root = wp_normalize_path($basedir);
        return strpos($path, $root) === 0 ? $path : '';
    }

    private static function safe_delete_upload_file($path) {
        $path = wp_normalize_path((string)$path);
        if (!$path) return false;
        $upload = wp_upload_dir();
        $root = wp_normalize_path(trailingslashit((string)$upload['basedir']));
        if (strpos($path, $root) !== 0 || !is_file($path)) return false;
        return (bool)@unlink($path);
    }

    private static function safe_delete_upload_tree($dir) {
        $dir = untrailingslashit(wp_normalize_path((string)$dir));
        if (!$dir || !is_dir($dir)) return 0;
        $upload = wp_upload_dir();
        $root = untrailingslashit(wp_normalize_path((string)$upload['basedir']));
        if (strpos($dir, $root . '/') !== 0 || $dir === $root) return 0;
        $count = 0;
        $items = @scandir($dir);
        if (!is_array($items)) return 0;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path) && !is_link($path)) $count += self::safe_delete_upload_tree($path);
            elseif (is_file($path) && @unlink($path)) $count++;
        }
        @rmdir($dir);
        return $count;
    }

    private static function owner_has_other_aurora_identity($user_id) {
        global $wpdb;
        $keys = $wpdb->get_col($wpdb->prepare(
            "SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id=%d AND meta_key LIKE %s",
            (int)$user_id,
            'aurora\\_%'
        ));
        $fotoportal_keys = [
            'aurora_fotoportal_account_id','aurora_fotoportal_role','aurora_fotoportal_invitation_sent_at',
            'aurora_fotoportal_invitation_email','aurora_fotoportal_password_activated_at','aurora_fotoportal_client_id'
        ];
        foreach ((array)$keys as $key) {
            if (in_array($key, $fotoportal_keys, true)) continue;
            if (strpos($key, 'aurora_support_') === 0) continue;
            return true;
        }
        return false;
    }

    /**
     * Remove WordPress identities that belong only to one Fotoportal account.
     * Administrators and users with another Aurora identity are always preserved.
     * Returns a report so account deletion/reset can show/audit what happened.
     */
    public static function cleanup_account_wordpress_users($account_id, $owner_user_id = 0, $delete_owner = true) {
        global $wpdb;
        $account_id = (int)$account_id;
        $owner_user_id = (int)$owner_user_id;
        $report = ['customer_deleted'=>0,'customer_preserved'=>0,'owner_deleted'=>false,'owner_preserved_reason'=>''];
        if (!$account_id) return $report;

        $user_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key=%s AND meta_value=%s",
            'aurora_fotoportal_account_id', (string)$account_id
        ));
        if ($owner_user_id && !in_array($owner_user_id, array_map('intval',(array)$user_ids), true)) $user_ids[] = $owner_user_id;

        require_once ABSPATH . 'wp-admin/includes/user.php';
        foreach (array_unique(array_map('intval',(array)$user_ids)) as $user_id) {
            if (!$user_id) continue;
            $user = get_user_by('id',$user_id);
            if (!$user) continue;
            $is_owner = $owner_user_id && $user_id === $owner_user_id;

            // Never delete platform/site administrators or WooCommerce managers.
            if ($user->has_cap('manage_options') || $user->has_cap('manage_woocommerce')) {
                if ($is_owner) $report['owner_preserved_reason']='admin'; else $report['customer_preserved']++;
                continue;
            }

            // Another Aurora identity means this WP identity is shared and must survive.
            if (self::owner_has_other_aurora_identity($user_id)) {
                if ($is_owner) $report['owner_preserved_reason']='other_aurora'; else $report['customer_preserved']++;
                continue;
            }

            if ($is_owner && !$delete_owner) continue;

            // Only delete users that are actually mapped to this Fotoportal account.
            $mapped_account=(int)get_user_meta($user_id,'aurora_fotoportal_account_id',true);
            if ($mapped_account !== $account_id) {
                if ($is_owner) $report['owner_preserved_reason']='mapping_mismatch'; else $report['customer_preserved']++;
                continue;
            }

            if (wp_delete_user($user_id)) {
                if ($is_owner) $report['owner_deleted']=true; else $report['customer_deleted']++;
            } else {
                if ($is_owner) $report['owner_preserved_reason']='delete_failed'; else $report['customer_preserved']++;
            }
        }
        return $report;
    }

    public function handle_delete_photographer_account() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_delete_photographer_account');

        $account_id = absint($_POST['account_id'] ?? 0);
        $account = self::get_account($account_id);
        if (!$account) wp_die('Fotografkonto finnes ikke.');
        if (!empty($account->is_test_account)) wp_die('Test-fotograf skal nullstilles fra Test-fotograf-panelet og kan ikke slettes som en vanlig konto.');

        $default = self::default_account();
        if ($default && (int)$default->id === $account_id) {
            wp_safe_redirect(add_query_arg(['message'=>'account_delete_protected'], self::url('accounts')));
            exit;
        }

        global $wpdb;
        $deleted_files = 0;
        $skipped_shared_dirs = 0;

        // 1) Delete gallery trees only when the physical folder is not referenced by another tenant.
        $galleries_table = NLS1_Fotoportal_Admin::table('galleries');
        $gallery_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT id,base_dir FROM $galleries_table WHERE account_id=%d",
            $account_id
        ));
        foreach ((array)$gallery_rows as $gallery) {
            $base_dir = wp_normalize_path((string)$gallery->base_dir);
            if (!$base_dir) continue;
            $other_refs = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $galleries_table WHERE account_id<>%d AND base_dir=%s",
                $account_id, $gallery->base_dir
            ));
            if ($other_refs === 0) $deleted_files += self::safe_delete_upload_tree($base_dir);
            else $skipped_shared_dirs++;
        }

        // 2) Delete individually registered image/edited files as a second safety net.
        $images_table = NLS1_Fotoportal_Admin::table('images');
        $image_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT original_path,preview_path,thumbnail_path,edited_path FROM $images_table WHERE account_id=%d",
            $account_id
        ), ARRAY_A);
        foreach ((array)$image_rows as $row) {
            foreach (['original_path','preview_path','thumbnail_path','edited_path'] as $key) {
                if (!empty($row[$key]) && self::safe_delete_upload_file($row[$key])) $deleted_files++;
            }
        }

        // 3) Delete account-owned branding files uploaded during onboarding.
        $portal_option = '9ls1_fotoportal_portal_settings_' . $account_id;
        $portal_settings = get_option($portal_option, []);
        foreach (['logo_url','profile_image_url','cover_image_url','watermark_url'] as $key) {
            $path = self::upload_url_to_path(is_array($portal_settings) ? ($portal_settings[$key] ?? '') : '');
            if ($path && self::safe_delete_upload_file($path)) $deleted_files++;
        }

        // 4) Remove account-owned WordPress media attachments and generated delivery files.
        $attachment_ids = [];
        $clients_table = NLS1_Fotoportal_Admin::table('clients');
        $documents_table = NLS1_Fotoportal_Admin::table('documents');
        $contracts_table = NLS1_Fotoportal_Admin::table('contracts');
        $projects_table = NLS1_Fotoportal_Admin::table('projects');
        $attachment_ids = array_merge($attachment_ids, (array)$wpdb->get_col($wpdb->prepare("SELECT profile_image_id FROM $clients_table WHERE account_id=%d AND profile_image_id IS NOT NULL AND profile_image_id>0", $account_id)));
        $attachment_ids = array_merge($attachment_ids, (array)$wpdb->get_col($wpdb->prepare("SELECT attachment_id FROM $documents_table WHERE account_id=%d AND attachment_id IS NOT NULL AND attachment_id>0", $account_id)));
        $attachment_ids = array_merge($attachment_ids, (array)$wpdb->get_col($wpdb->prepare("SELECT attachment_id FROM $contracts_table WHERE account_id=%d AND attachment_id IS NOT NULL AND attachment_id>0", $account_id)));
        $attachment_ids = array_unique(array_map('intval', $attachment_ids));
        foreach ($attachment_ids as $attachment_id) {
            if (!$attachment_id) continue;
            $other_refs = 0;
            $other_refs += (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $clients_table WHERE account_id<>%d AND profile_image_id=%d", $account_id, $attachment_id));
            $other_refs += (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $documents_table WHERE account_id<>%d AND attachment_id=%d", $account_id, $attachment_id));
            $other_refs += (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $contracts_table WHERE account_id<>%d AND attachment_id=%d", $account_id, $attachment_id));
            if ($other_refs === 0) {
                $attached_file = get_attached_file($attachment_id);
                if ($attached_file && file_exists($attached_file)) $deleted_files++;
                wp_delete_attachment($attachment_id, true);
            }
        }
        $delivery_urls = $wpdb->get_results($wpdb->prepare("SELECT delivery_zip_url,delivery_selected_zip_url FROM $projects_table WHERE account_id=%d", $account_id), ARRAY_A);
        foreach ((array)$delivery_urls as $row) {
            foreach (['delivery_zip_url','delivery_selected_zip_url'] as $key) {
                $path = self::upload_url_to_path($row[$key] ?? '');
                if ($path && self::safe_delete_upload_file($path)) $deleted_files++;
            }
        }

        // 5) Remove dedicated WordPress identities while the account mapping still exists.
        // Customer users are always cleaned up when they belong only to this Fotoportal account.
        // The photographer owner is also removed automatically unless it has admin/other Aurora access.
        $owner_user_id = (int)$account->owner_user_id;
        $wp_user_report = self::cleanup_account_wordpress_users($account_id, $owner_user_id, true);

        // 6) Remove all tenant-scoped Fotoportal rows. The tenant migration guarantees account_id.
        $deleted_rows = 0;
        if (class_exists('NLS1_Aurora_Tenant_Context')) {
            foreach (NLS1_Aurora_Tenant_Context::domain_tables() as $table_key) {
                $table = NLS1_Aurora_Tenant_Context::table($table_key);
                if (!NLS1_Aurora_Tenant_Context::table_exists($table) || !NLS1_Aurora_Tenant_Context::table_has_account_id($table)) continue;
                $result = $wpdb->delete($table, ['account_id'=>$account_id], ['%d']);
                if (is_int($result)) $deleted_rows += $result;
            }
        }

        // 7) Remove account-specific options and platform rows.
        delete_option($portal_option);
        delete_option('aurora_fotoportal_demo_journey_enabled_' . $account_id);
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('9ls1_fotoportal_gallery_hero_'.$account_id.'_') . '%'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('9ls1_fotoportal_customer_hero_'.$account_id.'_') . '%'));
        $wpdb->delete(self::table('account_modules'), ['account_id'=>$account_id], ['%d']);
        $wpdb->delete(self::table('licenses'), ['account_id'=>$account_id], ['%d']);
        $wpdb->delete(self::table('support_logs'), ['account_id'=>$account_id], ['%d']);

        $owner_deleted = !empty($wp_user_report['owner_deleted']);
        $owner_preserved_reason = (string)($wp_user_report['owner_preserved_reason'] ?? '');

        $wpdb->delete(self::table('accounts'), ['id'=>$account_id], ['%d']);

        do_action('aurora_fotoportal_account_deleted', [
            'account_id'=>$account_id,
            'account_name'=>$account->account_name,
            'deleted_rows'=>$deleted_rows,
            'deleted_files'=>$deleted_files,
            'owner_user_id'=>$owner_user_id,
            'owner_deleted'=>$owner_deleted,
            'customer_users_deleted'=>(int)($wp_user_report['customer_deleted'] ?? 0),
            'customer_users_preserved'=>(int)($wp_user_report['customer_preserved'] ?? 0),
        ]);

        set_transient('aurora_fotoportal_delete_report_' . get_current_user_id(), [
            'account_name'=>$account->account_name,
            'rows'=>$deleted_rows,
            'files'=>$deleted_files,
            'shared_dirs'=>$skipped_shared_dirs,
            'owner_deleted'=>$owner_deleted,
            'owner_preserved_reason'=>$owner_preserved_reason,
            'customer_users_deleted'=>(int)($wp_user_report['customer_deleted'] ?? 0),
            'customer_users_preserved'=>(int)($wp_user_report['customer_preserved'] ?? 0),
        ], 120);
        wp_safe_redirect(add_query_arg(['message'=>'account_deleted'], self::url('accounts')));
        exit;
    }

    public function handle_save_photographer_account() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_save_photographer_account');

        $account_id = absint($_POST['account_id'] ?? 0);
        $account = self::get_account($account_id);
        if (!$account) wp_die('Fotografkonto finnes ikke.');

        $name = sanitize_text_field($_POST['account_name'] ?? '');
        $email = sanitize_email($_POST['contact_email'] ?? '');
        if ($name === '' || $email === '') wp_die('Studionavn og konto-/login-e-post må fylles ut.');

        $status = sanitize_key($_POST['status'] ?? $account->status);
        $include_demo_journey = !empty($_POST['include_demo_journey']);
        if (!in_array($status, ['trial','active','expired','suspended','cancelled','invalid'], true)) $status = $account->status;

        if (!empty($account->owner_user_id)) {
            $email_owner = email_exists($email);
            if ($email_owner && (int)$email_owner !== (int)$account->owner_user_id) {
                wp_safe_redirect(add_query_arg([
                    'account_id' => $account_id,
                    'message' => 'account_email_in_use',
                ], self::url('accounts')));
                exit;
            }
        }

        global $wpdb;
        $wpdb->update(self::table('accounts'), [
            'account_name' => $name,
            'contact_name' => sanitize_text_field($_POST['contact_name'] ?? ''),
            'contact_email' => $email,
            'contact_phone' => sanitize_text_field($_POST['contact_phone'] ?? ''),
            'organization_number' => sanitize_text_field($_POST['organization_number'] ?? ''),
            'website_url' => esc_url_raw($_POST['website_url'] ?? ''),
            'billing_name' => sanitize_text_field($_POST['billing_name'] ?? ''),
            'billing_address' => sanitize_text_field($_POST['billing_address'] ?? ''),
            'billing_postcode' => sanitize_text_field($_POST['billing_postcode'] ?? ''),
            'billing_city' => sanitize_text_field($_POST['billing_city'] ?? ''),
            'billing_country' => sanitize_text_field($_POST['billing_country'] ?? ''),
            'billing_email' => sanitize_email($_POST['billing_email'] ?? ''),
            'internal_notes' => sanitize_textarea_field($_POST['internal_notes'] ?? ''),
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ], ['id' => $account_id]);

        self::set_demo_journey_enabled($account_id, $include_demo_journey);
        if (!$include_demo_journey && class_exists('NLS1_Aurora_Demo_Content')) {
            NLS1_Aurora_Demo_Content::remove_legacy_materialized_pack($account_id);
        }

        // Keep the photographer owner user's email synchronized when the platform owner changes the canonical login email.
        if (!empty($account->owner_user_id)) {
            $user = get_user_by('id', (int)$account->owner_user_id);
            if ($user && strtolower($user->user_email) !== strtolower($email) && !email_exists($email)) {
                wp_update_user(['ID' => $user->ID, 'user_email' => $email]);
            }
        }

        wp_safe_redirect(add_query_arg([
            'account_id' => $account_id,
            'message' => 'account_saved',
        ], self::url('accounts')));
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
                'enabled' => (self::is_core_module($key) || in_array($key, $selected, true)) ? 1 : 0,
                'updated_at' => current_time('mysql'),
            ], ['%d','%s','%d','%s']);
        }

        wp_safe_redirect(add_query_arg(['account_id' => $account_id, 'message' => 'modules_saved'], self::url('accounts')));
        exit;
    }

    public function handle_extend_trial() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_extend_trial');

        $account_id = absint($_POST['account_id'] ?? 0);
        $days = absint($_POST['days'] ?? 7);
        if (!in_array($days, [7, 14, 30], true)) $days = 7;
        $account = self::get_account($account_id);
        if (!$account) wp_die('Fotografkonto finnes ikke.');

        $now = current_time('timestamp');
        $current_end = !empty($account->trial_ends_at) ? strtotime($account->trial_ends_at) : 0;
        $base = max($now, $current_end ?: 0);
        $new_end = wp_date('Y-m-d H:i:s', $base + DAY_IN_SECONDS * $days);

        global $wpdb;
        $wpdb->update(self::table('accounts'), [
            'status' => 'trial',
            'plan_name' => 'Trial',
            'onboarding_state' => 'trial_active',
            'trial_started_at' => $account->trial_started_at ?: current_time('mysql'),
            'trial_ends_at' => $new_end,
            'updated_at' => current_time('mysql'),
        ], ['id' => $account_id]);

        $license = self::get_license($account_id);
        $wpdb->replace(self::table('licenses'), [
            'account_id' => $account_id,
            'license_key' => $license ? $license->license_key : '',
            'license_name' => 'Trial',
            'status' => 'trial',
            'valid_from' => $license && $license->valid_from ? $license->valid_from : current_time('Y-m-d'),
            'valid_until' => wp_date('Y-m-d', strtotime($new_end)),
            'max_users' => $license ? $license->max_users : 1,
            'storage_gb' => $license ? $license->storage_gb : 10,
            'created_at' => $license ? $license->created_at : current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);

        wp_safe_redirect(add_query_arg(['account_id'=>$account_id,'message'=>'trial_extended'], self::url('accounts')));
        exit;
    }

    public function handle_expire_trial() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_expire_trial');

        $account_id = absint($_POST['account_id'] ?? 0);
        if (!self::get_account($account_id)) wp_die('Fotografkonto finnes ikke.');

        global $wpdb;
        $wpdb->update(self::table('accounts'), [
            'status' => 'expired',
            'onboarding_state' => 'trial_expired',
            'trial_ends_at' => wp_date('Y-m-d H:i:s', current_time('timestamp') - HOUR_IN_SECONDS),
            'updated_at' => current_time('mysql'),
        ], ['id'=>$account_id]);
        $wpdb->update(self::table('licenses'), [
            'status'=>'expired',
            'valid_until'=>wp_date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS),
            'updated_at'=>current_time('mysql'),
        ], ['account_id'=>$account_id]);

        wp_safe_redirect(add_query_arg(['account_id'=>$account_id,'message'=>'trial_expired'], self::url('accounts')));
        exit;
    }


    public function handle_start_support_session() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_start_support_session');

        $account_id = absint($_POST['account_id'] ?? 0);
        $account = self::get_account($account_id);
        if (!$account) wp_die('Fotografkonto finnes ikke.');
        if (empty($account->support_access_enabled)) {
            self::log_support_event($account_id, get_current_user_id(), 'denied', null);
            wp_safe_redirect(add_query_arg(['account_id'=>$account_id,'message'=>'support_not_allowed'], self::url('accounts')));
            exit;
        }

        $expires_ts = time() + (self::support_session_minutes() * MINUTE_IN_SECONDS);
        $expires_mysql = wp_date('Y-m-d H:i:s', current_time('timestamp') + (self::support_session_minutes() * MINUTE_IN_SECONDS));
        update_user_meta(get_current_user_id(), 'aurora_support_account_id', $account_id);
        update_user_meta(get_current_user_id(), 'aurora_support_expires', $expires_ts);
        self::log_support_event($account_id, get_current_user_id(), 'started', $expires_mysql);

        wp_safe_redirect(NLS1_Photographer_Workspace::url('dashboard', ['support_mode'=>1]));
        exit;
    }

    public function handle_end_support_session() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_end_support_session');
        self::clear_support_session(get_current_user_id(), true);
        $account_id = absint($_POST['account_id'] ?? 0);
        wp_safe_redirect($account_id ? add_query_arg(['account_id'=>$account_id,'message'=>'support_ended'], self::url('accounts')) : self::url('accounts'));
        exit;
    }

    public function handle_revoke_support_access() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_revoke_support_access');
        $account_id = absint($_POST['account_id'] ?? 0);
        $account = self::get_account($account_id);
        if (!$account) wp_die('Fotografkonto finnes ikke.');

        global $wpdb;
        $wpdb->update(self::table('accounts'), [
            'support_access_enabled' => 0,
            'updated_at' => current_time('mysql'),
        ], ['id'=>$account_id]);
        self::log_support_event($account_id, get_current_user_id(), 'revoked_by_admin', null);

        if (self::support_context_account_id(get_current_user_id()) === $account_id) {
            self::clear_support_session(get_current_user_id(), false);
        }
        wp_safe_redirect(add_query_arg(['account_id'=>$account_id,'message'=>'support_revoked'], self::url('accounts')));
        exit;
    }

    public function handle_save_platform_branding() {
        if (!current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_save_platform_branding');

        update_option('9ls1_aurora_platform_name', sanitize_text_field($_POST['platform_name'] ?? 'Aurora'), false);
        update_option('9ls1_aurora_company_name', sanitize_text_field($_POST['company_name'] ?? '9Ls1 Digital'), false);
        update_option('9ls1_aurora_support_email', sanitize_email($_POST['support_email'] ?? ''), false);
        update_option('9ls1_aurora_logo_url', esc_url_raw($_POST['logo_url'] ?? ''), false);
        if (!empty($_FILES['watermark_preview_image']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $upload = wp_handle_upload($_FILES['watermark_preview_image'], ['test_form' => false]);
            if (empty($upload['error']) && !empty($upload['url'])) update_option('9ls1_aurora_watermark_preview_url', esc_url_raw($upload['url']), false);
        }

        // Photographer authentication backgrounds are platform-owned branding.
        // Desktop and mobile are intentionally separate so cropping can be tuned.
        foreach ([
            'photographer_login_bg_desktop' => '9ls1_aurora_photographer_login_bg_desktop',
            'photographer_login_bg_mobile' => '9ls1_aurora_photographer_login_bg_mobile',
            'customer_login_bg_desktop' => '9ls1_aurora_customer_login_bg_desktop',
            'customer_login_bg_mobile' => '9ls1_aurora_customer_login_bg_mobile',
        ] as $field => $option) {
            if (!empty($_FILES[$field]['name'])) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $upload = wp_handle_upload($_FILES[$field], ['test_form' => false]);
                if (empty($upload['error']) && !empty($upload['url'])) {
                    update_option($option, esc_url_raw($upload['url']), false);
                }
            }
        }
        if (!empty($_POST['remove_mobile_login_bg'])) {
            delete_option('9ls1_aurora_photographer_login_bg_mobile');
        }
        if (!empty($_POST['remove_customer_login_bg_desktop'])) {
            delete_option('9ls1_aurora_customer_login_bg_desktop');
        }
        if (!empty($_POST['remove_customer_login_bg_mobile'])) {
            delete_option('9ls1_aurora_customer_login_bg_mobile');
        }

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
