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


    public function render_customer_portal(){if(!get_query_var('fotoportal_customer'))return;$c=NLS1_Fotoportal_Admin::get_public_client_by_token(sanitize_text_field($_GET['token']??''));status_header($c?200:404);nocache_headers();if(!$c){echo '<h1>Portalen ble ikke funnet</h1>';exit;}$s=NLS1_Fotoportal_Admin::photographer_portal_settings($c->account_id);$gs=NLS1_Fotoportal_Admin::get_public_client_projects_and_galleries($c);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';echo '<!doctype html><html lang="no"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($studio).' – Kundeportal</title><style>:root{--a:'.$a.'}*{box-sizing:border-box}body{margin:0;background:#f7f6f8;color:#29242e;font-family:Arial,sans-serif}.head,.shell{max-width:1200px;margin:auto}.top{background:#fff;border-bottom:1px solid #eee}.head{padding:26px 20px;display:flex;gap:18px;align-items:center}.logo{max-width:180px;max-height:65px}.avatar{width:64px;height:64px;border-radius:50%;object-fit:cover}.shell{padding:34px 20px 70px}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.card{background:#fff;border:1px solid #e9e4ed;border-radius:14px;overflow:hidden;text-decoration:none;color:inherit}.cover{aspect-ratio:16/10;background:#e9e6ec}.cover img{width:100%;height:100%;object-fit:cover}.cb{padding:15px}.open{color:var(--a);font-weight:bold}.project{margin:30px 0}.profile{background:#fff;padding:20px;border-radius:14px;display:flex;gap:18px}.footer{text-align:center;padding:24px;color:#948b99;font-size:12px}@media(max-width:800px){.cards{grid-template-columns:1fr 1fr}}@media(max-width:520px){.cards{grid-template-columns:1fr}}</style><body><div class="top"><header class="head">';if($s['logo_url'])echo '<img class="logo" src="'.esc_url($s['logo_url']).'">';elseif($s['profile_image_url'])echo '<img class="avatar" src="'.esc_url($s['profile_image_url']).'">';echo '<div><h1>'.esc_html($studio).'</h1><div>'.esc_html($s['photographer_name']).'</div></div></header></div><main class="shell"><h2>Hei '.esc_html($c->client_name).'</h2><p>Her finner du prosjektene og bildegalleriene dine.</p>';foreach($gs as $x){echo '<section class="project"><h3>'.esc_html($x['project']->project_name).'</h3><div class="cards">';foreach($x['galleries'] as $g)echo '<a class="card" href="'.esc_url($g->public_url).'"><div class="cover">'.($g->cover_url?'<img src="'.esc_url($g->cover_url).'">':'').'</div><div class="cb"><strong>'.esc_html($g->gallery_title).'</strong><p>'.$g->original_count.' bilder</p><span class="open">Åpne galleri →</span></div></a>';echo '</div></section>';}echo '<section class="profile">'.($s['profile_image_url']?'<img class="avatar" src="'.esc_url($s['profile_image_url']).'">':'').'<div><strong>'.esc_html($s['photographer_name']?:$studio).'</strong><p>'.nl2br(esc_html($s['about'])).'</p><p>'.esc_html(implode(' · ',array_filter([$s['phone'],$s['email'],$s['website']]))).'</p></div></section></main><footer class="footer">Powered by Aurora Fotoportal · Utviklet av 9Ls1 Digital</footer></body></html>';exit;}

    public function render_public_gallery() {
        if (!get_query_var('fotoportal_gallery')) return;

        $token = sanitize_text_field($_GET['token'] ?? '');
        $gallery = $token ? NLS1_Fotoportal_Admin::get_public_gallery_by_token($token) : null;

        status_header($gallery ? 200 : 404);
        nocache_headers();

        echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>' . esc_html($gallery ? $gallery->gallery_title : 'Galleri') . ' – Aurora Fotoportal</title>';
        echo '<style>
            *{box-sizing:border-box}body{margin:0;background:#f7f6f8;color:#26212b;font-family:Inter,Arial,sans-serif}
            .shell{max-width:1500px;margin:0 auto;padding:28px 20px 60px}.head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:24px}
            .eyebrow{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#7c6f87;font-weight:700}.head h1{font-size:clamp(26px,4vw,46px);margin:6px 0}.meta{color:#81788a}
            .masonry{column-count:4;column-gap:10px}.item{break-inside:avoid;margin:0 0 10px;cursor:zoom-in}.item img{display:block;width:100%;height:auto;border-radius:8px;background:#e9e6ec}
            .empty{padding:60px 20px;text-align:center;background:#fff;border-radius:14px}.lightbox{position:fixed;inset:0;background:rgba(12,10,15,.94);display:none;align-items:center;justify-content:center;padding:28px;z-index:9999}
            .lightbox.is-open{display:flex}.lightbox img{max-width:96vw;max-height:92vh;width:auto;height:auto;object-fit:contain}.close{position:absolute;right:18px;top:18px;border:0;background:#fff;color:#211b28;border-radius:999px;width:42px;height:42px;font-size:25px;cursor:pointer}
            .nav{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:58px;border:0;border-radius:12px;background:rgba(255,255,255,.92);color:#211b28;font-size:34px;line-height:1;cursor:pointer}
            .nav.prev{left:18px}.nav.next{right:18px}@media(max-width:600px){.nav{width:42px;height:52px}.nav.prev{left:8px}.nav.next{right:8px}}
            @media(max-width:1100px){.masonry{column-count:3}}@media(max-width:760px){.masonry{column-count:2}.shell{padding:20px 10px 40px}.head{align-items:start;flex-direction:column}}@media(max-width:440px){.masonry{column-count:2;column-gap:6px}.item{margin-bottom:6px}.item img{border-radius:5px}}
        </style></head><body>';

        if (!$gallery) {
            echo '<div class="shell"><div class="empty"><h1>Galleriet ble ikke funnet</h1><p>Lenken er ugyldig eller ikke lenger tilgjengelig.</p></div></div></body></html>';
            exit;
        }

        $images = NLS1_Fotoportal_Admin::get_public_gallery_images($gallery);
        echo '<main class="shell"><header class="head"><div><div class="eyebrow">Aurora Fotoportal</div><h1>' . esc_html($gallery->gallery_title) . '</h1><div class="meta">' . esc_html($gallery->project_name ?: '') . ($gallery->client_name ? ' · ' . esc_html($gallery->client_name) : '') . '</div></div><div class="meta">' . count($images) . ' bilder</div></header>';

        if ($images) {
            echo '<section class="masonry">';
            foreach ($images as $img) {
                $src = $img->preview_url ?: ($img->thumbnail_url ?: '');
                if (!$src) continue;
                echo '<figure class="item"><img loading="lazy" src="' . esc_url($src) . '" data-full="' . esc_url($src) . '" alt=""></figure>';
            }
            echo '</section>';
        } else {
            echo '<div class="empty"><h2>Ingen bilder i galleriet ennå</h2></div>';
        }

        echo '</main><div class="lightbox" id="aurora-gallery-lightbox"><button class="close" type="button" aria-label="Lukk">×</button><button class="nav prev" type="button" aria-label="Forrige bilde">‹</button><img src="" alt=""><button class="nav next" type="button" aria-label="Neste bilde">›</button></div>';
        echo '<script>
            (function(){
                var lb=document.getElementById("aurora-gallery-lightbox"),im=lb.querySelector("img"),items=[].slice.call(document.querySelectorAll(".item img")),index=0;
                function show(i){if(!items.length)return;index=(i+items.length)%items.length;var x=items[index];im.src=x.dataset.full||x.src;lb.classList.add("is-open");}
                items.forEach(function(x,i){x.addEventListener("click",function(){show(i);});});
                function close(){lb.classList.remove("is-open");im.src="";}
                lb.querySelector(".prev").addEventListener("click",function(e){e.stopPropagation();show(index-1);});
                lb.querySelector(".next").addEventListener("click",function(e){e.stopPropagation();show(index+1);});
                lb.addEventListener("click",function(e){if(e.target===lb||e.target.classList.contains("close"))close();});
                document.addEventListener("keydown",function(e){if(!lb.classList.contains("is-open"))return;if(e.key==="Escape")close();if(e.key==="ArrowLeft")show(index-1);if(e.key==="ArrowRight")show(index+1);});
            })();</script></body></html>';
        exit;
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
