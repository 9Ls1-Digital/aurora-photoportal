<?php
if (!defined('ABSPATH')) exit;

class NLS1_Fotoportal_Frontend {

    public function __construct() {
        add_action('init', [$this, 'add_rewrite']);
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('template_redirect', [$this, 'render_customer_portal']);
        add_action('template_redirect', [$this, 'render_public_gallery']);
        add_action('template_redirect', [$this, 'render_signing_page']);
        add_action('admin_post_nopriv_9ls1_fotoportal_sign_contract', [$this, 'handle_sign_contract']);
        add_action('admin_post_9ls1_fotoportal_sign_contract', [$this, 'handle_sign_contract']);
        add_action('wp_ajax_nopriv_9ls1_gallery_interaction', [$this, 'handle_gallery_interaction']);
        add_action('wp_ajax_9ls1_gallery_interaction', [$this, 'handle_gallery_interaction']);
    }

    public function add_rewrite() {
        add_rewrite_rule('^fotoportal-galleri/?$', 'index.php?fotoportal_gallery=1', 'top');
        add_rewrite_rule('^fotoportal-signer/?$', 'index.php?fotoportal_signer=1', 'top');
    }

    public function query_vars($vars) {
        $vars[] = 'fotoportal_customer';
        $vars[] = 'fotoportal_gallery';
        $vars[] = 'fotoportal_signer';
        return $vars;
    }


    private function brand_css(){return '*{box-sizing:border-box}body{margin:0;background:#f7f6f8;color:#29242e;font-family:Inter,Arial,sans-serif}.photo-top{background:#fff;border-bottom:1px solid #ebe7ef}.photo-head{max-width:1240px;margin:auto;padding:20px;display:flex;align-items:center;gap:16px}.photo-logo{max-width:170px;max-height:58px}.photo-avatar{width:58px;height:58px;border-radius:50%;object-fit:cover}.photo-brand{display:flex;flex-direction:column;gap:3px}.photo-brand strong{font-size:20px}.photo-brand span{color:#817889}.portal-link{margin-left:auto;text-decoration:none;color:var(--a);font-weight:700;border:1px solid #ddd;padding:9px 13px;border-radius:9px}.photo-signature{max-width:1240px;margin:38px auto 8px;padding:18px 20px;border-top:1px solid #e7e1ea;display:flex;align-items:center;justify-content:center;text-align:center;gap:12px;color:#6f6675}.photo-signature .photo-avatar{width:42px;height:42px}.photo-signature div{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:5px 12px}.photo-signature strong{color:#302a34}.photo-signature span,.photo-signature small{font-size:13px}.platform-footer{text-align:center;color:#9a929f;font-size:11px;padding:16px 20px 28px}.interaction-strip{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px}.interaction-strip span{font-size:12px;background:#fff;border:1px solid #e8e2eb;border-radius:999px;padding:6px 9px;color:#746a7a}.interaction-strip b{color:var(--a)}.photo-hero{position:relative;min-height:430px;background:#343039 center/cover no-repeat;display:flex;align-items:center;justify-content:center;text-align:center;color:#fff;overflow:hidden}.photo-hero-overlay{position:absolute;inset:0}.photo-hero.size-small{min-height:300px}.photo-hero.size-medium{min-height:430px}.photo-hero.size-large{min-height:590px}.photo-hero-content{position:relative;z-index:1;padding:55px 20px;max-width:900px}.photo-hero .hero-kicker{font-size:12px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;opacity:.92}.photo-hero h1{font-size:clamp(36px,5vw,68px);line-height:1.02;margin:10px 0}.photo-hero p{font-size:18px;margin:0;opacity:.94}.photo-hero.no-image{min-height:280px;background:linear-gradient(135deg,var(--a),#28222f)}';}
    private function brand_head($s,$studio,$portal=''){echo '<div class="photo-top"><div class="photo-head">';if($s['logo_url'])echo '<img class="photo-logo" src="'.esc_url($s['logo_url']).'" alt="">';elseif($s['profile_image_url'])echo '<img class="photo-avatar" src="'.esc_url($s['profile_image_url']).'" alt="">';echo '<div class="photo-brand"><strong>'.esc_html($studio).'</strong><span>'.esc_html($s['photographer_name']).'</span></div>';if($portal)echo '<a class="portal-link" href="'.esc_url($portal).'">Min portal</a>';echo '</div></div>';}
    private function brand_foot($s,$studio){$c=array_filter([$s['phone'],$s['email'],$s['website']]);echo '<div class="photo-signature">'.($s['profile_image_url']?'<img class="photo-avatar" src="'.esc_url($s['profile_image_url']).'" alt="">':'').'<div><strong>'.esc_html($s['photographer_name']?:$studio).'</strong>'.($s['about']?'<span>'.esc_html($s['about']).'</span>':'').($c?'<small>'.esc_html(implode(' · ',$c)).'</small>':'').'</div></div><footer class="platform-footer">Powered by Aurora Fotoportal · Utviklet av 9Ls1 Digital</footer>';}
    public function render_customer_portal(){if(!get_query_var('fotoportal_customer'))return;$c=NLS1_Fotoportal_Admin::get_public_client_by_token(sanitize_text_field($_GET['token']??''));status_header($c?200:404);nocache_headers();if(!$c){echo '<h1>Portalen ble ikke funnet</h1>';exit;}$s=NLS1_Fotoportal_Admin::photographer_portal_settings($c->account_id);$gs=NLS1_Fotoportal_Admin::get_public_client_projects_and_galleries($c);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($studio).' – Kundeportal</title><style>:root{--a:'.$a.'}'.$this->brand_css().'.shell{max-width:1240px;margin:auto;padding:34px 20px 55px}.welcome h1{font-size:34px;margin:0 0 8px}.welcome p{color:#817889}.project{margin:30px 0}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.card{background:#fff;border:1px solid #e9e4ed;border-radius:14px;overflow:hidden;text-decoration:none;color:inherit}.cover{aspect-ratio:16/10;background:#e9e6ec}.cover img{width:100%;height:100%;object-fit:cover}.cb{padding:15px}.cb strong{display:block}.cb p{color:#817889}.open{color:var(--a);font-weight:700}@media(max-width:800px){.cards{grid-template-columns:1fr 1fr}}@media(max-width:520px){.cards{grid-template-columns:1fr}}</style></head><body>';$this->brand_head($s,$studio);$chs=NLS1_Fotoportal_Admin::public_customer_hero_settings($c);$chi=[];foreach($gs as $xx)foreach($xx['galleries'] as $gg){$gi=NLS1_Fotoportal_Admin::get_public_gallery_images($gg);$chi=array_merge($chi,$gi);}$hero=NLS1_Fotoportal_Admin::hero_image_url($chs,$chi,$s['cover_image_url']??'');echo '<section class="photo-hero size-'.esc_attr($chs['size']).' '.($hero?'':'no-image').'"'.($hero?' style="background-image:url('.esc_url($hero).');background-position:'.(int)$chs['focal_x'].'% '.(int)$chs['focal_y'].'%"':'').'><span class="photo-hero-overlay" style="background:'.esc_attr($chs['overlay_color']).';opacity:'.esc_attr($chs['overlay_opacity']/100).'"></span><div class="photo-hero-content"><div class="hero-kicker">'.esc_html($studio).'</div><h1>'.esc_html($c->client_name).'</h1><p>Velkommen til din bildeportal</p></div></section><main class="shell">';foreach($gs as $x){echo '<section class="project"><h2>'.esc_html($x['project']->project_name).'</h2><div class="cards">';foreach($x['galleries'] as $g){$ic=NLS1_Fotoportal_Admin::gallery_interaction_counts((int)$g->gallery_id,(int)$g->account_id);echo '<a class="card" href="'.esc_url($g->public_url).'"><div class="cover">'.($g->cover_url?'<img src="'.esc_url($g->cover_url).'" alt="">':'').'</div><div class="cb"><strong>'.esc_html($g->gallery_title).'</strong><p>'.esc_html(!empty($g->gallery_description)?$g->gallery_description:((int)$g->original_count.' bilder')).'</p><div class="interaction-strip"><span>♡ <b>'.(int)$ic['favorites'].'</b></span><span>✓ <b>'.(int)$ic['approved'].'</b> valgt</span><span>💬 <b>'.(int)$ic['comments'].'</b></span></div><p class="open">Åpne galleri →</p></div></a>';}echo '</div></section>';}echo '</main>';$this->brand_foot($s,$studio);echo '</body></html>';exit;}
    private function gallery_interaction_state($g,$visitor=''){
        global $wpdb;
        $visitor=sanitize_text_field($visitor);
        $fav=self::safe_table('favorites');
        $com=self::safe_table('image_comments');
        $img=NLS1_Fotoportal_Admin::table('images');
        $state=['favorites'=>[],'approved'=>[],'comments'=>[],'counts'=>['favorites'=>0,'approved'=>0,'comments'=>0]];
        $state['counts']['favorites']=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT image_id) FROM $fav WHERE gallery_id=%d",$g->id));
        $state['counts']['comments']=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $com WHERE gallery_id=%d",$g->id));
        $state['counts']['approved']=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $img WHERE gallery_id=%d AND account_id=%d AND is_selected=1",$g->id,$g->account_id));
        $state['favorites']=array_values(array_unique(array_map('intval',$wpdb->get_col($wpdb->prepare("SELECT image_id FROM $fav WHERE gallery_id=%d",$g->id)))));
        $state['approved']=array_map('intval',$wpdb->get_col($wpdb->prepare("SELECT id FROM $img WHERE gallery_id=%d AND account_id=%d AND is_selected=1",$g->id,$g->account_id)));
        $rows=$wpdb->get_results($wpdb->prepare("SELECT image_id,comment_text,created_at FROM $com WHERE gallery_id=%d ORDER BY created_at DESC",$g->id));
        foreach($rows as $r){$id=(int)$r->image_id;if(!isset($state['comments'][$id]))$state['comments'][$id]=[];$state['comments'][$id][]=['text'=>$r->comment_text,'date'=>$r->created_at];}
        return $state;
    }
    private static function safe_table($name){return NLS1_Fotoportal_Admin::table($name);}
    public function handle_gallery_interaction(){
        check_ajax_referer('9ls1_gallery_interaction','nonce');
        global $wpdb;
        $token=sanitize_text_field($_POST['token']??'');$g=NLS1_Fotoportal_Admin::get_public_gallery_by_token($token);
        if(!$g)wp_send_json_error(['message'=>'Ugyldig galleri'],404);
        $image_id=absint($_POST['image_id']??0);$visitor=substr(sanitize_text_field($_POST['visitor']??''),0,64);$kind=sanitize_key($_POST['kind']??'');
        if($kind==='state')wp_send_json_success(['active'=>false,'state'=>$this->gallery_interaction_state($g,$visitor)]);
        $image=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".NLS1_Fotoportal_Admin::table('images')." WHERE id=%d AND gallery_id=%d AND account_id=%d",$image_id,$g->id,$g->account_id));
        if(!$image)wp_send_json_error(['message'=>'Bildet finnes ikke'],404);
        if($kind==='favorite'){
            $t=self::safe_table('favorites');$id=$wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE image_id=%d AND gallery_id=%d LIMIT 1",$image_id,$g->id));
            if($id){$wpdb->query($wpdb->prepare("DELETE FROM $t WHERE image_id=%d AND gallery_id=%d",$image_id,$g->id));$active=false;}else{$wpdb->insert($t,['image_id'=>$image_id,'gallery_id'=>$g->id,'project_id'=>$g->project_id,'client_id'=>$g->client_id,'user_email'=>$visitor,'is_test'=>0,'created_at'=>current_time('mysql')]);$active=true;}
        }elseif($kind==='approve'){
            $active=empty($image->is_selected);$wpdb->update(NLS1_Fotoportal_Admin::table('images'),['is_selected'=>$active?1:0,'updated_at'=>current_time('mysql')],['id'=>$image_id,'gallery_id'=>$g->id,'account_id'=>$g->account_id]);
        }elseif($kind==='comment'){
            $text=trim(sanitize_textarea_field($_POST['comment']??''));if($text==='')wp_send_json_error(['message'=>'Skriv en kommentar'],400);
            $wpdb->insert(self::safe_table('image_comments'),['image_id'=>$image_id,'gallery_id'=>$g->id,'project_id'=>$g->project_id,'client_id'=>$g->client_id,'user_email'=>$visitor,'comment_text'=>$text,'is_test'=>0,'created_at'=>current_time('mysql')]);$active=true;
        }else wp_send_json_error(['message'=>'Ukjent handling'],400);
        NLS1_Fotoportal_Admin::record_gallery_activity($g,$kind);
        wp_send_json_success(['active'=>$active,'state'=>$this->gallery_interaction_state($g,$visitor)]);
    }

    public function render_public_gallery(){
        if(!get_query_var('fotoportal_gallery'))return;
        $token=sanitize_text_field($_GET['token']??'');$g=NLS1_Fotoportal_Admin::get_public_gallery_by_token($token);status_header($g?200:404);nocache_headers();if(!$g){echo '<h1>Galleriet ble ikke funnet</h1>';exit;}
        $s=NLS1_Fotoportal_Admin::photographer_portal_settings($g->account_id);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';$portal=NLS1_Fotoportal_Admin::customer_portal_url($g->client_id);$ims=NLS1_Fotoportal_Admin::get_public_gallery_images($g);$state=$this->gallery_interaction_state($g,'');
        echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($g->gallery_title).' – '.esc_html($studio).'</title><style>:root{--a:'.$a.'}'.$this->brand_css().'.shell{max-width:1500px;margin:auto;padding:28px 20px 55px}.gh{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:20px}.ey{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--a);font-weight:700}.gh h2{font-size:clamp(28px,4vw,46px);margin:6px 0}.meta{color:#81788a}.masonry{column-count:4;column-gap:10px}.item{break-inside:avoid;margin:0 0 10px;position:relative;overflow:hidden;border-radius:8px;background:#eee}.item img{display:block;width:100%;height:auto;cursor:zoom-in}.image-actions{position:absolute;left:50%;bottom:10px;transform:translate(-50%,8px);display:flex;gap:5px;opacity:0;transition:.16s;z-index:3}.item:hover .image-actions,.item:focus-within .image-actions{opacity:1;transform:translate(-50%,0)}.image-action{width:32px;height:32px;border-radius:50%;border:1px solid rgba(35,31,39,.12);background:rgba(255,255,255,.88);box-shadow:0 2px 9px rgba(0,0,0,.13);font-size:15px;line-height:1;cursor:pointer;color:#39333d;backdrop-filter:blur(5px)}.image-action:hover{background:#fff}.image-action.is-active,.image-action.comment.has-comments{background:#342f38;color:#fff;border-color:#342f38}.comment-box{display:none;position:absolute;left:10px;right:10px;bottom:50px;background:rgba(255,255,255,.98);padding:11px;border-radius:10px;z-index:4;box-shadow:0 9px 30px rgba(0,0,0,.2)}.comment-box.is-open{display:block}.comment-list{max-height:135px;overflow:auto;margin:0 0 8px}.comment-entry{font-size:12px;line-height:1.4;color:#514a55;padding:7px 0;border-bottom:1px solid #eee9ef;white-space:pre-wrap}.comment-empty{font-size:12px;color:#8a818d;margin:2px 0 8px}.comment-box textarea{width:100%;min-height:58px;resize:vertical;border:1px solid #ddd7e0;border-radius:7px;padding:8px;font:inherit}.comment-box button{margin-top:6px;border:0;background:#342f38;color:#fff;border-radius:7px;padding:7px 10px;font-weight:700;cursor:pointer}.interaction-filter{appearance:none;font:inherit;cursor:pointer;font-size:12px;background:#fff;border:1px solid #e8e2eb;border-radius:999px;padding:6px 10px;color:#746a7a}.interaction-filter b{color:var(--a)}.interaction-filter.is-active{border-color:#746a7a;color:#302a34;background:#f0edf2}.item.is-filtered-out{display:none!important}.filter-empty{display:none;text-align:center;padding:45px 20px;color:#81788a}.filter-empty.is-visible{display:block}.lightbox{position:fixed;inset:0;background:rgba(12,10,15,.94);display:none;align-items:center;justify-content:center;padding:28px;z-index:9999}.lightbox.is-open{display:flex}.lightbox img{max-width:96vw;max-height:92vh}.close,.nav{position:absolute;border:0;background:#fff;cursor:pointer}.close{right:18px;top:18px;border-radius:50%;width:42px;height:42px;font-size:25px}.nav{top:50%;transform:translateY(-50%);width:48px;height:58px;border-radius:12px;font-size:34px}.prev{left:18px}.next{right:18px}@media(max-width:1100px){.masonry{column-count:3}}@media(max-width:760px){.masonry{column-count:2}.gh{flex-direction:column;align-items:start}.image-actions{opacity:1;transform:translate(-50%,0)}}@media(max-width:520px){.masonry{column-count:1}}</style></head><body>';
        $this->brand_head($s,$studio,$portal);$ghs=NLS1_Fotoportal_Admin::public_gallery_hero_settings($g);$fallback='';if(!empty($ims)){$first=$ims[0];$fallback=$first->preview_url?:$first->thumbnail_url;}if(!$fallback)$fallback=$s['cover_image_url']??'';$galleryHero=NLS1_Fotoportal_Admin::hero_image_url($ghs,$ims,$fallback);
        echo '<section class="photo-hero size-'.esc_attr($ghs['size']).' '.($galleryHero?'':'no-image').'"'.($galleryHero?' style="background-image:url('.esc_url($galleryHero).');background-position:'.(int)$ghs['focal_x'].'% '.(int)$ghs['focal_y'].'%"':'').'><span class="photo-hero-overlay" style="background:'.esc_attr($ghs['overlay_color']).';opacity:'.esc_attr($ghs['overlay_opacity']/100).'"></span><div class="photo-hero-content"><div class="hero-kicker">'.esc_html($g->project_name).'</div><h1>'.esc_html($g->gallery_title).'</h1><p>'.esc_html(!empty($g->gallery_description)?$g->gallery_description:(count($ims).' bilder')).'</p></div></section><main class="shell"><header class="gh"><div><div class="ey">'.esc_html($studio).'</div><h2>'.esc_html($g->gallery_title).'</h2><div class="interaction-strip" data-gallery-filters><button type="button" class="interaction-filter is-active" data-filter="all">Alle bilder</button><button type="button" class="interaction-filter" data-filter="favorites">♡ <b data-count="favorites">'.$state['counts']['favorites'].'</b> favoritter</button><button type="button" class="interaction-filter" data-filter="approved">✓ <b data-count="approved">'.$state['counts']['approved'].'</b> valgt</button><button type="button" class="interaction-filter" data-filter="comments">💬 <b data-count="comments">'.$state['counts']['comments'].'</b> kommentarer</button></div></div><div class="meta">'.count($ims).' bilder</div></header><section class="masonry">';
        foreach($ims as $im){$src=$im->preview_url?:$im->thumbnail_url;if(!$src)continue;$approved=!empty($im->is_selected);echo '<figure class="item" data-image-id="'.(int)$im->id.'"><img loading="lazy" src="'.esc_url($src).'" data-full="'.esc_url($src).'" alt=""><div class="image-actions"><button type="button" class="image-action favorite" title="Favoritt" aria-label="Favoritt">♡</button><button type="button" class="image-action approve'.($approved?' is-active':'').'" title="Godkjenn / velg" aria-label="Godkjenn / velg">✓</button><button type="button" class="image-action comment" title="Kommentar" aria-label="Kommentar">💬</button></div><div class="comment-box"><div class="comment-list"></div><textarea placeholder="Skriv en kommentar til bildet …"></textarea><button type="button">Lagre kommentar</button></div></figure>';}
        echo '</section><div class="filter-empty" data-filter-empty>Ingen bilder i dette utvalget.</div></main>';$this->brand_foot($s,$studio);echo '<div class="lightbox" id="aurora-gallery-lightbox"><button class="close">×</button><button class="nav prev">‹</button><img src=""><button class="nav next">›</button></div>';
        $ajax=admin_url('admin-ajax.php');$nonce=wp_create_nonce('9ls1_gallery_interaction');
        echo '<script>(function(){var ajax='.wp_json_encode($ajax).',token='.wp_json_encode($token).',nonce='.wp_json_encode($nonce).';var visitor=localStorage.getItem("aurora_gallery_visitor");if(!visitor){visitor="v"+Date.now().toString(36)+Math.random().toString(36).slice(2);localStorage.setItem("aurora_gallery_visitor",visitor)}function post(kind,id,comment,cb,err){var f=new FormData();f.append("action","9ls1_gallery_interaction");f.append("nonce",nonce);f.append("token",token);f.append("visitor",visitor);f.append("kind",kind);f.append("image_id",id);if(comment)f.append("comment",comment);fetch(ajax,{method:"POST",credentials:"same-origin",body:f}).then(function(r){return r.json()}).then(function(j){if(j.success){apply(j.data.state);if(cb)cb(j.data)}else{if(err)err();alert((j.data&&j.data.message)||"Kunne ikke lagre")}}).catch(function(){if(err)err();alert("Kunne ikke lagre handlingen")})}var lastState=null,currentFilter="all";function renderComments(it,comments){var list=it.querySelector(".comment-list");if(!list)return;list.innerHTML="";if(!comments||!comments.length){var empty=document.createElement("div");empty.className="comment-empty";empty.textContent="Ingen kommentarer ennå.";list.appendChild(empty);return}comments.forEach(function(c){var row=document.createElement("div");row.className="comment-entry";row.textContent=c.text||"";list.appendChild(row)})}function applyFilter(){if(!lastState)return;var visible=0;document.querySelectorAll(".item").forEach(function(it){var id=parseInt(it.dataset.imageId,10),show=currentFilter==="all"||(currentFilter==="favorites"&&lastState.favorites.indexOf(id)>-1)||(currentFilter==="approved"&&lastState.approved.indexOf(id)>-1)||(currentFilter==="comments"&&lastState.comments[id]&&lastState.comments[id].length);it.classList.toggle("is-filtered-out",!show);if(show)visible++});var empty=document.querySelector("[data-filter-empty]");if(empty)empty.classList.toggle("is-visible",visible===0)}function apply(st){lastState=st;["favorites","approved","comments"].forEach(function(k){var e=document.querySelector("[data-count=\""+k+"\"]");if(e)e.textContent=st.counts[k]||0});document.querySelectorAll(".item").forEach(function(it){var id=parseInt(it.dataset.imageId,10),fav=st.favorites.indexOf(id)>-1,app=st.approved.indexOf(id)>-1,comments=st.comments[id]||[];it.querySelector(".favorite").classList.toggle("is-active",fav);it.querySelector(".approve").classList.toggle("is-active",app);it.querySelector(".comment").classList.toggle("has-comments",comments.length>0);renderComments(it,comments)});applyFilter()}document.querySelectorAll("[data-gallery-filters] .interaction-filter").forEach(function(btn){btn.addEventListener("click",function(){currentFilter=btn.dataset.filter||"all";document.querySelectorAll("[data-gallery-filters] .interaction-filter").forEach(function(b){b.classList.toggle("is-active",b===btn)});applyFilter()})});document.querySelectorAll(".item").forEach(function(it){var id=it.dataset.imageId,img=it.querySelector("img"),box=it.querySelector(".comment-box");it.querySelector(".favorite").onclick=function(e){e.stopPropagation();var b=this,was=b.classList.contains("is-active");b.classList.toggle("is-active",!was);post("favorite",id,null,null,function(){b.classList.toggle("is-active",was)})};it.querySelector(".approve").onclick=function(e){e.stopPropagation();var b=this,was=b.classList.contains("is-active");b.classList.toggle("is-active",!was);post("approve",id,null,null,function(){b.classList.toggle("is-active",was)})};it.querySelector(".comment").onclick=function(e){e.stopPropagation();box.classList.toggle("is-open")};box.onclick=function(e){e.stopPropagation()};box.querySelector("button").onclick=function(){var t=box.querySelector("textarea");if(!t.value.trim())return;post("comment",id,t.value,function(){t.value="";box.classList.remove("is-open")})};img.onclick=function(){openLightbox(img)}});function load(){var f=new FormData();f.append("action","9ls1_gallery_interaction");f.append("nonce",nonce);f.append("token",token);f.append("visitor",visitor);f.append("kind","state");f.append("image_id",document.querySelector(".item")?document.querySelector(".item").dataset.imageId:0);fetch(ajax,{method:"POST",credentials:"same-origin",body:f}).then(function(r){return r.json()}).then(function(j){if(j.success)apply(j.data.state)})}load();var l=document.getElementById("aurora-gallery-lightbox"),im=l.querySelector("img"),x=[].slice.call(document.querySelectorAll(".item img")),i=0;function openLightbox(a){i=x.indexOf(a);im.src=a.dataset.full||a.src;l.classList.add("is-open")}function show(n){i=(n+x.length)%x.length;im.src=x[i].dataset.full||x[i].src}function close(){l.classList.remove("is-open")}l.querySelector(".prev").onclick=function(e){e.stopPropagation();show(i-1)};l.querySelector(".next").onclick=function(e){e.stopPropagation();show(i+1)};l.onclick=function(e){if(e.target===l||e.target.classList.contains("close"))close()};document.onkeydown=function(e){if(!l.classList.contains("is-open"))return;if(e.key==="Escape")close();if(e.key==="ArrowLeft")show(i-1);if(e.key==="ArrowRight")show(i+1)};})();</script></body></html>';exit;
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
