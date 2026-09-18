<?php
if (!defined('ABSPATH')) exit;

class NLS1_Aurora_Demo_Content {
    const OPTION='aurora_fotoportal_demo_content_v1';
    const VERSION='1.5';
    const DEMO_CLIENT_NAME='Aurora Demo – Bryllupskunde';
    const DEMO_PROJECT_NAME='Aurora Demo – Bryllup';
    const DEMO_GALLERY_TITLE='Demo – Bryllupsgalleri';

    public function __construct(){
        add_action('admin_post_aurora_demo_upload',[$this,'upload']);
        add_action('admin_post_aurora_demo_toggle',[$this,'toggle']);
        add_action('admin_post_aurora_demo_delete',[$this,'delete']);
        add_action('admin_post_aurora_demo_distribute',[$this,'distribute']);
        add_action('admin_post_aurora_demo_remove_resource',[$this,'remove_resource']);
        add_action('admin_post_aurora_demo_download_kit',[$this,'download_kit']);
    }

    public static function defaults(){
        $base=NLS1_FOTOPORTAL_PLUGIN_URL.'assets/demo-content/';
        $items=[];
        $add=function($cat,$title,$file,$type='file')use(&$items,$base){
            $items[]=[
                'id'=>'builtin-'.md5($cat.$file),
                'category'=>$cat,
                'title'=>$title,
                'filename'=>$file,
                'url'=>$base.$cat.'/'.$file,
                'active'=>1,
                'source'=>'aurora',
                'type'=>$type,
                'created_at'=>'2026-09-10 23:20:00'
            ];
        };
        $add('contracts','Fotoavtale – generell (Aurora dynamisk)','01_Fotoavtale_generell_DEMO.docx','dynamic');
        $add('contracts','Bryllupsavtale (Aurora dynamisk)','02_Bryllupsavtale_DEMO.docx','dynamic');
        $add('documents','Samtykke til publisering (Aurora dynamisk)','03_Samtykke_publisering_DEMO.docx','dynamic');
        $add('documents','Foresattsamtykke mindreårig (Aurora dynamisk)','04_Foresattsamtykke_mindrearig_DEMO.docx','dynamic');
        $add('contracts','Bryllupsavtale – utfyllbar mal','Bryllupsavtale_utfyllbar_template.docx','fillable');
        $add('documents','Foresattsamtykke mindreårig – utfyllbar mal','Foresattsamtykke_mindrearig_utfyllbar_template.docx','fillable');
        $add('documents','Samtykke publisering – utfyllbar mal','Samtykke_publisering_utfyllbar_template.docx','fillable');
        foreach(['Bryllup001-Big.png','Bryllup002-Big.png','Bryllup003-Big.png','Bryllup004-Big.png','Bryllup005-Big.png'] as $i=>$f){
            $add('gallery','Demo Bryllup '.($i+1),$f,'image');
        }
        $add('gallery','Demo Bryllup – poster','Bryllups-Poster.png','image');
        return $items;
    }

    public static function items(){
        $saved=get_option(self::OPTION,null);
        $items=is_array($saved)?$saved:self::defaults();
        $changed=self::ensure_master_storage($items);
        if($changed || !is_array($saved)) update_option(self::OPTION,$items,false);
        return $items;
    }

    public static function active($category=''){
        return array_values(array_filter(self::items(),function($i)use($category){
            return !empty($i['active'])&&(!$category||($i['category']??'')===$category);
        }));
    }

    public static function master_root(){
        $upload=wp_upload_dir();
        $basedir=trailingslashit($upload['basedir']).'9ls1-fotoportal/demo-content/';
        $baseurl=trailingslashit($upload['baseurl']).'9ls1-fotoportal/demo-content/';
        return ['basedir'=>$basedir,'baseurl'=>$baseurl];
    }

    private static function master_dirs($category){
        $root=self::master_root();
        $category=sanitize_key($category);
        $base_dir=trailingslashit($root['basedir']).$category.'/';
        $base_url=trailingslashit($root['baseurl']).$category.'/';
        if($category==='gallery'){
            foreach(['','original/','preview/','thumbnails/','zip/','export/'] as $sub) wp_mkdir_p($base_dir.$sub);
        }else wp_mkdir_p($base_dir);
        return ['dir'=>$base_dir,'url'=>$base_url];
    }

    private static function path_from_url($url){
        $upload=wp_upload_dir();
        if($url && strpos((string)$url,(string)$upload['baseurl'])===0){
            return (string)$upload['basedir'].substr((string)$url,strlen((string)$upload['baseurl']));
        }
        return '';
    }

    private static function source_path_for_item($item){
        if(!empty($item['master_original_path']) && file_exists($item['master_original_path'])) return $item['master_original_path'];
        if(!empty($item['path']) && file_exists($item['path'])) return $item['path'];
        if(($item['source']??'')==='aurora'){
            $cat=sanitize_key($item['category']??'');
            $candidate=NLS1_FOTOPORTAL_PLUGIN_DIR.'assets/demo-content/'.$cat.'/'.basename((string)($item['filename']??''));
            if(file_exists($candidate)) return $candidate;
        }
        $mapped=self::path_from_url($item['url']??'');
        return $mapped && file_exists($mapped)?$mapped:'';
    }

    private static function ensure_master_storage(&$items){
        $changed=false;
        foreach($items as &$item){
            $cat=sanitize_key($item['category']??'');
            if(!in_array($cat,['gallery','documents','contracts'],true)) continue;
            $src=self::source_path_for_item($item);
            if(!$src) continue;
            $dirs=self::master_dirs($cat);
            if($cat==='gallery'){
                $filename=sanitize_file_name((string)($item['filename']??basename($src)));
                $target=$dirs['dir'].'original/'.$filename;
                if(!file_exists($target) || @filesize($target)!==@filesize($src)){
                    if(realpath($src)!==realpath($target)) @copy($src,$target);
                }
                if(file_exists($target)){
                    $preview_name='preview_'.pathinfo($filename,PATHINFO_FILENAME).'.jpg';
                    $thumb_name='thumb_'.pathinfo($filename,PATHINFO_FILENAME).'.jpg';
                    $preview=$dirs['dir'].'preview/'.$preview_name;
                    $thumb=$dirs['dir'].'thumbnails/'.$thumb_name;
                    if(class_exists('NLS1_Fotoportal_Admin')){
                        if(!file_exists($preview) || filemtime($preview)<filemtime($target)) NLS1_Fotoportal_Admin::create_resized_image($target,$preview,2000,false,[],false);
                        if(!file_exists($thumb) || filemtime($thumb)<filemtime($target)) NLS1_Fotoportal_Admin::create_resized_image($target,$thumb,400,false,[],false);
                    }
                    $vals=[
                        'master_original_path'=>$target,
                        'master_original_url'=>$dirs['url'].'original/'.rawurlencode($filename),
                        'master_preview_path'=>$preview,
                        'master_preview_url'=>$dirs['url'].'preview/'.rawurlencode($preview_name),
                        'master_thumbnail_path'=>$thumb,
                        'master_thumbnail_url'=>$dirs['url'].'thumbnails/'.rawurlencode($thumb_name),
                        'url'=>$dirs['url'].'original/'.rawurlencode($filename),
                    ];
                    foreach($vals as $k=>$v){if(($item[$k]??'')!==$v){$item[$k]=$v;$changed=true;}}
                }
            }else{
                $filename=sanitize_file_name((string)($item['filename']??basename($src)));
                $target=$dirs['dir'].$filename;
                if(!file_exists($target) || @filesize($target)!==@filesize($src)){
                    if(realpath($src)!==realpath($target)) @copy($src,$target);
                }
                if(file_exists($target)){
                    $url=$dirs['url'].rawurlencode($filename);
                    foreach(['path'=>$target,'url'=>$url] as $k=>$v){if(($item[$k]??'')!==$v){$item[$k]=$v;$changed=true;}}
                }
            }
        }
        unset($item);
        return $changed;
    }

    private static function with_account_context($account_id,$callback){
        $account_id=(int)$account_id;
        $filter=static function($value)use($account_id){return $account_id;};
        add_filter('9ls1_aurora_current_account_id',$filter,9999,1);
        try{return call_user_func($callback);}finally{remove_filter('9ls1_aurora_current_account_id',$filter,9999);}
    }

    private static function ensure_demo_client($account_id,$state,$restore){
        global $wpdb;
        $table=NLS1_Fotoportal_Admin::table('clients');
        $client_id=(int)($state['demo_client_id']??0);
        $exists=$client_id?(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id=%d AND account_id=%d AND is_test=1",$client_id,$account_id)):0;
        if(!$exists){
            if($client_id && !$restore) return 0;
            $found=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE account_id=%d AND is_test=1 AND client_group=%s ORDER BY id LIMIT 1",$account_id,'Aurora Demo'));
            if($found) return $found;
            $wpdb->insert($table,[
                'account_id'=>$account_id,'customer_number'=>'DEMO-'.str_pad((string)$account_id,4,'0',STR_PAD_LEFT),
                'client_name'=>self::DEMO_CLIENT_NAME,'client_group'=>'Aurora Demo','client_type'=>'private',
                'email'=>'aurora-demo-kunde-'.(int)$account_id.'@example.com','phone'=>'','status'=>'active','is_test'=>1,'notes'=>'Aurora Demo Content Pack '.self::VERSION,
                'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')
            ]);
            return (int)$wpdb->insert_id;
        }
        return $exists;
    }

    private static function ensure_demo_project($account_id,$client_id,$state,$restore){
        global $wpdb;
        $table=NLS1_Fotoportal_Admin::table('projects');
        $project_id=(int)($state['demo_project_id']??0);
        $exists=$project_id?(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id=%d AND account_id=%d AND is_test=1",$project_id,$account_id)):0;
        if(!$exists){
            if($project_id && !$restore) return 0;
            $project_number='DEMO-BR-'.$account_id;
            $found=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE account_id=%d AND is_test=1 AND project_number=%s LIMIT 1",$account_id,$project_number));
            if($found) return $found;
            $wpdb->insert($table,[
                'account_id'=>$account_id,'client_id'=>$client_id,'project_number'=>$project_number,
                'project_name'=>self::DEMO_PROJECT_NAME,'project_type'=>'Bryllup','project_date'=>wp_date('Y-m-d'),
                'location'=>'Aurora Demo','description'=>'Eksempelprosjekt levert med Aurora Fotoportal Trial.',
                'status'=>'images_uploaded','payment_status'=>'unpaid','is_test'=>1,
                'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')
            ]);
            return (int)$wpdb->insert_id;
        }
        return $exists;
    }

    private static function ensure_demo_gallery($account_id,$client_id,$project_id,$state,$restore){
        global $wpdb;
        $table=NLS1_Fotoportal_Admin::table('galleries');
        $gallery_id=(int)($state['demo_gallery_id']??0);
        $exists=$gallery_id?(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id=%d AND account_id=%d AND is_test=1",$gallery_id,$account_id)):0;
        if(!$exists){
            if($gallery_id && !$restore) return 0;
            $found=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE account_id=%d AND project_id=%d AND is_test=1 AND gallery_title=%s LIMIT 1",$account_id,$project_id,self::DEMO_GALLERY_TITLE));
            if($found) return $found;
            $roots=NLS1_Fotoportal_Admin::gallery_upload_root();
            $project_folder=NLS1_Fotoportal_Admin::safe_project_folder('DEMO-BR-'.$account_id);
            $gallery_number='gallery-001';
            $base_dir=trailingslashit($roots['basedir']).$project_folder.'/galleries/'.$gallery_number.'/';
            $base_url=trailingslashit($roots['baseurl']).$project_folder.'/galleries/'.$gallery_number.'/';
            foreach(['','zip/','original/','preview/','thumbnails/','export/'] as $sub) wp_mkdir_p($base_dir.$sub);
            $wpdb->insert($table,[
                'account_id'=>$account_id,'client_id'=>$client_id,'project_id'=>$project_id,'gallery_number'=>$gallery_number,
                'gallery_title'=>self::DEMO_GALLERY_TITLE,'gallery_description'=>'Eksempelgalleri fra Aurora Demo Content Pack.',
                'public_token'=>wp_generate_password(32,false,false),'base_dir'=>$base_dir,'base_url'=>$base_url,
                'zip_filename'=>'','original_count'=>0,'preview_count'=>0,'thumbnail_count'=>0,
                'watermark_enabled'=>1,'download_enabled'=>0,'status'=>'uploaded','is_test'=>1,
                'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')
            ]);
            return (int)$wpdb->insert_id;
        }
        return $exists;
    }

    private static function sync_demo_gallery_images($account_id,$project_id,$gallery_id,$state,$restore){
        global $wpdb;
        $gallery_table=NLS1_Fotoportal_Admin::table('galleries');
        $images_table=NLS1_Fotoportal_Admin::table('images');
        $gallery=$wpdb->get_row($wpdb->prepare("SELECT * FROM $gallery_table WHERE id=%d AND account_id=%d",$gallery_id,$account_id));
        if(!$gallery) return ['count'=>0,'map'=>[]];
        $map=is_array($state['gallery_items']??null)?$state['gallery_items']:[];
        $sort=0;$count=0;
        foreach(self::active('gallery') as $item){
            $demo_id=(string)($item['id']??'');
            $src=self::source_path_for_item($item);
            if(!$demo_id||!$src||!file_exists($src)) continue;
            $old_id=(int)($map[$demo_id]['image_id']??0);
            $row=$old_id?$wpdb->get_row($wpdb->prepare("SELECT * FROM $images_table WHERE id=%d AND account_id=%d AND gallery_id=%d",$old_id,$account_id,$gallery_id)):null;
            if(!$row && $old_id && !$restore) continue; // photographer removed it: ordinary push respects deletion
            $base=sanitize_file_name((string)($item['filename']??basename($src)));
            $stable='demo-'.substr(md5($demo_id),0,8).'-'.$base;
            $target=trailingslashit($gallery->base_dir).'original/'.$stable;
            if(!file_exists($target) || @filesize($target)!==@filesize($src)) @copy($src,$target);
            if(!file_exists($target)) continue;
            $url=trailingslashit($gallery->base_url).'original/'.rawurlencode($stable);
            $data=[
                'account_id'=>$account_id,'gallery_id'=>$gallery_id,'project_id'=>$project_id,
                'original_filename'=>$stable,'original_path'=>$target,'original_url'=>$url,
                'file_ext'=>strtolower(pathinfo($stable,PATHINFO_EXTENSION)),'file_size'=>(int)filesize($target),
                'sort_order'=>$sort++,'status'=>'original_uploaded','is_test'=>1,'updated_at'=>current_time('mysql')
            ];
            if($row){
                $wpdb->update($images_table,$data,['id'=>(int)$row->id,'account_id'=>$account_id]);
                $image_id=(int)$row->id;
            }else{
                $data['created_at']=current_time('mysql');
                $wpdb->insert($images_table,$data);
                $image_id=(int)$wpdb->insert_id;
            }
            $map[$demo_id]=['image_id'=>$image_id,'filename'=>$stable,'pack_version'=>self::VERSION];
            $count++;
        }
        $wpdb->update($gallery_table,['original_count'=>$count,'status'=>'uploaded','updated_at'=>current_time('mysql')],['id'=>$gallery_id,'account_id'=>$account_id]);
        self::with_account_context($account_id,function()use($gallery_id){NLS1_Fotoportal_Admin::generate_gallery_derivatives($gallery_id);});
        return ['count'=>$count,'map'=>$map];
    }

    private static function ensure_account_demo_gallery($account_id,$state,$restore=false){
        if(!class_exists('NLS1_Fotoportal_Admin')) return $state;
        return self::with_account_context($account_id,function()use($account_id,$state,$restore){
            $client_id=self::ensure_demo_client($account_id,$state,$restore);
            if(!$client_id){$state['gallery_status']='removed';return $state;}
            $project_id=self::ensure_demo_project($account_id,$client_id,$state,$restore);
            if(!$project_id){$state['gallery_status']='removed';return $state;}
            $gallery_id=self::ensure_demo_gallery($account_id,$client_id,$project_id,$state,$restore);
            if(!$gallery_id){$state['gallery_status']='removed';return $state;}
            $sync=self::sync_demo_gallery_images($account_id,$project_id,$gallery_id,$state,$restore);
            $state['demo_client_id']=$client_id;
            $state['demo_project_id']=$project_id;
            $state['demo_gallery_id']=$gallery_id;
            $state['gallery_items']=$sync['map'];
            $state['gallery_count']=(int)$sync['count'];
            $state['gallery_status']='installed';
            return $state;
        });
    }

    private static function ensure_demo_customer_login($account_id,$client_id,$state){
        global $wpdb;
        $client=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".NLS1_Fotoportal_Admin::table('clients')." WHERE id=%d AND account_id=%d",(int)$client_id,(int)$account_id));
        if(!$client)return $state;
        $username='aurora-demo-kunde-'.(int)$account_id;
        $email='aurora-demo-kunde-'.(int)$account_id.'@example.com';
        $password='AuroraDemo2026!';
        $uid=(int)username_exists($username);
        if(!$uid){$by_email=get_user_by('email',$email);$uid=$by_email?(int)$by_email->ID:0;}
        if(!$uid){$uid=wp_create_user($username,$password,$email);if(is_wp_error($uid))return $state;$uid=(int)$uid;}
        wp_set_password($password,$uid);
        update_user_meta($uid,'aurora_fotoportal_client_id',(int)$client_id);
        update_user_meta($uid,'aurora_fotoportal_account_id',(int)$account_id);
        $wpdb->update(NLS1_Fotoportal_Admin::table('clients'),['email'=>$email,'updated_at'=>current_time('mysql')],['id'=>(int)$client_id,'account_id'=>(int)$account_id]);
        $state['demo_customer_username']=$username;$state['demo_customer_email']=$email;$state['demo_customer_password']=$password;$state['demo_customer_user_id']=$uid;
        return $state;
    }

    private static function ensure_project_demo_files($account_id,$client_id,$project_id,$state,$restore=false){
        global $wpdb;
        $contracts=NLS1_Fotoportal_Admin::table('contracts');$documents=NLS1_Fotoportal_Admin::table('documents');
        $contract_count=0;$document_count=0;
        foreach(self::active() as $item){
            $cat=$item['category']??''; if(!in_array($cat,['contracts','documents'],true))continue;
            $title=(string)($item['title']??$item['filename']??'Aurora Demo');$url=(string)($item['url']??'');
            if($cat==='contracts'){
                $existing=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $contracts WHERE project_id=%d AND is_test=1 AND contract_name=%s LIMIT 1",(int)$project_id,$title));
                if(!$existing){$wpdb->insert($contracts,['project_id'=>(int)$project_id,'contract_name'=>$title,'contract_version'=>self::VERSION,'contract_text'=>'Aurora Demo Content Pack – eksempelavtale. Åpne vedlagt mal for komplett innhold.','contract_source'=>'aurora-demo','file_url'=>$url,'notes'=>'Demo-mal fra Aurora Content Pack','status'=>'draft','is_test'=>1,'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);}
                $contract_count++;
            }else{
                $existing=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $documents WHERE project_id=%d AND is_test=1 AND document_title=%s LIMIT 1",(int)$project_id,$title));
                if(!$existing){$wpdb->insert($documents,['client_id'=>(int)$client_id,'project_id'=>(int)$project_id,'document_title'=>$title,'document_type'=>'Aurora Demo','file_url'=>$url,'notes'=>'Demo-dokument fra Aurora Content Pack','status'=>'active','is_test'=>1,'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);}
                $document_count++;
            }
        }
        $state['demo_contract_count']=$contract_count;$state['demo_document_count']=$document_count;return $state;
    }

    public static function remove_legacy_materialized_pack($account_id){
        $account_id=(int)$account_id;
        if(!$account_id||!class_exists('NLS1_Fotoportal_Admin'))return;
        $state=self::account_state($account_id);
        $pid=(int)($state['demo_project_id']??0);
        $cid=(int)($state['demo_client_id']??0);
        if($pid){
            // Project helpers are tenant-scoped. Switch explicitly to the target
            // account so platform-admin migrations cannot accidentally miss the
            // legacy seeded project (or touch another tenant).
            self::with_account_context($account_id,function()use($pid,$account_id){
                $project=NLS1_Fotoportal_Admin::get_project($pid);
                if($project && (int)$project->account_id===$account_id && !empty($project->is_test)){
                    NLS1_Fotoportal_Admin::delete_project_completely($pid,true);
                }
            });
        }
        if($cid){
            global $wpdb;
            $client=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".NLS1_Fotoportal_Admin::table('clients')." WHERE id=%d AND account_id=%d",$cid,$account_id));
            if($client && !empty($client->is_test)){
                $uid=(int)($state['demo_customer_user_id']??0);
                if($uid){
                    require_once ABSPATH.'wp-admin/includes/user.php';
                    $u=get_user_by('id',$uid);
                    if($u && !user_can($u,'manage_options') && !user_can($u,'manage_woocommerce')) wp_delete_user($uid);
                }
                $wpdb->delete(NLS1_Fotoportal_Admin::table('contacts'),['client_id'=>$cid,'account_id'=>$account_id]);
                $wpdb->delete(NLS1_Fotoportal_Admin::table('clients'),['id'=>$cid,'account_id'=>$account_id]);
            }
        }
        foreach(['demo_project_id','demo_client_id','demo_gallery_id','gallery_items','demo_customer_username','demo_customer_email','demo_customer_password','demo_customer_user_id','demo_contract_count','demo_document_count'] as $k) unset($state[$k]);
        $state['gallery_count']=0;$state['gallery_status']='resources_only';
        update_option('aurora_fotoportal_demo_pack_account_'.$account_id,$state,false);
    }

    public static function assign_to_account($account_id,$restore=false){
        $account_id=(int)$account_id;
        if(!$account_id)return ['added'=>0,'updated'=>0,'gallery'=>0];
        $resources=get_option('9ls1_fotoportal_resources_'.$account_id,[]);
        if(!is_array($resources))$resources=[];
        $by_demo=[];foreach($resources as $k=>$r){if(!empty($r['demo_item_id']))$by_demo[$r['demo_item_id']]=$k;}
        $deleted=get_option('aurora_fotoportal_demo_deleted_'.$account_id,[]);
        if(!is_array($deleted))$deleted=[];
        $added=0;$updated=0;
        foreach(self::active() as $item){
            if(($item['category']??'')==='gallery') continue; // gallery is a real Fotoportal gallery, not merely a resource link
            $id=(string)($item['id']??'');if(!$id)continue;
            if(!$restore && in_array($id,$deleted,true))continue;
            $cat=($item['category']??'documents')==='contracts'?'kontraktmal':'dokumentmal';
            $row=[
                'id'=>'demo-'.$id,'title'=>$item['title']??$item['filename'],'category'=>$cat,'url'=>$item['url'],
                'filename'=>$item['filename'],'created_at'=>current_time('mysql'),'is_demo'=>1,'demo_item_id'=>$id,
                'demo_pack_version'=>self::VERSION,'demo_type'=>$item['type']??'file'
            ];
            if(isset($by_demo[$id])){$resources[$by_demo[$id]]=array_merge($resources[$by_demo[$id]],$row);$updated++;}
            else{$resources[]=$row;$added++;}
        }
        if($restore) update_option('aurora_fotoportal_demo_deleted_'.$account_id,[],false);
        update_option('9ls1_fotoportal_resources_'.$account_id,array_values($resources),false);

        $state=self::account_state($account_id);
        // Central Demo Content Pack now supplies files/resources only. A real demo
        // customer/project/gallery is created exclusively by the optional Journey.
        self::remove_legacy_materialized_pack($account_id);
        $state=self::account_state($account_id);
        $state=array_merge($state,[
            'version'=>self::VERSION,'assigned_at'=>current_time('mysql'),'added'=>$added,'updated'=>$updated,
            'resource_count'=>count(array_filter($resources,fn($r)=>!empty($r['is_demo']))),
            'gallery_count'=>0,'gallery_status'=>'resources_only',
        ]);
        update_option('aurora_fotoportal_demo_pack_account_'.$account_id,$state,false);
        // Provisioning the centrally maintained demo pack is not the same as the
        // photographer downloading the guided Demo-kit. Journey step 1 is completed
        // only by the actual download endpoint.
        return ['added'=>$added,'updated'=>$updated,'gallery'=>(int)($state['gallery_count']??0)];
    }

    public static function account_state($account_id){
        $s=get_option('aurora_fotoportal_demo_pack_account_'.(int)$account_id,[]);
        return is_array($s)?$s:[];
    }

    public static function clear_kit_download_marker($account_id){
        $account_id=(int)$account_id;
        if(!$account_id)return;
        $state=self::account_state($account_id);
        if(array_key_exists('kit_downloaded_at',$state)){
            unset($state['kit_downloaded_at']);
            update_option('aurora_fotoportal_demo_pack_account_'.$account_id,$state,false);
        }
    }

    public function distribute(){
        if(!current_user_can('manage_options'))wp_die('Ingen tilgang.');
        check_admin_referer('aurora_demo_distribute');
        $restore=!empty($_POST['restore']);
        $ids=array_map('absint',(array)($_POST['account_ids']??[]));
        if(!empty($_POST['all_trials'])){
            $ids=array_map(fn($a)=>(int)$a->id,NLS1_Aurora_Account_Platform::get_accounts(['status'=>'trial']));
        }
        $ids=array_values(array_unique(array_filter($ids)));
        $count=0;$images=0;
        foreach($ids as $id){
            $a=NLS1_Aurora_Account_Platform::get_account($id);
            if($a&&$a->status==='trial'){
                $result=self::assign_to_account($id,$restore);$count++;$images+=(int)($result['gallery']??0);
            }
        }
        wp_safe_redirect(add_query_arg([
            'message'=>$restore?'demo_restored':'demo_distributed','count'=>$count,'images'=>$images
        ],NLS1_Aurora_Account_Platform::url('demo')));exit;
    }

    public function remove_resource(){
        if(!current_user_can('manage_options')&&!current_user_can('aurora_fotoportal_photographer'))wp_die('Ingen tilgang.');
        check_admin_referer('aurora_demo_remove_resource');
        $aid=class_exists('NLS1_Fotoportal_Admin')?NLS1_Fotoportal_Admin::tenant_account_id():0;
        if(!$aid&&current_user_can('aurora_fotoportal_photographer'))$aid=(int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);
        if(!$aid)wp_die('Fotografkonto mangler.');
        $rid=sanitize_text_field($_POST['resource_id']??'');
        $resources=get_option('9ls1_fotoportal_resources_'.$aid,[]);
        $deleted=get_option('aurora_fotoportal_demo_deleted_'.$aid,[]);if(!is_array($deleted))$deleted=[];
        foreach($resources as $r)if(($r['id']??'')===$rid&&!empty($r['is_demo'])&&!empty($r['demo_item_id']))$deleted[]=$r['demo_item_id'];
        $resources=array_values(array_filter($resources,fn($r)=>(($r['id']??'')!==$rid)||empty($r['is_demo'])));
        update_option('9ls1_fotoportal_resources_'.$aid,$resources,false);
        update_option('aurora_fotoportal_demo_deleted_'.$aid,array_values(array_unique($deleted)),false);
        wp_safe_redirect(NLS1_Photographer_Workspace::url('resources',['message'=>'demo_resource_removed']));exit;
    }

    public function download_kit(){
        if(!current_user_can('manage_options')&&!current_user_can('aurora_fotoportal_photographer'))wp_die('Ingen tilgang.');
        check_admin_referer('aurora_demo_download_kit');
        $account_id=0;
        if(current_user_can('aurora_fotoportal_photographer'))$account_id=(int)get_user_meta(get_current_user_id(),'aurora_fotoportal_account_id',true);
        if(!$account_id&&current_user_can('manage_options'))$account_id=absint($_GET['account_id']??0);
        if(!$account_id)wp_die('Fotografkonto mangler.');
        $account=NLS1_Aurora_Account_Platform::get_account($account_id);
        if(!$account||(($account->status??'')!=='trial'&&($account->plan_name??'')!=='Trial'))wp_die('Demo-kit er bare tilgjengelig for Trial-kontoer.');
        if(!class_exists('ZipArchive'))wp_die('ZIP-støtte mangler på serveren.');

        $tmp=wp_tempnam('aurora-fotoportal-demo-kit.zip');
        $zip=new ZipArchive();
        if($zip->open($tmp,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true)wp_die('Kunne ikke opprette Demo-kit.');
        $readme="AURORA FOTOPORTAL – DEMO-KIT\r\n\r\n".
            "Pakk ut denne HOVEDFILEN og lagre mappen lokalt. Du skal bruke filene gjennom den guidede demoen.\r\nVIKTIG: Ikke pakk ut galleri-ZIP-en som er tydelig merket IKKE PAKK UT DENNE.\r\n\r\n".
            "1. Registrer en demo-kunde i Aurora Fotoportal.\r\n".
            "2. Opprett et prosjekt og bruk en kontrakt fra mappen 02 Kontrakt.\r\n".
            "3. Simuler utsending og kundesignering i Demo-guiden.\r\n".
            "4. Etter fotooppdraget laster du opp 01 Bilder til galleri/DEMO-BILDER_IKKE PAKK UT DENNE.zip direkte i Galleri-steget. ZIP-en skal inneholde ferdige HIGH QUALITY-originaler. IKKE pakk ut denne indre ZIP-filen.\r\n".
            "Aurora beholder HQ-originalene, lager automatisk preview- og thumbnail-versjoner og legger vannmerke bare på kundevisningen. Når levering frigis, laster kunden ned HQ-originalene uten vannmerke.\r\n".
            "5. Bruk dokumentene i 03 Dokumenter ved behov.\r\n".
            "6. Fullfør kundevalg og levering i Aurora.\r\n";
        $zip->addFromString('README – Start her.txt',$readme);
        $prefilled=NLS1_FOTOPORTAL_PLUGIN_DIR.'assets/demo-content/contracts/DEMO_AVTALE_ferdig_utfylt.docx';
        if(file_exists($prefilled))$zip->addFile($prefilled,'02 Kontrakt/DEMO AVTALE – ferdig utfylt.docx');
        $gallery_items=self::active('gallery');
        $gallery_zip_tmp=wp_tempnam('aurora-demo-bilder.zip');
        $gallery_zip=new ZipArchive();
        if($gallery_zip->open($gallery_zip_tmp,ZipArchive::CREATE|ZipArchive::OVERWRITE)===true){
            foreach($gallery_items as $item){$src=self::source_path_for_item($item);if($src&&file_exists($src))$gallery_zip->addFile($src,basename((string)($item['filename']??$src)));}
            $gallery_zip->close();
            if(file_exists($gallery_zip_tmp))$zip->addFile($gallery_zip_tmp,'01 Bilder til galleri/DEMO-BILDER_IKKE PAKK UT DENNE.zip');
        }
        foreach($gallery_items as $item){
            $src=self::source_path_for_item($item);
            if($src&&file_exists($src))$zip->addFile($src,'01 Bilder til galleri/Originaler/'.basename((string)($item['filename']??$src)));
        }
        foreach(self::active('contracts') as $item){
            $src=self::source_path_for_item($item);
            if($src&&file_exists($src))$zip->addFile($src,'02 Kontrakt/'.basename((string)($item['filename']??$src)));
        }
        foreach(self::active('documents') as $item){
            $src=self::source_path_for_item($item);
            if($src&&file_exists($src))$zip->addFile($src,'03 Dokumenter/'.basename((string)($item['filename']??$src)));
        }
        $zip->close();
        if(!empty($gallery_zip_tmp)&&file_exists($gallery_zip_tmp))@unlink($gallery_zip_tmp);
        $state=self::account_state($account_id);
        if(empty($state['kit_downloaded_at'])) $state['kit_downloaded_at']=current_time('mysql');
        $state['version']=self::VERSION;
        update_option('aurora_fotoportal_demo_pack_account_'.$account_id,$state,false);
        if(class_exists('NLS1_Aurora_Demo_Journey'))NLS1_Aurora_Demo_Journey::mark_kit_downloaded($account_id);

        while(ob_get_level())ob_end_clean();
        nocache_headers();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="Aurora-Fotoportal-Demo-kit-v'.self::VERSION.'.zip"');
        header('Content-Length: '.filesize($tmp));
        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    public function upload(){
        if(!current_user_can('manage_options'))wp_die('Ingen tilgang.');
        check_admin_referer('aurora_demo_upload');
        $cat=sanitize_key($_POST['demo_category']??'documents');
        if(!in_array($cat,['gallery','documents','contracts'],true))$cat='documents';
        if(empty($_FILES['demo_files']['name'])||!is_array($_FILES['demo_files']['name']))wp_die('Velg minst én fil.');
        $items=self::items();
        $dirs=self::master_dirs($cat);
        foreach($_FILES['demo_files']['name'] as $k=>$name){
            if(!$name||!empty($_FILES['demo_files']['error'][$k]))continue;
            $tmp=$_FILES['demo_files']['tmp_name'][$k]??'';
            if(!$tmp||!is_uploaded_file($tmp))continue;
            $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
            $allowed=$cat==='gallery'?['jpg','jpeg','png','webp']:['doc','docx','pdf','odt','rtf'];
            if(!in_array($ext,$allowed,true))continue;
            $filename=wp_unique_filename($cat==='gallery'?$dirs['dir'].'original/':$dirs['dir'],sanitize_file_name($name));
            $target=$cat==='gallery'?$dirs['dir'].'original/'.$filename:$dirs['dir'].$filename;
            if(!move_uploaded_file($tmp,$target))continue;
            $url=$cat==='gallery'?$dirs['url'].'original/'.rawurlencode($filename):$dirs['url'].rawurlencode($filename);
            $item=[
                'id'=>wp_generate_uuid4(),'category'=>$cat,'title'=>sanitize_text_field(pathinfo($name,PATHINFO_FILENAME)),
                'filename'=>$filename,'url'=>$url,'path'=>$target,'active'=>1,'source'=>'admin',
                'type'=>$cat==='gallery'?'image':'file','created_at'=>current_time('mysql')
            ];
            if($cat==='gallery')$item['master_original_path']=$target;
            $items[]=$item;
        }
        self::ensure_master_storage($items);
        update_option(self::OPTION,$items,false);
        wp_safe_redirect(add_query_arg('message','demo_uploaded',NLS1_Aurora_Account_Platform::url('demo')));exit;
    }

    public function toggle(){
        if(!current_user_can('manage_options'))wp_die('Ingen tilgang.');check_admin_referer('aurora_demo_toggle');
        $id=sanitize_text_field($_POST['item_id']??'');$items=self::items();
        foreach($items as &$i)if(($i['id']??'')===$id)$i['active']=empty($i['active'])?1:0;unset($i);
        update_option(self::OPTION,$items,false);wp_safe_redirect(NLS1_Aurora_Account_Platform::url('demo'));exit;
    }

    public function delete(){
        if(!current_user_can('manage_options'))wp_die('Ingen tilgang.');check_admin_referer('aurora_demo_delete');
        $id=sanitize_text_field($_POST['item_id']??'');$items=self::items();$removed=null;
        foreach($items as $i)if(($i['id']??'')===$id){$removed=$i;break;}
        $items=array_values(array_filter($items,fn($i)=>($i['id']??'')!==$id));
        update_option(self::OPTION,$items,false);
        if($removed && ($removed['source']??'')==='admin'){
            foreach(['path','master_original_path','master_preview_path','master_thumbnail_path'] as $k){
                $p=$removed[$k]??'';if($p&&file_exists($p))@unlink($p);
            }
        }
        wp_safe_redirect(NLS1_Aurora_Account_Platform::url('demo'));exit;
    }
}
