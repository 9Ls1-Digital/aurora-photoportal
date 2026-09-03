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
        add_action('admin_post_aurora_save_photographer_onboarding', [$this, 'save_onboarding']);
        add_action('admin_post_aurora_toggle_support_access', [$this, 'handle_toggle_support_access']);
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
            'read',
            self::PAGE_SLUG,
            [$this, 'render']
        );
    }

    public function enqueue_assets($hook) {
        if (strpos((string)$hook, self::PAGE_SLUG) === false) return;
        wp_enqueue_style('9ls1-fotoportal-admin', NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/css/admin.css', [], NLS1_FOTOPORTAL_VERSION);
    }

    public static function url($view = 'dashboard', $args = []) {
        return add_query_arg(array_merge([
            'page' => self::PAGE_SLUG,
            'workspace_view' => sanitize_key($view),
        ], $args), admin_url('admin.php'));
    }

    public function save_onboarding() {
        if (!current_user_can('aurora_fotoportal_photographer') && !current_user_can('manage_options')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_save_photographer_onboarding');
        $account_id=(int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);
        if (!$account_id && current_user_can('manage_options')) $account_id=absint($_POST['account_id']??0);
        $account=NLS1_Aurora_Account_Platform::get_account($account_id);
        if (!$account) wp_die('Fotografkonto finnes ikke.');

        $step=max(1,min(6,absint($_POST['onboarding_step']??1)));
        $settings=NLS1_Fotoportal_Admin::photographer_portal_settings($account_id);
        $upload=function($field,$old){
            if(empty($_FILES[$field]['name'])) return $old;
            require_once ABSPATH.'wp-admin/includes/file.php';
            $u=wp_handle_upload($_FILES[$field],['test_form'=>false]);
            return empty($u['error'])?$u['url']:$old;
        };
        if($step===1){
            $settings['studio_name']=sanitize_text_field($_POST['studio_name']??$settings['studio_name']);
            $settings['photographer_name']=sanitize_text_field($_POST['photographer_name']??$settings['photographer_name']);
            $settings['address']=sanitize_textarea_field($_POST['portal_address']??$settings['address']);
        } elseif($step===2){
            $settings['email']=sanitize_email($_POST['portal_email']??$settings['email']);
            $settings['phone']=sanitize_text_field($_POST['portal_phone']??$settings['phone']);
            $url=trim((string)($_POST['portal_website']??$settings['website']));
            if($url && !preg_match('~^https?://~i',$url)) $url='https://'.$url;
            $settings['website']=esc_url_raw($url);
            $settings['about']=sanitize_textarea_field($_POST['portal_about']??$settings['about']);
        } elseif($step===3){
            $settings['logo_url']=$upload('portal_logo',$settings['logo_url']);
            $settings['profile_image_url']=$upload('portal_profile_image',$settings['profile_image_url']);
            $settings['cover_image_url']=$upload('portal_cover_image',$settings['cover_image_url']);
            $settings['accent_color']=sanitize_hex_color($_POST['accent_color']??$settings['accent_color']) ?: '#6f4bf2';
        } elseif($step===4){
            $settings['watermark_url']=$upload('portal_watermark',$settings['watermark_url']);
            $positions=['top_left','top_center','top_right','center','bottom_left','bottom_center','bottom_right'];
            $pos=sanitize_key($_POST['watermark_position']??$settings['watermark_position']);
            $settings['watermark_position']=in_array($pos,$positions,true)?$pos:'bottom_right';
            $settings['watermark_size']=max(5,min(70,(int)($_POST['watermark_size']??18)));
            $settings['watermark_opacity']=max(5,min(95,(int)($_POST['watermark_opacity']??35)));
        } elseif($step===5){
            $settings['email_subject']=sanitize_text_field($_POST['portal_email_subject']??$settings['email_subject']);
            $settings['email_body']=sanitize_textarea_field($_POST['portal_email_body']??$settings['email_body']);
        }
        update_option('9ls1_fotoportal_portal_settings_'.$account_id,$settings,false);

        global $wpdb;
        $finish=!empty($_POST['finish_onboarding']) && $step===6;
        $wpdb->update(NLS1_Aurora_Account_Platform::table('accounts'),[
            'onboarding_state'=>$finish?'completed':'in_progress',
            'onboarding_step'=>$finish?6:min(6,$step+1),
            'onboarding_completed_at'=>$finish?current_time('mysql'):null,
            'updated_at'=>current_time('mysql')
        ],['id'=>$account_id]);

        wp_safe_redirect(self::url($finish?'dashboard':'onboarding', $finish?['welcome'=>1]:['step'=>min(6,$step+1)]));
        exit;
    }


    public function handle_toggle_support_access() {
        if (!current_user_can('aurora_fotoportal_photographer')) wp_die('Ingen tilgang.');
        check_admin_referer('aurora_toggle_support_access');

        $user_id = get_current_user_id();
        $account_id = (int)get_user_meta($user_id, 'aurora_fotoportal_account_id', true);
        $account = $account_id ? NLS1_Aurora_Account_Platform::get_account($account_id) : null;
        if (!$account || (int)$account->owner_user_id !== $user_id) wp_die('Fotografkonto kunne ikke bekreftes.');

        $enabled = !empty($_POST['support_access_enabled']) ? 1 : 0;
        global $wpdb;
        $wpdb->update(NLS1_Aurora_Account_Platform::table('accounts'), [
            'support_access_enabled' => $enabled,
            'support_access_granted_at' => $enabled ? current_time('mysql') : null,
            'support_access_granted_by' => $enabled ? $user_id : 0,
            'updated_at' => current_time('mysql'),
        ], ['id'=>$account_id]);

        NLS1_Aurora_Account_Platform::log_support_event(
            $account_id,
            $user_id,
            $enabled ? 'granted_by_photographer' : 'revoked_by_photographer',
            null
        );

        wp_safe_redirect(self::url('settings', ['message'=>$enabled?'support_enabled':'support_disabled']));
        exit;
    }

    public function render() {
        if (!current_user_can('manage_options') && !current_user_can('aurora_fotoportal_photographer')) wp_die('Ingen tilgang.');

        $support_account_id = current_user_can('manage_options') ? NLS1_Aurora_Account_Platform::support_context_account_id() : 0;
        $account_id = $support_account_id ?: (int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);
        $account=$account_id ? NLS1_Aurora_Account_Platform::get_account($account_id) : NLS1_Aurora_Account_Platform::default_account();
        if (!$account) wp_die('Ingen fotografkonto er konfigurert.');

        $enabled = NLS1_Aurora_Account_Platform::get_account_modules($account->id);
        $view = sanitize_key($_GET['workspace_view'] ?? 'dashboard');
        if (!current_user_can('manage_options') && ($account->onboarding_state ?? '') !== 'completed') $view='onboarding';

        $allowed = ['dashboard', 'onboarding', 'new', 'settings', 'resources', 'hq_delivery', 'selections'];
        foreach ($this->module_pages as $key => $meta) {
            if (!empty($enabled[$key])) $allowed[] = $key;
        }
        if (!in_array($view, $allowed, true)) $view = 'dashboard';

        $menu = $this->module_pages;
        include NLS1_FOTOPORTAL_PLUGIN_DIR . 'admin/view-photographer-workspace.php';
    }
}
