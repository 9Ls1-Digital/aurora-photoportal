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
        add_action('admin_post_9ls1_fotoportal_update_payment_status', [$this, 'handle_update_payment_status']);
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
        add_action('admin_post_9ls1_fotoportal_add_gallery_images', [$this, 'handle_add_gallery_images']);
        add_action('admin_post_9ls1_fotoportal_delete_gallery', [$this, 'handle_delete_gallery']);
        add_action('admin_post_9ls1_fotoportal_save_branding', [$this, 'handle_save_branding']);
        add_action('admin_post_9ls1_fotoportal_save_portal_settings', [$this, 'handle_save_portal_settings']);
        add_action('admin_post_9ls1_fotoportal_upload_resource', [$this, 'handle_upload_resource']);
        add_action('admin_post_9ls1_fotoportal_send_customer_portal', [$this, 'handle_send_customer_portal']);
        add_action('admin_post_9ls1_fotoportal_save_gallery_hero', [$this, 'handle_save_gallery_hero']);
        add_action('admin_post_9ls1_fotoportal_update_gallery_details', [$this, 'handle_update_gallery_details']);
        add_action('admin_post_9ls1_fotoportal_mark_gallery_activity_read', [$this, 'handle_mark_gallery_activity_read']);
        add_action('admin_post_9ls1_fotoportal_update_selection_status', [$this, 'handle_update_selection_status']);
        add_action('admin_post_9ls1_fotoportal_save_customer_hero', [$this, 'handle_save_customer_hero']);
        add_action('admin_post_9ls1_fotoportal_ensure_customer_login', [$this, 'handle_ensure_customer_login']);
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

    public static function get_clients($include_test=false,$search='',$group='',$type='',$sort='created',$order='desc') {
        global $wpdb; $clients=self::table('clients'); $contacts=self::table('contacts');
        $where=['c.account_id = %d']; $params=[self::tenant_account_id()];
        if(!$include_test)$where[]='c.is_test = 0';
        if($search){$where[]='(c.client_name LIKE %s OR c.email LIKE %s OR c.phone LIKE %s OR pc.first_name LIKE %s OR pc.last_name LIKE %s)';$like='%'.$wpdb->esc_like($search).'%';$params=array_merge($params,[$like,$like,$like,$like,$like]);}
        if($group){$where[]='c.client_group = %s';$params[]=$group;} if($type){$where[]='c.client_type = %s';$params[]=$type;}
        $map=['customer'=>'c.client_name','contact'=>'pc.first_name','type'=>'c.client_type','city'=>'c.city','status'=>'c.is_test','created'=>'c.created_at'];
        $sort_sql=$map[$sort]??$map['created'];$order_sql=strtolower($order)==='asc'?'ASC':'DESC';
        $sql="SELECT c.*,pc.first_name AS primary_first_name,pc.last_name AS primary_last_name FROM $clients c LEFT JOIN $contacts pc ON pc.client_id=c.id AND pc.account_id=c.account_id AND pc.is_primary=1 WHERE ".implode(' AND ',$where)." ORDER BY $sort_sql $order_sql,c.id DESC LIMIT 200";
        return $wpdb->get_results($wpdb->prepare($sql,$params));
    }

    public static function get_projects($include_test = false, $search = '', $project_type = '', $status = '', $sort = 'created', $order = 'desc') {
        global $wpdb;
        $projects = self::table('projects');
        $clients = self::table('clients');
        $where = ['p.account_id = %d'];
        $params = [self::tenant_account_id()];
        if (!$include_test) $where[] = 'p.is_test = 0';
        if ($search) { $where[] = '(p.project_name LIKE %s OR p.project_number LIKE %s OR c.client_name LIKE %s)'; $like = '%' . $wpdb->esc_like($search) . '%'; $params = array_merge($params, [$like, $like, $like]); }
        if ($project_type) { $where[] = 'p.project_type = %s'; $params[] = $project_type; }
        if ($status) { $where[] = 'p.status = %s'; $params[] = $status; }
        $sort_map = [
            'project' => 'p.project_name',
            'customer' => 'c.client_name',
            'type' => 'p.project_type',
            'date' => 'p.project_date',
            'status' => 'p.status',
            'created' => 'p.created_at',
        ];
        $sort_sql = $sort_map[$sort] ?? $sort_map['created'];
        $order_sql = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        $sql = "SELECT p.*, c.client_name FROM $projects p LEFT JOIN $clients c ON c.id = p.client_id AND c.account_id = p.account_id";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY $sort_sql $order_sql, p.id DESC LIMIT 200";
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
        $address=sanitize_text_field($_POST['address']??''); $postal_code=sanitize_text_field($_POST['postal_code']??''); $city=sanitize_text_field($_POST['city']??'');
        $organization_number=sanitize_text_field($_POST['organization_number']??''); $billing_same=!empty($_POST['billing_same_as_customer'])?1:0;
        $billing_name=sanitize_text_field($_POST['billing_name']??''); $billing_address=sanitize_text_field($_POST['billing_address']??''); $billing_postal_code=sanitize_text_field($_POST['billing_postal_code']??''); $billing_city=sanitize_text_field($_POST['billing_city']??'');
        $project_name = sanitize_text_field($_POST['project_name'] ?? '');
        $project_type = sanitize_text_field($_POST['project_type'] ?? '');
        $project_date = sanitize_text_field($_POST['project_date'] ?? '');
        $location = sanitize_text_field($_POST['location'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $is_test = !empty($_POST['is_test']) ? 1 : 0;

        if (!$client_name || !$first_name || !$email || !$project_name || !$project_type) {
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
            'phone'=>$phone,'address'=>$address,'postal_code'=>$postal_code,'city'=>$city,'organization_number'=>$organization_number,
            'billing_same_as_customer'=>$billing_same,'billing_name'=>$billing_same?$client_name:$billing_name,'billing_address'=>$billing_same?$address:$billing_address,'billing_postal_code'=>$billing_same?$postal_code:$billing_postal_code,'billing_city'=>$billing_same?$city:$billing_city,
            'status'=>'active',
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

        $project_id=(int)$wpdb->insert_id;
        if(!empty($_FILES['project_document']['name']) && empty($_FILES['project_document']['error'])){
            require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
            $attachment_id=media_handle_upload('project_document',0);
            if(!is_wp_error($attachment_id)){ $file_url=wp_get_attachment_url($attachment_id);$document_title=sanitize_text_field($_POST['project_document_title']??'');if(!$document_title)$document_title=get_the_title($attachment_id)?:basename((string)$file_url);
                $wpdb->insert(self::table('documents'),['account_id'=>$account_id,'client_id'=>$client_id,'project_id'=>$project_id,'attachment_id'=>(int)$attachment_id,'document_title'=>$document_title,'document_type'=>sanitize_text_field($_POST['project_document_type']??'Annet'),'file_url'=>$file_url,'notes'=>'Lastet opp ved opprettelse av prosjekt.','status'=>'active','is_test'=>$is_test,'created_at'=>$now]);}
        }
        $this->log($client_id,$project_id,'created','Kunde og prosjekt opprettet.',$is_test);

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
        $workspace = !empty($_POST['aurora_workspace']);
        global $wpdb;
        $project_id = (int)($_POST['project_id'] ?? 0);
        $status = sanitize_key($_POST['status'] ?? 'created');
        $project = self::get_project($project_id);
        if ($project && isset(self::$project_statuses[$status])) {
            $wpdb->update(self::table('projects'), ['status' => $status, 'updated_at' => current_time('mysql')], ['id' => $project_id, 'account_id' => self::tenant_account_id()]);
            $this->log((int)$project->client_id, $project_id, 'status', 'Prosjektstatus endret til: ' . self::status_label($status), (int)$project->is_test);
        }
        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('hq_delivery', ['project_id'=>$project_id,'message'=>'status_updated'])
            : self::project_url($project_id));
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



    public static function get_contracts($include_test = true, $search = '', $status = '', $source = '', $sort = 'created', $order = 'desc') {
        global $wpdb;
        $contracts = self::table('contracts');
        $projects = self::table('projects');
        $clients = self::table('clients');
        $where = ['ct.account_id = %d'];
        $params = [self::tenant_account_id()];
        if (!$include_test) $where[] = 'ct.is_test = 0';
        if ($search) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(ct.contract_name LIKE %s OR p.project_name LIKE %s OR p.project_number LIKE %s OR c.client_name LIKE %s OR ct.signer_email LIKE %s)';
            $params = array_merge($params, [$like,$like,$like,$like,$like]);
        }
        if ($status) { $where[] = 'ct.status = %s'; $params[] = $status; }
        if ($source) { $where[] = 'ct.contract_source = %s'; $params[] = $source; }
        $sort_map = ['contract'=>'ct.contract_name','project'=>'p.project_name','customer'=>'c.client_name','source'=>'ct.contract_source','status'=>'ct.status','created'=>'ct.created_at'];
        $sort_sql = $sort_map[$sort] ?? $sort_map['created'];
        $order_sql = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        $sql = "SELECT ct.*, p.project_name, p.project_number, p.project_type, c.client_name
            FROM $contracts ct
            LEFT JOIN $projects p ON p.id = ct.project_id AND p.account_id = ct.account_id
            LEFT JOIN $clients c ON c.id = p.client_id AND c.account_id = ct.account_id
            WHERE " . implode(' AND ', $where) . " ORDER BY $sort_sql $order_sql, ct.id DESC LIMIT 200";
        return $wpdb->get_results($wpdb->prepare($sql, $params));
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
            wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
                ? NLS1_Photographer_Workspace::url('contracts')
                : self::fotoportal_url('contracts'));
            exit;
        }

        $source = 'aurora';
        $contract_name = sanitize_text_field($_POST['contract_name'] ?? $_POST['contract_title'] ?? 'Kontrakt');
        $contract_version = sanitize_text_field($_POST['contract_version'] ?? '1.0');
        $contract_text = wp_kses_post($_POST['contract_text'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        $signer_name = sanitize_text_field($_POST['signer_name'] ?? '');
        $signer_email = sanitize_email($_POST['signer_email'] ?? '');
        $attachment_id = 0;
        $file_url = '';

        if (!empty($_FILES['contract_file']['name']) && empty($_FILES['contract_file']['error'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attachment_id = media_handle_upload('contract_file', 0);
            if (!is_wp_error($attachment_id)) {
                $file_url = (string)wp_get_attachment_url($attachment_id);
            } else {
                $attachment_id = 0;
            }
        }

        $missing = !$contract_name || !$contract_text || !$signer_email;

        if ($missing) {
            wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
                ? NLS1_Photographer_Workspace::url('contracts', ['project_id'=>$project_id,'message'=>'contract_missing'])
                : self::project_url($project_id) . '&message=contract_missing');
            exit;
        }

        $wpdb->insert(self::table('contracts'), [
            'account_id' => self::tenant_account_id(),
            'project_id' => $project_id,
            'contract_name' => $contract_name,
            'contract_version' => $contract_version ?: '1.0',
            'contract_text' => $contract_text,
            'contract_source' => $source,
            'attachment_id' => $attachment_id ?: null,
            'file_url' => $file_url ?: null,
            'notes' => $notes,
            'signer_name' => $signer_name,
            'signer_email' => $signer_email,
            'status' => 'draft',
            'is_test' => (int)$project->is_test,
            'created_at' => current_time('mysql'),
        ]);

        $contract_id = (int)$wpdb->insert_id;
        self::create_signing_token($contract_id);
        $wpdb->update(self::table('projects'), ['status'=>'contract_created','updated_at'=>current_time('mysql')], ['id'=>$project_id,'account_id'=>self::tenant_account_id()]);
        $this->log((int)$project->client_id, $project_id, 'contract', 'ADS-kontrakt opprettet: ' . $contract_name, (int)$project->is_test);

        wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
            ? NLS1_Photographer_Workspace::url('contracts', ['project_id'=>$project_id,'message'=>'contract_created'])
            : self::project_url($project_id) . '&message=contract_created');
        exit;
    }

    public function handle_mark_contract_sent() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        $contract_id = (int)($_POST['contract_id'] ?? 0);
        check_admin_referer('9ls1_fotoportal_mark_contract_sent_' . $contract_id);
        $workspace = !empty($_POST['aurora_workspace']);
        global $wpdb;

        $contract = self::get_contract($contract_id);
        if (!$contract) {
            wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
                ? NLS1_Photographer_Workspace::url('contracts')
                : self::fotoportal_url('contracts'));
            exit;
        }

        $project_id = (int)$contract->project_id;
        $project = self::get_project($project_id);
        if (($contract->contract_source ?? 'aurora') === 'upload') {
            wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
                ? NLS1_Photographer_Workspace::url('contracts', ['project_id'=>$project_id,'message'=>'external_contract'])
                : self::project_url($project_id));
            exit;
        }

        $wpdb->update(self::table('contracts'), ['status'=>'sent','sent_at'=>current_time('mysql')], ['id'=>$contract_id,'account_id'=>self::tenant_account_id()]);
        if ($project) {
            $wpdb->update(self::table('projects'), ['status'=>'contract_sent','updated_at'=>current_time('mysql')], ['id'=>$project_id,'account_id'=>self::tenant_account_id()]);
            $this->log((int)$project->client_id,$project_id,'contract','Kontrakt markert som sendt: '.$contract->contract_name,(int)$project->is_test);
        }

        wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
            ? NLS1_Photographer_Workspace::url('contracts', ['project_id'=>$project_id,'message'=>'contract_sent'])
            : self::project_url($project_id).'&message=contract_sent');
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

    public static function project_delivery_state($project_id){
        $p=self::get_project((int)$project_id); if(!$p)return ['project'=>false,'contract_registered'=>false,'contract_signed'=>false,'documents'=>false,'gallery'=>false,'paid'=>false,'portal_ready'=>false];
        $contracts=self::get_project_contracts((int)$project_id); $docs=self::get_documents((int)$project_id,true); $gals=self::get_galleries((int)$project_id,true);
        $signed=self::has_signed_contract((int)$project_id); $paid=(($p->payment_status??'unpaid')==='paid');
        return ['project'=>true,'contract_registered'=>!empty($contracts),'contract_signed'=>$signed,'documents'=>!empty($docs),'gallery'=>!empty($gals),'paid'=>$paid,'portal_ready'=>($signed && $paid && !empty($gals))];
    }
    public static function client_user_authorized($client){
        if(!$client || !is_user_logged_in())return false;
        return self::repair_client_user_authorization($client,(int)get_current_user_id());
    }
    public static function repair_client_user_authorization($client,$user_id=0){
        if(!$client)return false;
        $user_id=(int)$user_id; if(!$user_id)$user_id=(int)get_current_user_id(); if(!$user_id)return false;
        $u=get_user_by('id',$user_id); if(!$u)return false;
        $client_id=(int)$client->id; $account_id=(int)$client->account_id;
        $meta_client=(int)get_user_meta($user_id,'aurora_fotoportal_client_id',true);
        $meta_account=(int)get_user_meta($user_id,'aurora_fotoportal_account_id',true);
        if($meta_client===$client_id && (!$meta_account || $meta_account===$account_id)){
            if($meta_account!==$account_id)update_user_meta($user_id,'aurora_fotoportal_account_id',$account_id);
            return true;
        }
        global $wpdb;
        $emails=[];
        if(!empty($client->email))$emails[]=strtolower(sanitize_email((string)$client->email));
        $contact_email=$wpdb->get_var($wpdb->prepare(
            "SELECT email FROM ".self::table('contacts')." WHERE client_id=%d AND account_id=%d AND is_primary=1 ORDER BY id ASC LIMIT 1",
            $client_id,$account_id
        ));
        if($contact_email)$emails[]=strtolower(sanitize_email((string)$contact_email));
        $emails=array_values(array_unique(array_filter($emails)));
        $user_email=strtolower(sanitize_email((string)$u->user_email));
        if($user_email && in_array($user_email,$emails,true)){
            update_user_meta($user_id,'aurora_fotoportal_client_id',$client_id);
            update_user_meta($user_id,'aurora_fotoportal_account_id',$account_id);
            return true;
        }
        return false;
    }
    public static function project_portal_ready($project_id){$x=self::project_delivery_state((int)$project_id);return !empty($x['portal_ready']);}
    public static function maybe_release_customer_portal($project_id){
        $p=self::get_project((int)$project_id); if(!$p)return false; $state=self::project_delivery_state((int)$project_id); if(empty($state['portal_ready']))return false;
        if(!empty($p->portal_released_at))return true; $c=self::get_client((int)$p->client_id); if(!$c)return false; $pc=self::get_primary_contact((int)$c->id); $to=sanitize_email($pc&&$pc->email?$pc->email:$c->email); if(!$to)return false;
        self::ensure_client_portal_user((int)$c->id); $ps=self::photographer_portal_settings(); $studio=$ps['studio_name']?:($ps['photographer_name']?:get_bloginfo('name'));
        $subject='Bildene dine er klare – '.$p->project_name; $body="Hei ".$c->client_name.",\n\nKontrakten er signert og fakturaen er registrert som betalt. Du kan nå åpne kundeportalen og se galleriene dine.\n\nLogg inn og åpne portalen her:\n".self::customer_portal_url((int)$c->id)."\n\nMed vennlig hilsen\n".$studio;
        $headers=['Content-Type: text/plain; charset=UTF-8']; if(!empty($ps['email']))$headers[]='Reply-To: '.$ps['email']; if(!wp_mail($to,$subject,$body,$headers))return false;
        global $wpdb; $wpdb->update(self::table('projects'),['portal_released_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')],['id'=>(int)$project_id,'account_id'=>self::tenant_account_id()]); return true;
    }
    public static function standard_contract_text(){
        $default="AVTALE OM FOTOGRAFERING OG BILDELEVERANSE\n\nDenne avtalen gjelder fotograferingsoppdraget mellom fotografen og kunden. Omfang, dato, sted, pris og leveranse følger prosjektets avtalte vilkår. Kunden bekrefter at opplysningene er korrekte og godtar vilkårene for oppdraget.\n\nEndringer kan avtales skriftlig mellom partene. Ved digital signering registrerer Aurora tidspunkt og signaturinformasjon.";
        return (string)get_option('9ls1_fotoportal_standard_contract_text',$default);
    }
    public static function client_portal_email($client_id){
        $c=self::get_client((int)$client_id); if(!$c)return ''; $pc=self::get_primary_contact((int)$client_id);
        return sanitize_email($pc&&$pc->email?$pc->email:$c->email);
    }
    public static function client_portal_user($client_id){$email=self::client_portal_email((int)$client_id);return $email?get_user_by('email',$email):false;}
    public static function ensure_client_portal_user($client_id){
        $c=self::get_client((int)$client_id); if(!$c)return 0; $email=self::client_portal_email((int)$client_id); if(!$email)return 0;
        $u=get_user_by('email',$email); if($u){update_user_meta((int)$u->ID,'aurora_fotoportal_client_id',(int)$c->id);update_user_meta((int)$u->ID,'aurora_fotoportal_account_id',(int)$c->account_id);return (int)$u->ID;}
        $base=sanitize_user(strstr($email,'@',true)?:'kunde',true); if(!$base)$base='kunde'; $login=$base; $n=1; while(username_exists($login)){$login=$base.$n++;}
        $id=wp_create_user($login,wp_generate_password(24,true,true),$email); if(is_wp_error($id))return 0;
        $user=new WP_User($id); $user->set_role('subscriber'); update_user_meta($id,'aurora_fotoportal_client_id',(int)$c->id);update_user_meta($id,'aurora_fotoportal_account_id',(int)$c->account_id);
        // Aurora customer accounts must never be sent through the standard WordPress password UI.
        $key=get_password_reset_key($user);
        if(!is_wp_error($key)){
            $token=self::ensure_client_portal_token((int)$c->id); $ps=self::photographer_portal_settings((int)$c->account_id); $studio=$ps['studio_name']?:($ps['photographer_name']?:get_bloginfo('name'));
            $reset=add_query_arg(['fotoportal_password'=>1,'mode'=>'reset','token'=>rawurlencode($token),'key'=>rawurlencode($key),'login'=>rawurlencode($user->user_login)],home_url('/'));
            $subject='Opprett passord til bildeportalen – '.$studio; $body="Hei ".$c->client_name.",\n\nDin private bildeportal er opprettet. Opprett passord her:\n".$reset."\n\nMed vennlig hilsen\n".$studio;
            $headers=['Content-Type: text/plain; charset=UTF-8']; if(!empty($ps['email']))$headers[]='Reply-To: '.$ps['email']; wp_mail($email,$subject,$body,$headers);
        }
        return (int)$id;
    }
    public static function photographer_portal_defaults(){return ['studio_name'=>'','photographer_name'=>'','email'=>'','phone'=>'','website'=>'','address'=>'','about'=>'','logo_url'=>'','profile_image_url'=>'','cover_image_url'=>'','watermark_url'=>'','watermark_position'=>'bottom_right','watermark_opacity'=>35,'watermark_size'=>18,'accent_color'=>'#6f4bf2','email_subject'=>'Dine bilder er klare – {project_name}','email_body'=>"Hei {customer_name},\n\nBildene dine er nå tilgjengelige i kundeportalen.\n\nÅpne kundeportalen her:\n{customer_portal_url}\n\nMed vennlig hilsen\n{photographer_name}"];}
    public static function photographer_portal_settings($account_id=0){$account_id=$account_id?:self::tenant_account_id();$x=get_option('9ls1_fotoportal_portal_settings_'.(int)$account_id,[]);return array_merge(self::photographer_portal_defaults(),is_array($x)?$x:[]);}
    public static function ensure_client_portal_token($client_id){global $wpdb;$c=self::get_client($client_id);if(!$c)return '';if($c->portal_token)return $c->portal_token;$t=wp_generate_password(40,false,false);$wpdb->update(self::table('clients'),['portal_token'=>$t],['id'=>(int)$client_id,'account_id'=>self::tenant_account_id()]);return $t;}
    public static function customer_portal_url($client_id){$t=self::ensure_client_portal_token($client_id);return $t?add_query_arg(['fotoportal_customer'=>1,'token'=>rawurlencode($t)],home_url('/')):'';}
    public static function get_public_client_by_token($t){global $wpdb;$t=sanitize_text_field($t);return $t?$wpdb->get_row($wpdb->prepare("SELECT * FROM ".self::table('clients')." WHERE portal_token=%s LIMIT 1",$t)):null;}
    public static function get_public_client_by_id_account($id,$account_id){global $wpdb;return $wpdb->get_row($wpdb->prepare("SELECT * FROM ".self::table('clients')." WHERE id=%d AND account_id=%d LIMIT 1",(int)$id,(int)$account_id));}
    public static function public_project_portal_ready($project_id,$account_id){global $wpdb;$p=$wpdb->get_row($wpdb->prepare("SELECT payment_status FROM ".self::table('projects')." WHERE id=%d AND account_id=%d LIMIT 1",(int)$project_id,(int)$account_id));if(!$p||($p->payment_status??'unpaid')!=='paid')return false;$n=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".self::table('contracts')." WHERE project_id=%d AND account_id=%d AND status='signed'",(int)$project_id,(int)$account_id));return $n>0;}
    public static function public_project_delivery_state($project_id,$account_id){
        global $wpdb;
        $project_id=(int)$project_id; $account_id=(int)$account_id;
        if(!$project_id||!$account_id)return ['project'=>false,'contract_registered'=>false,'contract_signed'=>false,'documents'=>false,'document_count'=>0,'gallery'=>false,'gallery_count'=>0,'paid'=>false,'portal_ready'=>false];
        $p=$wpdb->get_row($wpdb->prepare("SELECT id,payment_status FROM ".self::table('projects')." WHERE id=%d AND account_id=%d LIMIT 1",$project_id,$account_id));
        if(!$p)return ['project'=>false,'contract_registered'=>false,'contract_signed'=>false,'documents'=>false,'document_count'=>0,'gallery'=>false,'gallery_count'=>0,'paid'=>false,'portal_ready'=>false];
        $contract_count=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".self::table('contracts')." WHERE project_id=%d AND account_id=%d",$project_id,$account_id));
        $signed_count=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".self::table('contracts')." WHERE project_id=%d AND account_id=%d AND status='signed'",$project_id,$account_id));
        $document_count=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".self::table('documents')." WHERE project_id=%d AND account_id=%d",$project_id,$account_id));
        $gallery_count=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".self::table('galleries')." WHERE project_id=%d AND account_id=%d",$project_id,$account_id));
        $paid=(($p->payment_status??'unpaid')==='paid');
        $signed=$signed_count>0; $gallery=$gallery_count>0;
        return ['project'=>true,'contract_registered'=>$contract_count>0,'contract_signed'=>$signed,'documents'=>$document_count>0,'document_count'=>$document_count,'gallery'=>$gallery,'gallery_count'=>$gallery_count,'paid'=>$paid,'portal_ready'=>($signed&&$paid&&$gallery)];
    }
    public static function get_public_client_project_statuses($c){
        global $wpdb; if(!$c)return [];
        return $wpdb->get_results($wpdb->prepare(
            "SELECT id project_id,project_name,project_number,project_date,payment_status,created_at FROM ".self::table('projects')." WHERE client_id=%d AND account_id=%d ORDER BY COALESCE(project_date,created_at) DESC,id DESC",
            (int)$c->id,(int)$c->account_id
        ));
    }
    public static function get_public_client_projects_and_galleries($c){global $wpdb;if(!$c)return [];$p=self::table('projects');$g=self::table('galleries');$i=self::table('images');$ct=self::table('contracts');$rows=$wpdb->get_results($wpdb->prepare("SELECT p.id project_id,p.project_name,p.project_number,p.project_date,p.payment_status,g.id gallery_id,g.account_id,g.gallery_title,g.gallery_description,g.selection_status,g.selection_submitted_at,g.selection_processing_at,g.selection_ready_at,g.public_token,g.original_count FROM $p p LEFT JOIN $g g ON g.project_id=p.id AND g.account_id=p.account_id WHERE p.client_id=%d AND p.account_id=%d AND p.payment_status='paid' AND EXISTS (SELECT 1 FROM $ct c WHERE c.project_id=p.id AND c.account_id=p.account_id AND c.status='signed') ORDER BY COALESCE(p.project_date,p.created_at) DESC,g.created_at DESC",$c->id,$c->account_id));$o=[];foreach($rows as $x){$pid=(int)$x->project_id;if(!isset($o[$pid]))$o[$pid]=['project'=>$x,'galleries'=>[]];if($x->gallery_id){if(!$x->public_token){$x->public_token=wp_generate_password(32,false,false);$wpdb->update($g,['public_token'=>$x->public_token],['id'=>$x->gallery_id,'account_id'=>$c->account_id]);}$x->public_url=add_query_arg(['fotoportal_gallery'=>1,'token'=>rawurlencode($x->public_token)],home_url('/'));$x->cover_url=$wpdb->get_var($wpdb->prepare("SELECT thumbnail_url FROM $i WHERE gallery_id=%d AND account_id=%d AND thumbnail_url<>'' ORDER BY sort_order,id LIMIT 1",$x->gallery_id,$c->account_id));$o[$pid]['galleries'][]=$x;}}return array_values($o);}
    public static function replace_mail_tokens($t,$v){foreach($v as $k=>$x)$t=str_replace('{'.$k.'}',$x,$t);return $t;}
    public static function hero_defaults(){return ['image_id'=>0,'size'=>'medium','focal_x'=>50,'focal_y'=>50,'overlay_color'=>'#000000','overlay_opacity'=>38];}
    public static function gallery_hero_settings($gallery_id){$g=self::get_gallery((int)$gallery_id);if(!$g)return self::hero_defaults();$x=get_option('9ls1_fotoportal_gallery_hero_'.(int)$g->account_id.'_'.(int)$g->id,[]);return array_merge(self::hero_defaults(),is_array($x)?$x:[]);}
    public static function public_gallery_hero_settings($g){if(!$g)return self::hero_defaults();$x=get_option('9ls1_fotoportal_gallery_hero_'.(int)$g->account_id.'_'.(int)$g->id,[]);return array_merge(self::hero_defaults(),is_array($x)?$x:[]);}
    public static function customer_hero_settings($client_id){$c=self::get_client((int)$client_id);if(!$c)return self::hero_defaults();$x=get_option('9ls1_fotoportal_customer_hero_'.(int)$c->account_id.'_'.(int)$c->id,[]);return array_merge(self::hero_defaults(),is_array($x)?$x:[]);}
    public static function public_customer_hero_settings($c){if(!$c)return self::hero_defaults();$x=get_option('9ls1_fotoportal_customer_hero_'.(int)$c->account_id.'_'.(int)$c->id,[]);return array_merge(self::hero_defaults(),is_array($x)?$x:[]);}
    public static function customer_hero_images($client_id,$limit=120){global $wpdb;$c=self::get_client((int)$client_id);if(!$c)return [];$i=self::table('images');$g=self::table('galleries');return $wpdb->get_results($wpdb->prepare("SELECT i.* FROM $i i INNER JOIN $g g ON g.id=i.gallery_id AND g.account_id=i.account_id WHERE g.client_id=%d AND i.account_id=%d AND (i.preview_url<>'' OR i.thumbnail_url<>'') ORDER BY i.id DESC LIMIT %d",$c->id,$c->account_id,$limit));}
    public static function hero_image_url($settings,$images,$fallback=''){if(!empty($settings['image_id']))foreach($images as $im)if((int)$im->id===(int)$settings['image_id'])return $im->preview_url?:$im->thumbnail_url;return $fallback;}

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


    public static function ensure_gallery_public_token($gallery_id) {
        global $wpdb;
        $gallery = self::get_gallery($gallery_id);
        if (!$gallery) return '';
        if (!empty($gallery->public_token)) return (string)$gallery->public_token;

        $token = wp_generate_password(32, false, false);
        $wpdb->update(self::table('galleries'), ['public_token'=>$token], [
            'id'=>(int)$gallery_id,
            'account_id'=>self::tenant_account_id(),
        ]);
        return $token;
    }

    public static function gallery_public_url($gallery) {
        if (!$gallery) return '';
        $token = !empty($gallery->public_token) ? (string)$gallery->public_token : self::ensure_gallery_public_token((int)$gallery->id);
        return $token ? add_query_arg([
            'fotoportal_gallery' => 1,
            'token' => rawurlencode($token),
        ], home_url('/')) : '';
    }

    public static function get_public_gallery_by_token($token) {
        global $wpdb;
        $token = sanitize_text_field((string)$token);
        if (!$token) return null;
        $galleries = self::table('galleries');
        $projects = self::table('projects');
        $clients = self::table('clients');
        return $wpdb->get_row($wpdb->prepare("
            SELECT g.*, p.project_name, p.project_number, c.client_name
            FROM $galleries g
            LEFT JOIN $projects p ON p.id=g.project_id AND p.account_id=g.account_id
            LEFT JOIN $clients c ON c.id=g.client_id AND c.account_id=g.account_id
            WHERE g.public_token=%s
            LIMIT 1
        ", $token));
    }

    public static function get_public_gallery_images($gallery) {
        global $wpdb;
        if (!$gallery || empty($gallery->id) || empty($gallery->account_id)) return [];
        $images = self::table('images');
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $images
            WHERE gallery_id=%d AND account_id=%d
            ORDER BY sort_order ASC, id ASC
        ", (int)$gallery->id, (int)$gallery->account_id));
    }

    public static function gallery_interaction_counts($gallery_id,$account_id=0){global $wpdb;$gallery_id=(int)$gallery_id;$account_id=(int)($account_id?:self::tenant_account_id());$f=self::table('favorites');$c=self::table('image_comments');$i=self::table('images');return ['favorites'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT image_id) FROM $f WHERE gallery_id=%d",$gallery_id)),'approved'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $i WHERE gallery_id=%d AND account_id=%d AND is_selected=1",$gallery_id,$account_id)),'comments'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $c WHERE gallery_id=%d",$gallery_id))];}

    public static function selection_status_label($status){
        $map=['open'=>'Ingen forespørsel','submitted'=>'Redigeringsønske','processing'=>'Under behandling','ready'=>'Ferdig behandlet'];
        return $map[sanitize_key((string)$status)]??'Pågår';
    }
    public static function public_selection_status_badge($gallery){
        $status=sanitize_key((string)($gallery->selection_status??'open'));
        if($status==='open') return '';
        return '<div style="margin-top:10px"><span style="display:inline-flex;align-items:center;gap:5px;border:1px solid #e6dff0;background:#f8f5fb;color:#5c4b68;border-radius:999px;padding:5px 8px;font-size:11px;font-weight:700">'.esc_html(self::selection_status_label($status)).'</span></div>';
    }
    public static function public_selection_submit_panel($gallery,$state){
        $status=sanitize_key((string)($gallery->selection_status??'open'));
        $selected=(int)($state['counts']['approved']??0);
        $submitted=in_array($status,['submitted','processing','ready'],true);
        $copy=$submitted ? ('Status: '.self::selection_status_label($status).'. Du kan fortsatt favorisere og velge bilder til egen nedlasting.') : 'Velg bilder og legg gjerne inn kommentarer dersom du ønsker at fotografen skal arbeide videre med noen av dem.';
        $button=$submitted ? 'Send oppdatert redigeringsønske' : 'Send redigeringsønske';
        return '<section data-selection-submit-panel class="selection-submit'.($submitted?' is-submitted':'').'" style="margin:0 0 24px;background:#fff;border:1px solid #e8e2eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap"><div><strong style="display:block;margin-bottom:4px">Videre behandling</strong><span data-selection-submit-copy style="color:#746a7a;font-size:13px">'.esc_html($copy).'</span></div><div style="display:flex;align-items:center;gap:10px"><span style="font-size:12px;color:#746a7a">✓ <b data-count="approved">'.$selected.'</b> valgt</span><button type="button" data-submit-selection style="border:0;border-radius:10px;padding:10px 14px;background:var(--a);color:#fff;font-weight:700;cursor:pointer">'.esc_html($button).'</button></div></section>';
    }

    public static function photographer_selection_items($account_id = 0) {
        global $wpdb;
        $account_id = (int)($account_id ?: self::tenant_account_id());
        if (!$account_id) return [];
        $images = self::table('images');
        $galleries = self::table('galleries');
        $projects = self::table('projects');
        $clients = self::table('clients');
        $favorites = self::table('favorites');
        $comments = self::table('image_comments');
        return $wpdb->get_results($wpdb->prepare("\n            SELECT i.id image_id, i.gallery_id, i.project_id, i.original_filename, i.preview_url, i.thumbnail_url, i.is_selected,\n                   g.gallery_title, g.selection_status, g.selection_submitted_at, g.selection_processing_at, g.selection_ready_at, p.project_name, c.id client_id, c.client_name,\n                   CASE WHEN EXISTS(SELECT 1 FROM $favorites f WHERE f.image_id=i.id AND f.gallery_id=i.gallery_id) THEN 1 ELSE 0 END is_favorite,\n                   (SELECT COUNT(*) FROM $comments cm WHERE cm.image_id=i.id AND cm.gallery_id=i.gallery_id) comment_count,\n                   (SELECT cm2.comment_text FROM $comments cm2 WHERE cm2.image_id=i.id AND cm2.gallery_id=i.gallery_id ORDER BY cm2.created_at DESC, cm2.id DESC LIMIT 1) latest_comment,\n                   (SELECT cm3.created_at FROM $comments cm3 WHERE cm3.image_id=i.id AND cm3.gallery_id=i.gallery_id ORDER BY cm3.created_at DESC, cm3.id DESC LIMIT 1) latest_comment_at\n            FROM $images i\n            INNER JOIN $galleries g ON g.id=i.gallery_id AND g.account_id=i.account_id\n            INNER JOIN $projects p ON p.id=i.project_id AND p.account_id=i.account_id\n            LEFT JOIN $clients c ON c.id=g.client_id AND c.account_id=i.account_id\n            WHERE i.account_id=%d AND (i.is_selected=1 OR EXISTS(SELECT 1 FROM $favorites f2 WHERE f2.image_id=i.id AND f2.gallery_id=i.gallery_id) OR EXISTS(SELECT 1 FROM $comments cm4 WHERE cm4.image_id=i.id AND cm4.gallery_id=i.gallery_id))\n            ORDER BY COALESCE((SELECT MAX(cm5.created_at) FROM $comments cm5 WHERE cm5.image_id=i.id AND cm5.gallery_id=i.gallery_id), i.updated_at, i.created_at) DESC, i.id DESC\n        ", $account_id));
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
        $portal_settings = self::photographer_portal_settings((int)$gallery->account_id);
        if (!empty($portal_settings['watermark_url'])) {
            $settings['watermark_type'] = 'logo';
            $settings['watermark_logo_url'] = $portal_settings['watermark_url'];
            $settings['watermark_position'] = $portal_settings['watermark_position'] ?: 'bottom_right';
            $settings['watermark_opacity'] = (int)$portal_settings['watermark_opacity'];
            $settings['watermark_size'] = (int)$portal_settings['watermark_size'];
        }

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
            $x = $margin; $y = $h - $target_h - $margin;
        } elseif ($position === 'bottom_center') {
            $x = (int)(($w - $target_w) / 2); $y = $h - $target_h - $margin;
        } elseif ($position === 'top_left') {
            $x = $margin; $y = $margin;
        } elseif ($position === 'top_center') {
            $x = (int)(($w - $target_w) / 2); $y = $margin;
        } elseif ($position === 'top_right') {
            $x = $w - $target_w - $margin; $y = $margin;
        } elseif ($position === 'center' || $position === 'center_large') {
            $x = (int)(($w - $target_w) / 2); $y = (int)(($h - $target_h) / 2);
        } else {
            $x = $w - $target_w - $margin; $y = $h - $target_h - $margin;
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
                $x = $margin; $y = $h - $target_h - $margin;
            } elseif ($position === 'bottom_center') {
                $x = (int)(($w - $target_w) / 2); $y = $h - $target_h - $margin;
            } elseif ($position === 'top_left') {
                $x = $margin; $y = $margin;
            } elseif ($position === 'top_center') {
                $x = (int)(($w - $target_w) / 2); $y = $margin;
            } elseif ($position === 'top_right') {
                $x = $w - $target_w - $margin; $y = $margin;
            } elseif ($position === 'center' || $position === 'center_large') {
                $x = (int)(($w - $target_w) / 2); $y = (int)(($h - $target_h) / 2);
            } else {
                $x = $w - $target_w - $margin; $y = $h - $target_h - $margin;
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
        $workspace = !empty($_POST['aurora_workspace']);

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
            wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
                ? NLS1_Photographer_Workspace::url('galleries', ['project_id'=>(int)$project->id,'message'=>'proof_pdf_generated'])
                : self::project_url((int)$project->id) . '&message=proof_pdf_generated');
        } else {
            wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
                ? NLS1_Photographer_Workspace::url('galleries', ['project_id'=>(int)$project->id,'message'=>'proof_pdf_failed'])
                : self::project_url((int)$project->id) . '&message=proof_pdf_failed');
        }
        exit;
    }

    public function handle_regenerate_gallery() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_regenerate_gallery');
        $workspace = !empty($_POST['aurora_workspace']);

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

        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('galleries', ['project_id'=>$project_id,'message'=>'gallery_regenerated'])
            : self::project_url($project_id) . '&message=gallery_regenerated');
        exit;
    }

    public function handle_upload_gallery_zip() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_upload_gallery_zip');
        $workspace = !empty($_POST['aurora_workspace']);

        global $wpdb;

        $project_id = (int)($_POST['project_id'] ?? 0);
        $project = self::get_project($project_id);
        if (!$project) {
            wp_safe_redirect(self::fotoportal_url('galleries'));
            exit;
        }

        // Aurora workflow gate: gallery production starts after a signed contract.
        if (!self::has_signed_contract($project_id)) {
            wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
                ? NLS1_Photographer_Workspace::url('galleries', ['project_id'=>$project_id,'message'=>'gallery_contract_required'])
                : add_query_arg(['project_step'=>'gallery','message'=>'gallery_contract_required'], self::project_url($project_id)));
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
        self::maybe_release_customer_portal($project_id);
        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('galleries', ['project_id'=>$project_id,'message'=>'gallery_uploaded'])
            : self::project_url($project_id) . '&message=gallery_uploaded');
        exit;
    }


    public function handle_add_gallery_images() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_add_gallery_images');
        $workspace = !empty($_POST['aurora_workspace']);
        global $wpdb;

        $gallery_id = (int)($_POST['gallery_id'] ?? 0);
        $gallery = self::get_gallery($gallery_id);
        if (!$gallery) { wp_safe_redirect(self::fotoportal_url('galleries')); exit; }

        $project_id = (int)$gallery->project_id;
        $project = self::get_project($project_id);
        if (!$project || !self::has_signed_contract($project_id)) {
            wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
                ? NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id,'message'=>'gallery_contract_required'])
                : self::project_url($project_id).'&message=gallery_contract_required');
            exit;
        }

        $original_dir=trailingslashit($gallery->base_dir).'original/';
        $zip_dir=trailingslashit($gallery->base_dir).'zip/';
        wp_mkdir_p($original_dir); wp_mkdir_p($zip_dir);
        $registered=[];

        $unique_target=static function($filename) use($original_dir){
            $basename=sanitize_file_name(basename((string)$filename));
            if(!$basename) return [null,null];
            $name=pathinfo($basename,PATHINFO_FILENAME); $ext=pathinfo($basename,PATHINFO_EXTENSION);
            $candidate=$basename; $counter=1;
            while(file_exists($original_dir.$candidate)){
                $candidate=sanitize_file_name($name.'-'.$counter.($ext?'.'.$ext:''));
                $counter++;
            }
            return [$candidate,$original_dir.$candidate];
        };

        if(!empty($_FILES['gallery_zip']['name']) && is_uploaded_file($_FILES['gallery_zip']['tmp_name'])){
            $zf=$_FILES['gallery_zip'];
            if(strtolower(pathinfo($zf['name'],PATHINFO_EXTENSION))==='zip'){
                $zip_name=sanitize_file_name(date('Ymd-His').'-'.$zf['name']);
                $zip_path=$zip_dir.$zip_name;
                if(move_uploaded_file($zf['tmp_name'],$zip_path) && class_exists('ZipArchive')){
                    $zip=new ZipArchive();
                    if($zip->open($zip_path)===true){
                        for($i=0;$i<$zip->numFiles;$i++){
                            $entry=$zip->getNameIndex($i);
                            if(!$entry || substr($entry,-1)==='/' || !self::is_allowed_image_file($entry)) continue;
                            $norm=str_replace('\\','/',strtolower($entry));
                            if(strpos($norm,'__macosx/')!==false || strpos($norm,'preview/')!==false || strpos($norm,'thumbnails/')!==false || strpos($norm,'export/')!==false) continue;
                            [$filename,$target]=$unique_target($entry); if(!$target) continue;
                            $stream=$zip->getStream($entry); if(!$stream) continue;
                            $out=fopen($target,'w'); if(!$out){fclose($stream);continue;}
                            while(!feof($stream)) fwrite($out,fread($stream,8192));
                            fclose($out); fclose($stream);
                            if(file_exists($target)) $registered[]=['filename'=>$filename,'path'=>$target,'url'=>trailingslashit($gallery->base_url).'original/'.rawurlencode($filename),'ext'=>strtolower(pathinfo($filename,PATHINFO_EXTENSION)),'size'=>filesize($target)];
                        }
                        $zip->close();
                    }
                }
            }
        }

        if(!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])){
            foreach($_FILES['gallery_images']['name'] as $i=>$original_name){
                if(!$original_name || !self::is_allowed_image_file($original_name)) continue;
                $tmp=$_FILES['gallery_images']['tmp_name'][$i]??'';
                $error=(int)($_FILES['gallery_images']['error'][$i]??UPLOAD_ERR_NO_FILE);
                if($error!==UPLOAD_ERR_OK || !$tmp || !is_uploaded_file($tmp)) continue;
                [$filename,$target]=$unique_target($original_name);
                if(!$target || !move_uploaded_file($tmp,$target)) continue;
                $registered[]=['filename'=>$filename,'path'=>$target,'url'=>trailingslashit($gallery->base_url).'original/'.rawurlencode($filename),'ext'=>strtolower(pathinfo($filename,PATHINFO_EXTENSION)),'size'=>filesize($target)];
            }
        }

        if(!$registered){
            wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
                ? NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id,'add_images'=>$gallery_id,'message'=>'gallery_images_missing'])
                : self::project_url($project_id).'&message=gallery_images_missing');
            exit;
        }

        $images=self::table('images');
        $sort=(int)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(MAX(sort_order),-1)+1 FROM $images WHERE gallery_id=%d AND account_id=%d",$gallery_id,self::tenant_account_id()));
        foreach($registered as $img){
            $wpdb->insert($images,['account_id'=>self::tenant_account_id(),'gallery_id'=>$gallery_id,'project_id'=>$project_id,
                'original_filename'=>$img['filename'],'original_path'=>$img['path'],'original_url'=>$img['url'],'file_ext'=>$img['ext'],
                'file_size'=>(int)$img['size'],'sort_order'=>$sort++,'status'=>'original_uploaded','is_test'=>(int)$project->is_test,'created_at'=>current_time('mysql')]);
        }
        $total=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $images WHERE gallery_id=%d AND account_id=%d",$gallery_id,self::tenant_account_id()));
        $wpdb->update(self::table('galleries'),['original_count'=>$total,'status'=>'uploaded','updated_at'=>current_time('mysql')],['id'=>$gallery_id,'account_id'=>self::tenant_account_id()]);
        $derivatives=self::generate_gallery_derivatives($gallery_id);
        $this->log((int)$project->client_id,$project_id,'gallery','La til '.count($registered).' bilder i '.$gallery->gallery_title.'. Totalt: '.$total.' bilder. Preview: '.$derivatives['preview'].', thumbnails: '.$derivatives['thumbs'].'.',(int)$project->is_test);

        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id,'message'=>'gallery_images_added'])
            : self::project_url($project_id).'&message=gallery_images_added');
        exit;
    }

    public function handle_delete_gallery() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_gallery');
        $workspace = !empty($_POST['aurora_workspace']);

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

        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('galleries', ['project_id'=>$project_id,'message'=>'gallery_deleted'])
            : self::project_url($project_id) . '&message=gallery_deleted');
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

    public static function get_documents($project_id = 0, $include_test = true, $search = '', $type = '', $sort = 'created', $order = 'desc') {
        global $wpdb;
        $documents = self::table('documents');
        $projects = self::table('projects');
        $clients = self::table('clients');

        if ($project_id) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $documents WHERE project_id = %d AND account_id = %d ORDER BY created_at DESC", $project_id,self::tenant_account_id()));
        }

        $where = ['d.account_id = %d'];
        $params = [self::tenant_account_id()];
        if (!$include_test) $where[] = 'd.is_test = 0';
        if ($search) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(d.document_title LIKE %s OR d.document_type LIKE %s OR p.project_name LIKE %s OR p.project_number LIKE %s OR c.client_name LIKE %s)';
            $params = array_merge($params, [$like,$like,$like,$like,$like]);
        }
        if ($type) { $where[] = 'd.document_type = %s'; $params[] = $type; }
        $sort_map = ['document'=>'d.document_title','project'=>'p.project_name','customer'=>'c.client_name','type'=>'d.document_type','created'=>'d.created_at'];
        $sort_sql = $sort_map[$sort] ?? $sort_map['created'];
        $order_sql = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        $sql = "SELECT d.*, p.project_name, p.project_number, c.client_name
            FROM $documents d
            LEFT JOIN $projects p ON p.id = d.project_id AND p.account_id = d.account_id
            LEFT JOIN $clients c ON c.id = p.client_id AND c.account_id = d.account_id
            WHERE " . implode(' AND ', $where) . " ORDER BY $sort_sql $order_sql, d.id DESC LIMIT 300";
        return $wpdb->get_results($wpdb->prepare($sql, $params));
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
        $workspace = !empty($_POST['aurora_workspace']);
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
        $attachment_id = 0;
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!empty($_FILES['document_file']['name']) && empty($_FILES['document_file']['error'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attachment_id = media_handle_upload('document_file', 0);
            if (!is_wp_error($attachment_id)) {
                $file_url = (string)wp_get_attachment_url($attachment_id);
                if (!$document_title) $document_title = get_the_title($attachment_id) ?: basename($file_url);
            } else {
                $attachment_id = 0;
            }
        }

        if (!$document_title || !$file_url) {
            wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
                ? NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id,'message'=>'document_missing'])
                : self::project_url($project_id).'&message=document_missing');
            exit;
        }

        $wpdb->insert(self::table('documents'), [
            'account_id'=>self::tenant_account_id(),
            'client_id'=>(int)$project->client_id,
            'project_id'=>$project_id,
            'attachment_id'=>$attachment_id ?: null,
            'document_title'=>$document_title,
            'document_type'=>$document_type,
            'file_url'=>$file_url,
            'notes'=>$notes,
            'status'=>'active',
            'is_test'=>(int)$project->is_test,
            'created_at'=>current_time('mysql'),
        ]);

        $this->log((int)$project->client_id,$project_id,'document','Dokument lagt til: '.$document_title,(int)$project->is_test);
        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id,'message'=>'document_added'])
            : self::project_url($project_id).'&message=document_added');
        exit;
    }
    public function handle_delete_document() {
        if (!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_delete_document');
        $workspace = !empty($_POST['aurora_workspace']);
        global $wpdb;

        $document_id = (int)($_POST['document_id'] ?? 0);
        $project_id = (int)($_POST['project_id'] ?? 0);

        if ($document_id) {
            $wpdb->delete(self::table('documents'), ['id' => $document_id, 'account_id' => self::tenant_account_id()], ['%d']);
        }

        wp_safe_redirect(($workspace && class_exists('NLS1_Photographer_Workspace'))
            ? NLS1_Photographer_Workspace::url('documents', ['project_id'=>$project_id,'message'=>'document_deleted'])
            : self::project_url($project_id) . '&message=document_deleted');
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
        $workspace = !empty($_POST['aurora_workspace']);
        $client_id = (int)($_POST['client_id'] ?? 0);
        $client = self::get_client($client_id);
        if (!$client) {
            wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace') ? NLS1_Photographer_Workspace::url('customers') : self::fotoportal_url('clients'));
            exit;
        }

        $client_name=sanitize_text_field($_POST['client_name']??'');
        $client_group=sanitize_text_field($_POST['client_group']??'');
        $client_type=sanitize_key($_POST['client_type']??'private');
        $first_name=sanitize_text_field($_POST['first_name']??'');
        $last_name=sanitize_text_field($_POST['last_name']??'');
        $email=sanitize_email($_POST['email']??'');
        $phone=sanitize_text_field($_POST['phone']??'');
        $address=sanitize_text_field($_POST['address']??'');
        $postal_code=sanitize_text_field($_POST['postal_code']??'');
        $city=sanitize_text_field($_POST['city']??'');
        $organization_number=sanitize_text_field($_POST['organization_number']??'');
        $billing_same=!empty($_POST['billing_same_as_customer'])?1:0;
        $billing_name=sanitize_text_field($_POST['billing_name']??'');
        $billing_address=sanitize_text_field($_POST['billing_address']??'');
        $billing_postal_code=sanitize_text_field($_POST['billing_postal_code']??'');
        $billing_city=sanitize_text_field($_POST['billing_city']??'');

        if ($client_name) {
            $wpdb->update(self::table('clients'), [
                'client_name'=>$client_name,'client_group'=>$client_group,'client_type'=>$client_type,'email'=>$email,'phone'=>$phone,
                'address'=>$address,'postal_code'=>$postal_code,'city'=>$city,'organization_number'=>$organization_number,
                'billing_same_as_customer'=>$billing_same,'billing_name'=>$billing_same?$client_name:$billing_name,
                'billing_address'=>$billing_same?$address:$billing_address,'billing_postal_code'=>$billing_same?$postal_code:$billing_postal_code,
                'billing_city'=>$billing_same?$city:$billing_city,'updated_at'=>current_time('mysql'),
            ], ['id'=>$client_id,'account_id'=>self::tenant_account_id()]);

            $primary=self::get_primary_contact($client_id);
            if($primary){
                $wpdb->update(self::table('contacts'), [
                    'first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,'phone'=>$phone,'updated_at'=>current_time('mysql')
                ], ['id'=>(int)$primary->id,'account_id'=>self::tenant_account_id()]);
            }
            $this->log($client_id,0,'updated','Kundeinformasjon oppdatert.',(int)$client->is_test);
        }

        wp_safe_redirect($workspace && class_exists('NLS1_Photographer_Workspace')
            ? NLS1_Photographer_Workspace::url('customers',['customer_id'=>$client_id,'message'=>'client_updated'])
            : self::client_url($client_id).'&message=client_updated');
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
    public function handle_update_payment_status(){
        if(!current_user_can('manage_options'))wp_die('Mangler tilgang.'); check_admin_referer('9ls1_fotoportal_update_payment_status');
        $pid=absint($_POST['project_id']??0); $p=self::get_project($pid); if(!$p)wp_die('Prosjekt mangler.'); $status=sanitize_key($_POST['payment_status']??'unpaid'); if(!in_array($status,['unpaid','paid'],true))$status='unpaid';
        global $wpdb; $now=current_time('mysql'); $data=['payment_status'=>$status,'payment_marked_at'=>$status==='paid'?$now:null,'updated_at'=>$now]; if($status==='unpaid')$data['portal_released_at']=null; $wpdb->update(self::table('projects'),$data,['id'=>$pid,'account_id'=>self::tenant_account_id()]);
        if($status==='paid')self::maybe_release_customer_portal($pid);
        wp_safe_redirect(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>$pid,'message'=>'payment_updated']));exit;
    }

    public static function photographer_resources($account_id=0){
        $account_id=(int)($account_id?:self::tenant_account_id());
        $items=get_option('9ls1_fotoportal_resources_'.$account_id,[]);
        return is_array($items)?$items:[];
    }

    public function handle_upload_resource(){
        if(!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_upload_resource');
        if(empty($_FILES['resource_file']['name'])) wp_die('Velg en fil.');
        require_once ABSPATH.'wp-admin/includes/file.php';
        $upload=wp_handle_upload($_FILES['resource_file'],['test_form'=>false]);
        if(!empty($upload['error'])) wp_die(esc_html($upload['error']));
        $aid=self::tenant_account_id();
        $items=self::photographer_resources($aid);
        $items[]=['id'=>wp_generate_uuid4(),'title'=>sanitize_text_field($_POST['resource_title']??pathinfo($_FILES['resource_file']['name'],PATHINFO_FILENAME)),'category'=>sanitize_key($_POST['resource_category']??'annet'),'url'=>esc_url_raw($upload['url']??''),'filename'=>sanitize_file_name($_FILES['resource_file']['name']),'created_at'=>current_time('mysql')];
        update_option('9ls1_fotoportal_resources_'.$aid,$items,false);
        wp_safe_redirect(NLS1_Photographer_Workspace::url('resources',['message'=>'resource_uploaded']));
        exit;
    }

    public function handle_save_portal_settings(){
        if(!current_user_can('manage_options') && !current_user_can('aurora_fotoportal_photographer')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_save_portal_settings');
        $a=self::tenant_account_id();
        if(!$a && current_user_can('aurora_fotoportal_photographer')){
            $a=(int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);
        }
        if(!$a) wp_die('Fotografkonto kunne ikke bestemmes.');
        $c=self::photographer_portal_settings($a);
        $up=function($n,$old){if(empty($_FILES[$n]['name']))return $old;require_once ABSPATH.'wp-admin/includes/file.php';$u=wp_handle_upload($_FILES[$n],['test_form'=>false]);return empty($u['error'])?$u['url']:$old;};
        $positions=['top_left','top_center','top_right','center','bottom_left','bottom_center','bottom_right'];
        $pos=sanitize_key($_POST['watermark_position']??$c['watermark_position']); if(!in_array($pos,$positions,true))$pos='bottom_right';
        $v=[
            'studio_name'=>sanitize_text_field($_POST['studio_name']??$c['studio_name']),
            'photographer_name'=>sanitize_text_field($_POST['photographer_name']??$c['photographer_name']),
            'email'=>sanitize_email($_POST['portal_email']??$c['email']),
            'phone'=>sanitize_text_field($_POST['portal_phone']??$c['phone']),
            'website'=>esc_url_raw((function($u){$u=trim((string)$u);if($u!==''&&!preg_match('~^https?://~i',$u))$u='https://'.$u;return $u;})($_POST['portal_website']??$c['website'])),
            'address'=>sanitize_textarea_field($_POST['portal_address']??$c['address']),
            'about'=>sanitize_textarea_field($_POST['portal_about']??$c['about']),
            'logo_url'=>$up('portal_logo',$c['logo_url']),
            'profile_image_url'=>$up('portal_profile_image',$c['profile_image_url']),
            'cover_image_url'=>$up('portal_cover_image',$c['cover_image_url']),
            'watermark_url'=>$up('portal_watermark',$c['watermark_url']),
            'watermark_position'=>$pos,
            'watermark_opacity'=>max(5,min(95,(int)($_POST['watermark_opacity']??$c['watermark_opacity']))),
            'watermark_size'=>max(5,min(70,(int)($_POST['watermark_size']??$c['watermark_size']))),
            'accent_color'=>sanitize_hex_color($_POST['accent_color']??$c['accent_color'])?:'#6f4bf2',
            'email_subject'=>sanitize_text_field($_POST['portal_email_subject']??$c['email_subject']),
            'email_body'=>sanitize_textarea_field($_POST['portal_email_body']??$c['email_body'])
        ];
        update_option('9ls1_fotoportal_portal_settings_'.$a,$v,false);
        wp_safe_redirect(NLS1_Photographer_Workspace::url('settings',['message'=>'portal_settings_saved']));exit;
    }
    private function clean_hero_post(){ $size=sanitize_key($_POST['hero_size']??'medium');if(!in_array($size,['small','medium','large'],true))$size='medium';$color=sanitize_hex_color($_POST['overlay_color']??'#000000')?:'#000000';return ['image_id'=>absint($_POST['hero_image_id']??0),'size'=>$size,'focal_x'=>max(0,min(100,(int)($_POST['focal_x']??50))),'focal_y'=>max(0,min(100,(int)($_POST['focal_y']??50))),'overlay_color'=>$color,'overlay_opacity'=>max(0,min(80,(int)($_POST['overlay_opacity']??38)))];}

    public static function gallery_activity_notifications($account_id=0){
        $account_id=(int)($account_id?:self::tenant_account_id());
        $items=get_option('9ls1_fotoportal_gallery_activity_'.$account_id,[]);
        return is_array($items)?$items:[];
    }
    public static function record_gallery_activity($gallery,$kind){
        if(!$gallery)return; $aid=(int)$gallery->account_id; $items=self::gallery_activity_notifications($aid); $key='gallery_'.(int)$gallery->id;
        $counts=self::gallery_interaction_counts((int)$gallery->id,$aid);
        $items[$key]=['gallery_id'=>(int)$gallery->id,'project_id'=>(int)$gallery->project_id,'client_id'=>(int)$gallery->client_id,'gallery_title'=>(string)$gallery->gallery_title,'counts'=>$counts,'last_kind'=>sanitize_key($kind),'updated_at'=>current_time('mysql'),'unread'=>1];
        uasort($items,function($a,$b){return strcmp($b['updated_at']??'',$a['updated_at']??'');});
        $items=array_slice($items,0,30,true); update_option('9ls1_fotoportal_gallery_activity_'.$aid,$items,false);
    }
    public function handle_mark_gallery_activity_read(){
        if(!current_user_can('manage_options'))wp_die('Mangler tilgang.'); check_admin_referer('9ls1_fotoportal_mark_gallery_activity_read');
        $gid=absint($_POST['gallery_id']??0); $items=self::gallery_activity_notifications(); $key='gallery_'.$gid; if(isset($items[$key])){$items[$key]['unread']=0;update_option('9ls1_fotoportal_gallery_activity_'.self::tenant_account_id(),$items,false);}
        $g=self::get_gallery($gid);
        $kind=''; if(isset($items[$key]['last_kind']))$kind=(string)$items[$key]['last_kind'];
        if($g && $kind==='selection_submitted') $target=NLS1_Photographer_Workspace::url('selections',['gallery_id'=>$gid,'status'=>'submitted']);
        else $target=$g?NLS1_Photographer_Workspace::url('galleries',['project_id'=>$g->project_id,'gallery_id'=>$g->id]):NLS1_Photographer_Workspace::url('galleries');
        wp_safe_redirect($target); exit;
    }
    public function handle_update_selection_status(){
        if(!current_user_can('manage_options')) wp_die('Mangler tilgang.');
        check_admin_referer('9ls1_fotoportal_update_selection_status');
        $gid=absint($_POST['gallery_id']??0); $g=self::get_gallery($gid); if(!$g) wp_die('Galleri mangler.');
        $status=sanitize_key($_POST['selection_status']??'open'); if(!in_array($status,['open','submitted','processing','ready'],true)) $status='open';
        $now=current_time('mysql'); $data=['selection_status'=>$status,'updated_at'=>$now];
        if($status==='submitted' && empty($g->selection_submitted_at)) $data['selection_submitted_at']=$now;
        if($status==='processing') $data['selection_processing_at']=$now;
        if($status==='ready') $data['selection_ready_at']=$now;
        global $wpdb; $wpdb->update(self::table('galleries'),$data,['id'=>$gid,'account_id'=>(int)$g->account_id]);
        wp_safe_redirect(NLS1_Photographer_Workspace::url('selections',['gallery_id'=>$gid,'message'=>'selection_status_updated'])); exit;
    }

    public function handle_update_gallery_details(){
        if(!current_user_can('manage_options'))wp_die('Mangler tilgang.'); check_admin_referer('9ls1_fotoportal_update_gallery_details');
        $gid=absint($_POST['gallery_id']??0); $g=self::get_gallery($gid); if(!$g)wp_die('Galleri mangler.');
        $title=sanitize_text_field($_POST['gallery_title']??''); if($title==='')$title=$g->gallery_title; $description=sanitize_textarea_field($_POST['gallery_description']??'');
        global $wpdb; $wpdb->update(self::table('galleries'),['gallery_title'=>$title,'gallery_description'=>$description,'updated_at'=>current_time('mysql')],['id'=>$gid,'account_id'=>$g->account_id],['%s','%s','%s'],['%d','%d']);
        wp_safe_redirect(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$g->project_id,'gallery_id'=>$gid,'message'=>'gallery_updated'])); exit;
    }
    public function handle_save_gallery_hero(){if(!current_user_can('manage_options'))wp_die('Mangler tilgang.');check_admin_referer('9ls1_fotoportal_save_gallery_hero');$g=self::get_gallery(absint($_POST['gallery_id']??0));if(!$g)wp_die('Galleri mangler.');$x=$this->clean_hero_post();if($x['image_id']){global $wpdb;$ok=$wpdb->get_var($wpdb->prepare("SELECT id FROM ".self::table('images')." WHERE id=%d AND gallery_id=%d AND account_id=%d",$x['image_id'],$g->id,$g->account_id));if(!$ok)$x['image_id']=0;}update_option('9ls1_fotoportal_gallery_hero_'.(int)$g->account_id.'_'.(int)$g->id,$x,false);wp_safe_redirect(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$g->project_id,'gallery_id'=>$g->id,'message'=>'hero_saved']));exit;}

    public function handle_ensure_customer_login(){
        if(!current_user_can('manage_options'))wp_die('Mangler tilgang.'); check_admin_referer('9ls1_fotoportal_ensure_customer_login');
        $client_id=absint($_POST['client_id']??0); $c=self::get_client($client_id); if(!$c)wp_die('Kunde mangler.');
        $uid=self::ensure_client_portal_user($client_id); $args=['customer_id'=>$client_id,'login_status'=>$uid?'ready':'failed'];
        wp_safe_redirect(NLS1_Photographer_Workspace::url('customers',$args)); exit;
    }
    public function handle_save_customer_hero(){if(!current_user_can('manage_options'))wp_die('Mangler tilgang.');check_admin_referer('9ls1_fotoportal_save_customer_hero');$c=self::get_client(absint($_POST['client_id']??0));if(!$c)wp_die('Kunde mangler.');$x=$this->clean_hero_post();if($x['image_id']){global $wpdb;$ok=$wpdb->get_var($wpdb->prepare("SELECT i.id FROM ".self::table('images')." i INNER JOIN ".self::table('galleries')." g ON g.id=i.gallery_id AND g.account_id=i.account_id WHERE i.id=%d AND g.client_id=%d AND i.account_id=%d",$x['image_id'],$c->id,$c->account_id));if(!$ok)$x['image_id']=0;}update_option('9ls1_fotoportal_customer_hero_'.(int)$c->account_id.'_'.(int)$c->id,$x,false);wp_safe_redirect(NLS1_Photographer_Workspace::url('customers',['customer_id'=>$c->id,'hero_saved'=>1]));exit;}
    public function handle_send_customer_portal(){if(!current_user_can('manage_options'))wp_die('Mangler tilgang.');check_admin_referer('9ls1_fotoportal_send_customer_portal');$g=self::get_gallery((int)($_POST['gallery_id']??0));if(!$g)wp_die('Galleri mangler.');$c=self::get_client($g->client_id);$pc=self::get_primary_contact($c->id);$to=sanitize_email($pc&&$pc->email?$pc->email:$c->email);if(!$to)wp_die('Kunden mangler e-post.');$s=self::photographer_portal_settings();$v=['customer_name'=>$c->client_name,'project_name'=>$g->project_name?:'','gallery_name'=>$g->gallery_title,'customer_portal_url'=>self::customer_portal_url($c->id),'gallery_url'=>self::gallery_public_url($g),'photographer_name'=>$s['photographer_name']?:($s['studio_name']?:get_bloginfo('name')),'studio_name'=>$s['studio_name']];$h=['Content-Type: text/plain; charset=UTF-8'];$reply_to=$s['email'];
        if(!$reply_to && class_exists('NLS1_Aurora_Account_Platform')){
            $account=NLS1_Aurora_Account_Platform::get_account((int)$g->account_id);
            if($account) $reply_to=sanitize_email($account->contact_email);
        }
        if($reply_to)$h[]='Reply-To: '.$reply_to;$ok=wp_mail($to,self::replace_mail_tokens($s['email_subject'],$v),self::replace_mail_tokens($s['email_body'],$v),$h);wp_safe_redirect(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$g->project_id,'gallery_id'=>$g->id,'message'=>$ok?'portal_mail_sent':'portal_mail_failed']));exit;}

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
