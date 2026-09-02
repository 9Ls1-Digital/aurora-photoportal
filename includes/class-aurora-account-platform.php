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
    const SCHEMA_VERSION = '0.4.0';

    private static $module_catalog = [
        // [Name, description, type, trial_default]
        'customers' => ['Kunder', 'Fotografens kunderegister', 'core', true],
        'projects' => ['Prosjekter', 'Fotooppdrag og prosjektflyt', 'core', true],
        'contracts' => ['Kontrakter', 'Avtaler og digital signering', 'core', true],
        'documents' => ['Dokumenter', 'Prosjektfiler og underlag', 'core', true],
        'galleries' => ['Gallerier', 'Preview, proof og bildegalleri', 'core', true],

        'premium_proof' => ['Premium Proof / PDF', 'Kontaktark og branded proof-PDF', 'addon', true],
        'customer_portal' => ['Kundeportal', 'Kundens innloggede arbeidsflate', 'addon', true],
        'favorites_comments' => ['Favoritter & kommentarer', 'Kundevalg og tilbakemeldinger', 'addon', true],
        'hq_delivery' => ['HQ-levering', 'Kontrollert levering og nedlasting', 'addon', true],

        // Future add-ons: visible in the catalogue, but not enabled by default in Trial yet.
        'shop' => ['Nettbutikk', 'Produkter, print og ordre', 'addon', false],
        'customer_app' => ['Customer App / PWA', 'Installerbar mobil kundeapp', 'addon', false],
    ];

    public function __construct() {
        add_action('admin_init', [__CLASS__, 'maybe_install']);
        add_action('admin_init', [__CLASS__, 'maybe_install_tenant'], 11);
        add_action('admin_menu', [$this, 'register_menu'], 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('aurora_core_products', [$this, 'register_with_aurora_core']);

        add_action('admin_post_aurora_create_photographer_account', [$this, 'handle_create_account']);
        add_action('admin_post_aurora_save_account_modules', [$this, 'handle_save_account_modules']);
        add_action('admin_post_aurora_save_platform_branding', [$this, 'handle_save_platform_branding']);
        add_action('admin_post_aurora_save_license', [$this, 'handle_save_license']);
        add_action('admin_post_aurora_extend_trial', [$this, 'handle_extend_trial']);
        add_action('admin_post_aurora_expire_trial', [$this, 'handle_expire_trial']);
        add_action('admin_post_aurora_resend_photographer_invitation', [$this, 'handle_resend_photographer_invitation']);
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
            onboarding_state VARCHAR(50) DEFAULT 'ready',
            trial_started_at DATETIME NULL,
            trial_ends_at DATETIME NULL,
            owner_user_id BIGINT UNSIGNED DEFAULT 0,
            onboarding_step TINYINT UNSIGNED DEFAULT 1,
            onboarding_completed_at DATETIME NULL,
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

        // v0.4.0 policy migration:
        // - Core Fotoportal functions are always included/enabled.
        // - Existing Trial accounts receive the standard Trial add-on set.
        $existing_accounts = $wpdb->get_results("SELECT id,status,plan_name FROM $accounts");
        foreach ($existing_accounts as $existing_account) {
            foreach (self::$module_catalog as $key => $meta) {
                $must_enable = self::is_core_module($key);
                if (
                    !$must_enable
                    && ($existing_account->status === 'trial' || $existing_account->plan_name === 'Trial')
                    && !empty($meta[3])
                ) {
                    $must_enable = true;
                }
                if ($must_enable) {
                    $wpdb->replace($account_modules, [
                        'account_id' => (int)$existing_account->id,
                        'module_key' => $key,
                        'enabled' => 1,
                        'updated_at' => current_time('mysql'),
                    ]);
                }
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
        $modules = self::get_account_modules((int)$account_id);
        return !empty($modules[sanitize_key($module_key)]);
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
        ];
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

        $workspace = NLS1_Photographer_Workspace::url('dashboard');
        $photographer_login = add_query_arg([
            'aurora_photographer_login' => 1,
            'account_id' => (int)$account_id,
            'login' => $email,
        ], home_url('/'));

        // Photographer invitations use Aurora's own authentication surface.
        // WordPress still validates the reset key and owns the password hash,
        // but wp-login.php is deliberately not part of this user journey.
        $reset = add_query_arg([
            'aurora_photographer_password' => 1,
            'account_id' => (int)$account_id,
            'key' => rawurlencode($key),
            'login' => rawurlencode($user->user_login),
        ], home_url('/'));
        $trial_end = self::trial_end_label($account);

        $subject = 'Velkommen til Aurora Fotoportal – opprett passord';
        $body = "Hei " . ($account->contact_name ?: $account->account_name) . ",\n\n";
        $body .= "Velkommen til Aurora Fotoportal.\n\n";
        $body .= "Demoperioden din er aktiv til " . $trial_end . ".\n\n";
        $body .= "Opprett passord og aktiver innloggingen din her:\n" . $reset . "\n\n";
        $body .= "Når du logger inn første gang, guider Aurora deg gjennom oppsett av studio, kontaktinformasjon, branding, vannmerke og kundeportal.\n\n";
        $body .= "Logg inn i Aurora Fotoportal her:\n" . $photographer_login . "\n\n";
        $body .= "Denne innloggingslenken bruker Aurora sin egen fotografinnlogging. Du skal ikke bruke WordPress sin wp-admin/wp-login-side.\n\n";
        $body .= "Med vennlig hilsen\nAurora / 9Ls1 Digital";

        self::$last_invitation_mail_error = '';
        add_action('wp_mail_failed', [__CLASS__, 'capture_invitation_mail_failure']);
        $sent = wp_mail($email, $subject, $body, ['Content-Type: text/plain; charset=UTF-8']);
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
            'status' => 'trial',
            'plan_name' => 'Trial',
            'onboarding_state' => 'onboarding_pending',
            'trial_started_at' => current_time('mysql'),
            'trial_ends_at' => wp_date('Y-m-d H:i:s', current_time('timestamp') + (DAY_IN_SECONDS * self::trial_days())),
            'created_at' => current_time('mysql'),
        ]);
        $account_id = (int)$wpdb->insert_id;

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
