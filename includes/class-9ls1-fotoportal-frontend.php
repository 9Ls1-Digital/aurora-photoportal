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
    public function render_customer_portal(){if(!get_query_var('fotoportal_customer'))return;$c=NLS1_Fotoportal_Admin::get_public_client_by_token(sanitize_text_field($_GET['token']??''));status_header($c?200:404);nocache_headers();if(!$c){echo '<h1>Portalen ble ikke funnet</h1>';exit;}$s=NLS1_Fotoportal_Admin::photographer_portal_settings($c->account_id);$gs=NLS1_Fotoportal_Admin::get_public_client_projects_and_galleries($c);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($studio).' – Kundeportal</title><style>:root{--a:'.$a.'}'.$this->brand_css().'.shell{max-width:1240px;margin:auto;padding:34px 20px 55px}.welcome h1{font-size:34px;margin:0 0 8px}.welcome p{color:#817889}.project{margin:30px 0}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.card{background:#fff;border:1px solid #e9e4ed;border-radius:14px;overflow:hidden;text-decoration:none;color:inherit}.cover{aspect-ratio:16/10;background:#e9e6ec}.cover img{width:100%;height:100%;object-fit:cover}.cb{padding:15px}.cb strong{display:block}.cb p{color:#817889}.open{color:var(--a);font-weight:700}@media(max-width:800px){.cards{grid-template-columns:1fr 1fr}}@media(max-width:520px){.cards{grid-template-columns:1fr}}</style></head><body>';$this->brand_head($s,$studio);$chs=NLS1_Fotoportal_Admin::public_customer_hero_settings($c);$chi=[];foreach($gs as $xx)foreach($xx['galleries'] as $gg){$gi=NLS1_Fotoportal_Admin::get_public_gallery_images($gg);$chi=array_merge($chi,$gi);}$hero=NLS1_Fotoportal_Admin::hero_image_url($chs,$chi,$s['cover_image_url']??'');echo '<section class="photo-hero size-'.esc_attr($chs['size']).' '.($hero?'':'no-image').'"'.($hero?' style="background-image:url('.esc_url($hero).');background-position:'.(int)$chs['focal_x'].'% '.(int)$chs['focal_y'].'%"':'').'><span class="photo-hero-overlay" style="background:'.esc_attr($chs['overlay_color']).';opacity:'.esc_attr($chs['overlay_opacity']/100).'"></span><div class="photo-hero-content"><div class="hero-kicker">'.esc_html($studio).'</div><h1>'.esc_html($c->client_name).'</h1><p>Velkommen til din bildeportal</p></div></section><main class="shell">';foreach($gs as $x){echo '<section class="project"><h2>'.esc_html($x['project']->project_name).'</h2><div class="cards">';foreach($x['galleries'] as $g)echo '<a class="card" href="'.esc_url($g->public_url).'"><div class="cover">'.($g->cover_url?'<img src="'.esc_url($g->cover_url).'" alt="">':'').'</div><div class="cb"><strong>'.esc_html($g->gallery_title).'</strong><p>'.(int)$g->original_count.' bilder</p><div class="interaction-strip"><span>♡ <b>0</b></span><span>✓ <b>0</b> valgt</span><span>💬 <b>0</b></span></div><p class="open">Åpne galleri →</p></div></a>';echo '</div></section>';}echo '</main>';$this->brand_foot($s,$studio);echo '</body></html>';exit;}
    public function render_public_gallery(){if(!get_query_var('fotoportal_gallery'))return;$g=NLS1_Fotoportal_Admin::get_public_gallery_by_token(sanitize_text_field($_GET['token']??''));status_header($g?200:404);nocache_headers();if(!$g){echo '<h1>Galleriet ble ikke funnet</h1>';exit;}$s=NLS1_Fotoportal_Admin::photographer_portal_settings($g->account_id);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';$portal=NLS1_Fotoportal_Admin::customer_portal_url($g->client_id);$ims=NLS1_Fotoportal_Admin::get_public_gallery_images($g);echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($g->gallery_title).' – '.esc_html($studio).'</title><style>:root{--a:'.$a.'}'.$this->brand_css().'.shell{max-width:1500px;margin:auto;padding:28px 20px 55px}.gh{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:20px}.ey{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--a);font-weight:700}.gh h1{font-size:clamp(28px,4vw,46px);margin:6px 0}.meta{color:#81788a}.masonry{column-count:4;column-gap:10px}.item{break-inside:avoid;margin:0 0 10px;cursor:zoom-in}.item img{display:block;width:100%;height:auto;border-radius:8px}.lightbox{position:fixed;inset:0;background:rgba(12,10,15,.94);display:none;align-items:center;justify-content:center;padding:28px;z-index:9999}.lightbox.is-open{display:flex}.lightbox img{max-width:96vw;max-height:92vh}.close,.nav{position:absolute;border:0;background:#fff;cursor:pointer}.close{right:18px;top:18px;border-radius:50%;width:42px;height:42px;font-size:25px}.nav{top:50%;transform:translateY(-50%);width:48px;height:58px;border-radius:12px;font-size:34px}.prev{left:18px}.next{right:18px}@media(max-width:1100px){.masonry{column-count:3}}@media(max-width:760px){.masonry{column-count:2}.gh{flex-direction:column;align-items:start}}</style></head><body>';$this->brand_head($s,$studio,$portal);$ghs=NLS1_Fotoportal_Admin::public_gallery_hero_settings($g);$fallback='';if(!empty($ims)){ $first=$ims[0];$fallback=$first->preview_url?:$first->thumbnail_url; }if(!$fallback)$fallback=$s['cover_image_url']??'';$galleryHero=NLS1_Fotoportal_Admin::hero_image_url($ghs,$ims,$fallback);echo '<section class="photo-hero size-'.esc_attr($ghs['size']).' '.($galleryHero?'':'no-image').'"'.($galleryHero?' style="background-image:url('.esc_url($galleryHero).');background-position:'.(int)$ghs['focal_x'].'% '.(int)$ghs['focal_y'].'%"':'').'><span class="photo-hero-overlay" style="background:'.esc_attr($ghs['overlay_color']).';opacity:'.esc_attr($ghs['overlay_opacity']/100).'"></span><div class="photo-hero-content"><div class="hero-kicker">'.esc_html($g->project_name).'</div><h1>'.esc_html($g->gallery_title).'</h1><p>'.esc_html($g->client_name).' · '.count($ims).' bilder</p></div></section><main class="shell"><header class="gh"><div><div class="ey">'.esc_html($studio).'</div><h2>'.esc_html($g->gallery_title).'</h2><div class="interaction-strip"><span>♡ <b>0</b> favoritter</span><span>✓ <b>0</b> valgt</span><span>💬 <b>0</b> kommentarer</span></div></div><div class="meta">'.count($ims).' bilder</div></header><section class="masonry">';foreach($ims as $im){$src=$im->preview_url?:$im->thumbnail_url;if($src)echo '<figure class="item"><img loading="lazy" src="'.esc_url($src).'" data-full="'.esc_url($src).'" alt=""></figure>';}echo '</section></main>';$this->brand_foot($s,$studio);echo '<div class="lightbox" id="aurora-gallery-lightbox"><button class="close">×</button><button class="nav prev">‹</button><img src=""><button class="nav next">›</button></div><script>(function(){var l=document.getElementById("aurora-gallery-lightbox"),im=l.querySelector("img"),x=[].slice.call(document.querySelectorAll(".item img")),i=0;function s(n){i=(n+x.length)%x.length;im.src=x[i].dataset.full||x[i].src;l.classList.add("is-open")}x.forEach(function(a,n){a.onclick=function(){s(n)}});function c(){l.classList.remove("is-open")}l.querySelector(".prev").onclick=function(e){e.stopPropagation();s(i-1)};l.querySelector(".next").onclick=function(e){e.stopPropagation();s(i+1)};l.onclick=function(e){if(e.target===l||e.target.classList.contains("close"))c()};document.onkeydown=function(e){if(!l.classList.contains("is-open"))return;if(e.key==="Escape")c();if(e.key==="ArrowLeft")s(i-1);if(e.key==="ArrowRight")s(i+1)}})();</script></body></html>';exit;}

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
