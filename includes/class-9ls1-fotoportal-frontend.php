<?php
if (!defined('ABSPATH')) exit;

class NLS1_Fotoportal_Frontend {

    public function __construct() {
        add_action('init', [$this, 'add_rewrite']);
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('template_redirect', [$this, 'render_customer_portal']);
        add_action('template_redirect', [$this, 'render_public_gallery']);
        add_action('template_redirect', [$this, 'render_signing_page']);
        add_action('template_redirect', [$this, 'render_customer_password_page']);
        add_action('template_redirect', [$this, 'render_photographer_password_page'], 0);
        add_action('template_redirect', [$this, 'render_photographer_login_page'], 0);
        add_action('wp_mail_failed', [$this, 'capture_mail_failure']);
        add_filter('login_redirect', [$this, 'aurora_customer_login_redirect'], 20, 3);
        add_filter('logout_redirect', [$this, 'aurora_customer_logout_redirect'], 20, 3);
        add_action('login_init', [$this, 'intercept_wordpress_customer_auth'], 0);
        add_action('template_redirect', [$this, 'redirect_aurora_customer_account_pages'], 1);
        add_action('admin_post_nopriv_9ls1_fotoportal_sign_contract', [$this, 'handle_sign_contract']);
        add_action('admin_post_9ls1_fotoportal_sign_contract', [$this, 'handle_sign_contract']);
        add_action('wp_ajax_nopriv_9ls1_gallery_interaction', [$this, 'handle_gallery_interaction']);
        add_action('wp_ajax_9ls1_gallery_interaction', [$this, 'handle_gallery_interaction']);
        add_action('wp_ajax_nopriv_9ls1_submit_gallery_selection', [$this, 'handle_submit_gallery_selection']);
        add_action('wp_ajax_9ls1_submit_gallery_selection', [$this, 'handle_submit_gallery_selection']);
    }

    public function add_rewrite() {
        add_rewrite_rule('^fotoportal-galleri/?$', 'index.php?fotoportal_gallery=1', 'top');
        add_rewrite_rule('^fotoportal-signer/?$', 'index.php?fotoportal_signer=1', 'top');
    }

    public function query_vars($vars) {
        $vars[] = 'fotoportal_customer';
        $vars[] = 'fotoportal_gallery';
        $vars[] = 'fotoportal_signer';
        $vars[] = 'fotoportal_password';
        $vars[] = 'aurora_photographer_password';
        $vars[] = 'aurora_photographer_login';
        return $vars;
    }



    private function customer_auth_context_cookie_name(){ return 'aurora_fotoportal_customer_ctx'; }

    private function set_customer_auth_context($client){
        if(!$client || empty($client->id) || empty($client->account_id) || empty($client->portal_token) || headers_sent()) return;
        $payload=(int)$client->id.'|'.(int)$client->account_id.'|'.(string)$client->portal_token;
        $value=base64_encode($payload).'|'.hash_hmac('sha256',$payload,wp_salt('auth'));
        $path=defined('SITECOOKIEPATH') && SITECOOKIEPATH ? SITECOOKIEPATH : '/';
        $domain=defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        setcookie($this->customer_auth_context_cookie_name(),$value,[
            'expires'=>time()+DAY_IN_SECONDS,
            'path'=>$path,
            'domain'=>$domain ?: '',
            'secure'=>is_ssl(),
            'httponly'=>true,
            'samesite'=>'Lax',
        ]);
        $_COOKIE[$this->customer_auth_context_cookie_name()]=$value;
    }

    private function customer_from_auth_context(){
        $raw=(string)($_COOKIE[$this->customer_auth_context_cookie_name()]??'');
        if(!$raw || strpos($raw,'|')===false)return null;
        $pos=strrpos($raw,'|');
        $encoded=substr($raw,0,$pos);$sig=substr($raw,$pos+1);
        $payload=base64_decode($encoded,true);
        if($payload===false || !$sig || !hash_equals(hash_hmac('sha256',$payload,wp_salt('auth')),$sig))return null;
        $parts=explode('|',$payload,3);if(count($parts)!==3)return null;
        [$client_id,$account_id,$token]=$parts;
        $client=NLS1_Fotoportal_Admin::get_public_client_by_id_account((int)$client_id,(int)$account_id);
        if(!$client || !hash_equals((string)$client->portal_token,(string)$token))return null;
        return $client;
    }

    private function customer_password_url($client,$mode='',$extra=[]){
        if(!$client)return home_url('/');
        $args=['fotoportal_password'=>1,'token'=>rawurlencode((string)$client->portal_token)];
        if($mode!=='')$args['mode']=$mode;
        return add_query_arg(array_merge($args,$extra),home_url('/'));
    }

    public function intercept_wordpress_customer_auth(){
        $action=sanitize_key($_REQUEST['action']??'login');
        if($action==='logout') return;

        /*
         * IMPORTANT AUTH BOUNDARY
         * -----------------------
         * wp-login.php is WordPress' shared authentication endpoint. Aurora must
         * never infer "customer login" solely from a stale portal context cookie.
         * Doing so hijacks administrator and photographer logins.
         *
         * Customer portal login/password handling is implemented on Aurora's own
         * frontend routes. We only intercept wp-login.php when the request is
         * explicitly marked as Aurora customer authentication.
         */
        $explicit_customer_auth = !empty($_REQUEST['aurora_customer_auth']);

        if(!$explicit_customer_auth){
            // Generic WordPress/admin/photographer login: remove stale customer
            // context so it cannot influence a later request, then leave WP alone.
            $cookie=$this->customer_auth_context_cookie_name();
            if(isset($_COOKIE[$cookie]) && !headers_sent()){
                $path=defined('SITECOOKIEPATH') && SITECOOKIEPATH ? SITECOOKIEPATH : '/';
                $domain=defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
                setcookie($cookie,'',[
                    'expires'=>time()-3600,
                    'path'=>$path,
                    'domain'=>$domain ?: '',
                    'secure'=>is_ssl(),
                    'httponly'=>true,
                    'samesite'=>'Lax',
                ]);
                unset($_COOKIE[$cookie]);
            }
            return;
        }

        $client=$this->customer_from_auth_context();
        if(!$client) return;

        $this->set_customer_auth_context($client);
        $portal=NLS1_Fotoportal_Admin::customer_portal_url((int)$client->id);

        if(in_array($action,['rp','resetpass'],true)){
            $key=sanitize_text_field(wp_unslash($_REQUEST['key']??''));
            $login=sanitize_text_field(wp_unslash($_REQUEST['login']??''));
            if($key&&$login){
                wp_safe_redirect($this->customer_password_url($client,'reset',[
                    'key'=>rawurlencode($key),
                    'login'=>rawurlencode($login)
                ]));
                exit;
            }
            wp_safe_redirect($this->customer_password_url($client));
            exit;
        }

        if(in_array($action,['lostpassword','retrievepassword'],true)){
            wp_safe_redirect($this->customer_password_url($client));
            exit;
        }

        // Explicit customer auth may return to the customer's portal.
        wp_safe_redirect($portal);
        exit;
    }

    private function aurora_customer_portal_for_user($user){
        if(!($user instanceof WP_User))return '';
        $client_id=(int)get_user_meta($user->ID,'aurora_fotoportal_client_id',true);
        $account_id=(int)get_user_meta($user->ID,'aurora_fotoportal_account_id',true);
        if(!$client_id||!$account_id)return '';
        $client=NLS1_Fotoportal_Admin::get_public_client_by_id_account($client_id,$account_id);
        if(!$client||empty($client->portal_token))return '';
        return add_query_arg(['fotoportal_customer'=>1,'token'=>rawurlencode((string)$client->portal_token)],home_url('/'));
    }

    public function aurora_customer_login_redirect($redirect_to,$requested_redirect_to,$user){
        $portal=$this->aurora_customer_portal_for_user($user);
        if($portal && $user instanceof WP_User){
            $client_id=(int)get_user_meta($user->ID,'aurora_fotoportal_client_id',true);
            $account_id=(int)get_user_meta($user->ID,'aurora_fotoportal_account_id',true);
            if($client_id&&$account_id){$client=NLS1_Fotoportal_Admin::get_public_client_by_id_account($client_id,$account_id);if($client)$this->set_customer_auth_context($client);}
        }
        return $portal?:$redirect_to;
    }

    public function aurora_customer_logout_redirect($redirect_to,$requested_redirect_to,$user){
        $portal=$this->aurora_customer_portal_for_user($user);
        return $portal?:$redirect_to;
    }

    public function redirect_aurora_customer_account_pages(){
        if(!is_user_logged_in()||get_query_var('fotoportal_customer')||get_query_var('fotoportal_gallery')||get_query_var('fotoportal_password')||get_query_var('fotoportal_signer'))return;
        $user=wp_get_current_user(); $portal=$this->aurora_customer_portal_for_user($user); if(!$portal)return;
        if(function_exists('is_account_page') && is_account_page()){wp_safe_redirect($portal);exit;}
    }

    private function brand_css(){return '*{box-sizing:border-box}body{margin:0;background:#f7f6f8;color:#29242e;font-family:Inter,Arial,sans-serif}.photo-top{background:#fff;border-bottom:1px solid #ebe7ef}.photo-head{max-width:1240px;margin:auto;padding:20px;display:flex;align-items:center;gap:16px}.photo-logo{max-width:170px;max-height:58px}.photo-avatar{width:58px;height:58px;border-radius:50%;object-fit:cover}.photo-brand{display:flex;flex-direction:column;gap:3px}.photo-brand strong{font-size:20px}.photo-brand span{color:#817889}.portal-link{margin-left:auto;text-decoration:none;color:var(--a);font-weight:700;border:1px solid #ddd;padding:9px 13px;border-radius:9px}.customer-account{margin-left:auto;position:relative}.customer-account-btn{border:1px solid #e4dfea;background:#fff;border-radius:999px;padding:7px 10px 7px 7px;display:flex;align-items:center;gap:9px;cursor:pointer;color:#302a34}.customer-account-avatar{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#eee8ff;color:var(--a);font-weight:800}.customer-account-menu{display:none;position:absolute;right:0;top:48px;width:245px;background:#fff;border:1px solid #e7e1ea;border-radius:14px;padding:8px;box-shadow:0 18px 45px rgba(45,35,55,.14);z-index:30}.customer-account.is-open .customer-account-menu{display:block}.customer-account-menu a{display:block;padding:10px 11px;border-radius:9px;text-decoration:none;color:#302a34}.customer-account-menu a:hover{background:#f6f3fa}.customer-account-menu hr{border:0;border-top:1px solid #eee9f0;margin:6px}.customer-account-menu small{display:block;padding:8px 11px 4px;color:#817889}.customer-account-menu .logout{color:#a12d2d}.photo-signature{max-width:1240px;margin:38px auto 8px;padding:18px 20px;border-top:1px solid #e7e1ea;display:flex;align-items:center;justify-content:center;text-align:center;gap:12px;color:#6f6675}.photo-signature .photo-avatar{width:42px;height:42px}.photo-signature div{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:5px 12px}.photo-signature strong{color:#302a34}.photo-signature span,.photo-signature small{font-size:13px}.platform-footer{text-align:center;color:#9a929f;font-size:11px;padding:16px 20px 28px}.interaction-strip{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px}.interaction-strip span{font-size:12px;background:#fff;border:1px solid #e8e2eb;border-radius:999px;padding:6px 9px;color:#746a7a}.interaction-strip b{color:var(--a)}.photo-hero{position:relative;min-height:430px;background:#343039 center/cover no-repeat;display:flex;align-items:center;justify-content:center;text-align:center;color:#fff;overflow:hidden}.photo-hero-overlay{position:absolute;inset:0}.photo-hero.size-small{min-height:300px}.photo-hero.size-medium{min-height:430px}.photo-hero.size-large{min-height:590px}.photo-hero-content{position:relative;z-index:1;padding:55px 20px;max-width:900px}.photo-hero .hero-kicker{font-size:12px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;opacity:.92}.photo-hero h1{font-size:clamp(36px,5vw,68px);line-height:1.02;margin:10px 0}.photo-hero p{font-size:18px;margin:0;opacity:.94}.photo-hero.no-image{min-height:280px;background:linear-gradient(135deg,var(--a),#28222f)}';}
    private function brand_head($s,$studio,$portal='',$client=null){echo '<div class="photo-top"><div class="photo-head">';if($s['logo_url'])echo '<img class="photo-logo" src="'.esc_url($s['logo_url']).'" alt="">';elseif($s['profile_image_url'])echo '<img class="photo-avatar" src="'.esc_url($s['profile_image_url']).'" alt="">';echo '<div class="photo-brand"><strong>'.esc_html($studio).'</strong><span>'.esc_html($s['photographer_name']).'</span></div>';if($client && is_user_logged_in() && NLS1_Fotoportal_Admin::client_user_authorized($client)){ $pc=NLS1_Fotoportal_Admin::get_primary_contact((int)$client->id); $name=$pc&&$pc->contact_name?$pc->contact_name:$client->client_name; $initial=mb_strtoupper(mb_substr(trim($name),0,1)); $reset=add_query_arg(['fotoportal_password'=>1,'token'=>rawurlencode((string)$client->portal_token)],home_url('/')); $logout=wp_logout_url($portal?:home_url('/')); echo '<div class="customer-account" data-customer-account><button type="button" class="customer-account-btn" aria-expanded="false"><span class="customer-account-avatar">'.esc_html($initial).'</span><span>'.esc_html($name).'</span><span>⌄</span></button><div class="customer-account-menu"><small>'.esc_html(wp_get_current_user()->user_email).'</small><a href="'.esc_url(add_query_arg('account_view','profile',$portal?:home_url('/'))).'">Min profil</a><a href="'.esc_url(add_query_arg('account_view','status',$portal?:home_url('/'))).'">Status</a><a href="'.esc_url($portal?:home_url('/')).'">Mine prosjekter og gallerier</a><a href="'.esc_url($reset).'">Endre passord</a><hr><a class="logout" href="'.esc_url($logout).'">Logg ut</a></div></div><script>document.addEventListener("click",function(e){var w=document.querySelector("[data-customer-account]");if(!w)return;var b=w.querySelector("button");if(b.contains(e.target)){w.classList.toggle("is-open");b.setAttribute("aria-expanded",w.classList.contains("is-open")?"true":"false");}else if(!w.contains(e.target)){w.classList.remove("is-open");b.setAttribute("aria-expanded","false");}});</script>'; } elseif($portal)echo '<a class="portal-link" href="'.esc_url($portal).'">Min portal</a>';echo '</div></div>';}
    private function brand_foot($s,$studio){$c=array_filter([$s['phone'],$s['email'],$s['website']]);echo '<div class="photo-signature">'.($s['profile_image_url']?'<img class="photo-avatar" src="'.esc_url($s['profile_image_url']).'" alt="">':'').'<div><strong>'.esc_html($s['photographer_name']?:$studio).'</strong>'.($s['about']?'<span>'.esc_html($s['about']).'</span>':'').($c?'<small>'.esc_html(implode(' · ',$c)).'</small>':'').'</div></div><footer class="platform-footer">Powered by Aurora Fotoportal · Utviklet av 9Ls1 Digital</footer>';}
    private function render_customer_login_gate($client,$return_url){
        $this->set_customer_auth_context($client);
        $settings=NLS1_Fotoportal_Admin::photographer_portal_settings((int)$client->account_id);
        $studio=$settings['studio_name']?:($settings['photographer_name']?:get_bloginfo('name'));
        $accent=sanitize_hex_color($settings['accent_color'])?:'#6f4bf2';
        $login_error='';

        // Never leave the portal in a half-authenticated state. If WordPress has a
        // session that does not belong to this client, clear it and show a complete
        // Aurora login form instead of a blank/intermediate card.
        if(is_user_logged_in() && !NLS1_Fotoportal_Admin::client_user_authorized($client)){
            wp_logout();
            wp_set_current_user(0);
        }

        if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['aurora_customer_login'])){
            if(!isset($_POST['aurora_customer_login_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aurora_customer_login_nonce'])),'aurora_customer_login')){
                $login_error='Innloggingen kunne ikke bekreftes. Prøv igjen.';
            }else{
                $creds=['user_login'=>sanitize_text_field(wp_unslash($_POST['log']??'')),'user_password'=>(string)($_POST['pwd']??''),'remember'=>!empty($_POST['rememberme'])];
                $signed=wp_signon($creds,is_ssl());
                if(is_wp_error($signed)){
                    $login_error='Feil e-post/brukernavn eller passord.';
                }elseif(!NLS1_Fotoportal_Admin::repair_client_user_authorization($client,(int)$signed->ID)){
                    wp_logout(); wp_set_current_user(0);
                    $login_error='Denne innloggingen har ikke tilgang til denne kundeportalen.';
                }else{
                    wp_set_current_user((int)$signed->ID);
                    wp_set_auth_cookie((int)$signed->ID,!empty($_POST['rememberme']),is_ssl());
                    $this->set_customer_auth_context($client);
                    $canonical=NLS1_Fotoportal_Admin::customer_portal_url((int)$client->id);
                    wp_safe_redirect($canonical?:$return_url); exit;
                }
            }
        }

        // If a valid customer session already exists, never render the login gate.
        if(is_user_logged_in() && NLS1_Fotoportal_Admin::client_user_authorized($client)){
            $canonical=NLS1_Fotoportal_Admin::customer_portal_url((int)$client->id);
            wp_safe_redirect($canonical?:$return_url); exit;
        }

        status_header(200); nocache_headers();
        echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Logg inn – '.esc_html($studio).'</title><style>:root{--a:'.esc_attr($accent).'}'.$this->brand_css().'.login-shell{min-height:78vh;display:grid;place-items:center;padding:30px}.login-card{width:min(430px,100%);background:#fff;border:1px solid #e8e2eb;border-radius:18px;padding:28px;box-shadow:0 16px 45px rgba(45,35,55,.08)}.login-card h1{margin:0 0 8px}.login-card p{color:#746a7a}.login-card label{display:block;margin:14px 0 5px;font-weight:700}.login-card input[type=text],.login-card input[type=password]{width:100%;padding:12px;border:1px solid #dcd5df;border-radius:9px}.login-card input[type=submit]{width:100%;margin-top:16px;border:0;border-radius:10px;padding:12px;background:var(--a);color:#fff;font-weight:800;cursor:pointer}.login-card .login-remember label{font-weight:400}.wrong{background:#fff4f4;border:1px solid #f2cccc;padding:12px;border-radius:9px;margin-bottom:14px}</style></head><body>';
        $this->brand_head($settings,$studio);
        echo '<main class="login-shell"><section class="login-card"><h1>Kundeinnlogging</h1><p>Logg inn for å åpne din private bildeportal.</p>';
        if($login_error)echo '<div class="wrong">'.esc_html($login_error).'</div>';
        echo '<form method="post" action="'.esc_url($return_url).'">';
        wp_nonce_field('aurora_customer_login','aurora_customer_login_nonce');
        echo '<input type="hidden" name="aurora_customer_login" value="1"><label for="aurora-log">E-post / brukernavn</label><input id="aurora-log" type="text" name="log" autocomplete="username" required><label for="aurora-pwd">Passord</label><input id="aurora-pwd" type="password" name="pwd" autocomplete="current-password" required><p class="login-remember"><label><input type="checkbox" name="rememberme" value="forever"> Husk meg</label></p><input type="submit" value="Logg inn"></form>';
        $reset_url=add_query_arg(['fotoportal_password'=>1,'token'=>rawurlencode((string)$client->portal_token)],home_url('/'));
        echo '<p><a href="'.esc_url($reset_url).'">Glemt passord?</a></p></section></main>';
        $this->brand_foot($settings,$studio); echo '</body></html>'; exit;
    }

    private function customer_password_page_shell($client,$title,$content){
        $settings=NLS1_Fotoportal_Admin::photographer_portal_settings((int)$client->account_id);
        $studio=$settings['studio_name']?:($settings['photographer_name']?:get_bloginfo('name'));
        $accent=sanitize_hex_color($settings['accent_color'])?:'#6f4bf2';
        status_header(200); nocache_headers();
        echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($title).' – '.esc_html($studio).'</title><style>:root{--a:'.esc_attr($accent).'}'.$this->brand_css().'.login-shell{min-height:78vh;display:grid;place-items:center;padding:30px}.login-card{width:min(460px,100%);background:#fff;border:1px solid #e8e2eb;border-radius:18px;padding:28px;box-shadow:0 16px 45px rgba(45,35,55,.08)}.login-card h1{margin:0 0 8px}.login-card p{color:#746a7a;line-height:1.55}.login-card label{display:block;margin:14px 0 5px;font-weight:700}.login-card input[type=password]{width:100%;padding:12px;border:1px solid #dcd5df;border-radius:9px}.aurora-btn{display:inline-block;width:100%;margin-top:14px;border:0;border-radius:10px;padding:12px;background:var(--a);color:#fff;font-weight:800;cursor:pointer;text-align:center;text-decoration:none}.notice{padding:12px 14px;border-radius:10px;margin:14px 0}.notice.ok{background:#f0fbf5;border:1px solid #c8ead7;color:#17613b}.notice.err{background:#fff4f4;border:1px solid #f2cccc;color:#8d2929}.back{display:inline-block;margin-top:16px;color:var(--a);font-weight:700;text-decoration:none}</style></head><body>';
        $this->brand_head($settings,$studio);
        echo '<main class="login-shell"><section class="login-card"><h1>'.esc_html($title).'</h1>'.$content.'</section></main>';
        $this->brand_foot($settings,$studio); echo '</body></html>'; exit;
    }

    private function photographer_auth_shell($account,$title,$content){
        $branding=class_exists('NLS1_Aurora_Account_Platform') ? NLS1_Aurora_Account_Platform::platform_branding() : [];
        $bg_desktop=$branding['photographer_login_bg_desktop']??(NLS1_FOTOPORTAL_PLUGIN_URL.'assets/aurora-login-background.png');
        $bg_mobile=$branding['photographer_login_bg_mobile']??'';
        if(!$bg_mobile) $bg_mobile=$bg_desktop;

        status_header(200); nocache_headers();
        echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($title).' – Aurora Fotoportal</title><style>
        *{box-sizing:border-box}
        html,body{margin:0;width:100%;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#03111d;color:#fff}
        body{min-height:100vh;background-image:linear-gradient(180deg,rgba(0,10,22,.12),rgba(0,10,22,.22)),url("'.esc_url($bg_desktop).'");background-position:center center;background-size:cover;background-repeat:no-repeat;background-attachment:fixed}
        .aurora-auth-shell{width:100%;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:42px 20px}
        .aurora-auth-brand{text-align:center;margin:0 0 22px;text-shadow:0 4px 20px rgba(0,0,0,.5)}
        .aurora-auth-mark{font-size:54px;line-height:1;font-weight:200;letter-spacing:.12em;color:#fff;margin-bottom:4px}
        .aurora-auth-mark span{background:linear-gradient(90deg,#21e6b7,#38c6f4);-webkit-background-clip:text;background-clip:text;color:transparent}
        .aurora-auth-brand h2{margin:0;font-size:38px;letter-spacing:.28em;font-weight:300;padding-left:.28em}
        .aurora-auth-brand p{margin:8px 0 0;color:#4cf0d4;font-size:10px;font-weight:850;letter-spacing:.22em;text-transform:uppercase}
        .aurora-auth-product{display:flex;align-items:center;justify-content:center;gap:14px;margin:12px auto 0;color:#4cf0d4;font-size:11px;letter-spacing:.30em;font-weight:850;text-transform:uppercase}
        .aurora-auth-product:before,.aurora-auth-product:after{content:"";display:block;width:62px;height:1px;background:#42e5d0}
        .login-card{width:min(500px,100%);padding:28px 30px 30px;border:1px solid rgba(78,230,220,.56);border-radius:20px;background:rgba(2,24,38,.80);box-shadow:0 24px 70px rgba(0,0,0,.46);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}
        .login-card .eyebrow{font-size:10px;font-weight:900;letter-spacing:.16em;color:#43e9d0;text-transform:uppercase}
        .login-card h1{margin:7px 0 8px;font-size:29px;line-height:1.15;color:#fff}
        .login-card p{margin:7px 0 15px;color:#c1cdd4;line-height:1.55}
        .login-card label{display:block;margin:14px 0 6px;color:#d7e4e9;font-size:12px;font-weight:750;letter-spacing:.04em}
        .login-card input[type=password],.login-card input[type=text],.login-card input[type=email]{width:100%;height:52px;padding:0 15px;border:1px solid rgba(118,178,194,.45);border-radius:8px;background:rgba(0,12,24,.62);color:#fff;font:inherit;outline:none}
        .login-card input:focus{border-color:#38e8d2;box-shadow:0 0 0 3px rgba(56,232,210,.12)}
        .login-card input[type=checkbox]{accent-color:#36e4cc}
        .login-card label:has(input[type=checkbox]){display:flex;align-items:center;gap:8px;font-size:13px;letter-spacing:0}
        .aurora-btn{display:inline-flex;width:100%;min-height:50px;margin-top:16px;align-items:center;justify-content:center;border:0;border-radius:8px;padding:12px 18px;background:linear-gradient(90deg,#16d0aa,#39c7f4);color:#fff;font-weight:850;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;text-align:center;text-decoration:none;box-shadow:0 8px 24px rgba(22,208,170,.18)}
        .aurora-btn:hover{filter:brightness(1.06)}
        .notice{padding:12px 14px;border-radius:9px;margin:14px 0;font-size:13px}
        .notice.ok{background:rgba(31,160,111,.18);border:1px solid rgba(86,221,164,.4);color:#bff7dc}
        .notice.err{background:rgba(188,48,69,.18);border:1px solid rgba(255,111,133,.35);color:#ffd1d8}
        .back{display:inline-block;margin-top:16px;color:#42ecd5;font-weight:700;text-decoration:none}
        .aurora-powered{margin-top:28px;text-align:center;color:#c2cdd3;font-size:11px;text-shadow:0 2px 10px rgba(0,0,0,.7)}
        .aurora-powered strong{display:block;margin-top:4px;color:#45f0d0;font-size:15px;letter-spacing:.04em}
        @media(max-width:700px){
            body{background-image:linear-gradient(180deg,rgba(0,10,22,.10),rgba(0,10,22,.22)),url("'.esc_url($bg_mobile).'");background-attachment:scroll;background-position:center center}
            .aurora-auth-shell{justify-content:flex-start;padding:8vh 14px 28px}
            .aurora-auth-brand{margin-bottom:17px}
            .aurora-auth-mark{font-size:42px}
            .aurora-auth-brand h2{font-size:28px}
            .aurora-auth-product:before,.aurora-auth-product:after{width:38px}
            .login-card{padding:23px 20px;border-radius:17px}
            .login-card h1{font-size:25px}
        }
        </style></head><body>';
        echo '<main class="aurora-auth-shell"><header class="aurora-auth-brand"><div class="aurora-auth-mark"><span>∿</span></div><h2>AURORA</h2><p>Intelligent Business Platform</p><div class="aurora-auth-product">Fotoportal</div></header><section class="login-card"><div class="eyebrow">Aurora · Fotograf</div><h1>'.esc_html($title).'</h1>'.$content.'</section><div class="aurora-powered">Powered by<strong>9Ls1 Digital</strong></div></main>';
        echo '</body></html>'; exit;
    }

    public function render_photographer_password_page(){
        if(!get_query_var('aurora_photographer_password')) return;
        $account_id=absint($_REQUEST['account_id']??0);
        $account=NLS1_Aurora_Account_Platform::get_account($account_id);
        if(!$account){status_header(404);echo '<h1>Ugyldig invitasjon</h1>';exit;}
        $key=sanitize_text_field(wp_unslash($_REQUEST['key']??''));
        $login=sanitize_user(wp_unslash($_REQUEST['login']??''));
        $user=($key&&$login)?check_password_reset_key($key,$login):new WP_Error('invalid_key','Ugyldig lenke');
        $valid=!is_wp_error($user)
            && ((int)get_user_meta($user->ID,'aurora_fotoportal_account_id',true)===$account_id)
            && (in_array('aurora_photographer',(array)$user->roles,true) || $user->has_cap('aurora_fotoportal_photographer'));
        if(!$valid){
            $content='<div class="notice err"><strong>Invitasjonslenken er ugyldig eller utløpt.</strong></div><p>Be Aurora-administrator sende invitasjonen på nytt.</p>';
            $this->photographer_auth_shell($account,'Lenken kan ikke brukes',$content);
        }
        $err='';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            check_admin_referer('aurora_photographer_password_reset');
            $p1=(string)($_POST['pass1']??''); $p2=(string)($_POST['pass2']??'');
            if(strlen($p1)<8) $err='Passordet må inneholde minst 8 tegn.';
            elseif($p1!==$p2) $err='Passordene er ikke like.';
            else {
                reset_password($user,$p1);
                update_user_meta($user->ID,'aurora_fotoportal_password_activated_at',current_time('mysql'));
                $login_url=add_query_arg([
                    'aurora_photographer_login'=>1,
                    'account_id'=>$account_id,
                    'login'=>$user->user_email ?: $user->user_login
                ],home_url('/'));
                $content='<div class="notice ok"><strong>Passordet er lagret.</strong></div><p>Fotografkontoen din er nå aktivert. Logg inn for å starte førstegangsoppsettet av Aurora Fotoportal.</p><a class="aurora-btn" href="'.esc_url($login_url).'">Gå til fotografinnlogging</a>';
                $this->photographer_auth_shell($account,'Kontoen er aktivert',$content);
            }
        }
        $err_html=$err?'<div class="notice err">'.esc_html($err).'</div>':'';
        $action=add_query_arg(['aurora_photographer_password'=>1,'account_id'=>$account_id,'key'=>rawurlencode($key),'login'=>rawurlencode($login)],home_url('/'));
        ob_start(); echo $err_html.'<p>Velg passordet du vil bruke når du logger inn som fotograf.</p><form method="post" action="'.esc_url($action).'"><label>Nytt passord</label><input type="password" name="pass1" minlength="8" autocomplete="new-password" required><label>Gjenta nytt passord</label><input type="password" name="pass2" minlength="8" autocomplete="new-password" required>'; wp_nonce_field('aurora_photographer_password_reset'); echo '<button class="aurora-btn" type="submit">Lagre passord</button></form>'; $content=ob_get_clean();
        $this->photographer_auth_shell($account,'Opprett passord',$content);
    }

    public function render_photographer_login_page(){
        if(!get_query_var('aurora_photographer_login')) return;
        $account_id=absint($_REQUEST['account_id']??0);
        $account=NLS1_Aurora_Account_Platform::get_account($account_id);
        if(!$account){status_header(404);echo '<h1>Fotografkonto finnes ikke</h1>';exit;}
        if(is_user_logged_in()){
            $current=wp_get_current_user();
            if((int)get_user_meta($current->ID,'aurora_fotoportal_account_id',true)===$account_id && (in_array('aurora_photographer',(array)$current->roles,true)||$current->has_cap('aurora_fotoportal_photographer'))){wp_safe_redirect(NLS1_Photographer_Workspace::url('dashboard'));exit;}
            wp_logout();
        }
        $login=sanitize_text_field(wp_unslash($_REQUEST['login']??'')); $err='';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            check_admin_referer('aurora_photographer_login');
            $creds=['user_login'=>sanitize_text_field(wp_unslash($_POST['user_login']??'')),'user_password'=>(string)($_POST['user_password']??''),'remember'=>!empty($_POST['remember'])];
            $signed=wp_signon($creds,is_ssl());
            if(is_wp_error($signed)) $err='E-post/brukernavn eller passord er ikke riktig.';
            else {
                $belongs=(int)get_user_meta($signed->ID,'aurora_fotoportal_account_id',true)===$account_id;
                $photographer=in_array('aurora_photographer',(array)$signed->roles,true)||$signed->has_cap('aurora_fotoportal_photographer');
                if(!$belongs||!$photographer){wp_logout();$err='Denne innloggingen har ikke tilgang til denne fotografkontoen.';}
                else {
                    // Repair legacy/test users that previously existed as WooCommerce
                    // customers. Photographer owners must use the Aurora photographer role.
                    if (
                        !$signed->has_cap('manage_options')
                        && !$signed->has_cap('manage_woocommerce')
                        && !in_array('aurora_photographer',(array)$signed->roles,true)
                    ) {
                        $signed->set_role('aurora_photographer');
                    }
                    wp_safe_redirect(NLS1_Photographer_Workspace::url('dashboard'));exit;
                }
            }
        }
        $err_html=$err?'<div class="notice err">'.esc_html($err).'</div>':'';
        $action=add_query_arg(['aurora_photographer_login'=>1,'account_id'=>$account_id],home_url('/'));
        ob_start(); echo $err_html.'<p>Logg inn for å åpne ditt fotograf-Workspace.</p><form method="post" action="'.esc_url($action).'"><label>E-post / brukernavn</label><input type="text" name="user_login" value="'.esc_attr($login).'" autocomplete="username" required><label>Passord</label><input type="password" name="user_password" autocomplete="current-password" required><label style="font-weight:400"><input type="checkbox" name="remember" value="1"> Husk meg</label>'; wp_nonce_field('aurora_photographer_login'); echo '<button class="aurora-btn" type="submit">Logg inn</button></form>'; $content=ob_get_clean();
        $this->photographer_auth_shell($account,'Fotografinnlogging',$content);
    }

    private $aurora_mail_failure = '';

    public function capture_mail_failure($error){
        if(is_wp_error($error)){
            $this->aurora_mail_failure = $error->get_error_message();
        }
    }

    private function password_mail_debug_log($client,$email,$user,$subject,$sent,$detail=''){
        $uid=$user instanceof WP_User ? (int)$user->ID : 0;
        $login=$user instanceof WP_User ? $user->user_login : '';
        $status=$sent?'SUCCESS':'FAILED';
        $line=sprintf('[Aurora Fotoportal][Password reset] %s | client_id=%d | email=%s | user_id=%d | login=%s | subject=%s | wp_mail=%s%s',
            current_time('mysql'),(int)$client->id,$email?:'(empty)',$uid,$login?:'(none)',$subject?:'(none)',$status,$detail?' | detail='.$detail:'');
        error_log($line);
        return $line;
    }

    public function render_customer_password_page(){
        if(!get_query_var('fotoportal_password'))return;
        $token=sanitize_text_field($_REQUEST['token']??'');
        $client=NLS1_Fotoportal_Admin::get_public_client_by_token($token);
        if($client)$this->set_customer_auth_context($client);
        if(!$client){status_header(404);echo '<h1>Ugyldig lenke</h1>';exit;}
        $portal_url=NLS1_Fotoportal_Admin::customer_portal_url((int)$client->id);
        $email=NLS1_Fotoportal_Admin::client_portal_email((int)$client->id);
        $user=$email?get_user_by('email',$email):false;
        // Self-heal legacy customers: the contact email is the source of truth for the portal login.
        if(!$user && $email){$uid=NLS1_Fotoportal_Admin::ensure_client_portal_user((int)$client->id);if($uid)$user=get_user_by('id',$uid);}
        elseif($user){NLS1_Fotoportal_Admin::ensure_client_portal_user((int)$client->id);}
        $mode=sanitize_key($_REQUEST['mode']??'request');

        if($_SERVER['REQUEST_METHOD']==='POST' && $mode==='request'){
            check_admin_referer('9ls1_customer_password_request');
            $sent=false; $detail=''; $subject='';
            $this->aurora_mail_failure='';
            if($user){
                $key=get_password_reset_key($user);
                if(!is_wp_error($key)){
                    $settings=NLS1_Fotoportal_Admin::photographer_portal_settings((int)$client->account_id);
                    $studio=$settings['studio_name']?:($settings['photographer_name']?:get_bloginfo('name'));
                    $reset=add_query_arg(['fotoportal_password'=>1,'mode'=>'reset','token'=>rawurlencode($token),'key'=>rawurlencode($key),'login'=>rawurlencode($user->user_login)],home_url('/'));
                    $subject='Opprett nytt passord – '.$studio;
                    $body="Hei,\n\nVi har mottatt en forespørsel om nytt passord til din private bildeportal.\n\nOpprett nytt passord her:\n".$reset."\n\nHvis du ikke ba om dette, kan du ignorere e-posten.\n\nMed vennlig hilsen\n".$studio;
                    $headers=['Content-Type: text/plain; charset=UTF-8']; if(!empty($settings['email']))$headers[]='Reply-To: '.$settings['email'];
                    $sent=wp_mail($email,$subject,$body,$headers);
                    if(!$sent){$detail=$this->aurora_mail_failure?:'wp_mail returned false';}
                } else {$detail=$key->get_error_message();}
            } else {$detail='No WordPress user matched the customer email address';}
            $this->password_mail_debug_log($client,$email,$user,$subject,$sent,$detail);
            if($sent){$masked=$email?preg_replace('/(^.).*(@.*$)/','$1***$2',$email):'din registrerte e-post';$content='<div class="notice ok"><strong>E-post sendt.</strong><br>Vi har sendt en lenke for å opprette nytt passord til '.esc_html($masked).'.</div><p>Sjekk også søppelpost dersom meldingen ikke dukker opp etter kort tid.</p><a class="back" href="'.esc_url($portal_url).'">← Tilbake til innlogging</a>';}
            else {$content='<div class="notice err"><strong>E-posten kunne ikke sendes.</strong><br>Aurora forsøkte å sende meldingen, men WordPress/serveren rapporterte en feil.</div><p>Kontroller SMTP/e-postoppsettet på nettstedet. Dette er nå logget i serverens PHP/WordPress-logg.</p><a class="back" href="'.esc_url($portal_url).'">← Tilbake til innlogging</a>';}
            $this->customer_password_page_shell($client,'Glemt passord',$content);
        }

        if($mode==='reset'){
            $key=sanitize_text_field($_REQUEST['key']??''); $login=sanitize_user(wp_unslash($_REQUEST['login']??''));
            $reset_user=($key&&$login)?check_password_reset_key($key,$login):new WP_Error('invalid_key','Ugyldig lenke');
            if(is_wp_error($reset_user)){$content='<div class="notice err"><strong>Lenken er ugyldig eller utløpt.</strong></div><p>Be om en ny passordlenke.</p><a class="aurora-btn" href="'.esc_url(add_query_arg(['fotoportal_password'=>1,'token'=>rawurlencode($token)],home_url('/'))).'">Be om ny lenke</a>'; $this->customer_password_page_shell($client,'Opprett nytt passord',$content);}
            if($_SERVER['REQUEST_METHOD']==='POST'){
                check_admin_referer('9ls1_customer_password_reset');
                $p1=(string)($_POST['pass1']??''); $p2=(string)($_POST['pass2']??'');
                if(strlen($p1)<8){$err='Passordet må inneholde minst 8 tegn.';} elseif($p1!==$p2){$err='Passordene er ikke like.';} else {reset_password($reset_user,$p1);$content='<div class="notice ok"><strong>Passordet er endret.</strong></div><p>Du kan nå logge inn i din private bildeportal med det nye passordet.</p><a class="aurora-btn" href="'.esc_url($portal_url).'">Gå til innlogging</a>'; $this->customer_password_page_shell($client,'Nytt passord lagret',$content);}
            }
            $err_html=!empty($err)?'<div class="notice err">'.esc_html($err).'</div>':'';
            $action=add_query_arg(['fotoportal_password'=>1,'mode'=>'reset','token'=>rawurlencode($token),'key'=>rawurlencode($key),'login'=>rawurlencode($login)],home_url('/'));
            ob_start(); echo $err_html.'<p>Velg et nytt passord for kundeportalen.</p><form method="post" action="'.esc_url($action).'"><label>Nytt passord</label><input type="password" name="pass1" minlength="8" required><label>Gjenta nytt passord</label><input type="password" name="pass2" minlength="8" required>'; wp_nonce_field('9ls1_customer_password_reset'); echo '<button class="aurora-btn" type="submit">Lagre nytt passord</button></form>'; $content=ob_get_clean();
            $this->customer_password_page_shell($client,'Opprett nytt passord',$content);
        }

        if(!$user){$content='<div class="notice err">Det finnes ingen kundeinnlogging knyttet til denne portalen ennå.</div><a class="back" href="'.esc_url($portal_url).'">← Tilbake</a>'; $this->customer_password_page_shell($client,'Glemt passord',$content);}
        ob_start(); echo '<p>Vi sender en sikker lenke til e-postadressen som er registrert på kundeportalen. Der kan du opprette et nytt passord.</p><form method="post" action="'.esc_url(add_query_arg(['fotoportal_password'=>1,'mode'=>'request','token'=>rawurlencode($token)],home_url('/'))).'">'; wp_nonce_field('9ls1_customer_password_request'); echo '<button class="aurora-btn" type="submit">Send lenke for nytt passord</button></form><a class="back" href="'.esc_url($portal_url).'">← Tilbake til innlogging</a>'; $content=ob_get_clean();
        $this->customer_password_page_shell($client,'Glemt passord',$content);
    }

    public function render_customer_portal(){if(!get_query_var('fotoportal_customer'))return;$c=NLS1_Fotoportal_Admin::get_public_client_by_token(sanitize_text_field($_GET['token']??''));status_header($c?200:404);nocache_headers();if(!$c){echo '<h1>Portalen ble ikke funnet</h1>';exit;}$this->set_customer_auth_context($c);$return_url=home_url(add_query_arg([],$_SERVER['REQUEST_URI']??'/'));if(!is_user_logged_in()||!NLS1_Fotoportal_Admin::client_user_authorized($c)){$this->render_customer_login_gate($c,$return_url);}$s=NLS1_Fotoportal_Admin::photographer_portal_settings($c->account_id);$gs=NLS1_Fotoportal_Admin::get_public_client_projects_and_galleries($c);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($studio).' – Kundeportal</title><style>:root{--a:'.$a.'}'.$this->brand_css().'.shell{max-width:1240px;margin:auto;padding:34px 20px 55px}.welcome h1{font-size:34px;margin:0 0 8px}.welcome p{color:#817889}.project{margin:30px 0}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.card{background:#fff;border:1px solid #e9e4ed;border-radius:14px;overflow:hidden;text-decoration:none;color:inherit}.cover{aspect-ratio:16/10;background:#e9e6ec}.cover img{width:100%;height:100%;object-fit:cover}.cb{padding:15px}.cb strong{display:block}.cb p{color:#817889}.open{color:var(--a);font-weight:700}@media(max-width:800px){.cards{grid-template-columns:1fr 1fr}}@media(max-width:520px){.cards{grid-template-columns:1fr}}</style></head><body>';$this->brand_head($s,$studio,NLS1_Fotoportal_Admin::customer_portal_url((int)$c->id),$c);$chs=NLS1_Fotoportal_Admin::public_customer_hero_settings($c);$chi=[];foreach($gs as $xx)foreach($xx['galleries'] as $gg){$gi=NLS1_Fotoportal_Admin::get_public_gallery_images($gg);$chi=array_merge($chi,$gi);}$hero=NLS1_Fotoportal_Admin::hero_image_url($chs,$chi,$s['cover_image_url']??'');echo '<section class="photo-hero size-'.esc_attr($chs['size']).' '.($hero?'':'no-image').'"'.($hero?' style="background-image:url('.esc_url($hero).');background-position:'.(int)$chs['focal_x'].'% '.(int)$chs['focal_y'].'%"':'').'><span class="photo-hero-overlay" style="background:'.esc_attr($chs['overlay_color']).';opacity:'.esc_attr($chs['overlay_opacity']/100).'"></span><div class="photo-hero-content"><div class="hero-kicker">'.esc_html($studio).'</div><h1>'.esc_html($c->client_name).'</h1><p>Velkommen til din bildeportal</p></div></section><main class="shell">'; $pc=NLS1_Fotoportal_Admin::get_primary_contact((int)$c->id); $all_signed=true;$all_paid=true;$has_gallery=false; foreach($gs as $sx){$st=NLS1_Fotoportal_Admin::public_project_delivery_state((int)$sx['project']->project_id,(int)$c->account_id);if(empty($st['contract_signed']))$all_signed=false;if(empty($st['paid']))$all_paid=false;if(!empty($st['gallery']))$has_gallery=true;} $account_view=sanitize_key($_GET['account_view']??'');
        if($account_view==='profile'){
            $phone=$pc->phone??($c->phone??''); $address=$c->address??''; $postal=$c->postal_code??($c->zip??''); $city=$c->city??'';
            echo '<section class="account-view" style="background:#fff;border:1px solid #e9e4ed;border-radius:14px;padding:28px;margin:0 0 24px"><a class="open" href="'.esc_url(NLS1_Fotoportal_Admin::customer_portal_url((int)$c->id)).'">← Tilbake til portalen</a><h2>Min profil</h2><div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 28px;max-width:760px"><p><small>Navn</small><br><strong>'.esc_html($pc&&$pc->contact_name?$pc->contact_name:$c->client_name).'</strong></p><p><small>E-post</small><br><strong>'.esc_html($pc&&$pc->email?$pc->email:$c->email).'</strong></p><p><small>Telefon</small><br><strong>'.esc_html($phone?:'—').'</strong></p><p><small>Adresse</small><br><strong>'.esc_html(trim($address.' '.$postal.' '.$city)?:'—').'</strong></p></div><p style="color:#817889">Kontakt fotografen dersom opplysningene må endres.</p></section>';
        } elseif($account_view==='status'){
            echo '<section class="account-view" style="background:#fff;border:1px solid #e9e4ed;border-radius:14px;padding:28px;margin:0 0 24px"><a class="open" href="'.esc_url(NLS1_Fotoportal_Admin::customer_portal_url((int)$c->id)).'">← Tilbake til portalen</a><h2>Status</h2><p style="color:#817889">Status for dine prosjekter og leveranser.</p>';
            $status_projects=NLS1_Fotoportal_Admin::get_public_client_project_statuses($c);
            foreach($status_projects as $sp){
                $st=NLS1_Fotoportal_Admin::public_project_delivery_state((int)$sp->project_id,(int)$c->account_id);
                $items=[
                    ['Kontrakt',!empty($st['contract_signed']),'Signert',(!empty($st['contract_registered'])?'Venter på signering':'Ikke registrert')],
                    ['Betaling',!empty($st['paid']),'Betalt','Venter'],
                    ['Galleri',!empty($st['gallery']),((int)$st['gallery_count']).' galleri'.((int)$st['gallery_count']===1?'':'er').' tilgjengelig','Venter'],
                    ['Leveranse',!empty($st['portal_ready']),'Tilgjengelig','Venter'],
                ];
                echo '<div style="border-top:1px solid #eee7f0;padding:18px 0"><h3 style="margin:0 0 12px">'.esc_html($sp->project_name).'</h3><div class="interaction-strip">';
                foreach($items as $it){echo '<span>'.esc_html($it[0]).' <b style="color:'.($it[1]?'#159455':'#b48a22').'">'.esc_html($it[1]?('✓ '.$it[2]):$it[3]).'</b></span>';}
                if(!empty($st['documents'])) echo '<span>Dokumenter <b style="color:#159455">✓ '.(int)$st['document_count'].'</b></span>';
                echo '</div></div>';
            }
            echo '</section>';
        }
        echo '<div id="aurora-prosjekter"></div>';foreach($gs as $x){echo '<section class="project"><h2>'.esc_html($x['project']->project_name).'</h2><div class="cards">';foreach($x['galleries'] as $g){$ic=NLS1_Fotoportal_Admin::gallery_interaction_counts((int)$g->gallery_id,(int)$g->account_id);echo '<a class="card" href="'.esc_url($g->public_url).'"><div class="cover">'.($g->cover_url?'<img src="'.esc_url($g->cover_url).'" alt="">':'').'</div><div class="cb"><strong>'.esc_html($g->gallery_title).'</strong><p>'.esc_html(!empty($g->gallery_description)?$g->gallery_description:((int)$g->original_count.' bilder')).'</p><div class="interaction-strip"><span>♡ <b>'.(int)$ic['favorites'].'</b></span><span>✓ <b>'.(int)$ic['approved'].'</b> valgt</span><span>💬 <b>'.(int)$ic['comments'].'</b></span></div>'.NLS1_Fotoportal_Admin::public_selection_status_badge($g).'<p class="open">Åpne galleri →</p></div></a>';}echo '</div></section>';}echo '</main>';$this->brand_foot($s,$studio);echo '</body></html>';exit;}
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
        $client=NLS1_Fotoportal_Admin::get_public_client_by_id_account((int)$g->client_id,(int)$g->account_id);
        if(!is_user_logged_in()||!$client||!NLS1_Fotoportal_Admin::client_user_authorized($client)||!NLS1_Fotoportal_Admin::public_project_portal_ready((int)$g->project_id,(int)$g->account_id))wp_send_json_error(['message'=>'Ingen tilgang til galleriet'],403);
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
            $text=trim(sanitize_textarea_field($_POST['comment']??''));if($text==='')wp_send_json_error(['message'=>'Beskriv ønsket redigering'],400);
            $wpdb->insert(self::safe_table('image_comments'),['image_id'=>$image_id,'gallery_id'=>$g->id,'project_id'=>$g->project_id,'client_id'=>$g->client_id,'user_email'=>$visitor,'comment_text'=>$text,'is_test'=>0,'created_at'=>current_time('mysql')]);$active=true;
        }else wp_send_json_error(['message'=>'Ukjent handling'],400);
        NLS1_Fotoportal_Admin::record_gallery_activity($g,$kind);
        wp_send_json_success(['active'=>$active,'state'=>$this->gallery_interaction_state($g,$visitor)]);
    }

    public function render_public_gallery(){
        if(!get_query_var('fotoportal_gallery'))return;
        $token=sanitize_text_field($_GET['token']??'');$g=NLS1_Fotoportal_Admin::get_public_gallery_by_token($token);status_header($g?200:404);nocache_headers();if(!$g){echo '<h1>Galleriet ble ikke funnet</h1>';exit;}$c=NLS1_Fotoportal_Admin::get_public_client_by_id_account((int)$g->client_id,(int)$g->account_id);if(!$c){status_header(404);echo '<h1>Galleriet ble ikke funnet</h1>';exit;}$this->set_customer_auth_context($c);$return_url=home_url(add_query_arg([],$_SERVER['REQUEST_URI']??'/'));if(!is_user_logged_in()||!NLS1_Fotoportal_Admin::client_user_authorized($c)){$this->render_customer_login_gate($c,$return_url);}if(!NLS1_Fotoportal_Admin::public_project_portal_ready((int)$g->project_id,(int)$g->account_id)){status_header(403);echo '<h1>Galleriet er ikke tilgjengelig</h1><p>Prosjektet er ikke frigitt for levering.</p>';exit;}
        $s=NLS1_Fotoportal_Admin::photographer_portal_settings($g->account_id);$studio=$s['studio_name']?:($s['photographer_name']?:get_bloginfo('name'));$a=sanitize_hex_color($s['accent_color'])?:'#6f4bf2';$portal=NLS1_Fotoportal_Admin::customer_portal_url($g->client_id);$ims=NLS1_Fotoportal_Admin::get_public_gallery_images($g);$state=$this->gallery_interaction_state($g,'');
        echo '<!doctype html><html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($g->gallery_title).' – '.esc_html($studio).'</title><style>:root{--a:'.$a.'}'.$this->brand_css().'.shell{max-width:1500px;margin:auto;padding:28px 20px 55px}.gh{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:20px}.ey{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--a);font-weight:700}.gh h2{font-size:clamp(28px,4vw,46px);margin:6px 0}.meta{color:#81788a}.masonry{column-count:4;column-gap:10px}.item{break-inside:avoid;margin:0 0 10px;position:relative;overflow:hidden;border-radius:8px;background:#eee}.item img{display:block;width:100%;height:auto;cursor:zoom-in}.image-actions{position:absolute;left:50%;bottom:10px;transform:translate(-50%,8px);display:flex;gap:5px;opacity:0;transition:.16s;z-index:3}.item:hover .image-actions,.item:focus-within .image-actions{opacity:1;transform:translate(-50%,0)}.image-action{width:32px;height:32px;border-radius:50%;border:1px solid rgba(35,31,39,.12);background:rgba(255,255,255,.88);box-shadow:0 2px 9px rgba(0,0,0,.13);font-size:15px;line-height:1;cursor:pointer;color:#39333d;backdrop-filter:blur(5px)}.image-action:hover{background:#fff}.image-action:disabled{cursor:default;opacity:.72}.image-action.is-active,.image-action.comment.has-comments{background:#342f38;color:#fff;border-color:#342f38}.comment-box{display:none;position:absolute;left:10px;right:10px;bottom:50px;background:rgba(255,255,255,.98);padding:11px;border-radius:10px;z-index:4;box-shadow:0 9px 30px rgba(0,0,0,.2)}.comment-box.is-open{display:block}.comment-list{max-height:135px;overflow:auto;margin:0 0 8px}.comment-entry{font-size:12px;line-height:1.4;color:#514a55;padding:7px 0;border-bottom:1px solid #eee9ef;white-space:pre-wrap}.comment-empty{font-size:12px;color:#8a818d;margin:2px 0 8px}.comment-box textarea{width:100%;min-height:58px;resize:vertical;border:1px solid #ddd7e0;border-radius:7px;padding:8px;font:inherit}.comment-box button{margin-top:6px;border:0;background:#342f38;color:#fff;border-radius:7px;padding:7px 10px;font-weight:700;cursor:pointer}.interaction-filter{appearance:none;font:inherit;cursor:pointer;font-size:12px;background:#fff;border:1px solid #e8e2eb;border-radius:999px;padding:6px 10px;color:#746a7a}.interaction-filter b{color:var(--a)}.interaction-filter.is-active{border-color:#746a7a;color:#302a34;background:#f0edf2}.item.is-filtered-out{display:none!important}.filter-empty{display:none;text-align:center;padding:45px 20px;color:#81788a}.filter-empty.is-visible{display:block}.lightbox{position:fixed;inset:0;background:rgba(12,10,15,.94);display:none;align-items:center;justify-content:center;padding:28px;z-index:9999}.lightbox.is-open{display:flex}.lightbox img{max-width:96vw;max-height:92vh}.close,.nav{position:absolute;border:0;background:#fff;cursor:pointer}.close{right:18px;top:18px;border-radius:50%;width:42px;height:42px;font-size:25px}.nav{top:50%;transform:translateY(-50%);width:48px;height:58px;border-radius:12px;font-size:34px}.prev{left:18px}.next{right:18px}@media(max-width:1100px){.masonry{column-count:3}}@media(max-width:760px){.masonry{column-count:2}.gh{flex-direction:column;align-items:start}.image-actions{opacity:1;transform:translate(-50%,0)}}@media(max-width:520px){.masonry{column-count:1}}</style></head><body>';
        $this->brand_head($s,$studio,$portal,$c);$ghs=NLS1_Fotoportal_Admin::public_gallery_hero_settings($g);$fallback='';if(!empty($ims)){$first=$ims[0];$fallback=$first->preview_url?:$first->thumbnail_url;}if(!$fallback)$fallback=$s['cover_image_url']??'';$galleryHero=NLS1_Fotoportal_Admin::hero_image_url($ghs,$ims,$fallback);
        echo '<section class="photo-hero size-'.esc_attr($ghs['size']).' '.($galleryHero?'':'no-image').'"'.($galleryHero?' style="background-image:url('.esc_url($galleryHero).');background-position:'.(int)$ghs['focal_x'].'% '.(int)$ghs['focal_y'].'%"':'').'><span class="photo-hero-overlay" style="background:'.esc_attr($ghs['overlay_color']).';opacity:'.esc_attr($ghs['overlay_opacity']/100).'"></span><div class="photo-hero-content"><div class="hero-kicker">'.esc_html($g->project_name).'</div><h1>'.esc_html($g->gallery_title).'</h1><p>'.esc_html(!empty($g->gallery_description)?$g->gallery_description:(count($ims).' bilder')).'</p></div></section><main class="shell"><header class="gh"><div><div class="ey">'.esc_html($studio).'</div><h2>'.esc_html($g->gallery_title).'</h2><div class="interaction-strip" data-gallery-filters><button type="button" class="interaction-filter is-active" data-filter="all">Alle bilder</button><button type="button" class="interaction-filter" data-filter="favorites">♡ <b data-count="favorites">'.$state['counts']['favorites'].'</b> favoritter</button><button type="button" class="interaction-filter" data-filter="approved">✓ <b data-count="approved">'.$state['counts']['approved'].'</b> valgt</button><button type="button" class="interaction-filter" data-filter="comments">💬 <b data-count="comments">'.$state['counts']['comments'].'</b> kommentarer</button></div></div><div class="meta">'.count($ims).' bilder</div></header>'.NLS1_Fotoportal_Admin::public_selection_submit_panel($g,$state).'<section class="masonry">';
        foreach($ims as $im){$src=$im->preview_url?:$im->thumbnail_url;if(!$src)continue;$approved=!empty($im->is_selected);echo '<figure class="item" data-image-id="'.(int)$im->id.'"><img loading="lazy" src="'.esc_url($src).'" data-full="'.esc_url($src).'" alt=""><div class="image-actions"><button type="button" class="image-action favorite" title="Favoritt" aria-label="Favoritt">♡</button><button type="button" class="image-action approve'.($approved?' is-active':'').'" title="Godkjenn / velg" aria-label="Godkjenn / velg">✓</button><button type="button" class="image-action comment" title="Kommentar til ønsket redigering" aria-label="Kommentar til ønsket redigering">💬</button></div><div class="comment-box"><div class="comment-list"></div><textarea placeholder="Beskriv ønsket redigering …"></textarea><button type="button">Lagre redigeringsønske</button></div></figure>';}
        echo '</section><div class="filter-empty" data-filter-empty>Ingen bilder i dette utvalget.</div></main>';$this->brand_foot($s,$studio);echo '<div class="lightbox" id="aurora-gallery-lightbox"><button class="close">×</button><button class="nav prev">‹</button><img src=""><button class="nav next">›</button></div>';
        $ajax=admin_url('admin-ajax.php');$nonce=wp_create_nonce('9ls1_gallery_interaction');
        echo '<script>(function(){var ajax='.wp_json_encode($ajax).',token='.wp_json_encode($token).',nonce='.wp_json_encode($nonce).',selectionLocked=false;var visitor=localStorage.getItem("aurora_gallery_visitor");if(!visitor){visitor="v"+Date.now().toString(36)+Math.random().toString(36).slice(2);localStorage.setItem("aurora_gallery_visitor",visitor)}function post(kind,id,comment,cb,err){var f=new FormData();f.append("action","9ls1_gallery_interaction");f.append("nonce",nonce);f.append("token",token);f.append("visitor",visitor);f.append("kind",kind);f.append("image_id",id);if(comment)f.append("comment",comment);fetch(ajax,{method:"POST",credentials:"same-origin",body:f}).then(function(r){return r.json()}).then(function(j){if(j.success){apply(j.data.state);if(cb)cb(j.data)}else{if(err)err();alert((j.data&&j.data.message)||"Kunne ikke lagre")}}).catch(function(){if(err)err();alert("Kunne ikke lagre handlingen")})}var lastState=null,currentFilter="all";function renderComments(it,comments){var list=it.querySelector(".comment-list");if(!list)return;list.innerHTML="";if(!comments||!comments.length){var empty=document.createElement("div");empty.className="comment-empty";empty.textContent="Ingen kommentarer ennå.";list.appendChild(empty);return}comments.forEach(function(c){var row=document.createElement("div");row.className="comment-entry";row.textContent=c.text||"";list.appendChild(row)})}function applyFilter(){if(!lastState)return;var visible=0;document.querySelectorAll(".item").forEach(function(it){var id=parseInt(it.dataset.imageId,10),show=currentFilter==="all"||(currentFilter==="favorites"&&lastState.favorites.indexOf(id)>-1)||(currentFilter==="approved"&&lastState.approved.indexOf(id)>-1)||(currentFilter==="comments"&&lastState.comments[id]&&lastState.comments[id].length);it.classList.toggle("is-filtered-out",!show);if(show)visible++});var empty=document.querySelector("[data-filter-empty]");if(empty)empty.classList.toggle("is-visible",visible===0)}function apply(st){lastState=st;["favorites","approved","comments"].forEach(function(k){var e=document.querySelector("[data-count=\""+k+"\"]");if(e)e.textContent=st.counts[k]||0});document.querySelectorAll(".item").forEach(function(it){var id=parseInt(it.dataset.imageId,10),fav=st.favorites.indexOf(id)>-1,app=st.approved.indexOf(id)>-1,comments=st.comments[id]||[];it.querySelector(".favorite").classList.toggle("is-active",fav);it.querySelector(".approve").classList.toggle("is-active",app);it.querySelector(".comment").classList.toggle("has-comments",comments.length>0);renderComments(it,comments)});applyFilter()}document.querySelectorAll("[data-gallery-filters] .interaction-filter").forEach(function(btn){btn.addEventListener("click",function(){currentFilter=btn.dataset.filter||"all";document.querySelectorAll("[data-gallery-filters] .interaction-filter").forEach(function(b){b.classList.toggle("is-active",b===btn)});applyFilter()})});document.querySelectorAll(".item").forEach(function(it){var id=it.dataset.imageId,img=it.querySelector("img"),box=it.querySelector(".comment-box");it.querySelector(".favorite").onclick=function(e){e.stopPropagation();if(selectionLocked)return;var b=this,was=b.classList.contains("is-active");b.classList.toggle("is-active",!was);post("favorite",id,null,null,function(){b.classList.toggle("is-active",was)})};it.querySelector(".approve").onclick=function(e){e.stopPropagation();if(selectionLocked)return;var b=this,was=b.classList.contains("is-active");b.classList.toggle("is-active",!was);post("approve",id,null,null,function(){b.classList.toggle("is-active",was)})};it.querySelector(".comment").onclick=function(e){e.stopPropagation();if(selectionLocked)return;box.classList.toggle("is-open")};box.onclick=function(e){e.stopPropagation()};box.querySelector("button").onclick=function(){if(selectionLocked)return;var t=box.querySelector("textarea");if(!t.value.trim())return;post("comment",id,t.value,function(){t.value="";box.classList.remove("is-open")})};img.onclick=function(){openLightbox(img)}});var submit=document.querySelector("[data-submit-selection]");if(submit){submit.addEventListener("click",function(){if(submit.disabled)return;var label=submit.textContent;submit.disabled=true;submit.textContent="Sender …";var f=new FormData();f.append("action","9ls1_submit_gallery_selection");f.append("nonce",nonce);f.append("token",token);fetch(ajax,{method:"POST",credentials:"same-origin",body:f}).then(function(r){return r.json()}).then(function(j){if(!j.success)throw new Error((j.data&&j.data.message)||"Kunne ikke sende redigeringsønsket");var panel=document.querySelector("[data-selection-submit-panel]");if(panel){panel.classList.add("is-submitted");panel.querySelector("[data-selection-submit-copy]").textContent="Redigeringsønsket er sendt til fotografen."}submit.textContent="Redigeringsønske sendt ✓";selectionLocked=false;setTimeout(function(){submit.disabled=false;submit.textContent="Send oppdatert redigeringsønske"},900);}).catch(function(e){submit.disabled=false;submit.textContent=label;alert(e.message||"Kunne ikke sende redigeringsønsket")})})}function load(){var f=new FormData();f.append("action","9ls1_gallery_interaction");f.append("nonce",nonce);f.append("token",token);f.append("visitor",visitor);f.append("kind","state");f.append("image_id",document.querySelector(".item")?document.querySelector(".item").dataset.imageId:0);fetch(ajax,{method:"POST",credentials:"same-origin",body:f}).then(function(r){return r.json()}).then(function(j){if(j.success)apply(j.data.state)})}load();var l=document.getElementById("aurora-gallery-lightbox"),im=l.querySelector("img"),x=[].slice.call(document.querySelectorAll(".item img")),i=0;function openLightbox(a){i=x.indexOf(a);im.src=a.dataset.full||a.src;l.classList.add("is-open")}function show(n){i=(n+x.length)%x.length;im.src=x[i].dataset.full||x[i].src}function close(){l.classList.remove("is-open")}l.querySelector(".prev").onclick=function(e){e.stopPropagation();show(i-1)};l.querySelector(".next").onclick=function(e){e.stopPropagation();show(i+1)};l.onclick=function(e){if(e.target===l||e.target.classList.contains("close"))close()};document.onkeydown=function(e){if(!l.classList.contains("is-open"))return;if(e.key==="Escape")close();if(e.key==="ArrowLeft")show(i-1);if(e.key==="ArrowRight")show(i+1)};})();</script></body></html>';exit;
    }

    public function handle_submit_gallery_selection(){
        check_ajax_referer('9ls1_gallery_interaction','nonce');
        global $wpdb;
        $token=sanitize_text_field($_POST['token']??'');
        $g=NLS1_Fotoportal_Admin::get_public_gallery_by_token($token);
        if(!$g) wp_send_json_error(['message'=>'Ugyldig galleri'],404);
        $client=NLS1_Fotoportal_Admin::get_public_client_by_id_account((int)$g->client_id,(int)$g->account_id);
        if(!is_user_logged_in()||!$client||!NLS1_Fotoportal_Admin::client_user_authorized($client)||!NLS1_Fotoportal_Admin::public_project_portal_ready((int)$g->project_id,(int)$g->account_id)) wp_send_json_error(['message'=>'Ingen tilgang til galleriet'],403);
        $counts=NLS1_Fotoportal_Admin::gallery_interaction_counts((int)$g->id,(int)$g->account_id);
        if((int)$counts['approved']<1) wp_send_json_error(['message'=>'Velg minst ett bilde før du sender redigeringsønsket.'],400);
        $now=current_time('mysql');
        $wpdb->update(NLS1_Fotoportal_Admin::table('galleries'),[
            'selection_status'=>'submitted','selection_submitted_at'=>$now,'updated_at'=>$now
        ],['id'=>(int)$g->id,'account_id'=>(int)$g->account_id],['%s','%s','%s'],['%d','%d']);
        NLS1_Fotoportal_Admin::record_gallery_activity($g,'selection_submitted');
        wp_send_json_success(['status'=>'submitted','submitted_at'=>$now,'counts'=>$counts]);
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

            NLS1_Fotoportal_Admin::maybe_release_customer_portal((int)$project->id);

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
