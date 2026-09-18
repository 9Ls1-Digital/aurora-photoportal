<?php
if (!defined('ABSPATH')) exit;

/**
 * Permanent development/regression photographer account.
 * Destructive actions are hard-gated to accounts flagged is_test_account=1.
 */
class NLS1_Aurora_Test_Photographer {
    const SLUG = 'aurora-test-fotograf';

    public function __construct(){
        add_action('admin_post_aurora_create_test_photographer',[$this,'create']);
        add_action('admin_post_aurora_test_photographer_reset_onboarding',[$this,'reset_onboarding']);
        add_action('admin_post_aurora_test_photographer_reset_demo',[$this,'reset_demo']);
        add_action('admin_post_aurora_test_photographer_reset_all',[$this,'reset_all']);
    }
    public static function get_account(){
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NLS1_Aurora_Account_Platform::table('accounts')." WHERE is_test_account=1 OR account_slug=%s ORDER BY is_test_account DESC,id ASC LIMIT 1",self::SLUG));
    }
    private static function require_admin(){if(!current_user_can('manage_options'))wp_die('Ingen tilgang.');}
    private static function assert_test($id){$a=NLS1_Aurora_Account_Platform::get_account((int)$id);if(!$a||empty($a->is_test_account))wp_die('Sikkerhetssperre: handlingen kan bare brukes på Test-fotograf.');return $a;}
    private static function asset($name){return trailingslashit(NLS1_FOTOPORTAL_PLUGIN_URL).'assets/test-fixtures/'.$name;}
    public static function fixture_settings(){
        $s=NLS1_Fotoportal_Admin::photographer_portal_defaults();
        return array_merge($s,[
            'studio_name'=>'Aurora Test Foto','photographer_name'=>'Test Fotograf','email'=>'testfotograf@example.com','phone'=>'+47 900 00 555','website'=>'https://example.com','address'=>'Testveien 55','address_postal_code'=>'1540','address_city'=>'Vestby','about'=>'Dette er Aurora sin permanente test-fotograf. Profilen brukes til onboarding, Demo Journey og regresjonstesting.',
            'logo_url'=>self::asset('test-logo.png'),'profile_image_url'=>self::asset('test-profile.jpg'),'cover_image_url'=>self::asset('test-hero.jpg'),'watermark_url'=>self::asset('test-watermark.png'),'watermark_position'=>'bottom_right','watermark_opacity'=>35,'watermark_size'=>18,'accent_color'=>'#6f4bf2',
            'email_subject'=>'Dine bilder er klare – {project_name}','email_body'=>"Hei {customer_name},\n\nBildene dine er nå tilgjengelige i kundeportalen.\n\nÅpne kundeportalen her:\n{customer_portal_url}\n\nMed vennlig hilsen\n{photographer_name}"
        ]);
    }
    private static function seed_profile($id){update_option('9ls1_fotoportal_portal_settings_'.(int)$id,self::fixture_settings(),false);}
    private static function ensure_owner($id){
        $a=NLS1_Aurora_Account_Platform::get_account($id); if(!$a)return 0;
        $uid=(int)$a->owner_user_id; $u=$uid?get_user_by('id',$uid):false;
        if(!$u){
            $login='aurora_test_fotograf'; $existing=get_user_by('login',$login); if(!$existing)$existing=get_user_by('email','testfotograf@example.com');
            if($existing)$uid=(int)$existing->ID; else {$uid=wp_create_user($login,wp_generate_password(32,true,true),'testfotograf@example.com'); if(is_wp_error($uid))$uid=0;}
            if($uid){global $wpdb;$wpdb->update(NLS1_Aurora_Account_Platform::table('accounts'),['owner_user_id'=>$uid,'updated_at'=>current_time('mysql')],['id'=>$id]);}
        }
        if($uid){$u=get_user_by('id',$uid);if($u){$u->set_role('aurora_photographer');$u->add_cap('aurora_fotoportal_photographer');update_user_meta($uid,'aurora_fotoportal_account_id',$id);update_user_meta($uid,'aurora_fotoportal_role','photographer');}}
        return $uid;
    }
    private static function enter_context($id,$view='dashboard',$args=[]){
        update_user_meta(get_current_user_id(),'aurora_support_account_id',(int)$id);
        global $wpdb; $wpdb->update(NLS1_Aurora_Account_Platform::table('accounts'),['support_access_enabled'=>1,'support_access_granted_at'=>current_time('mysql'),'support_access_granted_by'=>get_current_user_id()],['id'=>(int)$id]);
        update_user_meta(get_current_user_id(),'aurora_support_expires',time()+2*HOUR_IN_SECONDS);
        $url=NLS1_Photographer_Workspace::url($view,$args);wp_safe_redirect($url);exit;
    }
    public function create(){
        self::require_admin();check_admin_referer('aurora_create_test_photographer');
        if($a=self::get_account()){global $wpdb;$wpdb->update(NLS1_Aurora_Account_Platform::table('accounts'),['is_test_account'=>1],['id'=>(int)$a->id]);self::seed_profile($a->id);self::ensure_owner($a->id);NLS1_Aurora_Account_Platform::set_demo_journey_enabled((int)$a->id,true);wp_safe_redirect(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$a->id,'message'=>'test_account_ready']));exit;}
        global $wpdb;$now=current_time('mysql');
        $wpdb->insert(NLS1_Aurora_Account_Platform::table('accounts'),[
            'account_name'=>'Aurora Test Foto','account_slug'=>self::SLUG,'contact_name'=>'Test Fotograf','contact_email'=>'testfotograf@example.com','contact_phone'=>'+47 900 00 555','organization_number'=>'999 999 999','website_url'=>'https://example.com','billing_name'=>'Aurora Test Foto','billing_address'=>'Testveien 55','billing_postcode'=>'1540','billing_city'=>'Vestby','billing_country'=>'Norge','billing_email'=>'testfotograf@example.com','internal_notes'=>'Permanent Aurora testkonto. Skal nullstilles – ikke slettes.','status'=>'active','plan_name'=>'Development Test','onboarding_state'=>'onboarding_pending','onboarding_step'=>1,'onboarding_completed_at'=>null,'is_test_account'=>1,'created_at'=>$now,'updated_at'=>$now
        ]);$id=(int)$wpdb->insert_id;if(!$id)wp_die('Kunne ikke opprette Test-fotograf.');
        foreach(NLS1_Aurora_Account_Platform::module_catalog() as $key=>$meta){$wpdb->replace(NLS1_Aurora_Account_Platform::table('account_modules'),['account_id'=>$id,'module_key'=>$key,'enabled'=>1,'updated_at'=>$now]);}
        $wpdb->replace(NLS1_Aurora_Account_Platform::table('licenses'),['account_id'=>$id,'license_name'=>'Development Test','status'=>'active','max_users'=>1,'storage_gb'=>50,'created_at'=>$now,'updated_at'=>$now]);
        self::seed_profile($id);self::ensure_owner($id);NLS1_Aurora_Account_Platform::set_demo_journey_enabled($id,true);
        wp_safe_redirect(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$id,'message'=>'test_account_created']));exit;
    }
    private static function remove_tree($dir){
        $uploads=wp_upload_dir();$root=wp_normalize_path($uploads['basedir']);$dir=wp_normalize_path((string)$dir);if(!$dir||strpos($dir,$root)!==0||!is_dir($dir))return;
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$p=$f->getPathname();$f->isDir()?@rmdir($p):@unlink($p);}@rmdir($dir);
    }
    private static function cleanup_account_content($id){
        global $wpdb;$id=(int)$id;$account=self::assert_test($id);
        // Preserve the permanent Test-fotograf owner, but remove all generated customer WP users
        // before their tenant rows disappear. This prevents orphan demo users between test runs.
        if(class_exists('NLS1_Aurora_Account_Platform')) NLS1_Aurora_Account_Platform::cleanup_account_wordpress_users($id,(int)$account->owner_user_id,false);
        $g=NLS1_Fotoportal_Admin::table('galleries');$rows=$wpdb->get_results($wpdb->prepare("SELECT base_dir FROM $g WHERE account_id=%d",$id));foreach((array)$rows as $r){if(!empty($r->base_dir))self::remove_tree($r->base_dir);}
        if(class_exists('NLS1_Aurora_Tenant_Context'))foreach(NLS1_Aurora_Tenant_Context::domain_tables() as $key){$t=NLS1_Aurora_Tenant_Context::table($key);if(NLS1_Aurora_Tenant_Context::table_exists($t)&&NLS1_Aurora_Tenant_Context::table_has_account_id($t))$wpdb->delete($t,['account_id'=>$id],['%d']);}
        delete_option('aurora_fotoportal_demo_journey_'.$id);
        delete_option('aurora_fotoportal_demo_pack_account_'.$id);
        delete_option('aurora_fotoportal_demo_content_account_'.$id); // legacy typo from dev.55/dev.56
        delete_option('aurora_fotoportal_demo_deleted_'.$id);
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('9ls1_fotoportal_gallery_hero_'.$id.'_').'%'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('9ls1_fotoportal_customer_hero_'.$id.'_').'%'));
    }
    private static function set_account_state($id,$onboarding){
        global $wpdb;$completed=$onboarding==='completed';$wpdb->update(NLS1_Aurora_Account_Platform::table('accounts'),['status'=>'active','plan_name'=>'Development Test','onboarding_state'=>$completed?'completed':'onboarding_pending','onboarding_step'=>$completed?6:1,'onboarding_completed_at'=>$completed?current_time('mysql'):null,'is_test_account'=>1,'updated_at'=>current_time('mysql')],['id'=>(int)$id]);
        self::seed_profile($id);self::ensure_owner($id);NLS1_Aurora_Account_Platform::set_demo_journey_enabled((int)$id,true);
    }
    public static function prepare_invitation($id){
        self::assert_test($id);
        self::cleanup_account_content($id);
        self::set_account_state($id,'pending');
    }

    public function reset_onboarding(){self::require_admin();check_admin_referer('aurora_test_photographer_reset_onboarding');$id=absint($_POST['account_id']??0);self::assert_test($id);self::cleanup_account_content($id);self::set_account_state($id,'pending');self::enter_context($id,'onboarding',['step'=>1,'test_harness'=>1]);}
    public function reset_demo(){self::require_admin();check_admin_referer('aurora_test_photographer_reset_demo');$id=absint($_POST['account_id']??0);self::assert_test($id);self::cleanup_account_content($id);self::set_account_state($id,'completed');self::enter_context($id,'dashboard',['demo_journey'=>1,'test_harness'=>1]);}
    public function reset_all(){self::require_admin();check_admin_referer('aurora_test_photographer_reset_all');$id=absint($_POST['account_id']??0);self::assert_test($id);self::cleanup_account_content($id);self::set_account_state($id,'pending');wp_safe_redirect(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$id,'message'=>'test_account_reset']));exit;}
}
