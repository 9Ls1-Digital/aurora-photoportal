<?php
if (!defined('ABSPATH')) exit;

/**
 * Aurora Demo Journey Engine.
 * Keeps the guided Trial exercise isolated from the pre-provisioned demo pack.
 */
class NLS1_Aurora_Demo_Journey {
    const VERSION = '1.2';
    const CLIENT_GROUP = 'Aurora Demo Journey';

    public function __construct(){
        add_action('admin_post_aurora_demo_journey_start',[$this,'start']);
        add_action('admin_post_aurora_demo_journey_restart',[$this,'restart']);
        add_action('admin_post_aurora_demo_journey_create_customer',[$this,'create_customer']);
        add_action('admin_post_aurora_demo_journey_create_project',[$this,'create_project']);
        add_action('admin_post_aurora_demo_journey_upload_agreement',[$this,'upload_agreement']);
        add_action('admin_post_aurora_demo_journey_send_contract',[$this,'send_contract']);
        add_action('admin_post_aurora_demo_journey_sign_contract',[$this,'sign_contract']);
        add_action('admin_post_aurora_demo_journey_gallery_email',[$this,'gallery_email']);
        add_action('admin_post_aurora_demo_journey_simulate_selection',[$this,'simulate_selection']);
        add_action('admin_post_aurora_demo_journey_mark_paid',[$this,'mark_paid']);
    }

    public static function current_account_id(){
        $aid=(int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);
        if(!$aid && current_user_can('manage_options') && class_exists('NLS1_Aurora_Account_Platform')){
            $aid=(int)NLS1_Aurora_Account_Platform::support_context_account_id();
        }
        return $aid;
    }
    private static function key($account_id){return 'aurora_fotoportal_demo_journey_'.(int)$account_id;}
    public static function state($account_id=0){
        $account_id=(int)($account_id?:self::current_account_id());
        $s=$account_id?get_option(self::key($account_id),[]):[];
        if(!is_array($s))$s=[];
        // Demo Journey owns its own progress. Demo Content may already be provisioned
        // centrally, but that must never count as the photographer having downloaded the kit.
        $s=array_merge(['version'=>self::VERSION,'started_at'=>'','kit_downloaded_at'=>'','client_id'=>0,'project_id'=>0,'contract_id'=>0,'gallery_id'=>0,'mail_contract_sent'=>0,'contract_signed'=>0,'shoot_intro_seen'=>0,'gallery_mail_sent'=>0,'selection_simulated'=>0,'delivery_rights_tested'=>0,'paid'=>0,'completed_at'=>''], $s);
        return $s;
    }
    private static function save($account_id,$s){$s['version']=self::VERSION;update_option(self::key($account_id),$s,false);}
    public static function mark_kit_downloaded($account_id){$s=self::state($account_id);if(empty($s['started_at']))$s['started_at']=current_time('mysql');if(empty($s['kit_downloaded_at']))$s['kit_downloaded_at']=current_time('mysql');self::save($account_id,$s);}
    public static function active($account_id=0){$s=self::state($account_id);return !empty($s['started_at'])||!empty($s['kit_downloaded_at']);}
    public static function refresh_state($account_id=0){
        $account_id=(int)($account_id?:self::current_account_id());$s=self::state($account_id);
        if(!empty($s['client_id'])&&!NLS1_Fotoportal_Admin::get_client((int)$s['client_id'])){$s['client_id']=0;$s['project_id']=0;$s['contract_id']=0;$s['gallery_id']=0;}
        $p=!empty($s['project_id'])?NLS1_Fotoportal_Admin::get_project((int)$s['project_id']):null;
        if(!$p&& !empty($s['project_id'])){$s['project_id']=0;$s['contract_id']=0;$s['gallery_id']=0;$s['paid']=0;$s['delivery_rights_tested']=0;}
        if($p){
            $flow=NLS1_Fotoportal_Admin::project_delivery_state((int)$p->id);
            $s['paid']=!empty($flow['paid'])?1:0;
            if(!empty($flow['contract_signed']))$s['contract_signed']=1;
            if(empty($s['gallery_id'])&&!empty($flow['gallery'])){global $wpdb;$s['gallery_id']=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM ".NLS1_Fotoportal_Admin::table('galleries')." WHERE project_id=%d AND account_id=%d ORDER BY id DESC LIMIT 1",(int)$p->id,$account_id));}
        }
        if(!empty($s['contract_id'])){global $wpdb;$ct=$wpdb->get_row($wpdb->prepare("SELECT id,status,sent_at,signed_at FROM ".NLS1_Fotoportal_Admin::table('contracts')." WHERE id=%d AND account_id=%d",(int)$s['contract_id'],$account_id));if(!$ct){$s['contract_id']=0;$s['mail_contract_sent']=0;$s['contract_signed']=0;}else{$s['mail_contract_sent']=!empty($ct->sent_at)||in_array($ct->status,['sent','signed'],true)?1:0;$s['contract_signed']=$ct->status==='signed'?1:0;}}
        if(!empty($s['gallery_id'])){global $wpdb;$g=$wpdb->get_row($wpdb->prepare("SELECT id,selection_status FROM ".NLS1_Fotoportal_Admin::table('galleries')." WHERE id=%d AND account_id=%d",(int)$s['gallery_id'],$account_id));if(!$g){$s['gallery_id']=0;$s['gallery_mail_sent']=0;$s['selection_simulated']=0;}elseif(in_array($g->selection_status,['submitted','processing','ready'],true)){$s['selection_simulated']=1;}}
        if(!empty($s['paid'])&&!empty($s['delivery_rights_tested'])&&empty($s['completed_at']))$s['completed_at']=current_time('mysql');
        self::save($account_id,$s);return $s;
    }
    public static function step($account_id=0){
        $s=self::refresh_state($account_id);
        if(empty($s['kit_downloaded_at']))return 'kit';
        if(empty($s['client_id']))return 'customer';
        if(empty($s['project_id']))return 'project';
        if(empty($s['contract_id']))return 'agreement';
        if(empty($s['mail_contract_sent']))return 'send';
        if(empty($s['contract_signed']))return 'sign';
        if(empty($s['shoot_intro_seen']))return 'shoot';
        if(empty($s['gallery_id']))return 'gallery';
        if(empty($s['gallery_mail_sent']))return 'gallery_mail';
        if(empty($s['selection_simulated']))return 'selection';
        if(empty($s['paid'])||empty($s['delivery_rights_tested']))return 'delivery';
        return 'complete';
    }
    private static function require_access(){
        if(!is_user_logged_in()||(!current_user_can('aurora_fotoportal_photographer')&&!current_user_can('manage_options')))wp_die('Ingen tilgang.');
        $aid=self::current_account_id();if(!$aid)wp_die('Fotografkonto mangler.');
        if(class_exists('NLS1_Aurora_Account_Platform') && !NLS1_Aurora_Account_Platform::demo_journey_enabled($aid)) wp_die('Demo Journey er ikke aktivert for denne fotografkontoen.');
        return $aid;
    }
    private static function redirect($step=''){
        $args=['demo_journey'=>1];if($step)$args['demo_step']=$step;
        wp_safe_redirect(NLS1_Photographer_Workspace::url('dashboard',$args));exit;
    }
    public function start(){self::require_access();check_admin_referer('aurora_demo_journey_start');$aid=self::current_account_id();if(class_exists('NLS1_Aurora_Demo_Content'))NLS1_Aurora_Demo_Content::remove_legacy_materialized_pack($aid);$s=self::state($aid);if(empty($s['started_at']))$s['started_at']=current_time('mysql');self::save($aid,$s);self::redirect();}

    private static function remove_tree($dir){if(!$dir||!is_dir($dir))return;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$f->isDir()?@rmdir($f->getPathname()):@unlink($f->getPathname());}@rmdir($dir);}
    private static function cleanup($aid,$s){
        global $wpdb;
        $gid=(int)($s['gallery_id']??0);$pid=(int)($s['project_id']??0);$cid=(int)($s['client_id']??0);
        if($pid&&class_exists('NLS1_Fotoportal_Admin'))NLS1_Fotoportal_Admin::delete_project_completely($pid,true);
        elseif($gid){$g=NLS1_Fotoportal_Admin::get_gallery($gid);if($g&&!empty($g->base_dir))self::remove_tree($g->base_dir);}
        if($cid){$wpdb->delete(NLS1_Fotoportal_Admin::table('contacts'),['client_id'=>$cid,'account_id'=>$aid]);$wpdb->delete(NLS1_Fotoportal_Admin::table('clients'),['id'=>$cid,'account_id'=>$aid]);}
    }
    public function restart(){
        self::require_access();
        check_admin_referer('aurora_demo_journey_restart');
        $aid=self::current_account_id();
        if(class_exists('NLS1_Aurora_Demo_Content')) NLS1_Aurora_Demo_Content::remove_legacy_materialized_pack($aid);
        $s=self::state($aid);
        self::cleanup($aid,$s);
        if(class_exists('NLS1_Aurora_Demo_Content')) NLS1_Aurora_Demo_Content::clear_kit_download_marker($aid);
        // A restart is a real restart: step 1 must be shown again and the kit must be
        // downloaded again before the customer step can become active.
        self::save($aid,['version'=>self::VERSION,'started_at'=>current_time('mysql'),'kit_downloaded_at'=>'']);
        self::redirect('kit');
    }

    public function create_customer(){
        self::require_access();check_admin_referer('aurora_demo_journey_create_customer');$aid=self::current_account_id();$s=self::state($aid);if(!empty($s['client_id']))self::redirect('project');global $wpdb;$now=current_time('mysql');
        $email=sanitize_email($_POST['email']??('demo-kunde-'.$aid.'@example.com'));
        $wpdb->insert(NLS1_Fotoportal_Admin::table('clients'),['account_id'=>$aid,'customer_number'=>'DEMO-J-'.str_pad((string)$aid,4,'0',STR_PAD_LEFT),'client_name'=>sanitize_text_field($_POST['client_name']??'Kari & Ola Demo'),'client_group'=>self::CLIENT_GROUP,'client_type'=>'private','email'=>$email,'phone'=>sanitize_text_field($_POST['phone']??'900 00 000'),'address'=>sanitize_text_field($_POST['address']??'Demoveien 1'),'postal_code'=>sanitize_text_field($_POST['postal_code']??'0001'),'city'=>sanitize_text_field($_POST['city']??'Oslo'),'status'=>'active','is_test'=>1,'notes'=>'Aurora guided demo journey','created_at'=>$now]);
        $cid=(int)$wpdb->insert_id;if(!$cid)wp_die('Kunne ikke opprette demo-kunde.');
        $wpdb->insert(NLS1_Fotoportal_Admin::table('contacts'),['account_id'=>$aid,'client_id'=>$cid,'first_name'=>'Kari','last_name'=>'Demo','email'=>$email,'phone'=>'900 00 000','contact_role'=>'Hovedkontakt','is_primary'=>1,'is_test'=>1,'created_at'=>$now]);
        $s['client_id']=$cid;$s['customer_created_at']=$now;self::save($aid,$s);self::redirect('project');
    }
    public function create_project(){
        self::require_access();check_admin_referer('aurora_demo_journey_create_project');$aid=self::current_account_id();$s=self::state($aid);$cid=(int)$s['client_id'];if(!$cid)self::redirect('customer');if(!empty($s['project_id']))self::redirect('agreement');global $wpdb;$now=current_time('mysql');
        $wpdb->insert(NLS1_Fotoportal_Admin::table('projects'),['account_id'=>$aid,'client_id'=>$cid,'project_number'=>'DEMO-JOURNEY-'.$aid,'project_name'=>sanitize_text_field($_POST['project_name']??'Aurora Demo – Bryllupsoppdrag'),'project_type'=>'Bryllup','project_date'=>sanitize_text_field($_POST['project_date']??date('Y-m-d',strtotime('+30 days'))),'location'=>sanitize_text_field($_POST['location']??'Son'),'description'=>'Guidet Aurora DEMO. Her kan fotografen teste hele kundereisen.','status'=>'created','payment_status'=>'unpaid','is_test'=>1,'created_at'=>$now]);
        $pid=(int)$wpdb->insert_id;if(!$pid)wp_die('Kunne ikke opprette demo-prosjekt.');$s['project_id']=$pid;$s['project_created_at']=$now;self::save($aid,$s);self::redirect('agreement');
    }
    public function upload_agreement(){
        self::require_access();check_admin_referer('aurora_demo_journey_upload_agreement');$aid=self::current_account_id();$s=self::state($aid);$pid=(int)$s['project_id'];if(!$pid)self::redirect('project');if(empty($_FILES['demo_agreement']['name'])||!is_uploaded_file($_FILES['demo_agreement']['tmp_name']))wp_die('Velg DEMO AVTALE-filen fra Demo-kitet.');
        require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
        $att=media_handle_upload('demo_agreement',0);if(is_wp_error($att))wp_die('Kunne ikke laste opp avtalen.');$url=(string)wp_get_attachment_url($att);$project=NLS1_Fotoportal_Admin::get_project($pid);$client=NLS1_Fotoportal_Admin::get_client((int)$s['client_id']);global $wpdb;$now=current_time('mysql');
        $wpdb->insert(NLS1_Fotoportal_Admin::table('contracts'),['account_id'=>$aid,'project_id'=>$pid,'contract_name'=>'DEMO AVTALE – Bryllupsfotografering','contract_version'=>'DEMO 1.0','contract_text'=>NLS1_Fotoportal_Admin::standard_contract_text(),'contract_source'=>'aurora','attachment_id'=>(int)$att,'file_url'=>$url,'notes'=>'Guided Demo Journey','signer_name'=>$client?$client->client_name:'Kari & Ola Demo','signer_email'=>$client?$client->email:'','status'=>'draft','is_test'=>1,'created_at'=>$now]);
        $ct=(int)$wpdb->insert_id;if(!$ct)wp_die('Kunne ikke registrere DEMO-avtalen.');NLS1_Fotoportal_Admin::create_signing_token($ct);$wpdb->update(NLS1_Fotoportal_Admin::table('projects'),['status'=>'contract_created','updated_at'=>$now],['id'=>$pid,'account_id'=>$aid]);$s['contract_id']=$ct;$s['agreement_uploaded_at']=$now;self::save($aid,$s);self::redirect('send');
    }
    public function send_contract(){
        self::require_access();check_admin_referer('aurora_demo_journey_send_contract');$aid=self::current_account_id();$s=self::state($aid);$ct=(int)$s['contract_id'];if(!$ct)self::redirect('agreement');global $wpdb;$now=current_time('mysql');$wpdb->update(NLS1_Fotoportal_Admin::table('contracts'),['status'=>'sent','sent_at'=>$now],['id'=>$ct,'account_id'=>$aid]);$wpdb->update(NLS1_Fotoportal_Admin::table('projects'),['status'=>'contract_sent','updated_at'=>$now],['id'=>(int)$s['project_id'],'account_id'=>$aid]);$s['mail_contract_sent']=1;$s['contract_sent_at']=$now;self::save($aid,$s);self::redirect('sign');
    }
    public function sign_contract(){
        self::require_access();check_admin_referer('aurora_demo_journey_sign_contract');$aid=self::current_account_id();$s=self::state($aid);$ct=(int)$s['contract_id'];global $wpdb;$now=current_time('mysql');$wpdb->update(NLS1_Fotoportal_Admin::table('contracts'),['status'=>'signed','signed_at'=>$now,'signer_name'=>'Kari & Ola Demo'],['id'=>$ct,'account_id'=>$aid]);$wpdb->update(NLS1_Fotoportal_Admin::table('projects'),['status'=>'contract_signed','updated_at'=>$now],['id'=>(int)$s['project_id'],'account_id'=>$aid]);NLS1_Fotoportal_Admin::notify_photographer_contract_signed((int)$s['project_id'],'Kari & Ola Demo','demo.kunde@example.com',$now);$s['contract_signed']=1;$s['contract_signed_at']=$now;$s['shoot_intro_seen']=0;self::save($aid,$s);self::redirect('shoot');
    }
    public static function mark_shoot_seen($account_id){$s=self::state($account_id);$s['shoot_intro_seen']=1;self::save($account_id,$s);}
    public static function mark_gallery_created($account_id,$gallery_id){$s=self::state($account_id);$s['gallery_id']=(int)$gallery_id;$s['gallery_uploaded_at']=current_time('mysql');$s['shoot_intro_seen']=1;self::save($account_id,$s);}
    public static function note_delivery_saved($project_id){$aid=self::current_account_id();if(!$aid)return;$s=self::state($aid);if((int)($s['project_id']??0)!==(int)$project_id)return;$s['delivery_rights_tested']=1;$s['delivery_rights_tested_at']=current_time('mysql');self::save($aid,$s);}
    public static function note_payment_status($project_id,$status){$aid=self::current_account_id();if(!$aid)return;$s=self::state($aid);if((int)($s['project_id']??0)!==(int)$project_id)return;$s['paid']=$status==='paid'?1:0;if($status!=='paid')$s['completed_at']='';self::save($aid,$s);}
    public static function note_project_deleted($project_id){$aid=self::current_account_id();if(!$aid)return;$s=self::state($aid);if((int)($s['project_id']??0)!==(int)$project_id)return;$keep=['version'=>self::VERSION,'started_at'=>current_time('mysql'),'kit_downloaded_at'=>$s['kit_downloaded_at']?:''];self::save($aid,$keep);}
    public static function is_demo_project($project_id,$account_id=0){$account_id=(int)($account_id?:self::current_account_id());if(!$account_id)return false;$s=self::state($account_id);return !empty($s['project_id'])&&(int)$s['project_id']===(int)$project_id;}
    public function gallery_email(){self::require_access();check_admin_referer('aurora_demo_journey_gallery_email');$aid=self::current_account_id();$s=self::state($aid);$s['gallery_mail_sent']=1;$s['gallery_mail_sent_at']=current_time('mysql');self::save($aid,$s);self::redirect('selection');}
    public function simulate_selection(){
        self::require_access();check_admin_referer('aurora_demo_journey_simulate_selection');$aid=self::current_account_id();$s=self::state($aid);$gid=(int)$s['gallery_id'];if(!$gid)self::redirect('gallery');global $wpdb;$images=NLS1_Fotoportal_Admin::table('images');$favorites=NLS1_Fotoportal_Admin::table('favorites');$gallery=NLS1_Fotoportal_Admin::get_gallery($gid);$ids=$wpdb->get_col($wpdb->prepare("SELECT id FROM $images WHERE gallery_id=%d AND account_id=%d ORDER BY sort_order,id LIMIT 3",$gid,$aid));foreach($ids as $idx=>$id){$wpdb->update($images,['is_selected'=>1],['id'=>(int)$id,'account_id'=>$aid]);if($idx<2){$exists=$wpdb->get_var($wpdb->prepare("SELECT id FROM $favorites WHERE image_id=%d AND gallery_id=%d LIMIT 1",(int)$id,$gid));if(!$exists)$wpdb->insert($favorites,['account_id'=>$aid,'image_id'=>(int)$id,'gallery_id'=>$gid,'project_id'=>(int)$s['project_id'],'client_id'=>(int)$s['client_id'],'user_email'=>'demo-kunde@example.com','is_test'=>1,'created_at'=>current_time('mysql')]);}}
        $wpdb->update(NLS1_Fotoportal_Admin::table('galleries'),['selection_status'=>'submitted','selection_submitted_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')],['id'=>$gid,'account_id'=>$aid]);$s['selection_simulated']=1;$s['selection_simulated_at']=current_time('mysql');self::save($aid,$s);self::redirect('delivery');
    }
    public function mark_paid(){
        self::require_access();check_admin_referer('aurora_demo_journey_mark_paid');$aid=self::current_account_id();$s=self::state($aid);global $wpdb;$now=current_time('mysql');$pid=(int)$s['project_id'];$gid=(int)$s['gallery_id'];$wpdb->update(NLS1_Fotoportal_Admin::table('projects'),['payment_status'=>'paid','payment_marked_at'=>$now,'status'=>'delivery_ready','updated_at'=>$now],['id'=>$pid,'account_id'=>$aid]);if($gid)$wpdb->update(NLS1_Fotoportal_Admin::table('galleries'),['download_enabled'=>1,'updated_at'=>$now],['id'=>$gid,'account_id'=>$aid]);$wpdb->query($wpdb->prepare("UPDATE ".NLS1_Fotoportal_Admin::table('images')." SET is_download_enabled=1 WHERE project_id=%d AND account_id=%d",$pid,$aid));$s['paid']=1;$s['completed_at']=$now;self::save($aid,$s);self::redirect('complete');
    }

    public static function preview_url($client_id){
        $client_id=(int)$client_id;if(!$client_id)return '';
        $base=NLS1_Fotoportal_Admin::customer_portal_url($client_id);
        return add_query_arg(['aurora_demo_preview'=>1,'_aurora_demo_nonce'=>wp_create_nonce('aurora_demo_preview_'.$client_id)],$base);
    }
    public static function is_valid_preview($client){
        if(!$client||empty($_GET['aurora_demo_preview'])||!is_user_logged_in()||!current_user_can('aurora_fotoportal_photographer'))return false;
        if(empty($client->is_test)||!wp_verify_nonce(sanitize_text_field($_GET['_aurora_demo_nonce']??''),'aurora_demo_preview_'.(int)$client->id))return false;
        $aid=(int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);return $aid>0&&$aid===(int)$client->account_id;
    }

    public static function render_dashboard($account_id){
        $s=self::refresh_state($account_id);$step=self::step($account_id);$labels=['kit'=>'Demo-kit','customer'=>'Kunde','project'=>'Prosjekt','agreement'=>'Avtale','send'=>'Send kontrakt','sign'=>'Kundesignering','shoot'=>'Fotooppdrag','gallery'=>'Galleri','gallery_mail'=>'Kundegalleri','selection'=>'Bildevalg','delivery'=>'Leveranse','complete'=>'Ferdig'];$order=array_keys($labels);$idx=array_search($step,$order,true);if($idx===false)$idx=0;$preview=!empty($s['client_id'])?self::preview_url((int)$s['client_id']):'';
        if(!self::active($account_id) && empty($_GET['demo_journey']) && empty($_GET['demo_hide_welcome'])){
            $start=wp_nonce_url(admin_url('admin-post.php?action=aurora_demo_journey_start'),'aurora_demo_journey_start');
            echo '<div class="aurora-demo-engine-modal is-welcome"><div class="aurora-demo-engine-backdrop"></div><div class="aurora-demo-engine-dialog aurora-demo-engine-welcome"><span class="aurora-demo-badge">DEMO</span><h2>Velkommen til Aurora Fotoportal Demo</h2><p>Vi guider deg gjennom et komplett fotooppdrag. Kunde og prosjekt er ferdig utfylt, og du lærer flyten ved å klikke deg gjennom den.</p><div class="aurora-demo-welcome-points"><span>1. Last ned og pakk ut Demo-kitet</span><span>2. Opprett DEMO-kunde og prosjekt</span><span>3. Last opp avtale og simuler e-post/signering</span><span>4. Last opp bilder, se kundevalg og fullfør leveranse</span></div><a class="aurora-primary-action" href="'.esc_url($start).'">Start guidet demo →</a><a class="aurora-secondary-action" href="'.esc_url(NLS1_Photographer_Workspace::url('dashboard',['demo_hide_welcome'=>1])).'">Gjør det senere</a></div></div>';
        }
        echo '<section class="aurora-workspace-card aurora-dashboard-demo-card aurora-demo-engine-card"><div class="aurora-dashboard-demo-head"><div><span class="aurora-workspace-eyebrow">AURORA DEMO JOURNEY</span><h2>Lær Fotoportal ved å gjennomføre et ekte demo-oppdrag</h2><p>Alt er ferdig utfylt. Du gjør bare handlingene som er viktige å lære.</p></div><span class="aurora-demo-badge">DEMO</span></div>';
        echo '<div class="aurora-demo-engine-progress">';$visible=array_values(array_filter($order,fn($k)=>$k!=='shoot'&&$k!=='gallery_mail'));$current_visible=array_search($step,$visible,true);if($step==='shoot')$current_visible=array_search('gallery',$visible,true);if($step==='gallery_mail')$current_visible=array_search('selection',$visible,true);if($current_visible===false)$current_visible=0;foreach($visible as $vi=>$k){$cls=$vi<$current_visible?'is-done':($vi===$current_visible?'is-current':'');echo '<span class="'.$cls.'"><b>'.($vi<$current_visible?'✓':($vi+1)).'</b>'.esc_html($labels[$k]).'</span>';}echo '</div>';
        echo '<div class="aurora-demo-engine-actions">';
        if($step==='complete')echo '<a class="aurora-primary-action" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=aurora_demo_journey_restart'),'aurora_demo_journey_restart')).'">Start demo på nytt</a>';
        else echo '<a class="aurora-primary-action" href="'.esc_url(add_query_arg(['demo_journey'=>1,'demo_step'=>$step],NLS1_Photographer_Workspace::url('dashboard'))).'">'.(self::active($account_id)?'Fortsett demo':'Start demo').'</a>';
        echo '<a class="aurora-secondary-action" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=aurora_demo_journey_restart'),'aurora_demo_journey_restart')).'" onclick="return confirm(\'Starte DEMO på nytt? Bare DEMO-kunde/prosjekt slettes.\')">Start på nytt</a>';
        if($preview)echo '<a class="aurora-secondary-action" href="'.esc_url($preview).'" target="_blank" rel="noopener">Se DEMO kundeportal ↗</a>';
        echo '</div><p class="aurora-demo-engine-status"><strong>Neste:</strong> '.esc_html($labels[$step]??'Demo').'</p></section>';
        if(!empty($_GET['demo_journey']))self::render_modal($account_id,$s,$step);
    }

    private static function modal_form_open($action){echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" enctype="multipart/form-data"><input type="hidden" name="action" value="'.esc_attr($action).'">';wp_nonce_field($action);}
    public static function render_modal($account_id,$s,$step){
        $client=$s['client_id']?NLS1_Fotoportal_Admin::get_client((int)$s['client_id']):null;$project=$s['project_id']?NLS1_Fotoportal_Admin::get_project((int)$s['project_id']):null;$preview=$client?self::preview_url((int)$client->id):'';$poster=NLS1_FOTOPORTAL_PLUGIN_URL.'assets/demo-content/gallery/Bryllups-Poster.png';
        echo '<div class="aurora-demo-engine-modal"><div class="aurora-demo-engine-backdrop"></div><div class="aurora-demo-engine-dialog"><div class="aurora-demo-engine-top"><span class="aurora-demo-badge">GUIDET DEMO</span><a href="'.esc_url(NLS1_Photographer_Workspace::url('dashboard')).'">Lukk ×</a></div>';
        if($step==='kit'){
            echo '<h2>1. Last ned Demo-kitet</h2><p>Demo-kitet inneholder bilder, <strong>DEMO AVTALE</strong> og dokumenter som brukes senere.</p><div class="aurora-demo-tip"><strong>Viktig:</strong> Pakk ut hovedfilen du laster ned. Inne i mappen ligger en egen galleri-ZIP merket <strong>IKKE PAKK UT DENNE</strong>. Den skal lastes opp som ZIP senere.</div><a class="aurora-primary-action" data-demo-kit-download target="_blank" rel="noopener" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=aurora_demo_download_kit'),'aurora_demo_download_kit')).'">Last ned Demo-kit</a><a class="aurora-secondary-action" data-demo-kit-next hidden href="'.esc_url(add_query_arg(['demo_journey'=>1,'demo_step'=>'customer'],NLS1_Photographer_Workspace::url('dashboard'))).'">Neste: registrer DEMO-kunde →</a><p data-demo-kit-note><small>Last ned filen og pakk den ut. Deretter klikker du Neste.</small></p><script>document.addEventListener("click",function(e){var a=e.target.closest("[data-demo-kit-download]");if(!a)return;var n=document.querySelector("[data-demo-kit-next]");var t=document.querySelector("[data-demo-kit-note]");setTimeout(function(){if(n)n.hidden=false;if(t)t.innerHTML="<strong style=\"color:#9ee6ba\">✓ Demo-kitet er lastet ned.</strong> Pakk ut hovedfilen før du går videre – men ikke galleri-ZIP-en merket IKKE PAKK UT DENNE.";},600);});</script>';
        }elseif($step==='customer'){
            echo '<h2>2. Registrer DEMO-kunden</h2><p>Dette er samme kunderegistrering som i ekte bruk, men vi har fylt ut alt for deg.</p>';self::modal_form_open('aurora_demo_journey_create_customer');echo '<div class="aurora-demo-form-grid"><label>Kundenavn<input name="client_name" value="Kari & Ola Demo" readonly></label><label>E-post<input name="email" value="demo.kunde@example.com" readonly></label><label>Telefon<input name="phone" value="900 00 000" readonly></label><label>Adresse<input name="address" value="Demoveien 1" readonly></label><label>Postnr<input name="postal_code" value="0001" readonly></label><label>Sted<input name="city" value="Oslo" readonly></label></div><button class="aurora-primary-action" type="submit">Opprett DEMO-kunde og gå videre →</button></form>';
        }elseif($step==='project'){
            echo '<h2>3. Opprett DEMO-prosjektet</h2><p>Kunden er opprettet. Nå registrerer vi selve fotooppdraget.</p>';self::modal_form_open('aurora_demo_journey_create_project');echo '<div class="aurora-demo-form-grid"><label>Prosjektnavn<input name="project_name" value="Aurora Demo – Bryllupsoppdrag" readonly></label><label>Type<input value="Bryllup" readonly></label><label>Dato<input name="project_date" value="'.esc_attr(date('Y-m-d',strtotime('+30 days'))).'" readonly></label><label>Sted<input name="location" value="Son" readonly></label></div><button class="aurora-primary-action" type="submit">Opprett DEMO-prosjekt →</button></form>';
        }elseif($step==='agreement'){
            echo '<h2>4. Last opp DEMO AVTALE</h2><p>Åpne Demo-kitet du pakket ut og finn filen <strong>02 Kontrakt / DEMO AVTALE – ferdig utfylt.docx</strong>.</p><div class="aurora-demo-tip"><strong>Husket du å pakke ut ZIP-filen?</strong> Hvis ikke, gjør det nå før du går videre.</div>';self::modal_form_open('aurora_demo_journey_upload_agreement');echo '<label class="aurora-demo-file">Velg DEMO AVTALE<input type="file" name="demo_agreement" accept=".doc,.docx,.pdf" required></label><button class="aurora-primary-action" type="submit">Last opp avtale →</button></form>';
        }elseif($step==='send'){
            echo '<h2>5. Send kontrakten</h2><p>Nå simulerer Aurora e-posten kunden ville fått.</p><div class="aurora-demo-mail-preview"><small>FOTOGRAF → KUNDE</small><strong>Kontrakt til signering – Aurora Demo – Bryllupsoppdrag</strong><p>Hei Kari & Ola Demo,<br><br>Avtalen for fotooppdraget er klar. Åpne den sikre lenken og signer digitalt i Aurora Fotoportal.</p></div>';self::modal_form_open('aurora_demo_journey_send_contract');echo '<button class="aurora-primary-action" type="submit">Send simulert e-post →</button></form>';
        }elseif($step==='sign'){
            echo '<h2>6. Kunden signerer</h2><p>Du kan se kundeportalen uten å logge fotografen ut. Aurora beholder fotograf-sessionen og åpner en sikker DEMO-kundekontekst.</p>';if($preview)echo '<a class="aurora-secondary-action" href="'.esc_url($preview).'" target="_blank" rel="noopener">👁 Se DEMO kundeportal</a>';echo '<div class="aurora-demo-mail-preview"><small>KUNDE → FOTOGRAF</small><strong>Kontrakten signeres digitalt</strong><p>I demoen simulerer vi signeringen når du er klar. Samtidig sendes en e-post til fotografen om at avtalen er signert.</p></div>';self::modal_form_open('aurora_demo_journey_sign_contract');echo '<button class="aurora-primary-action" type="submit">Simuler kundesignering →</button></form>';
        }elseif($step==='shoot'){
            echo '<div class="aurora-demo-shoot"><img src="'.esc_url($poster).'" alt="Fotooppdrag"><div><span class="aurora-demo-badge">NESTE DEL</span><h2>Da later vi som du har vært på fotooppdrag 📷</h2><p>Kontrakten er signert. Nå kommer du hjem med bildene og skal laste dem opp til prosjektet.</p><p>Finn ZIP-filen/bildemappen fra Demo-kitet du lastet ned tidligere.</p>';self::modal_form_open('aurora_demo_journey_start');echo '<input type="hidden" name="aurora_demo_shoot_continue" value="1"><button class="aurora-primary-action" type="button" onclick="window.location.href=\''.esc_js(add_query_arg(['demo_journey'=>1,'demo_step'=>'gallery','demo_shoot'=>1],NLS1_Photographer_Workspace::url('dashboard'))).'\'">Neste: last opp bilder →</button></form></div></div>';
            self::mark_shoot_seen($account_id);
        }elseif($step==='gallery'){
            echo '<h2>7. Last opp HIGH QUALITY-bildene fra fotooppdraget</h2><p>Last opp <strong>01 Bilder til galleri / DEMO-BILDER_IKKE PAKK UT DENNE.zip</strong> fra Demo-kitet. ZIP-en representerer fotografens ferdige HQ-originaler. Aurora beholder originalene, lager automatisk preview- og thumbnail-versjoner og legger vannmerke bare på kundevisningen. Ved godkjent levering laster kunden ned HQ-originalene <strong>uten vannmerke</strong>.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'" enctype="multipart/form-data"><input type="hidden" name="action" value="9ls1_fotoportal_upload_gallery_zip"><input type="hidden" name="project_id" value="'.(int)$s['project_id'].'"><input type="hidden" name="aurora_workspace" value="1"><input type="hidden" name="aurora_demo_journey" value="1"><input type="hidden" name="gallery_title" value="DEMO – Kundegalleri"><input type="hidden" name="local_backup_confirmed" value="1"><input type="hidden" name="watermark_enabled" value="1">';wp_nonce_field('9ls1_fotoportal_upload_gallery_zip');echo '<label class="aurora-demo-file">Velg bilde-ZIP<input type="file" name="gallery_zip" accept=".zip" required></label><button class="aurora-primary-action" type="submit">Last opp bilder og opprett galleri →</button></form>';
        }elseif($step==='gallery_mail'){
            echo '<h2>8. Galleriet er klart – kunden får e-post</h2><div class="aurora-demo-mail-preview"><small>AURORA → KUNDE</small><strong>Bildene dine er klare</strong><p>Hei Kari & Ola Demo,<br><br>Galleriet fra fotooppdraget er nå tilgjengelig i Aurora Fotoportal. Åpne kundeportalen for å se bildene, velge favoritter og sende ønsker til fotografen.</p></div>';if($preview)echo '<a class="aurora-secondary-action" href="'.esc_url($preview).'" target="_blank" rel="noopener">Se kundeportalen som kunde ↗</a>';self::modal_form_open('aurora_demo_journey_gallery_email');echo '<button class="aurora-primary-action" type="submit">E-post sendt – gå videre →</button></form>';
        }elseif($step==='selection'){
            echo '<h2>9. Kunden velger bilder</h2><p>Nå simulerer vi at kunden har åpnet galleriet, markert favoritter og valgt bilder til videre behandling.</p><div class="aurora-demo-illustration"><span>♡ Favoritter</span><span>✓ Valgte bilder</span><span>💬 Redigeringsønsker</span></div>';self::modal_form_open('aurora_demo_journey_simulate_selection');echo '<button class="aurora-primary-action" type="submit">Simuler kundens bildevalg →</button></form>';
        }elseif($step==='delivery'){
            $s=self::refresh_state($account_id);$rights=!empty($s['delivery_rights_tested']);$paid=!empty($s['paid']);$delivery_url=NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>(int)$s['project_id'],'demo_return'=>1]);
            echo '<h2>10. Test Leveranse på ekte</h2><p>Nå skal du bruke den vanlige Leveranse-siden – akkurat slik du gjør på et ekte prosjekt.</p><div class="aurora-demo-tip"><strong>Merk:</strong> Statussirklene er ikke avkrysningsbokser. Klikk <strong>Åpne Leveranse</strong>, og bruk feltene lenger ned på Leveranse-siden.<br><br><strong>Gjør disse to handlingene:</strong><br>1. Velg ønsket <strong>Bruksrett</strong> og klikk <strong>Lagre leveransevalg</strong>.<br>2. Klikk <strong>Marker faktura som betalt</strong>.</div><div class="aurora-demo-illustration"><span>'.($rights?'✓':'○').' Bruksrett lagret</span><span>'.($paid?'✓':'○').' Faktura betalt</span></div><p><small>Leveringsbetingelser godkjennes av kunden før HQ-nedlasting. Den trenger derfor ikke være grønn ennå i denne fotograf-delen av demoen.</small></p><a class="aurora-primary-action" href="'.esc_url($delivery_url).'">Åpne Leveranse for DEMO-prosjekt →</a>';
            if($rights&&$paid)echo '<a class="aurora-secondary-action" href="'.esc_url(add_query_arg(['demo_journey'=>1,'demo_step'=>'complete'],NLS1_Photographer_Workspace::url('dashboard'))).'">Fortsett demo →</a>';
        }else{
            echo '<h2>Demo fullført ✓</h2><p>Du har nå gått gjennom hele kundereisen: kunde, prosjekt, kontrakt, signering, galleri, bildevalg, betaling og levering.</p><div class="aurora-demo-mail-preview"><small>AURORA → KUNDE</small><strong>Galleriet er klart for nedlasting</strong><p>Betaling er registrert. Kunden kan nå laste ned ferdige høyoppløselige bilder.</p></div>';if($preview)echo '<a class="aurora-secondary-action" href="'.esc_url($preview).'" target="_blank" rel="noopener">Se ferdig DEMO kundeportal ↗</a>';echo '<a class="aurora-primary-action" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=aurora_demo_journey_restart'),'aurora_demo_journey_restart')).'">Start demo på nytt</a>';
        }
        echo '</div></div>';
    }
}
