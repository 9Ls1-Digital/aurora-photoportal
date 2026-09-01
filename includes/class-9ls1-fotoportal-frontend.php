<?php
if (!defined('ABSPATH')) exit;

class NLS1_Fotoportal_Frontend {

    public function __construct() {
        add_action('init', [$this, 'add_rewrite']);
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('template_redirect', [$this, 'render_signing_page']);
        add_action('admin_post_nopriv_9ls1_fotoportal_sign_contract', [$this, 'handle_sign_contract']);
        add_action('admin_post_9ls1_fotoportal_sign_contract', [$this, 'handle_sign_contract']);
    }

    public function add_rewrite() {
        add_rewrite_rule('^fotoportal-signer/?$', 'index.php?fotoportal_signer=1', 'top');
    }

    public function query_vars($vars) {
        $vars[] = 'fotoportal_signer';
        return $vars;
    }

    public function render_signing_page() {
        if (!get_query_var('fotoportal_signer')) {
            return;
        }

        $token = sanitize_text_field($_GET['token'] ?? '');
        $contract = $token ? NLS1_Fotoportal_Admin::get_contract_by_token($token) : null;

        status_header(200);
        nocache_headers();

        echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Signer avtale - 9Ls1 Fotoportal</title>';
        echo '<style>
            body{font-family:Arial,sans-serif;background:#f4f4f5;margin:0;padding:32px;color:#111827}
            .box{max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.12)}
            h1{margin-top:0}.meta{color:#6b7280;margin-bottom:20px}.contract{border:1px solid #e5e7eb;border-radius:12px;padding:20px;background:#fafafa;line-height:1.6}
            .actions{margin-top:24px;padding-top:18px;border-top:1px solid #e5e7eb}.btn{background:#2563eb;color:#fff;border:0;border-radius:8px;padding:12px 18px;font-weight:700;cursor:pointer}
            .error{max-width:700px;margin:60px auto;background:#fff;border-radius:16px;padding:28px}
            label{display:block;margin:12px 0}
            input[type=text],input[type=email]{width:100%;max-width:420px;padding:10px;margin-top:4px}
        </style></head><body>';

        if (!$contract) {
            echo '<div class="error"><h1>Avtalen ble ikke funnet</h1><p>Lenken er ugyldig eller utløpt.</p></div></body></html>';
            exit;
        }

        if ($contract->status === 'signed') {
            echo '<div class="box"><h1>Avtalen er allerede signert</h1><p>Denne avtalen ble signert ' . esc_html($contract->signed_at) . '.</p></div></body></html>';
            exit;
        }

        echo '<div class="box">';
        echo '<h1>' . esc_html($contract->contract_name) . '</h1>';
        echo '<div class="meta">Kontraktversjon: ' . esc_html($contract->contract_version) . '</div>';
        echo '<div class="contract">' . wp_kses_post(wpautop($contract->contract_text)) . '</div>';
        echo '<form class="actions" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="9ls1_fotoportal_sign_contract">';
        echo '<input type="hidden" name="token" value="' . esc_attr($token) . '">';
        wp_nonce_field('9ls1_fotoportal_sign_contract');
        echo '<label>Navn <input type="text" name="signer_name" value="' . esc_attr($contract->signer_name) . '" required></label>';
        echo '<label>E-post <input type="email" name="signer_email" value="' . esc_attr($contract->signer_email) . '" required></label>';
        echo '<label><input type="checkbox" name="accepted" value="1" required> Jeg har lest og godtar avtalen.</label>';
        echo '<button class="btn">Godkjenn og signer avtale</button>';
        echo '</form></div></body></html>';
        exit;
    }

    public function handle_sign_contract() {
        check_admin_referer('9ls1_fotoportal_sign_contract');

        $token = sanitize_text_field($_POST['token'] ?? '');
        $contract = $token ? NLS1_Fotoportal_Admin::get_contract_by_token($token) : null;

        if (!$contract || empty($_POST['accepted'])) {
            wp_die('Ugyldig signering.');
        }

        global $wpdb;

        $signer_name = sanitize_text_field($_POST['signer_name'] ?? '');
        $signer_email = sanitize_email($_POST['signer_email'] ?? '');
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');

        $wpdb->update(NLS1_Fotoportal_Admin::table('contracts'), [
            'signer_name' => $signer_name,
            'signer_email' => $signer_email,
            'status' => 'signed',
            'signed_at' => current_time('mysql'),
            'signed_ip' => $ip,
        ], ['id' => (int)$contract->id]);

        $project = NLS1_Fotoportal_Admin::get_project((int)$contract->project_id);
        if ($project) {
            $wpdb->update(NLS1_Fotoportal_Admin::table('projects'), [
                'status' => 'contract_signed',
                'updated_at' => current_time('mysql')
            ], ['id' => (int)$project->id]);

            $wpdb->insert(NLS1_Fotoportal_Admin::table('signatures'), [
                'contract_id' => (int)$contract->id,
                'project_id' => (int)$project->id,
                'client_id' => (int)$project->client_id,
                'email' => $signer_email,
                'ip_address' => $ip,
                'signed_at' => current_time('mysql'),
                'token_hash' => hash('sha256', $token),
                'status' => 'signed',
                'is_test' => (int)$project->is_test,
            ]);

            $wpdb->insert(NLS1_Fotoportal_Admin::table('logs'), [
                'client_id' => (int)$project->client_id,
                'project_id' => (int)$project->id,
                'log_type' => 'contract',
                'message' => 'Kontrakt signert av ' . $signer_name . ' (' . $signer_email . ').',
                'is_test' => (int)$project->is_test,
                'created_at' => current_time('mysql'),
            ]);
        }

        echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Avtale signert</title><style>body{font-family:Arial,sans-serif;background:#f4f4f5;margin:0;padding:32px}.box{max-width:720px;margin:60px auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.12)}</style></head><body><div class="box"><h1>Avtalen er signert</h1><p>Takk. Avtalen er nå registrert som godkjent.</p></div></body></html>';
        exit;
    }
}
