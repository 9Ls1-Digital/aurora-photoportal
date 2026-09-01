<?php
if (!defined('ABSPATH')) exit;

class NLS1_Fotoportal_Admin {
    const MENU_SLUG = 'nls1-plugin-center';

    public static $project_types = [
        'Bryllup' => 'BR',
        'Konfirmasjon' => 'KO',
        'Familie' => 'FA',
        'Scene/Artist' => 'SC',
        'Event' => 'EV',
        'Bedrift' => 'BE',
        'Portrett' => 'PO',
        'Produktfoto' => 'PF',
        'Annet' => 'AN',
    ];

    public static $project_statuses = [
        'created' => 'Opprettet',
        'contract_created' => 'Kontrakt opprettet',
        'contract_sent' => 'Kontrakt sendt',
        'contract_signed' => 'Kontrakt signert',
        'shoot_done' => 'Fotografering utført',
        'images_uploaded' => 'Bilder lastet opp',
        'client_selecting' => 'Kunde velger bilder',
        'delivery_ready' => 'Levering klar',
        'delivered' => 'Levert',
        'archived' => 'Arkivert',
    ];

    public static $document_types = [
        'Kontrakt',
        'Samtykke',
        'Wedding Pose Guide',
        'Shot List',
        'Prisliste',
        'Leveranseinformasjon',
        'Bruksrettigheter',
        'Annet',
    ];

    public function __construct() {
        add_action('current_screen', [$this, 'prepare_legacy_fotoportal_screen']);
        add_action('admin_menu', [$this, 'register_menu'], 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_9ls1_fotoportal_save_client_project', [$this, 'handle_save_client_project']);
        add_action('admin_post_9ls1_fotoportal_update_project_status', [$this, 'handle_update_project_status']);
        add_action('admin_post_9ls1_fotoportal_add_contact', [$this, 'handle_add_contact']);
        add_action('admin_post_9ls1_fotoportal_add_log', [$this, 'handle_add_log']);
        add_action('admin_post_9ls1_fotoportal_update_client', [$this, 'handle_update_client']);
        add_action('admin_post_9ls1_fotoportal_update_project', [$this, 'handle_update_project']);
        add_action('admin_post_9ls1_fotoportal_delete_test_item', [$this, 'handle_delete_test_item']);
        add_action('admin_post_9ls1_fotoportal_create_contract', [$this, 'handle_create_contract']);
        add_action('admin_post_9ls1_fotoportal_mark_contract_sent', [$this, 'handle_mark_contract_sent']);
        add_action('admin_post_9ls1_fotoportal_add_document', [$this, 'handle_add_document']);
        add_action('admin_post_9ls1_fotoportal_delete_document', [$this, 'handle_delete_document']);
        add_action('admin_post_9ls1_fotoportal_save_template', [$this, 'handle_save_template']);
        add_action('admin_post_9ls1_fotoportal_delete_template', [$this, 'handle_delete_template']);
        add_action('admin_post_9ls1_fotoportal_upload_gallery_zip', [$this, 'handle_upload_gallery_zip']);
        add_action('admin_post_9ls1_fotoportal_delete_gallery', [$this, 'handle_delete_gallery']);
        add_action('admin_post_9ls1_fotoportal_save_branding', [$this, 'handle_save_branding']);
        add_action('admin_post_9ls1_fotoportal_regenerate_gallery', [$this, 'handle_regenerate_gallery']);
        add_action('admin_post_9ls1_fotoportal_generate_proof_pdf', [$this, 'handle_generate_proof_pdf']);
        add_action('admin_post_9ls1_fotoportal_create_testdata', [$this, 'handle_create_testdata']);
        add_action('admin_post_9ls1_fotoportal_delete_testdata', [$this, 'handle_delete_testdata']);
    }

    public function register_menu() {
        // Photographer workspace is intentionally hidden from the normal
        // Aurora platform-owner navigation. It remains available explicitly
        // as a development/support view until photographer account login exists.
        add_submenu_page(
            null,
            'Aurora Fotoportal – Fotografvisning',
            'Aurora Fotoportal',
            'manage_options',
            'nls1-fotoportal',
            [$this, 'render_fotoportal_entry']
        );
    }

    public function prepare_legacy_fotoportal_screen($screen) {
        if (!$screen || strpos((string)$screen->id, 'nls1-fotoportal') === false) return;
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, self::MENU_SLUG) === false && strpos($hook, 'nls1-fotoportal') === false) return;
        wp_enqueue_style('9ls1-fotoportal-admin', NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/css/admin.css', [], NLS1_FOTOPORTAL_VERSION);
        wp_enqueue_media();
    }

    public function render_fotoportal_entry() {
        // dev.11-fix1: The old Fotoportal route is no longer the normal
        // photographer entry point. Redirect to the new Photographer Workspace.
        // Legacy UI is available only via an explicit support/development flag.
        if (empty($_GET['aurora_legacy']) || $_GET['aurora_legacy'] !== '1') {
            if (class_exists('NLS1_Photographer_Workspace')) {
                wp_safe_redirect(NLS1_Photographer_Workspace::url('dashboard'));
                exit;
            }
        }
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
        include NLS1_FOTOPORTAL_PLUGIN_DIR . 'admin/view-fotoportal.php';
    }

    public function render_router() {
        $module = isset($_GET['module']) ? sanitize_key($_GET['module']) : '';
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
        if ($module === 'fotoportal') {
            include NLS1_FOTOPORTAL_PLUGIN_DIR . 'admin/view-fotoportal.php';
            return;
        }
        include NLS1_FOTOPORTAL_PLUGIN_DIR . 'admin/view-dashboard.php';
    }

    public static function dashboard_url() {
        return admin_url('admin.php?page=' . self::MENU_SLUG);
    }

    public static function fotoportal_url($tab = 'dashboard', $args = []) {
        return add_query_arg(array_merge([
            'page' => 'nls1-fotoportal',
            'tab' => sanitize_key($tab),
            'aurora_legacy' => '1',
        ], $args), admin_url('admin.php'));
    }

    public static function client_url($client_id) {
        return self::fotoportal_url('client_profile', ['client_id' => (int)$client_id]);
    }

    public static function project_url($project_id) {
        return self::fotoportal_url('project_profile', ['project_id' => (int)$project_id]);
    }

    public static function active_tab($current, $tab) {
        return $current === $tab ? ' nav-tab-active' : '';
    }

    public static function table($name) {
        global $wpdb;
        return $wpdb->prefix . '9ls1_fotoportal_' . $name;
    }

    public static function tenant_account_id() {
        return class_exists('NLS1_Aurora_Tenant_Context')
            ? NLS1_Aurora_Tenant_Context::current_account_id()
            : 0;
    }

    public static function count_rows($table, $where = '1=1') {
        global $wpdb;
        $table_name = self::table($table);
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
    }

    public static function project_prefix($type) {
        return self::$project_types[$type] ?? 'FP';
    }

    public static function project_number_matches_type($project_number, $project_type) {
        $expected = self::project_prefix($project_type);
        return strpos((string)$project_number, $expected . '-') === 0;
    }

    public static function generate_project_number($project_type = '') {
        global $wpdb;
        $year = date('Y');
        $prefix = self::project_prefix($project_type);
        $projects = self::table('projects');
        $like = $prefix . '-' . $year . '-%';
        $numbers = $wpdb->get_col($wpdb->prepare("SELECT project_number FROM $projects WHERE project_number LIKE %s", $like));
        $max = 0;
        foreach ($numbers as $number) {
            if (preg_match('/-(\d{4})$/', $number, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        return $prefix . '-' . $year . '-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function generate_customer_number() {
        global $wpdb;
        $clients = self::table('clients');
        $count = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $clients WHERE account_id=%d AND customer_number LIKE 'K-%%'",
            self::tenant_account_id()
        ));
        return 'K-' . str_pad((string)($count + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function get_clients($include_test = false, $search = '', $group = '', $type = '') {
        global $wpdb;
        $clients = self::table('clients');
        $where = ['account_id = %d'];
        $params = [self::tenant_account_id()];
        if (!$include_test) $where[] = 'is_test = 0';
        if ($search) { $where[] = '(client_name LIKE %s OR email LIKE %s OR phone LIKE %s)'; $like = '%' . $wpdb->esc_like($search) . '%'; $params = array_merge($params, [$like, $like, $like]); }
        if ($group) { $where[] = 'client_group = %s'; $params[] = $group; }
        if ($type) { $where[] = 'client_type = %s'; $params[] = $type; }
        $sql = "SELECT * FROM $clients";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY created_at DESC LIMIT 200";
        return $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : $wpdb->get_results($sql);
    }

    public static function get_projects($include_test = false, $search = '', $project_type = '', $status = '') {
        global $wpdb;
        $projects = self::table('projects');
        $clients = self::table('clients');
        $where = ['p.account_id = %d'];
        $params = [self::tenant_account_id()];
        if (!$include_test) $where[] = 'p.is_test = 0';
        if ($search) { $where[] = '(p.project_name LIKE %s OR p.project_number LIKE %s OR c.client_name LIKE %s)'; $like = '%' . $wpdb->esc_like($search) . '%'; $params = array_merge($params, [$like, $like, $like]); }
        if ($project_type) { $where[] = 'p.project_type = %s'; $params[] = $project_type; }
        if ($status) { $where[] = 'p.status = %s'; $params[] = $status; }
        $sql = "SELECT p.*, c.client_name FROM $projects p LEFT JOIN $clients c ON c.id = p.client_id AND c.account_id = p.account_id";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY p.created_at DESC LIMIT 200";
        return $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : $wpdb->get_results($sql);
    }

    public static function get_client($client_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('clients') . " WHERE id=%d AND account_id=%d",
            (int)$client_id, self::tenant_account_id()
        ));
    }

    public static function get_project($project_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, c.client_name FROM " . self::table('projects') . " p
             LEFT JOIN " . self::table('clients') . " c ON c.id=p.client_id AND c.account_id=p.account_id
             WHERE p.id=%d AND p.account_id=%d",
            $project_id, self::tenant_account_id()
        ));
    }

    public static function get_primary_contact($client_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('contacts') . " WHERE client_id=%d AND account_id=%d AND is_primary=1 ORDER BY id ASC LIMIT 1",
            $client_id, self::tenant_account_id()
        ));
    }

    public static function get_contacts($client_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::table('contacts') . " WHERE client_id=%d AND account_id=%d ORDER BY is_primary DESC,id ASC",
            $client_id, self::tenant_account_id()
        ));
    }

    public static function get_client_projects($client_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::table('projects') . " WHERE client_id=%d AND account_id=%d ORDER BY created_at DESC",
            $client_id, self::tenant_account_id()
        ));
    }

    public static function get_logs($client_id = 0, $project_id = 0) {
        global $wpdb;
        $logs = self::table('logs');
        if ($project_id) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $logs WHERE project_id = %d AND account_id = %d ORDER BY created_at DESC LIMIT 100", $project_id, self::tenant_account_id()));
        }
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $logs WHERE client_id = %d AND account_id = %d ORDER BY created_at DESC LIMIT 100", $client_id, self::tenant_account_id()));
    }

    public static function format_contact_name($contact) {
        if (!$contact) return '';
        $first = trim((string)$contact->first_name);
        $last = trim((string)$contact->last_name);
        if ($last && mb_strtolower($first) === mb_strtolower($last)) return $first;
        return trim($first . ' ' . $last);
    }

    public static function status_label($status) {
        return self::$project_statuses[$status] ?? $status;
    }

    public static function client_type_label($type) {
        $labels = [
            'private' => 'Privat',
            'business' => 'Bedrift',
            'artist' => 'Artist/Band',
            'organization' => 'Organisasjon',
        ];
        return $labels[$type] ?? $type;
    }

    private function log($client_id, $project_id, $type, $message, $is_test = 0) {
        global $wpdb;
        $wpdb->insert(self::table('logs'), [
            'account_id' => self::tenant_account_id(),
            'client_id' => $client_id ?: null,
            'project_id' => $project_id ?: null,
            'log_type' => $type,
            'message' => $message,
            'is_test' => $is_test,
            'created_at' => current_time('mysql'),
        ]);
    }

    public function handle_save_client_project() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_save_client_project');

        global $wpdb;
        $now = current_time('mysql');
        $workspace = !empty($_POST['aurora_workspace']);

        $client_name = sanitize_text_field($_POST['client_name'] ?? '');
        $client_group = sanitize_text_field($_POST['client_group'] ?? '');
        $client_type = sanitize_key($_POST['client_type'] ?? 'private');
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');
        $project_name = sanitize_text_field($_POST['project_name'] ?? '');
        $project_type = sanitize_text_field($_POST['project_type'] ?? '');
        $project_date = sanitize_text_field($_POST['project_date'] ?? '');
        $location = sanitize_text_field($_POST['location'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $is_test = !empty($_POST['is_test']) ? 1 : 0;

        if (!$client_name || !$client_group || !$first_name || !$email || !$project_name || !$project_type) {
            $target = ($workspace && class_exists('NLS1_Photographer_Workspace'))
                ? NLS1_Photographer_Workspace::url('new', ['message' => 'missing_fields'])
                : self::fotoportal_url('wizard', ['message' => 'missing_fields']);
            wp_safe_redirect($target);
            exit;
        }

        $account_id = self::tenant_account_id();
        $customer_number = self::generate_customer_number();

        $ok = $wpdb->insert(self::table('clients'), [
            'account_id' => $account_id,
            'customer_number' => $customer_number,
            'client_name' => $client_name,
            'client_group' => $client_group,
            'client_type' => $client_type,
            'email' => $email,
            'phone' => $phone,
            'city' => $city,
            'status' => 'active',
            'is_test' => $is_test,
            'created_at' => $now,
        ]);
        if (!$ok) wp_die('Kunne ikke opprette kunde.');

        $client_id = (int)$wpdb->insert_id;

        $wpdb->insert(self::table('contacts'), [
            'account_id' => $account_id,
            'client_id' => $client_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'contact_role' => 'Hovedkontakt',
            'is_primary' => 1,
            'is_test' => $is_test,
            'created_at' => $now,
        ]);

        $project_number = self::generate_project_number($project_type);
        $ok = $wpdb->insert(self::table('projects'), [
            'account_id' => $account_id,
            'client_id' => $client_id,
            'project_number' => $project_number,
            'project_name' => $project_name,
            'project_type' => $project_type,
            'project_date' => $project_date ?: null,
            'location' => $location,
            'description' => $description,
            'status' => 'created',
            'is_test' => $is_test,
            'created_at' => $now,
        ]);
        if (!$ok) wp_die('Kunden ble opprettet, men prosjektet kunne ikke opprettes.');

        $project_id = (int)$wpdb->insert_id;
        $this->log($client_id, $project_id, 'created', 'Kunde og prosjekt opprettet.', $is_test);

        if ($workspace && class_exists('NLS1_Photographer_Workspace')) {
            wp_safe_redirect(NLS1_Photographer_Workspace::url('customers', [
                'customer_id' => $client_id,
                'message' => 'created',
            ]));
        } else {
            wp_safe_redirect(self::client_url($client_id) . '&message=created');
        }
        exit;
    }

    public function handle_update_project_status() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_update_project_status');
        global $wpdb;
        $project_id = (int)($_POST['project_id'] ?? 0);
        $status = sanitize_key($_POST['status'] ?? 'created');
        $project = self::get_project($project_id);
        if ($project && isset(self::$project_statuses[$status])) {
            $wpdb->update(self::table('projects'), ['status' => $status, 'updated_at' => current_time('mysql')], ['id' => $project_id, 'account_id' => self::tenant_account_id()]);
            $this->log((int)$project->client_id, $project_id, 'status', 'Prosjektstatus endret til: ' . self::status_label($status), (int)$project->is_test);
        }
        wp_safe_redirect(self::project_url($project_id));
        exit;
    }

    public function handle_add_contact() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_add_contact');
        global $wpdb;
        $client_id = (int)($_POST['client_id'] ?? 0);
        $client = self::get_client($client_id);
        if ($client) {
            $wpdb->insert(self::table('contacts'), [
            'account_id' => self::tenant_account_id(),
                'client_id' => $client_id,
                'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
                'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
                'email' => sanitize_email($_POST['email'] ?? ''),
                'phone' => sanitize_text_field($_POST['phone'] ?? ''),
                'contact_role' => sanitize_text_field($_POST['contact_role'] ?? 'Kontakt'),
                'is_primary' => 0,
                'is_test' => (int)$client->is_test,
                'created_at' => current_time('mysql'),
            ]);
            $this->log($client_id, 0, 'contact', 'Ny kontakt lagt til.', (int)$client->is_test);
        }
        wp_safe_redirect(self::client_url($client_id));
        exit;
    }

    public function handle_add_log() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_add_log');
        $client_id = (int)($_POST['client_id'] ?? 0);
        $project_id = (int)($_POST['project_id'] ?? 0);
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $is_test = 0;
        if ($project_id) { $p = self::get_project($project_id); $is_test = $p ? (int)$p->is_test : 0; $client_id = $p ? (int)$p->client_id : $client_id; }
        elseif ($client_id) { $c = self::get_client($client_id); $is_test = $c ? (int)$c->is_test : 0; }
        if ($message) $this->log($client_id, $project_id, 'note', $message, $is_test);
        wp_safe_redirect($project_id ? self::project_url($project_id) : self::client_url($client_id));
        exit;
    }



    public static function get_contracts($include_test = true) {
        global $wpdb;
        $contracts = self::table('contracts');
        $projects = self::table('projects');
        $clients = self::table('clients');
        $where = $wpdb->prepare('ct.account_id = %d', self::tenant_account_id()) . ($include_test ? '' : ' AND ct.is_test = 0');
        return $wpdb->get_results("
            SELECT ct.*, p.project_name, p.project_number, p.project_type, c.client_name
            FROM $contracts ct
            LEFT JOIN $projects p ON p.id = ct.project_id
            LEFT JOIN $clients c ON c.id = p.client_id
            WHERE $where
            ORDER BY ct.created_at DESC
            LIMIT 200
        ");
    }

    public static function get_project_contracts($project_id) {
        global $wpdb; $contracts=self::table('contracts');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $contracts WHERE project_id=%d AND account_id=%d ORDER BY created_at DESC",
            $project_id,self::tenant_account_id()
        ));
    }

    public static function has_signed_contract($project_id) {
        global $wpdb;
        $contracts = self::table('contracts');
        $count = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $contracts WHERE project_id = %d AND status = %s AND account_id = %d",
            (int)$project_id,
            'signed',
            self::tenant_account_id()
        ));
        return $count > 0;
    }

    public static function get_contract($contract_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('contracts') . " WHERE id=%d AND account_id=%d",
            $contract_id,self::tenant_account_id()
        ));
    }

    public static function get_contract_by_token($token) {
        global $wpdb;
        $hash = hash('sha256', (string)$token);
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table('contracts') . " WHERE token_hash = %s LIMIT 1", $hash));
    }

    public static function contract_status_label($status) {
        $labels = [
            'draft' => 'Utkast',
            'sent' => 'Sendt',
            'signed' => 'Signert',
            'cancelled' => 'Kansellert',
        ];
        return $labels[$status] ?? $status;
    }

    public static function create_signing_token($contract_id) {
        global $wpdb;
        $token = wp_generate_password(40, false, false);
        $hash = hash('sha256', $token);
        $wpdb->update(self::table('contracts'), ['token_hash' => $hash], ['id' => (int)$contract_id, 'account_id' => self::tenant_account_id()]);
        return $token;
    }

    public static function signing_url($contract_id) {
        $contract = self::get_contract($contract_id);
        if (!$contract) return '';
        if (!$contract->token_hash) {
            $token = self::create_signing_token($contract_id);
        } else {
            // Existing token cannot be reversed. Generate a fresh one when link is requested.
            $token = self::create_signing_token($contract_id);
        }
        return home_url('/fotoportal-signer/?token=' . rawurlencode($token));
    }

    public function handle_create_contract() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_create_contract');
        $workspace = !empty($_POST['aurora_workspace']);
        global $wpdb;

        $project_id = (int)($_POST['project_id'] ?? 0);
        $project = self::get_project($project_id);
        if (!$project) {
            if ($workspace && class_exists('NLS1_Photographer_Workspace')) {
            wp_safe_redirect(NLS1_Photographer_Workspace::url('contracts', [
                'project_id' => (int)$project_id,
                'message' => 'contract_created',
            ]));
        } else {
            wp_safe_redirect(self::project_url($project_id) . '&message=contract_created');
        }
        exit;
        }

        $contract_name = sanitize_text_field($_POST['contract_name'] ?? '');
        $contract_version = sanitize_text_field($_POST['contract_version'] ?? '1.0');
        $contract_text = wp_kses_post($_POST['contract_text'] ?? '');
        $signer_name = sanitize_text_field($_POST['signer_name'] ?? '');
        $signer_email = sanitize_email($_POST['signer_email'] ?? '');

        if (!$contract_name || !$contract_text || !$signer_email) {
            wp_safe_redirect(self::project_url($project_id) . '&message=contract_missing');
            exit;
        }

        $wpdb->insert(self::table('contracts'), [
            'account_id' => self::tenant_account_id(),
            'project_id' => $project_id,
            'contract_name' => $contract_name,
            'contract_version' => $contract_version ?: '1.0',
            'contract_text' => $contract_text,
            'signer_name' => $signer_name,
            'signer_email' => $signer_email,
            'status' => 'draft',
            'is_test' => (int)$project->is_test,
            'created_at' => current_time('mysql'),
        ]);

        $contract_id = (int)$wpdb->insert_id;
        self::create_signing_token($contract_id);
        $wpdb->update(self::table('projects'), ['status' => 'contract_created', 'updated_at' => current_time('mysql')], ['id' => $project_id, 'account_id' => self::tenant_account_id()]);
        $this->log((int)$project->client_id, $project_id, 'contract', 'Kontrakt opprettet: ' . $contract_name, (int)$project->is_test);

        wp_safe_redirect(self::project_url($project_id) . '&message=contract_created');
        exit;
    }

    public function handle_mark_contract_sent() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_mark_contract_sent');
        $workspace = !empty($_POST['aurora_workspace']);
        global $wpdb;

        $contract_id = (int)($_POST['contract_id'] ?? 0);
        $contract = self::get_contract($contract_id);
        if (!$contract) {
            if ($workspace && class_exists('NLS1_Photographer_Workspace') && $project_id) {
            wp_safe_redirect(NLS1_Photographer_Workspace::url('contracts', [
                'project_id' => $project_id,
                'message' => 'contract_sent',
            ]));
        } else {
            wp_safe_redirect($project_id ? self::project_url($project_id) . '&message=contract_sent' : self::fotoportal_url('contracts'));
        }
        exit;
        }

        $project = self::get_project((int)$contract->project_id);

        $wpdb->update(self::table('contracts'), [
            'status' => 'sent',
            'sent_at' => current_time('mysql'),
        ], ['id' => $contract_id, 'account_id' => self::tenant_account_id()]);

        if ($project) {
            $wpdb->update(self::table('projects'), ['status' => 'contract_sent', 'updated_at' => current_time('mysql')], ['id' => (int)$project->id, 'account_id' => self::tenant_account_id()]);
            $this->log((int)$project->client_id, (int)$project->id, 'contract', 'Kontrakt markert som sendt: ' . $contract->contract_name, (int)$project->is_test);
        }

        wp_safe_redirect(self::project_url((int)$contract->project_id) . '&message=contract_sent');
        exit;
    }





    public static function sanitize_pdf_color($value, $fallback = '#111827') {
        $value = trim((string)$value);
        if ($value === '') return $fallback;
        if ($value[0] !== '#') $value = '#' . $value;
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return strtolower($value);
        }
        return $fallback;
    }

    public static function default_branding_settings() {
        return [
            'brand_name' => get_bloginfo('name'),
            'contact_name' => '',
            'contact_email' => get_bloginfo('admin_email'),
            'contact_phone' => '',
            'website' => home_url('/'),
            'watermark_type' => 'text',
            'watermark_text' => '© ' . get_bloginfo('name'),
            'watermark_logo_id' => '',
            'watermark_logo_url' => '',
            'watermark_position' => 'bottom_right',
            'watermark_opacity' => 42,
            'watermark_size' => 38,
            'preview_long_edge' => 2000,
            'thumbnail_size' => 400,
            'pdf_cover_image_id' => '',
            'pdf_cover_image_url' => '',
            'pdf_signature_image_id' => '',
            'pdf_signature_image_url' => '',
            'pdf_accent_color' => '#111827',
            'pdf_secondary_color' => '#f3f4f6',
            'pdf_gallery_url' => '',
        ];
    }

    public static function branding_settings() {
        $saved = get_option('9ls1_fotoportal_branding', []);
        if (!is_array($saved)) $saved = [];
        return array_merge(self::default_branding_settings(), $saved);
    }

    public static function gallery_upload_root() {
        $upload = wp_upload_dir();
        return [
            'basedir' => trailingslashit($upload['basedir']) . '9ls1-fotoportal/projects/',
            'baseurl' => trailingslashit($upload['baseurl']) . '9ls1-fotoportal/projects/',
        ];
    }

    public static function safe_project_folder($project_number) {
        return sanitize_file_name((string)$project_number ?: 'project');
    }

    public static function next_gallery_number($project_id) {
        global $wpdb;
        $galleries = self::table('galleries');
        $count = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $galleries WHERE project_id = %d", $project_id));
        return 'gallery-' . str_pad((string)($count + 1), 3, '0', STR_PAD_LEFT);
    }

    public static function get_galleries($project_id = 0, $include_test = true) {
        global $wpdb;
        $galleries = self::table('galleries');
        $projects = self::table('projects');
        $clients = self::table('clients');

        if ($project_id) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $galleries WHERE project_id = %d AND account_id = %d ORDER BY created_at DESC", $project_id,self::tenant_account_id()));
        }

        $where = $wpdb->prepare('g.account_id = %d', self::tenant_account_id()) . ($include_test ? '' : ' AND g.is_test = 0');
        return $wpdb->get_results("
            SELECT g.*, p.project_name, p.project_number, c.client_name
            FROM $galleries g
            LEFT JOIN $projects p ON p.id = g.project_id
            LEFT JOIN $clients c ON c.id = g.client_id
            WHERE $where
            ORDER BY g.created_at DESC
            LIMIT 200
        ");
    }

    public static function get_gallery($gallery_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('galleries') . " WHERE id=%d AND account_id=%d",
            $gallery_id,self::tenant_account_id()
        ));
    }

    public static function get_gallery_images($gallery_id, $limit = 80) {
        global $wpdb;
        $images = self::table('images');
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $images WHERE gallery_id = %d AND account_id = %d ORDER BY sort_order ASC, id ASC LIMIT %d", $gallery_id,self::tenant_account_id(),$limit));
    }

    public static function is_allowed_image_file($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg','jpeg','png','webp','tif','tiff'], true);
    }


    public static function generate_gallery_derivatives($gallery_id) {
        global $wpdb;
        $gallery = self::get_gallery($gallery_id);
        if (!$gallery) return ['preview' => 0, 'thumbs' => 0];

        $settings = self::branding_settings();

        // Sikkerhet: slett kun genererte filer. Originaler og ZIP berøres aldri her.
        $preview_dir = trailingslashit($gallery->base_dir) . 'preview/';
        $thumb_dir = trailingslashit($gallery->base_dir) . 'thumbnails/';
        wp_mkdir_p($preview_dir);
        wp_mkdir_p($thumb_dir);

        $images = self::get_gallery_images($gallery_id, 10000);
        $preview_count = 0;
        $thumb_count = 0;

        foreach ($images as $img) {
            $source = $img->original_path;
            if (!$source || !file_exists($source)) continue;

            $name_no_ext = pathinfo($img->original_filename, PATHINFO_FILENAME);
            $preview_filename = sanitize_file_name('preview_' . $name_no_ext . '.jpg');
            $thumb_filename = sanitize_file_name('thumb_' . $name_no_ext . '.jpg');

            $preview_path = trailingslashit($gallery->base_dir) . 'preview/' . $preview_filename;
            $thumb_path = trailingslashit($gallery->base_dir) . 'thumbnails/' . $thumb_filename;
            $preview_url = trailingslashit($gallery->base_url) . 'preview/' . rawurlencode($preview_filename);
            $thumb_url = trailingslashit($gallery->base_url) . 'thumbnails/' . rawurlencode($thumb_filename);

            $preview_ok = self::create_resized_image($source, $preview_path, (int)$settings['preview_long_edge'], true, $settings, (int)$gallery->watermark_enabled);
            $thumb_ok = self::create_resized_image($source, $thumb_path, (int)$settings['thumbnail_size'], false, $settings, false);

            $update = [];
            if ($preview_ok) {
                $update['preview_path'] = $preview_path;
                $update['preview_url'] = $preview_url;
                $preview_count++;
            }
            if ($thumb_ok) {
                $update['thumbnail_path'] = $thumb_path;
                $update['thumbnail_url'] = $thumb_url;
                $thumb_count++;
            }
            if ($update) {
                $update['status'] = $preview_ok ? 'preview_generated' : $img->status;
                $update['updated_at'] = current_time('mysql');
                $wpdb->update(self::table('images'), $update, ['id' => (int)$img->id, 'account_id' => self::tenant_account_id()]);
            }
        }

        $wpdb->update(self::table('galleries'), [
            'preview_count' => $preview_count,
            'thumbnail_count' => $thumb_count,
            'status' => $preview_count ? 'preview_generated' : 'uploaded',
            'updated_at' => current_time('mysql'),
        ], ['id' => $gallery_id, 'account_id' => self::tenant_account_id()]);

        return ['preview' => $preview_count, 'thumbs' => $thumb_count];
    }

    public static function create_resized_image($source, $destination, $max_edge, $watermark, $settings, $watermark_enabled) {
        if (!function_exists('imagecreatetruecolor')) return false;

        $info = @getimagesize($source);
        if (!$info) return false;

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        if ($width <= 0 || $height <= 0) return false;

        if ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($source);
        elseif ($mime === 'image/png') $src = @imagecreatefrompng($source);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($source);
        else return false;

        if (!$src) return false;

        $scale = min($max_edge / $width, $max_edge / $height, 1);
        $new_w = max(1, (int)round($width * $scale));
        $new_h = max(1, (int)round($height * $scale));

        $dst = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $width, $height);

        if ($watermark && $watermark_enabled) {
            self::apply_watermark($dst, $new_w, $new_h, $settings);
        }

        wp_mkdir_p(dirname($destination));
        $ok = imagejpeg($dst, $destination, 86);

        imagedestroy($src);
        imagedestroy($dst);

        return $ok;
    }

    public static function apply_watermark(&$img, $w, $h, $settings) {
        $type = $settings['watermark_type'] ?? 'text';
        $position = $settings['watermark_position'] ?? 'bottom_right';
        $opacity = max(5, min(95, (int)($settings['watermark_opacity'] ?? 28)));
        $size = max(12, min(120, (int)($settings['watermark_size'] ?? 22)));
        $text = trim((string)($settings['watermark_text'] ?? ''));

        if (($type === 'logo' || $type === 'text_logo') && !empty($settings['watermark_logo_url'])) {
            self::apply_logo_watermark($img, $w, $h, $settings['watermark_logo_url'], $position, $opacity, $size);
        }

        if (($type === 'text' || $type === 'text_logo') && $text !== '') {
            self::apply_text_watermark($img, $w, $h, $text, $position, $opacity, $size);
        }
    }

    public static function apply_text_watermark(&$img, $w, $h, $text, $position, $opacity, $size) {
        $alpha = 127 - (int)round($opacity * 1.27);
        $color = imagecolorallocatealpha($img, 255, 255, 255, $alpha);
        $shadow = imagecolorallocatealpha($img, 0, 0, 0, min(120, $alpha + 30));

        // GD sin innebygde font kan ikke skaleres direkte.
        // Derfor tegner vi teksten på et lite midlertidig bilde og skalerer dette opp.
        $font = 5;
        $base_w = max(1, imagefontwidth($font) * strlen($text) + 20);
        $base_h = imagefontheight($font) + 20;

        $text_layer = imagecreatetruecolor($base_w, $base_h);
        imagealphablending($text_layer, false);
        imagesavealpha($text_layer, true);
        $transparent = imagecolorallocatealpha($text_layer, 0, 0, 0, 127);
        imagefilledrectangle($text_layer, 0, 0, $base_w, $base_h, $transparent);
        imagestring($text_layer, $font, 11, 11, $text, $shadow);
        imagestring($text_layer, $font, 10, 10, $text, $color);

        $target_ratio = max(0.08, min(0.9, $size / 100));
        if ($position === 'center_large') {
            $target_ratio = max(0.45, min(0.9, $size / 100));
        }

        $target_w = (int)round($w * $target_ratio);
        $target_h = (int)round($base_h * ($target_w / $base_w));

        $scaled = imagecreatetruecolor($target_w, $target_h);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        $transparent2 = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefilledrectangle($scaled, 0, 0, $target_w, $target_h, $transparent2);
        imagecopyresampled($scaled, $text_layer, 0, 0, 0, 0, $target_w, $target_h, $base_w, $base_h);

        $draw_scaled = function($x, $y) use (&$img, &$scaled, $target_w, $target_h) {
            imagecopy($img, $scaled, $x, $y, 0, 0, $target_w, $target_h);
        };

        if ($position === 'pattern') {
            $step_x = max(220, (int)($target_w * 1.35));
            $step_y = max(120, (int)($target_h * 2.4));
            for ($y = 40; $y < $h; $y += $step_y) {
                for ($x = 40; $x < $w; $x += $step_x) {
                    $draw_scaled($x, $y);
                }
            }
            imagedestroy($text_layer);
            imagedestroy($scaled);
            return;
        }

        $margin = max(30, (int)($w * 0.03));

        if ($position === 'bottom_left') {
            $x = $margin;
            $y = $h - $target_h - $margin;
        } elseif ($position === 'center' || $position === 'center_large') {
            $x = (int)(($w - $target_w) / 2);
            $y = (int)(($h - $target_h) / 2);
        } else {
            $x = $w - $target_w - $margin;
            $y = $h - $target_h - $margin;
        }

        $draw_scaled(max(0, $x), max(0, $y));

        imagedestroy($text_layer);
        imagedestroy($scaled);
    }

    public static function apply_logo_watermark(&$img, $w, $h, $logo_url, $position, $opacity, $size) {
        $logo_id = attachment_url_to_postid($logo_url);
        $logo_path = $logo_id ? get_attached_file($logo_id) : '';

        // Fallback dersom attachment_url_to_postid ikke finner filen.
        if (!$logo_path || !file_exists($logo_path)) {
            $upload = wp_upload_dir();
            if (!empty($upload['baseurl']) && strpos($logo_url, $upload['baseurl']) === 0) {
                $relative = ltrim(str_replace($upload['baseurl'], '', $logo_url), '/');
                $candidate = trailingslashit($upload['basedir']) . $relative;
                if (file_exists($candidate)) {
                    $logo_path = $candidate;
                }
            }
        }

        if (!$logo_path || !file_exists($logo_path)) return;

        $info = @getimagesize($logo_path);
        if (!$info) return;

        $mime = $info['mime'] ?? '';
        if ($mime === 'image/png') {
            $logo = @imagecreatefrompng($logo_path);
        } elseif ($mime === 'image/jpeg') {
            $logo = @imagecreatefromjpeg($logo_path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $logo = @imagecreatefromwebp($logo_path);
        } else {
            return;
        }

        if (!$logo) return;

        imagealphablending($logo, true);
        imagesavealpha($logo, true);

        $lw = imagesx($logo);
        $lh = imagesy($logo);
        if ($lw <= 0 || $lh <= 0) {
            imagedestroy($logo);
            return;
        }

        $target_w = ($position === 'center_large') ? (int)($w * 0.50) : (int)($w * ($size / 100));
        $target_w = max(80, min($target_w, (int)($w * 0.90)));
        $target_h = (int)round($lh * ($target_w / $lw));

        $resized = imagecreatetruecolor($target_w, $target_h);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $target_w, $target_h, $transparent);
        imagecopyresampled($resized, $logo, 0, 0, 0, 0, $target_w, $target_h, $lw, $lh);

        // Juster opacity på alpha-kanalen. imagecopymerge brukes ikke fordi den lager sort bakgrunn på PNG.
        $opacity = max(5, min(100, (int)$opacity));
        if ($opacity < 100) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            for ($x = 0; $x < $target_w; $x++) {
                for ($y = 0; $y < $target_h; $y++) {
                    $rgba = imagecolorat($resized, $x, $y);
                    $a = ($rgba & 0x7F000000) >> 24;
                    if ($a < 127) {
                        $new_alpha = 127 - (int)((127 - $a) * ($opacity / 100));
                        $rgb = $rgba & 0x00FFFFFF;
                        imagesetpixel($resized, $x, $y, $rgb | ($new_alpha << 24));
                    }
                }
            }
        }

        $draw_logo = function($x, $y) use (&$img, &$resized, $target_w, $target_h) {
            imagealphablending($img, true);
            imagecopy($img, $resized, $x, $y, 0, 0, $target_w, $target_h);
        };

        if ($position === 'pattern') {
            $step_x = max(180, (int)($target_w * 1.45));
            $step_y = max(120, (int)($target_h * 2.2));
            for ($y = 40; $y < $h; $y += $step_y) {
                for ($x = 40; $x < $w; $x += $step_x) {
                    $draw_logo($x, $y);
                }
            }
        } else {
            $margin = max(30, (int)($w * 0.03));
            if ($position === 'bottom_left') {
                $x = $margin;
                $y = $h - $target_h - $margin;
            } elseif ($position === 'center' || $position === 'center_large') {
                $x = (int)(($w - $target_w) / 2);
                $y = (int)(($h - $target_h) / 2);
            } else {
                $x = $w - $target_w - $margin;
                $y = $h - $target_h - $margin;
            }
            $draw_logo(max(0, $x), max(0, $y));
        }

        imagedestroy($logo);
        imagedestroy($resized);
    }



    public static function hex_to_rgb($hex, $fallback = [17,24,39]) {
        $hex = ltrim((string)$hex, '#');
        if (strlen($hex) !== 6) return $fallback;
        return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
    }

    public static function media_url_to_path($url) {
        if (!$url) return '';
        $id = attachment_url_to_postid($url);
        if ($id) {
            $path = get_attached_file($id);
            if ($path && file_exists($path)) return $path;
        }
        $upload = wp_upload_dir();
        if (!empty($upload['baseurl']) && strpos($url, $upload['baseurl']) === 0) {
            $relative = ltrim(str_replace($upload['baseurl'], '', $url), '/');
            $candidate = trailingslashit($upload['basedir']) . $relative;
            if (file_exists($candidate)) return $candidate;
        }
        return '';
    }

    public static function ensure_pdf_jpeg($path, $suffix = 'pdf') {
        if (!$path || !file_exists($path)) return '';
        $info = @getimagesize($path);
        if (!$info) return '';
        if (($info['mime'] ?? '') === 'image/jpeg') return $path;
        if (!function_exists('imagecreatetruecolor')) return '';
        if ($info['mime'] === 'image/png') $src = @imagecreatefrompng($path);
        elseif ($info['mime'] === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($path);
        else return '';
        if (!$src) return '';
        $w = imagesx($src); $h = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $w, $h, $white);
        imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
        $tmp = trailingslashit(get_temp_dir()) . sanitize_file_name($suffix . '-' . md5($path . filemtime($path)) . '.jpg');
        imagejpeg($dst, $tmp, 90);
        imagedestroy($src); imagedestroy($dst);
        return file_exists($tmp) ? $tmp : '';
    }

    public static function draw_qr_placeholder($pdf, $x, $y, $url = '') {
        $pdf->rect_fill($x, $y, 58, 58, 0.92);
        $pdf->rect_rgb($x+6, $y+6, 14, 14, 20,20,20);
        $pdf->rect_rgb($x+38, $y+6, 14, 14, 20,20,20);
        $pdf->rect_rgb($x+6, $y+38, 14, 14, 20,20,20);
        for ($i=0; $i<5; $i++) {
            $pdf->rect_rgb($x+24+($i*5), $y+25, 3, 3, 20,20,20);
            $pdf->rect_rgb($x+28, $y+30+($i*4), 3, 3, 20,20,20);
        }
    }

    public static function get_gallery_export_pdfs($gallery) {
        if (!$gallery || empty($gallery->base_dir) || empty($gallery->base_url)) return [];
        $dir = trailingslashit($gallery->base_dir) . 'export/';
        $url = trailingslashit($gallery->base_url) . 'export/';
        if (!is_dir($dir)) return [];

        $files = glob($dir . '*.pdf');
        $out = [];
        foreach ($files as $file) {
            $out[] = [
                'name' => basename($file),
                'path' => $file,
                'url' => $url . rawurlencode(basename($file)),
            ];
        }
        return $out;
    }

    public function handle_generate_proof_pdf() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_generate_proof_pdf');

        $gallery_id = (int)($_POST['gallery_id'] ?? 0);
        $gallery = self::get_gallery($gallery_id);
        if (!$gallery) {
            wp_safe_redirect(self::fotoportal_url('galleries'));
            exit;
        }

        $project = self::get_project((int)$gallery->project_id);
        if (!$project) {
            wp_safe_redirect(self::fotoportal_url('galleries'));
            exit;
        }

        $client = self::get_client((int)$project->client_id);
        $branding = self::branding_settings();
        $images = self::get_gallery_images($gallery_id, 10000);

        $accent = self::hex_to_rgb($branding['pdf_accent_color'] ?? '#111827', [17,24,39]);
        $gallery_url = !empty($branding['pdf_gallery_url']) ? $branding['pdf_gallery_url'] : home_url('/galleri/' . sanitize_title($project->project_number));

        $usable = [];
        foreach ($images as $img) {
            $path = (!empty($img->preview_path) && file_exists($img->preview_path)) ? $img->preview_path : '';
            if (!$path && !empty($img->thumbnail_path) && file_exists($img->thumbnail_path)) $path = $img->thumbnail_path;
            if ($path) {
                $jpeg = self::ensure_pdf_jpeg($path, 'preview');
                if ($jpeg) $usable[] = [$img, $jpeg];
            }
        }

        $cover_path = '';
        if (!empty($branding['pdf_cover_image_url'])) {
            $cover_path = self::ensure_pdf_jpeg(self::media_url_to_path($branding['pdf_cover_image_url']), 'cover');
        }
        if (!$cover_path && !empty($usable[0][1])) $cover_path = $usable[0][1];

        $logo_path = '';
        if (!empty($branding['watermark_logo_url'])) {
            $logo_path = self::ensure_pdf_jpeg(self::media_url_to_path($branding['watermark_logo_url']), 'logo');
        }

        $signature_path = '';
        if (!empty($branding['pdf_signature_image_url'])) {
            $signature_path = self::ensure_pdf_jpeg(self::media_url_to_path($branding['pdf_signature_image_url']), 'signature');
        }

        $pdf = new NLS1_Fotoportal_PDF();

        // Premium cover
        $pdf->add_page();
        if ($cover_path) {
            $pdf->image($cover_path, 0, 0, 595, 842);
        } else {
            $pdf->rect_rgb(0, 0, 595, 842, $accent[0], $accent[1], $accent[2]);
        }

        $pdf->rect_rgb(38, 50, 265, 720, 18, 24, 32);

        if ($logo_path) {
            $pdf->image($logo_path, 70, 78, 105, 58);
        } else {
            $pdf->text_rgb(70, 110, $branding['brand_name'] ?: '9Ls1 Foto', 26, 255, 255, 255);
        }

        $pdf->line(70, 160, 255, 160, 230, 230, 230);
        $pdf->text_rgb(70, 224, 'KONTAKTARK', 32, 255, 255, 255);
        $pdf->text_rgb(72, 256, 'PROOF GALLERI', 12, 210, 210, 210);

        $y = 318;
        $items = [
            ['KUNDE', $client ? $client->client_name : ''],
            ['PROSJEKT', $project->project_name],
            ['PROSJEKTNR.', $project->project_number],
            ['DATO', $project->project_date ?: current_time('Y-m-d')],
            ['FOTOGRAF', $branding['contact_name'] ?: ''],
            ['NEDLASTBAR TIL', $gallery->downloadable_until ?: '-'],
        ];
        foreach ($items as $item) {
            $pdf->text_rgb(72, $y, $item[0], 8, 220,220,220);
            $pdf->text_rgb(72, $y+18, $item[1], 11, 255,255,255);
            $y += 58;
        }

        $pdf->line(70, 655, 255, 655, 230,230,230);
        $pdf->text_rgb(70, 682, ($branding['brand_name'] ?: '9Ls1 Foto') . ' - ' . ($branding['contact_name'] ?: ''), 8, 235,235,235);
        $pdf->text_rgb(70, 700, $branding['contact_email'] ?: '', 8, 235,235,235);
        $pdf->text_rgb(70, 718, $branding['website'] ?: '', 8, 235,235,235);
        $pdf->text_rgb(70, 736, $branding['contact_phone'] ?: '', 8, 235,235,235);

        self::draw_qr_placeholder($pdf, 218, 688, $gallery_url);
        $pdf->text_rgb(195, 762, 'Skann QR-kode for galleri', 7, 235,235,235);

        // Contact sheets
        $cols = 4; $rows = 4; $per_page = 16;
        $x0 = 54; $y0 = 106; $gap_x = 15; $gap_y = 34;
        $cell_w = 108; $img_h = 78;
        $chunks = array_chunk($usable, $per_page);
        $total_pages = count($chunks) + 2;
        $page_no = 2;

        foreach ($chunks as $chunk) {
            $pdf->add_page();
            $pdf->rect_fill(0, 0, 595, 842, 0.99);

            if ($logo_path) {
                $pdf->image($logo_path, 54, 30, 70, 40);
            } else {
                $pdf->text(54, 58, $branding['brand_name'] ?: '9Ls1 Foto', 18);
            }

            $pdf->text(455, 44, $project->project_number, 9);
            $pdf->text(455, 60, date('d.m.Y'), 9);
            $pdf->line(54, 78, 541, 78);

            foreach ($chunk as $i => $item) {
                [$img, $path] = $item;
                $col = $i % $cols;
                $row = intdiv($i, $cols);
                $x = $x0 + ($cell_w + $gap_x) * $col;
                $y = $y0 + ($img_h + $gap_y) * $row;

                $pdf->rect_fill($x, $y, $cell_w, $img_h, 0.94);
                $pdf->image($path, $x, $y, $cell_w, $img_h);
                $pdf->text($x + 3, $y + $img_h + 14, $img->original_filename, 7);
            }

            $pdf->line(54, 802, 541, 802);
            $pdf->text(54, 824, ($branding['brand_name'] ?: '9Ls1 Foto') . ' - ' . ($branding['contact_name'] ?: ''), 7);
            $pdf->text(260, 824, 'Kontaktark - Proof', 7);
            $pdf->text(500, 824, 'Side ' . $page_no . ' av ' . $total_pages, 7);
            $page_no++;
        }

        // Thank you page
        $pdf->add_page();
        $pdf->rect_fill(0, 0, 595, 842, 0.99);

        if ($logo_path) {
            $pdf->image($logo_path, 245, 54, 105, 60);
        } else {
            $pdf->text(220, 90, $branding['brand_name'] ?: '9Ls1 Foto', 24);
        }

        $pdf->line(100, 136, 495, 136);
        $pdf->text(155, 190, 'Takk for at du valgte ' . ($branding['brand_name'] ?: '9Ls1 Foto') . '!', 16);
        $pdf->text(155, 226, 'Dette kontaktarket er laget for enkel gjennomgang og bildeutvalg.', 10);

        $pdf->text(155, 288, 'Marker dine favoritter i galleriet', 10);
        $pdf->text(155, 320, 'Legg gjerne til kommentarer', 10);
        $pdf->text(155, 352, 'Høyoppløselige bilder leveres etter avtale', 10);
        $pdf->text(155, 384, 'Alle bilder er beskyttet og må ikke kopieres', 10);

        $pdf->line(100, 456, 495, 456);
        $pdf->text(120, 500, 'KONTAKTINFORMASJON', 11);
        $pdf->text(120, 530, $branding['contact_name'] ?: '', 10);
        $pdf->text(120, 552, $branding['contact_email'] ?: '', 10);
        $pdf->text(120, 574, $branding['contact_phone'] ?: '', 10);
        $pdf->text(120, 596, $branding['website'] ?: '', 10);

        if ($signature_path) {
            $pdf->image($signature_path, 385, 512, 110, 65);
        } else {
            $pdf->text(385, 548, 'Takk!', 22);
        }

        $pdf->rect_fill(100, 660, 395, 80, 0.94);
        self::draw_qr_placeholder($pdf, 248, 672, $gallery_url);
        $pdf->text(125, 700, 'Skann QR-koden for å komme', 8);
        $pdf->text(125, 716, 'tilbake til ditt galleri.', 8);
        $pdf->text(335, 700, 'Takk!', 15);
        $pdf->text(335, 720, ($branding['contact_name'] ?: ''), 8);

        $pdf->line(54, 802, 541, 802);
        $pdf->text(54, 824, ($branding['brand_name'] ?: '9Ls1 Foto') . ' - ' . ($branding['contact_name'] ?: ''), 7);
        $pdf->text(500, 824, 'Side ' . $total_pages . ' av ' . $total_pages, 7);

        $safe_name = sanitize_file_name('premium-preview-' . $project->project_number . '-' . $gallery->gallery_number . '-' . date('Ymd-His') . '.pdf');
        $export_path = trailingslashit($gallery->base_dir) . 'export/' . $safe_name;
        $ok = $pdf->output($export_path);

        if ($ok) {
            $this->log((int)$project->client_id, (int)$project->id, 'pdf', 'Premium PDF Preview Sheet generert: ' . $safe_name, (int)$gallery->is_test);
            wp_safe_redirect(self::project_url((int)$project->id) . '&message=proof_pdf_generated');
        } else {
            wp_safe_redirect(self::project_url((int)$project->id) . '&message=proof_pdf_failed');
        }
        exit;
    }

    public function handle_regenerate_gallery() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_regenerate_gallery');

        $gallery_id = (int)($_POST['gallery_id'] ?? 0);
        $project_id = (int)($_POST['project_id'] ?? 0);
        $gallery = self::get_gallery($gallery_id);

        if ($gallery) {
            $result = self::generate_gallery_derivatives($gallery_id);
            $project = self::get_project((int)$gallery->project_id);
            if ($project) {
                $this->log((int)$project->client_id, (int)$project->id, 'gallery', 'Preview/thumbnails regenerert for ' . $gallery->gallery_title . '. Preview: ' . $result['preview'] . ', thumbnails: ' . $result['thumbs'] . '.', (int)$gallery->is_test);
                $project_id = (int)$project->id;
            }
        }

        wp_safe_redirect(self::project_url($project_id) . '&message=gallery_regenerated');
        exit;
    }

    public function handle_upload_gallery_zip() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_upload_gallery_zip');

        global $wpdb;

        $project_id = (int)($_POST['project_id'] ?? 0);
        $project = self::get_project($project_id);
        if (!$project) {
            wp_safe_redirect(self::fotoportal_url('galleries'));
            exit;
        }

        // Aurora workflow gate: gallery production starts after a signed contract.
        if (!self::has_signed_contract($project_id)) {
            wp_safe_redirect(add_query_arg([
                'project_step' => 'gallery',
                'message' => 'gallery_contract_required',
            ], self::project_url($project_id)));
            exit;
        }

        if (empty($_FILES['gallery_zip']['name'])) {
            wp_safe_redirect(self::project_url($project_id) . '&message=gallery_zip_missing');
            exit;
        }

        $gallery_title = sanitize_text_field($_POST['gallery_title'] ?? '');
        $downloadable_until = sanitize_text_field($_POST['downloadable_until'] ?? '');
        $auto_delete_at = sanitize_text_field($_POST['auto_delete_at'] ?? '');
        $local_backup_confirmed = !empty($_POST['local_backup_confirmed']) ? 1 : 0;
        $watermark_enabled = !empty($_POST['watermark_enabled']) ? 1 : 0;
        $download_enabled = !empty($_POST['download_enabled']) ? 1 : 0;

        if (!$gallery_title) {
            $gallery_title = 'Galleri ' . current_time('Y-m-d H:i');
        }

        $file = $_FILES['gallery_zip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            wp_safe_redirect(self::project_url($project_id) . '&message=gallery_not_zip');
            exit;
        }

        $roots = self::gallery_upload_root();
        $project_folder = self::safe_project_folder($project->project_number);
        $gallery_number = self::next_gallery_number($project_id);

        $base_dir = trailingslashit($roots['basedir']) . $project_folder . '/galleries/' . $gallery_number . '/';
        $base_url = trailingslashit($roots['baseurl']) . $project_folder . '/galleries/' . $gallery_number . '/';

        $dirs = [
            $base_dir,
            $base_dir . 'zip/',
            $base_dir . 'original/',
            $base_dir . 'preview/',
            $base_dir . 'thumbnails/',
            $base_dir . 'export/',
        ];

        foreach ($dirs as $dir) {
            if (!wp_mkdir_p($dir)) {
                wp_safe_redirect(self::project_url($project_id) . '&message=gallery_dir_failed');
                exit;
            }
        }

        $zip_filename = sanitize_file_name($file['name']);
        $zip_path = $base_dir . 'zip/' . $zip_filename;

        if (!move_uploaded_file($file['tmp_name'], $zip_path)) {
            wp_safe_redirect(self::project_url($project_id) . '&message=gallery_upload_failed');
            exit;
        }

        $original_count = 0;
        $registered = [];

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_path) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                    if (!$entry || substr($entry, -1) === '/') continue;

                    $entry_norm = str_replace('\\', '/', strtolower($entry));
                    if (strpos($entry_norm, 'preview/') !== false || strpos($entry_norm, 'thumbnails/') !== false || strpos($entry_norm, 'export/') !== false || strpos($entry_norm, '__macosx/') !== false) {
                        continue;
                    }

                    if (!self::is_allowed_image_file($entry)) continue;

                    $basename = sanitize_file_name(basename($entry));
                    if (!$basename) continue;

                    $target = $base_dir . 'original/' . $basename;
                    $counter = 1;
                    while (file_exists($target)) {
                        $name = pathinfo($basename, PATHINFO_FILENAME);
                        $extension = pathinfo($basename, PATHINFO_EXTENSION);
                        $basename = sanitize_file_name($name . '-' . $counter . '.' . $extension);
                        $target = $base_dir . 'original/' . $basename;
                        $counter++;
                    }
                    $stream = $zip->getStream($entry);
                    if (!$stream) continue;

                    $out = fopen($target, 'w');
                    if (!$out) continue;
                    while (!feof($stream)) {
                        fwrite($out, fread($stream, 8192));
                    }
                    fclose($out);
                    fclose($stream);

                    if (file_exists($target)) {
                        $registered[] = [
                            'filename' => $basename,
                            'path' => $target,
                            'url' => $base_url . 'original/' . rawurlencode($basename),
                            'ext' => strtolower(pathinfo($basename, PATHINFO_EXTENSION)),
                            'size' => filesize($target),
                        ];
                        $original_count++;
                    }
                }
                $zip->close();
            }
        }

        $wpdb->insert(self::table('galleries'), [
            'account_id' => self::tenant_account_id(),
            'client_id' => (int)$project->client_id,
            'project_id' => $project_id,
            'gallery_number' => $gallery_number,
            'gallery_title' => $gallery_title,
            'base_dir' => $base_dir,
            'base_url' => $base_url,
            'zip_filename' => $zip_filename,
            'original_count' => $original_count,
            'preview_count' => 0,
            'thumbnail_count' => 0,
            'downloadable_until' => $downloadable_until ?: null,
            'auto_delete_at' => $auto_delete_at ?: null,
            'local_backup_confirmed' => $local_backup_confirmed,
            'watermark_enabled' => $watermark_enabled,
            'download_enabled' => $download_enabled,
            'status' => 'uploaded',
            'is_test' => (int)$project->is_test,
            'created_at' => current_time('mysql'),
        ]);

        $gallery_id = (int)$wpdb->insert_id;
        $sort = 0;

        foreach ($registered as $img) {
            $wpdb->insert(self::table('images'), [
            'account_id' => self::tenant_account_id(),
                'gallery_id' => $gallery_id,
                'project_id' => $project_id,
                'original_filename' => $img['filename'],
                'original_path' => $img['path'],
                'original_url' => $img['url'],
                'file_ext' => $img['ext'],
                'file_size' => (int)$img['size'],
                'sort_order' => $sort++,
                'status' => 'original_uploaded',
                'is_test' => (int)$project->is_test,
                'created_at' => current_time('mysql'),
            ]);
        }

        $derivatives = self::generate_gallery_derivatives($gallery_id);

        $wpdb->update(self::table('projects'), ['status' => 'images_uploaded', 'updated_at' => current_time('mysql')], ['id' => $project_id, 'account_id' => self::tenant_account_id()]);

        $this->log((int)$project->client_id, $project_id, 'gallery', 'Galleri lastet opp: ' . $gallery_title . ' (' . $original_count . ' bilder). Preview: ' . $derivatives['preview'] . ', thumbnails: ' . $derivatives['thumbs'] . '.', (int)$project->is_test);
        wp_safe_redirect(self::project_url($project_id) . '&message=gallery_uploaded');
        exit;
    }

    public function handle_delete_gallery() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_gallery');

        global $wpdb;

        $gallery_id = (int)($_POST['gallery_id'] ?? 0);
        $project_id = (int)($_POST['project_id'] ?? 0);
        $delete_files = !empty($_POST['delete_files']) ? 1 : 0;

        $gallery = self::get_gallery($gallery_id);
        if ($gallery) {
            if ($delete_files && $gallery->base_dir && is_dir($gallery->base_dir)) {
                self::delete_dir_recursive($gallery->base_dir);
            }

            $wpdb->delete(self::table('image_comments'), ['gallery_id' => $gallery_id], ['%d']);
            $wpdb->delete(self::table('favorites'), ['gallery_id' => $gallery_id], ['%d']);
            $wpdb->delete(self::table('downloads'), ['gallery_id' => $gallery_id], ['%d']);
            $wpdb->delete(self::table('images'), ['gallery_id' => $gallery_id], ['%d']);
            $wpdb->delete(self::table('galleries'), ['id' => $gallery_id, 'account_id' => self::tenant_account_id()], ['%d']);
        }

        wp_safe_redirect(self::project_url($project_id) . '&message=gallery_deleted');
        exit;
    }

    public static function delete_dir_recursive($dir) {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        if (!$items) return;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) self::delete_dir_recursive($path);
            else @unlink($path);
        }
        @rmdir($dir);
    }

    public static function get_documents($project_id = 0, $include_test = true) {
        global $wpdb;
        $documents = self::table('documents');
        $projects = self::table('projects');
        $clients = self::table('clients');

        if ($project_id) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $documents WHERE project_id = %d AND account_id = %d ORDER BY created_at DESC", $project_id,self::tenant_account_id()));
        }

        $where = $wpdb->prepare('d.account_id = %d', self::tenant_account_id()) . ($include_test ? '' : ' AND d.is_test = 0');
        return $wpdb->get_results("
            SELECT d.*, p.project_name, p.project_number, c.client_name
            FROM $documents d
            LEFT JOIN $projects p ON p.id = d.project_id
            LEFT JOIN $clients c ON c.id = d.client_id
            WHERE $where
            ORDER BY d.created_at DESC
            LIMIT 300
        ");
    }

    public static function get_document_templates($include_test = true) {
        global $wpdb;
        $templates = self::table('document_templates');
        $where = $wpdb->prepare('account_id = %d', self::tenant_account_id()) . ($include_test ? '' : ' AND is_test = 0');
        return $wpdb->get_results("SELECT * FROM $templates WHERE $where ORDER BY project_type ASC, template_name ASC LIMIT 300");
    }

    public static function get_template($template_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table('document_templates') . " WHERE id = %d", $template_id));
    }

    public static function replace_template_variables($content, $project) {
        $client = $project ? self::get_client((int)$project->client_id) : null;
        $primary = $client ? self::get_primary_contact((int)$client->id) : null;
        $vars = [
            '{kunde_navn}' => $client ? $client->client_name : '',
            '{prosjekt_navn}' => $project ? $project->project_name : '',
            '{prosjekt_nr}' => $project ? $project->project_number : '',
            '{prosjekt_dato}' => $project ? $project->project_date : '',
            '{lokasjon}' => $project ? $project->location : '',
            '{kontaktperson}' => $primary ? self::format_contact_name($primary) : '',
            '{fotograf}' => get_bloginfo('name'),
        ];
        return strtr((string)$content, $vars);
    }

    public function handle_add_document() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_add_document');
        global $wpdb;

        $project_id = (int)($_POST['project_id'] ?? 0);
        $project = self::get_project($project_id);
        if (!$project) {
            wp_safe_redirect(self::fotoportal_url('documents'));
            exit;
        }

        $document_title = sanitize_text_field($_POST['document_title'] ?? '');
        $document_type = sanitize_text_field($_POST['document_type'] ?? 'Annet');
        $file_url = esc_url_raw($_POST['file_url'] ?? '');
        $attachment_id = (int)($_POST['attachment_id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$document_title || !$file_url) {
            wp_safe_redirect(self::project_url($project_id) . '&message=document_missing');
            exit;
        }

        $wpdb->insert(self::table('documents'), [
            'account_id' => self::tenant_account_id(),
            'client_id' => (int)$project->client_id,
            'project_id' => $project_id,
            'attachment_id' => $attachment_id ?: null,
            'document_title' => $document_title,
            'document_type' => $document_type,
            'file_url' => $file_url,
            'notes' => $notes,
            'status' => 'active',
            'is_test' => (int)$project->is_test,
            'created_at' => current_time('mysql'),
        ]);

        $this->log((int)$project->client_id, $project_id, 'document', 'Dokument lagt til: ' . $document_title, (int)$project->is_test);
        wp_safe_redirect(self::project_url($project_id) . '&message=document_added');
        exit;
    }

    public function handle_delete_document() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_document');
        global $wpdb;

        $document_id = (int)($_POST['document_id'] ?? 0);
        $project_id = (int)($_POST['project_id'] ?? 0);

        if ($document_id) {
            $wpdb->delete(self::table('documents'), ['id' => $document_id, 'account_id' => self::tenant_account_id()], ['%d']);
        }

        wp_safe_redirect(self::project_url($project_id) . '&message=document_deleted');
        exit;
    }

    public function handle_save_template() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_save_template');
        global $wpdb;

        $template_id = (int)($_POST['template_id'] ?? 0);
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        $project_type = sanitize_text_field($_POST['project_type'] ?? '');
        $document_type = sanitize_text_field($_POST['document_type'] ?? 'Kontrakt');
        $template_content = wp_kses_post($_POST['template_content'] ?? '');
        $is_test = !empty($_POST['is_test']) ? 1 : 0;

        if (!$template_name || !$template_content) {
            wp_safe_redirect(self::fotoportal_url('settings', ['message' => 'template_missing']));
            exit;
        }

        $data = [
            'template_name' => $template_name,
            'project_type' => $project_type,
            'document_type' => $document_type,
            'template_content' => $template_content,
            'status' => 'active',
            'is_test' => $is_test,
            'updated_at' => current_time('mysql'),
        ];

        if ($template_id) {
            $wpdb->update(self::table('document_templates'), $data, ['id' => $template_id, 'account_id' => self::tenant_account_id()]);
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert(self::table('document_templates'), $data);
        }

        wp_safe_redirect(self::fotoportal_url('settings', ['message' => 'template_saved']));
        exit;
    }

    public function handle_delete_template() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_template');
        global $wpdb;

        $template_id = (int)($_POST['template_id'] ?? 0);
        if ($template_id) {
            $wpdb->delete(self::table('document_templates'), ['id' => $template_id], ['%d']);
        }

        wp_safe_redirect(self::fotoportal_url('settings', ['message' => 'template_deleted']));
        exit;
    }

    public function handle_update_client() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_update_client');
        global $wpdb;

        $client_id = (int)($_POST['client_id'] ?? 0);
        $client = self::get_client($client_id);
        if (!$client) {
            wp_safe_redirect(self::fotoportal_url('clients'));
            exit;
        }

        $client_name = sanitize_text_field($_POST['client_name'] ?? '');
        $client_group = sanitize_text_field($_POST['client_group'] ?? '');
        $client_type = sanitize_key($_POST['client_type'] ?? 'private');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if ($client_name && $client_group) {
            $wpdb->update(self::table('clients'), [
                'client_name' => $client_name,
                'client_group' => $client_group,
                'client_type' => $client_type,
                'email' => $email,
                'phone' => $phone,
                'city' => $city,
                'notes' => $notes,
                'updated_at' => current_time('mysql'),
            ], ['id' => $client_id, 'account_id' => self::tenant_account_id()]);
            $this->log($client_id, 0, 'updated', 'Kundeinformasjon oppdatert.', (int)$client->is_test);
        }

        wp_safe_redirect(self::client_url($client_id) . '&message=client_updated');
        exit;
    }

    public function handle_update_project() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_update_project');
        global $wpdb;

        $project_id = (int)($_POST['project_id'] ?? 0);
        $project = self::get_project($project_id);
        if (!$project) {
            wp_safe_redirect(self::fotoportal_url('projects'));
            exit;
        }

        $project_name = sanitize_text_field($_POST['project_name'] ?? '');
        $project_type = sanitize_text_field($_POST['project_type'] ?? '');
        $project_date = sanitize_text_field($_POST['project_date'] ?? '');
        $location = sanitize_text_field($_POST['location'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');

        if ($project_name && $project_type) {
            $new_project_number = $project->project_number;
            if (!self::project_number_matches_type($project->project_number, $project_type)) {
                $new_project_number = self::generate_project_number($project_type);
            }

            $wpdb->update(self::table('projects'), [
                'project_number' => $new_project_number,
                'project_name' => $project_name,
                'project_type' => $project_type,
                'project_date' => $project_date ?: null,
                'location' => $location,
                'description' => $description,
                'updated_at' => current_time('mysql'),
            ], ['id' => $project_id, 'account_id' => self::tenant_account_id()]);

            if ($new_project_number !== $project->project_number) {
                $this->log((int)$project->client_id, $project_id, 'updated', 'Prosjektnummer endret fra ' . $project->project_number . ' til ' . $new_project_number . '.', (int)$project->is_test);
            }
            $this->log((int)$project->client_id, $project_id, 'updated', 'Prosjektinformasjon oppdatert.', (int)$project->is_test);
        }

        wp_safe_redirect(self::project_url($project_id) . '&message=project_updated');
        exit;
    }

    public function handle_delete_test_item() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_test_item');
        global $wpdb;

        $type = sanitize_key($_POST['item_type'] ?? '');
        $id = (int)($_POST['item_id'] ?? 0);

        if ($type === 'client' && $id) {
            $client = self::get_client($id);
            if ($client && (int)$client->is_test === 1) {
                $project_ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM " . self::table('projects') . " WHERE client_id = %d AND is_test = 1", $id));
                foreach ($project_ids as $pid) {
                    $wpdb->delete(self::table('logs'), ['project_id' => (int)$pid, 'is_test' => 1], ['%d','%d']);
                    $wpdb->delete(self::table('contracts'), ['project_id' => (int)$pid, 'is_test' => 1], ['%d','%d']);
                    $wpdb->delete(self::table('projects'), ['id' => (int)$pid, 'is_test' => 1, 'account_id' => self::tenant_account_id(), 'account_id' => self::tenant_account_id()], ['%d','%d']);
                }
                $wpdb->delete(self::table('logs'), ['client_id' => $id, 'is_test' => 1], ['%d','%d']);
                $wpdb->delete(self::table('contacts'), ['client_id' => $id, 'is_test' => 1], ['%d','%d']);
                $wpdb->delete(self::table('clients'), ['id' => $id, 'is_test' => 1, 'account_id' => self::tenant_account_id(), 'account_id' => self::tenant_account_id(), 'account_id' => self::tenant_account_id()], ['%d','%d']);
            }
            wp_safe_redirect(self::fotoportal_url('clients', ['message' => 'test_item_deleted']));
            exit;
        }

        if ($type === 'project' && $id) {
            $project = self::get_project($id);
            if ($project && (int)$project->is_test === 1) {
                $wpdb->delete(self::table('logs'), ['project_id' => $id, 'is_test' => 1], ['%d','%d']);
                $wpdb->delete(self::table('contracts'), ['project_id' => $id, 'is_test' => 1], ['%d','%d']);
                $wpdb->delete(self::table('projects'), ['id' => $id, 'is_test' => 1, 'account_id' => self::tenant_account_id(), 'account_id' => self::tenant_account_id(), 'account_id' => self::tenant_account_id()], ['%d','%d']);
            }
            wp_safe_redirect(self::fotoportal_url('projects', ['message' => 'test_item_deleted']));
            exit;
        }

        wp_safe_redirect(self::fotoportal_url('dashboard'));
        exit;
    }

    public function handle_create_testdata() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_save_client_project');
        $_POST = [
            'client_name' => 'TEST - Hansen Bryllup',
            'client_group' => 'Bryllup',
            'client_type' => 'private',
            'first_name' => 'Test',
            'last_name' => 'Kunde',
            'email' => 'testkunde@example.com',
            'phone' => '00000000',
            'city' => 'Testby',
            'project_name' => 'TEST - Bryllup Hansen',
            'project_type' => 'Bryllup',
            'project_date' => date('Y-m-d'),
            'location' => 'Testlokasjon',
            'description' => 'Automatisk opprettet testprosjekt.',
            'is_test' => 1,
            '_wpnonce' => $_POST['_wpnonce'] ?? '',
        ];
        $this->handle_save_client_project();
    }

    public function handle_delete_testdata() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_testdata');
        global $wpdb;
        foreach (['logs','downloads','image_comments','favorites','images','galleries','documents','document_templates','signatures','access_tokens','contracts','projects','contacts','clients'] as $table) {
            $wpdb->delete(self::table($table), ['is_test' => 1], ['%d']);
        }
        wp_safe_redirect(self::fotoportal_url('settings', ['message' => 'testdata_deleted']));
        exit;
    }
    public function handle_save_branding() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_save_branding');

        $accent = trim((string)($_POST['pdf_accent_color'] ?? '#111827'));
        if ($accent === '') $accent = '#111827';
        if ($accent[0] !== '#') $accent = '#' . $accent;
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) $accent = '#111827';

        $secondary = trim((string)($_POST['pdf_secondary_color'] ?? '#f3f4f6'));
        if ($secondary === '') $secondary = '#f3f4f6';
        if ($secondary[0] !== '#') $secondary = '#' . $secondary;
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $secondary)) $secondary = '#f3f4f6';

        $settings = [
            'brand_name' => sanitize_text_field($_POST['brand_name'] ?? ''),
            'contact_name' => sanitize_text_field($_POST['contact_name'] ?? ''),
            'contact_email' => sanitize_email($_POST['contact_email'] ?? ''),
            'contact_phone' => sanitize_text_field($_POST['contact_phone'] ?? ''),
            'website' => esc_url_raw($_POST['website'] ?? ''),
            'watermark_type' => sanitize_key($_POST['watermark_type'] ?? 'text'),
            'watermark_text' => sanitize_text_field($_POST['watermark_text'] ?? ''),
            'watermark_logo_id' => (int)($_POST['watermark_logo_id'] ?? 0),
            'watermark_logo_url' => esc_url_raw($_POST['watermark_logo_url'] ?? ''),
            'watermark_position' => sanitize_key($_POST['watermark_position'] ?? 'bottom_right'),
            'watermark_opacity' => max(5, min(95, (int)($_POST['watermark_opacity'] ?? 42))),
            'watermark_size' => max(5, min(90, (int)($_POST['watermark_size'] ?? 38))),
            'preview_long_edge' => max(800, min(4000, (int)($_POST['preview_long_edge'] ?? 2000))),
            'thumbnail_size' => max(150, min(800, (int)($_POST['thumbnail_size'] ?? 400))),
            'pdf_cover_image_id' => (int)($_POST['pdf_cover_image_id'] ?? 0),
            'pdf_cover_image_url' => esc_url_raw($_POST['pdf_cover_image_url'] ?? ''),
            'pdf_signature_image_id' => (int)($_POST['pdf_signature_image_id'] ?? 0),
            'pdf_signature_image_url' => esc_url_raw($_POST['pdf_signature_image_url'] ?? ''),
            'pdf_accent_color' => strtolower($accent),
            'pdf_secondary_color' => strtolower($secondary),
            'pdf_gallery_url' => esc_url_raw($_POST['pdf_gallery_url'] ?? ''),
        ];

        update_option('9ls1_fotoportal_branding', $settings);
        wp_safe_redirect(self::fotoportal_url('settings', ['message' => 'branding_saved']));
        exit;
    }

    /**
     * Add current photographer ownership to new domain records.
     */
    private function tenant_data(array $data) {
        return class_exists('NLS1_Aurora_Tenant_Context')
            ? NLS1_Aurora_Tenant_Context::stamp_insert($data)
            : $data;
    }

    private function current_account_id() {
        return class_exists('NLS1_Aurora_Tenant_Context')
            ? NLS1_Aurora_Tenant_Context::current_account_id()
            : 0;
    }

}
