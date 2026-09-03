<?php
if (!defined('ABSPATH')) exit;

$branding = NLS1_Aurora_Account_Platform::platform_branding();
$user = wp_get_current_user();
$initials = '';
foreach (preg_split('/\s+/', trim($user->display_name)) as $part) {
    if ($part !== '') $initials .= strtoupper(substr($part, 0, 1));
}
$initials = substr($initials ?: 'PF', 0, 2);
$photographer_profile = NLS1_Fotoportal_Admin::photographer_portal_settings((int)$account->id);
$gallery_notifications = NLS1_Fotoportal_Admin::gallery_activity_notifications((int)$account->id);
$gallery_unread = count(array_filter($gallery_notifications, function($n){ return !empty($n['unread']); }));
$workspace_platform_name = trim((string)($branding['platform_name'] ?? 'Aurora'));
$workspace_platform_name = preg_replace('/\s+(?:Photo\s*Portal|Fotoportal)$/iu', '', $workspace_platform_name);
$workspace_platform_name = trim($workspace_platform_name) ?: 'Aurora';
$workspace_product_name = $workspace_platform_name . ' Fotoportal';
$support_mode = current_user_can('manage_options') && NLS1_Aurora_Account_Platform::support_context_account_id() === (int)$account->id;

$view_titles = [
    'dashboard' => ['Dashboard', 'Oversikt over Fotoportal og det som trenger oppfølging.'],
    'new' => ['Ny kunde / prosjekt', 'Start en ny kunde- og prosjektflyt.'],
    'customers' => ['Kunder', 'Administrer dine fotokunder.'],
    'projects' => ['Prosjekter', 'Fotooppdrag, status og prosjektflyt.'],
    'contracts' => ['Kontrakter', 'Avtaler, utsending og signeringsstatus.'],
    'documents' => ['Dokumenter', 'Dokumenter og underlag knyttet til prosjektene.'],
    'galleries' => ['Gallerier', 'Bildegallerier, proof og kundeleveranser.'],
    'selections' => ['Bildevalg', 'Samlet oversikt over kundenes favoritter, valgte bilder og kommentarer.'],
    'hq_delivery' => ['Leveranser', 'Ferdige leveranser og nedlastinger.'],
    'resources' => ['Ressurser', 'Maler, hjelp og arbeidsressurser.'],
    'shop' => ['Nettbutikk', 'Produkter og ordre.'],
    'settings' => ['Innstillinger', 'Din fotografkonto og Fotoportal-oppsett.'],
];
$title = $view_titles[$view] ?? $view_titles['dashboard'];

$dashboard_projects = NLS1_Fotoportal_Admin::get_projects(false);
$dashboard_contracts = NLS1_Fotoportal_Admin::get_contracts(false);
$dashboard_galleries = NLS1_Fotoportal_Admin::get_galleries(0, false);
$dashboard_active_projects = count(array_filter($dashboard_projects, function($p){ return !in_array(($p->status ?? ''), ['archived','completed'], true); }));
$dashboard_waiting_signatures = count(array_filter($dashboard_contracts, function($c){ return ($c->status ?? '') !== 'signed'; }));
$dashboard_active_galleries = count($dashboard_galleries);
$dashboard_to_delivery = 0;
$dashboard_unpaid = 0;
foreach ($dashboard_projects as $dp) {
    $state = NLS1_Fotoportal_Admin::project_delivery_state((int)$dp->id);
    if (!empty($state['contract_signed']) && !empty($state['gallery']) && empty($state['paid'])) $dashboard_to_delivery++;
    if (empty($state['paid'])) $dashboard_unpaid++;
}
$dashboard_edit_requests = count(array_filter($gallery_notifications, function($n){ return ($n['last_kind'] ?? '') === 'selection_submitted' && !empty($n['unread']); }));
$dashboard_resources = NLS1_Fotoportal_Admin::photographer_resources((int)$account->id);

$legacy_links = [

];
?>
<div class="aurora-workspace <?php echo $view==='onboarding' ? 'is-onboarding' : ''; ?>">
    <aside class="aurora-workspace-sidebar">
        <div class="aurora-workspace-brand aurora-workspace-brand-card">
            <button type="button" class="aurora-mobile-menu-close" data-aurora-menu-close aria-label="Lukk meny"><span class="dashicons dashicons-no-alt"></span></button>
            <?php if (!empty($branding['logo_url'])) : ?>
                <span class="aurora-workspace-logo is-image"><img src="<?php echo esc_url($branding['logo_url']); ?>" alt="<?php echo esc_attr($branding['platform_name'] ?: 'Aurora'); ?>"></span>
            <?php else : ?>
                <span class="aurora-workspace-logo">A</span>
            <?php endif; ?>
            <div class="aurora-workspace-brand-copy">
                <strong><?php echo esc_html($workspace_product_name); ?></strong>
                <small>Developed by <?php echo esc_html($branding['company_name'] ?: '9Ls1 Digital'); ?></small>
            </div>
        </div>

        <div class="aurora-workspace-account">
            <span class="aurora-workspace-avatar"><?php echo esc_html(strtoupper(substr($account->account_name,0,1))); ?></span>
            <div><strong><?php echo esc_html($account->account_name); ?></strong><small>Fotografkonto</small></div>
        </div>

        <nav class="aurora-workspace-menu">
            <span class="aurora-menu-label">ARBEIDSFLATE</span>
            <a class="<?php echo $view==='dashboard'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('dashboard')); ?>"><span class="dashicons dashicons-dashboard"></span>Dashboard</a>
            <a class="<?php echo $view==='new'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Ny kunde / prosjekt</a>

            <span class="aurora-menu-label">FOTOPORTAL</span>
            <?php foreach ($menu as $key => $meta) :
                if (empty($enabled[$key])) continue;
            ?>
                <a class="<?php echo $view===$key?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url($key)); ?>"><span class="dashicons <?php echo esc_attr($meta[1]); ?>"></span><?php echo esc_html($meta[0]); ?></a>
            <?php endforeach; ?>

            <a class="<?php echo $view==='selections'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('selections')); ?>"><span class="dashicons dashicons-yes-alt"></span>Bildevalg<?php if($gallery_unread): ?><b class="aurora-menu-badge"><?php echo (int)$gallery_unread; ?></b><?php endif; ?></a>

            <span class="aurora-menu-label">KONTO</span>
            <a class="<?php echo $view==='resources'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources')); ?>"><span class="dashicons dashicons-book-alt"></span>Ressurser</a>
            <a class="<?php echo $view==='settings'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings')); ?>"><span class="dashicons dashicons-admin-generic"></span>Innstillinger</a>
        </nav>

        <div class="aurora-workspace-sidebar-footer">
            <small>Aurora Fotoportal</small>
            <span>v<?php echo esc_html(NLS1_FOTOPORTAL_VERSION); ?></span>
            <small>9Ls1 Digital</small>
        </div>
    </aside>
    <button type="button" class="aurora-mobile-menu-overlay" data-aurora-menu-close aria-label="Lukk meny"></button>

    <main class="aurora-workspace-main">
        <?php if ($support_mode) : ?>
        <div class="aurora-support-mode-banner">
            <div><span class="dashicons dashicons-shield"></span><strong>Supportmodus</strong><span>Du ser nå Photographer Workspace for <?php echo esc_html($account->account_name); ?>. Tilgangen er midlertidig og logges.</span></div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="aurora_end_support_session">
                <input type="hidden" name="account_id" value="<?php echo (int)$account->id; ?>">
                <?php wp_nonce_field('aurora_end_support_session'); ?>
                <button type="submit">Avslutt supportmodus</button>
            </form>
        </div>
        <?php endif; ?>
        <header class="aurora-workspace-topbar">
            <div class="aurora-workspace-mobilebrand">
                <button type="button" class="aurora-mobile-menu-toggle" data-aurora-menu-open aria-label="Åpne meny" aria-expanded="false"><span class="dashicons dashicons-menu-alt"></span></button>
                <?php if (!empty($branding['logo_url'])) : ?><span class="aurora-workspace-logo is-image"><img src="<?php echo esc_url($branding['logo_url']); ?>" alt="<?php echo esc_attr($branding['platform_name'] ?: 'Aurora'); ?>"></span><?php else : ?><span class="aurora-workspace-logo">A</span><?php endif; ?><strong><?php echo esc_html($workspace_product_name); ?></strong>
            </div>
            <div class="aurora-workspace-topactions">
                <button type="button" class="aurora-icon-button" title="Hjelp"><span class="dashicons dashicons-editor-help"></span></button>
                <div class="aurora-notification-wrap"><button type="button" class="aurora-icon-button" title="Varsler" data-aurora-notification-toggle aria-expanded="false"><span class="dashicons dashicons-bell"></span><?php if($gallery_unread): ?><i></i><b class="aurora-notification-count"><?php echo (int)$gallery_unread; ?></b><?php endif; ?></button><div class="aurora-notification-dropdown" data-aurora-notification-dropdown hidden><div class="aurora-notification-head"><strong>Galleriaktivitet</strong><small><?php echo $gallery_unread ? (int)$gallery_unread.' ulest' : 'Ingen nye'; ?></small></div><?php if($gallery_notifications): foreach($gallery_notifications as $notice): $nc=$notice['counts']??[]; ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-notification-item <?php echo !empty($notice['unread'])?'is-unread':''; ?>"><input type="hidden" name="action" value="9ls1_fotoportal_mark_gallery_activity_read"><input type="hidden" name="gallery_id" value="<?php echo (int)$notice['gallery_id']; ?>"><?php wp_nonce_field('9ls1_fotoportal_mark_gallery_activity_read'); ?><button type="submit"><strong><?php echo esc_html($notice['gallery_title']??'Galleri'); ?></strong><?php if(($notice['last_kind']??'')==='selection_submitted'): ?><span><b>Redigeringsønske mottatt</b> · ✓ <?php echo (int)($nc['approved']??0); ?> valgte</span><?php else: ?><span>♡ <?php echo (int)($nc['favorites']??0); ?> · ✓ <?php echo (int)($nc['approved']??0); ?> · 💬 <?php echo (int)($nc['comments']??0); ?></span><?php endif; ?><small>Sist aktivitet <?php echo esc_html($notice['updated_at']??''); ?></small></button></form><?php endforeach; else: ?><div class="aurora-notification-empty">Ingen galleriaktivitet ennå.</div><?php endif; ?></div></div>
                <div class="aurora-profile-menu-wrap">
                    <button type="button" class="aurora-workspace-user aurora-profile-trigger" data-aurora-profile-toggle aria-expanded="false">
                        <?php if (!empty($photographer_profile['profile_image_url'])) : ?><img class="aurora-topbar-profile-image" src="<?php echo esc_url($photographer_profile['profile_image_url']); ?>" alt=""><?php else : ?><span><?php echo esc_html($initials); ?></span><?php endif; ?>
                        <div><strong><?php echo esc_html($photographer_profile['photographer_name'] ?: $user->display_name); ?></strong><small><?php echo esc_html($photographer_profile['studio_name'] ?: $account->account_name); ?></small></div>
                        <span class="dashicons dashicons-arrow-down-alt2 aurora-profile-chevron"></span>
                    </button>
                    <div class="aurora-profile-dropdown" data-aurora-profile-dropdown hidden>
                        <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings')); ?>"><span class="dashicons dashicons-admin-users"></span>Min profil</a>
                        <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings',['edit_profile'=>1])); ?>"><span class="dashicons dashicons-edit"></span>Rediger profil</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="aurora-workspace-content">
            <?php if (($view ?? '') === 'settings' && ($_GET['message'] ?? '') === 'support_enabled') : ?>
                <div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-shield-alt"></span><div><strong>Supporttilgang er aktivert</strong><p>Aurora/9Ls1 Digital kan nå åpne Workspace i en tidsbegrenset, logget supportøkt uten passordet ditt.</p></div></div>
            <?php elseif (($view ?? '') === 'settings' && ($_GET['message'] ?? '') === 'support_disabled') : ?>
                <div class="aurora-workspace-alert"><span class="dashicons dashicons-lock"></span><div><strong>Supporttilgang er deaktivert</strong><p>Aurora Admin kan ikke åpne Workspace før du aktiverer tilgangen igjen.</p></div></div>
            <?php endif; ?>
            <div class="aurora-workspace-titlebar">
                <div><span class="aurora-workspace-eyebrow">AURORA FOTOPORTAL</span><h1><?php echo esc_html($title[0]); ?></h1><p><?php echo esc_html($title[1]); ?></p></div>
                <?php if ($view === 'onboarding') : ?>
                <?php
                $step=max(1,min(6,absint($_GET['step']??($account->onboarding_step??1))));
                $ops=NLS1_Fotoportal_Admin::photographer_portal_settings((int)$account->id);
                $steps=[1=>'Studio',2=>'Kontakt',3=>'Branding',4=>'Vannmerke',5=>'Kundeportal',6=>'Ferdig'];
                ?>
                <section class="aurora-workspace-card aurora-onboarding-card">
                    <div class="aurora-onboarding-intro"><span class="aurora-workspace-eyebrow">FØRSTE OPPSETT</span><h2>Velkommen, <?php echo esc_html($account->contact_name?:$account->account_name); ?></h2><p>Vi setter opp studioet ditt i seks korte steg. Alt kan endres senere under Innstillinger.</p></div>
                    <div class="aurora-onboarding-stepper"><?php foreach($steps as $n=>$label):?><div class="<?php echo $n===$step?'is-current':($n<$step?'is-done':'');?>"><span><?php echo $n<$step?'✓':$n;?></span><b><?php echo esc_html($label);?></b></div><?php endforeach;?></div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="aurora-onboarding-form">
                        <input type="hidden" name="action" value="aurora_save_photographer_onboarding"><input type="hidden" name="account_id" value="<?php echo (int)$account->id;?>"><input type="hidden" name="onboarding_step" value="<?php echo (int)$step;?>"><?php wp_nonce_field('aurora_save_photographer_onboarding');?>
                        <?php if($step===1):?><h3>Studioinformasjon</h3><p class="aurora-onboarding-section-help">Dette identifiserer studioet ditt i Fotoportalen. Alt kan endres senere under <strong>Innstillinger → Profil og branding</strong>.</p><div class="aurora-form-grid"><label>Studio / firmanavn<input required name="studio_name" value="<?php echo esc_attr($ops['studio_name']?:$account->account_name);?>"><small>Vises som studionavn i kundeportalen, e-poster og på fotografprofilen.</small></label><label>Fotografens navn<input required name="photographer_name" value="<?php echo esc_attr($ops['photographer_name']?:$account->contact_name);?>"><small>Navnet kundene ser som sin fotograf og som brukes i signaturer og profilinformasjon.</small></label><label class="aurora-span-2">Adresse<textarea name="portal_address" placeholder="Gateadresse&#10;Postnummer og sted"><?php echo esc_textarea($ops['address']);?></textarea><small>Brukes i kontaktinformasjon og dokumenter der studioets adresse skal vises.</small></label></div>
                        <?php elseif($step===2):?><h3>Kontaktopplysninger</h3><p class="aurora-onboarding-section-help">Innloggingsadressen administreres av Aurora Admin og kan ikke endres her. Du kan i tillegg angi en egen e-post som kundene skal se. Øvrige kontaktopplysninger kan endres senere under <strong>Innstillinger → Profil og branding</strong>.</p><div class="aurora-form-grid"><label>Konto- og innloggings-e-post<input type="email" value="<?php echo esc_attr($account->contact_email);?>" readonly disabled><small>Fast e-post for innlogging, konto og administrativ kommunikasjon. Endres kun av Aurora Admin.</small></label><label>Kunde-e-post <em>(valgfritt)</em><input type="email" name="portal_email" value="<?php echo esc_attr($ops['email']);?>" placeholder="<?php echo esc_attr($account->contact_email);?>"><small>Brukes som synlig kontaktadresse/Reply-To mot kunder. Lar du feltet stå tomt, brukes konto-e-posten.</small></label><label>Telefon<input name="portal_phone" value="<?php echo esc_attr($ops['phone']);?>"><small>Vises i kontaktinformasjonen mot kundene dine.</small></label><label>Nettside<input name="portal_website" value="<?php echo esc_attr($ops['website']);?>" placeholder="www.dittstudio.no"><small>Lenke tilbake til din ordinære nettside fra Fotoportalen.</small></label><label class="aurora-span-2">Kort presentasjon<textarea name="portal_about" placeholder="Fortell kundene kort om deg eller studioet"><?php echo esc_textarea($ops['about']);?></textarea><small>En kort introduksjon av deg eller studioet som kan vises i kundeopplevelsen.</small></label></div>
                        <?php elseif($step===3):?><h3>Branding</h3><p class="aurora-onboarding-section-help">Gjør Fotoportalen gjenkjennelig for kundene dine. Alt kan senere endres under <strong>Innstillinger → Profil og branding</strong>.</p><div class="aurora-form-grid"><label>Logo<input type="file" name="portal_logo" accept="image/*"><small>Vises i kundeportalen og andre profilerte flater. Anbefalt: PNG eller WebP med god kvalitet.<?php echo $ops['logo_url']?' Logo er allerede lastet opp.':'';?></small></label><label>Profilbilde<input type="file" name="portal_profile_image" accept="image/*"><small>Bildet av fotografen som brukes i kundeportalens kontakt-/profilområde.</small></label><label class="aurora-span-2">Banner / hero-bilde<input type="file" name="portal_cover_image" accept="image/*"><small>Det store stemningsbildet som brukes som hero/banner i kundens Fotoportal.<?php echo $ops['cover_image_url']?' Et bannerbilde er allerede lastet opp.':'';?></small></label><label>Profilfarge<input type="color" name="accent_color" value="<?php echo esc_attr($ops['accent_color']);?>"><small>Brukes på knapper, lenker og utvalgte detaljer i kundeopplevelsen.</small></label></div>
                        <?php elseif($step===4):?><h3>Vannmerke</h3><p class="aurora-onboarding-section-help">Vannmerket beskytter forhåndsvisningene i galleriene og legges <strong>aldri</strong> på originalbildene. Innstillingene kan finjusteres senere under <strong>Innstillinger → Vannmerke</strong>.</p><div class="aurora-form-grid"><label class="aurora-span-2">Vannmerkefil<input type="file" name="portal_watermark" accept="image/png,image/webp,image/jpeg"><small>Anbefalt: PNG eller WebP, helst med transparent bakgrunn.<?php echo $ops['watermark_url']?' Et vannmerke er allerede lastet opp.':'';?></small></label><label>Plassering<select name="watermark_position"><?php foreach(['top_left'=>'Topp venstre','top_center'=>'Topp senter','top_right'=>'Topp høyre','center'=>'Midt','bottom_left'=>'Bunn venstre','bottom_center'=>'Bunn senter','bottom_right'=>'Bunn høyre'] as $x=>$l):?><option value="<?php echo esc_attr($x);?>" <?php selected($ops['watermark_position'],$x);?>><?php echo esc_html($l);?></option><?php endforeach;?></select><small>Bestemmer hvor vannmerket plasseres på alle preview-bilder.</small></label><label>Størrelse (%)<input type="number" min="5" max="70" name="watermark_size" value="<?php echo (int)$ops['watermark_size'];?>"><small>Hvor stort vannmerket skal være i forhold til bildet.</small></label><label>Transparens (%)<input type="number" min="5" max="95" name="watermark_opacity" value="<?php echo (int)$ops['watermark_opacity'];?>"><small>Lavere verdi gjør vannmerket mer diskret. Dette kan senere justeres med live preview.</small></label></div>
                        <?php elseif($step===5):?><h3>Kundeportal</h3><p class="aurora-onboarding-section-help">Dette er standardmeldingen Aurora bruker når du sender en kunde tilgang til bilder. Du kan redigere malen senere under <strong>Innstillinger → Profil og branding</strong>.</p><div class="aurora-form-grid"><label class="aurora-span-2">E-postemne<input name="portal_email_subject" value="<?php echo esc_attr($ops['email_subject']);?>"><small>Emnelinjen kunden ser. Variabler som <code>{project_name}</code> erstattes automatisk med prosjektdata.</small></label><label class="aurora-span-2">Standard e-posttekst<textarea rows="8" name="portal_email_body"><?php echo esc_textarea($ops['email_body']);?></textarea><small>Bruk variablene <code>{customer_name}</code>, <code>{customer_portal_url}</code> og <code>{photographer_name}</code>. Aurora fyller inn de riktige verdiene når meldingen sendes.</small></label></div>
                        <?php else:?><div class="aurora-onboarding-finish"><span class="dashicons dashicons-yes-alt"></span><h3>Fotoportalen din er klar</h3><p>Du kan når som helst endre profil, branding, vannmerke og kundeportal under Innstillinger.</p><div class="aurora-onboarding-summary"><div><b><?php echo esc_html($ops['studio_name']?:$account->account_name);?></b><span>Studio</span></div><div><b><?php echo esc_html($ops['email']?:$account->contact_email);?></b><span>Kontakt</span></div><div><b><?php echo $ops['watermark_url']?'Konfigurert':'Kan legges til senere';?></b><span>Vannmerke</span></div></div><input type="hidden" name="finish_onboarding" value="1"><?php endif;?>
                        <div class="aurora-onboarding-actions"><?php if($step>1&&$step<6):?><a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('onboarding',['step'=>$step-1]));?>">← Tilbake</a><?php endif;?><button class="aurora-primary-action" type="submit"><?php echo $step===6?'Fullfør oppsett':'Lagre og fortsett →';?></button></div>
                    </form>
                </section>
            <?php elseif ($view === 'dashboard') : ?>
                <?php if(!empty($_GET['welcome'])):?><div class="aurora-workspace-alert is-success aurora-dashboard-welcome"><span class="dashicons dashicons-sun"></span><div><strong>Velkommen – her er ditt dashboard</strong><p>Fotoportalen din er satt opp. Start med å opprette din første kunde eller ditt første prosjekt.</p></div></div><?php endif;?>

                    <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Nytt prosjekt</a>
                <?php elseif ($view === 'customers') : ?>
                    <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Ny kunde</a>
                <?php elseif ($view === 'projects') : ?>
                    <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Nytt prosjekt</a>
                <?php endif; ?>
            </div>

            <?php if ($view === 'onboarding') : ?>
                <?php /* Dedicated onboarding canvas: no dashboard/module content below the wizard. */ ?>
            <?php elseif ($view === 'dashboard') : ?>
                <section class="aurora-dashboard-status-grid">
                    <a class="aurora-dashboard-stat is-green" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects')); ?>"><span class="dashicons dashicons-portfolio"></span><div><small>Aktive prosjekter</small><strong><?php echo (int)$dashboard_active_projects; ?></strong><em>Se prosjekter →</em></div></a>
                    <a class="aurora-dashboard-stat is-amber" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts')); ?>"><span class="dashicons dashicons-media-document"></span><div><small>Venter på signering</small><strong><?php echo (int)$dashboard_waiting_signatures; ?></strong><em>Se kontrakter →</em></div></a>
                    <a class="aurora-dashboard-stat is-purple" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries')); ?>"><span class="dashicons dashicons-format-gallery"></span><div><small>Aktive gallerier</small><strong><?php echo (int)$dashboard_active_galleries; ?></strong><em>Se gallerier →</em></div></a>
                    <a class="aurora-dashboard-stat is-blue" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery')); ?>"><span class="dashicons dashicons-download"></span><div><small>Til levering</small><strong><?php echo (int)$dashboard_to_delivery; ?></strong><em>Se leveranser →</em></div></a>
                </section>

                <div class="aurora-dashboard-main-grid">
                    <div class="aurora-dashboard-leftcol">
                        <section class="aurora-workspace-card aurora-dashboard-follow-card">
                            <span class="aurora-workspace-eyebrow">KREVER OPPFØLGING</span>
                            <div class="aurora-dashboard-followups">
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts')); ?>"><span class="aurora-follow-icon is-purple"><i class="dashicons dashicons-media-document"></i></span><div><strong><?php echo (int)$dashboard_waiting_signatures; ?> kontrakter venter på signering</strong><small>Send påminnelse til kunder.</small></div><span class="aurora-follow-link">Se kontrakter →</span></a>
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('selections',['status'=>'submitted'])); ?>"><span class="aurora-follow-icon is-red"><i class="dashicons dashicons-edit"></i></span><div><strong><?php echo (int)$dashboard_edit_requests; ?> redigeringsønsker</strong><small>Kunden har sendt inn ønsker om videre behandling.</small></div><span class="aurora-follow-link">Gå til Bildevalg →</span></a>
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery')); ?>"><span class="aurora-follow-icon is-amber"><i class="dashicons dashicons-money-alt"></i></span><div><strong><?php echo (int)$dashboard_unpaid; ?> prosjekter mangler betaling</strong><small>Faktura er ikke registrert som betalt.</small></div><span class="aurora-follow-link">Se prosjekter →</span></a>
                            </div>
                        </section>

                        <section class="aurora-workspace-card aurora-dashboard-resources-card">
                            <span class="aurora-workspace-eyebrow">RESSURSER</span><h2>Alt du trenger i hverdagen</h2>
                            <div class="aurora-dashboard-resource-grid">
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources',['category'=>'kontraktmal'])); ?>"><span class="dashicons dashicons-media-document"></span><strong>Kontraktmaler</strong><small>Opprett og rediger maler for kontrakter.</small></a>
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources',['category'=>'dokumentmal'])); ?>"><span class="dashicons dashicons-media-text"></span><strong>Dokumentmaler</strong><small>Avtaler, skjemaer og andre dokumenter.</small></a>
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources',['category'=>'epostmal'])); ?>"><span class="dashicons dashicons-email-alt"></span><strong>E-postmaler</strong><small>Ferdige maler for e-post og påminnelser.</small></a>
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources',['category'=>'shotlist'])); ?>"><span class="dashicons dashicons-clipboard"></span><strong>Fotoplan / Shotlists</strong><small>Mal for fotoplan og bildelister.</small></a>
                                <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources')); ?>"><span class="dashicons dashicons-upload"></span><strong>Mine ressurser</strong><small><?php echo (int)count($dashboard_resources); ?> opplastede ressurser.</small></a>
                            </div>
                            <a class="aurora-dashboard-more" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources')); ?>">Se alle ressurser →</a>
                        </section>
                    </div>
                    <aside class="aurora-dashboard-rightcol">
                        <section class="aurora-workspace-card aurora-dashboard-customer-card"><span class="aurora-workspace-eyebrow">KUNDEOPPLEVELSE</span><h2>Vannmerke og kundevisning</h2><p>Slik vises bildene for kundene dine.</p><div class="aurora-dashboard-watermark-preview" data-position="<?php echo esc_attr($photographer_profile['watermark_position']??'bottom_right'); ?>" style="--wm-size:<?php echo (int)($photographer_profile['watermark_size']??18); ?>%;--wm-opacity:<?php echo ((int)($photographer_profile['watermark_opacity']??35))/100; ?>"><img src="<?php echo esc_url($branding['watermark_preview_url']); ?>" alt="Aurora testbilde"><?php if(!empty($photographer_profile['watermark_url'])):?><img class="wm" src="<?php echo esc_url($photographer_profile['watermark_url']); ?>" alt="Vannmerke"><?php else:?><span class="wm text">DITT VANNMERKE</span><?php endif;?></div><a class="aurora-dashboard-more" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings')); ?>">Rediger vannmerkeinnstillinger →</a></section>
                        <section class="aurora-workspace-card aurora-dashboard-account-card"><span class="aurora-workspace-eyebrow">KONTO</span><div class="aurora-dashboard-account-title"><h2>Fotografkonto</h2><span>Aktiv</span></div><dl><div><dt>Fotografkonto</dt><dd><?php echo esc_html($account->account_name); ?></dd></div><div><dt>Plan</dt><dd><?php echo esc_html($account->plan_name ?: 'Development'); ?></dd></div><div><dt>Aktive moduler</dt><dd><?php echo (int)count(array_filter($enabled)); ?></dd></div></dl><a class="aurora-dashboard-more" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings')); ?>">Se innstillinger →</a></section>
                    </aside>
                </div>

            <?php elseif ($view === 'customers') : ?>
                <?php
                $customer_id = absint($_GET['customer_id'] ?? 0);
                $customer = $customer_id ? NLS1_Fotoportal_Admin::get_client($customer_id) : null;
                ?>

                <?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
                    <div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong>Kunde og prosjekt er opprettet</strong><p>Opplysningene er lagret på fotografkontoen.</p></div></div>
                <?php endif; ?>

                <?php if ($customer_id && $customer) : ?>
                    <?php
                    $contact = NLS1_Fotoportal_Admin::get_primary_contact($customer_id);
                    $customer_projects = NLS1_Fotoportal_Admin::get_client_projects($customer_id);
                    ?>
                    <div class="aurora-customer-profile-head">
                        <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers')); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Alle kunder</a>
                        <div class="aurora-customer-profile-card">
                            <div class="aurora-customer-avatar"><?php echo esc_html(strtoupper(substr($customer->client_name, 0, 1))); ?></div>
                            <div class="aurora-customer-profile-main">
                                <span class="aurora-workspace-eyebrow"><?php echo esc_html($customer->customer_number ?: 'KUNDE'); ?></span>
                                <h2><?php echo esc_html($customer->client_name); ?></h2>
                                <div class="aurora-customer-meta">
                                    <span><?php echo esc_html(NLS1_Fotoportal_Admin::client_type_label($customer->client_type)); ?></span>
                                    <?php if (!empty($customer->client_group)) : ?><span><?php echo esc_html($customer->client_group); ?></span><?php endif; ?>
                                    <span class="aurora-status-pill is-active">Aktiv</span>
                                </div>
                            </div>
                            <div class="aurora-customer-profile-actions">
                                <?php if (!empty($customer->email)) : ?><a class="aurora-secondary-action" href="mailto:<?php echo esc_attr($customer->email); ?>"><span class="dashicons dashicons-email"></span>E-post</a><?php endif; ?>
                                <a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers',['customer_id'=>$customer_id,'edit'=>1])); ?>"><span class="dashicons dashicons-edit"></span>Rediger</a><?php $cpurl=NLS1_Fotoportal_Admin::customer_portal_url($customer_id); ?><a class="aurora-secondary-action" href="<?php echo esc_url($cpurl); ?>" target="_blank"><span class="dashicons dashicons-external"></span>Kundeportal</a>
                                <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Nytt prosjekt</a>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($_GET['edit'])) : ?>
                    <section class="aurora-workspace-card aurora-customer-edit-card">
                        <div class="aurora-workspace-cardhead">
                            <div><span class="aurora-workspace-eyebrow">REDIGER KUNDE</span><h2>Kundeopplysninger</h2></div>
                            <a class="aurora-text-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers',['customer_id'=>$customer_id])); ?>">Avbryt</a>
                        </div>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
                            <input type="hidden" name="action" value="9ls1_fotoportal_update_client">
                            <input type="hidden" name="client_id" value="<?php echo (int)$customer_id; ?>">
                            <input type="hidden" name="client_group" value="<?php echo esc_attr($customer->client_group ?? ''); ?>">
                            <input type="hidden" name="aurora_workspace" value="1">
                            <?php wp_nonce_field('9ls1_fotoportal_update_client'); ?>
                            <div class="nls1-form-grid">
                                <label>Kundenavn *<input type="text" name="client_name" required value="<?php echo esc_attr($customer->client_name); ?>"></label>
                                <label>Kundetype<select name="client_type">
                                    <option value="private" <?php selected($customer->client_type,'private'); ?>>Privat</option>
                                    <option value="business" <?php selected($customer->client_type,'business'); ?>>Bedrift</option>
                                    <option value="artist" <?php selected($customer->client_type,'artist'); ?>>Artist/Band</option>
                                    <option value="organization" <?php selected($customer->client_type,'organization'); ?>>Organisasjon</option>
                                </select></label>
                                <label>Hovedkontakt fornavn<input type="text" name="first_name" value="<?php echo esc_attr($contact->first_name ?? ''); ?>"></label>
                                <label>Hovedkontakt etternavn<input type="text" name="last_name" value="<?php echo esc_attr($contact->last_name ?? ''); ?>"></label>
                                <label>E-post<input type="email" name="email" value="<?php echo esc_attr($customer->email); ?>"></label>
                                <label>Telefon<input type="text" name="phone" value="<?php echo esc_attr($customer->phone); ?>"></label>
                                <label>Adresse<input type="text" name="address" value="<?php echo esc_attr($customer->address); ?>"></label>
                                <label>Postnummer<input type="text" name="postal_code" value="<?php echo esc_attr($customer->postal_code); ?>"></label>
                                <label>Sted/by<input type="text" name="city" value="<?php echo esc_attr($customer->city); ?>"></label>
                                <label>Organisasjonsnummer<input type="text" name="organization_number" value="<?php echo esc_attr($customer->organization_number); ?>"></label>
                                <div class="nls1-full aurora-billing-box">
                                    <strong>Fakturainformasjon</strong>
                                    <p>Bruk kundeadressen eller oppgi egen fakturaadresse.</p>
                                    <label class="nls1-checkbox"><input type="checkbox" name="billing_same_as_customer" value="1" <?php checked(!empty($customer->billing_same_as_customer)); ?> data-billing-toggle-edit> Bruk samme som kundeadresse</label>
                                    <div class="aurora-billing-fields" data-billing-fields-edit <?php echo !empty($customer->billing_same_as_customer) ? 'hidden' : ''; ?>>
                                        <label>Fakturanavn<input type="text" name="billing_name" value="<?php echo esc_attr($customer->billing_name); ?>"></label>
                                        <label>Fakturaadresse<input type="text" name="billing_address" value="<?php echo esc_attr($customer->billing_address); ?>"></label>
                                        <label>Postnummer<input type="text" name="billing_postal_code" value="<?php echo esc_attr($customer->billing_postal_code); ?>"></label>
                                        <label>Sted<input type="text" name="billing_city" value="<?php echo esc_attr($customer->billing_city); ?>"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="aurora-step-actions"><span></span><button class="aurora-primary-action" type="submit">Lagre endringer</button></div>
                        </form>
                    </section>
                <?php endif; ?>

                <div class="aurora-workspace-grid">
                        <section class="aurora-workspace-card">
                            <span class="aurora-workspace-eyebrow">KONTAKT</span><h2>Kontaktinformasjon</h2>
                            <dl class="aurora-customer-details">
                                <div><dt>Hovedkontakt</dt><dd><?php echo esc_html($contact ? NLS1_Fotoportal_Admin::format_contact_name($contact) : '—'); ?></dd></div>
                                <div><dt>E-post</dt><dd><?php echo esc_html($customer->email ?: '—'); ?></dd></div>
                                <?php $portal_email=NLS1_Fotoportal_Admin::client_portal_email($customer_id);$portal_user=NLS1_Fotoportal_Admin::client_portal_user($customer_id); ?>
                                <div><dt>Kundeinnlogging</dt><dd><?php if($portal_user): ?><span style="color:#16804b;font-weight:700">● Aktiv</span> · <?php echo esc_html($portal_email); ?><?php elseif($portal_email): ?><span style="color:#9a6a00;font-weight:700">○ Ikke opprettet</span><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;margin-left:8px"><input type="hidden" name="action" value="9ls1_fotoportal_ensure_customer_login"><input type="hidden" name="client_id" value="<?php echo (int)$customer_id; ?>"><?php wp_nonce_field('9ls1_fotoportal_ensure_customer_login'); ?><button type="submit" class="button button-small">Opprett innlogging</button></form><?php else: ?><span style="color:#a33">Mangler e-post</span><?php endif; ?></dd></div>
                                <div><dt>Telefon</dt><dd><?php echo esc_html($customer->phone ?: '—'); ?></dd></div>
                                <div><dt>Sted/by</dt><dd><?php echo esc_html($customer->city ?: '—'); ?></dd></div><div><dt>Registrert</dt><dd><?php echo !empty($customer->created_at) ? esc_html(date_i18n('d.m.Y', strtotime($customer->created_at))) : '—'; ?></dd></div>
                            </dl>
                        </section>
                        <section class="aurora-workspace-card"><span class="aurora-workspace-eyebrow">ADRESSE & FAKTURA</span><h2>Kundeinformasjon</h2><dl class="aurora-customer-details">
<div><dt>Adresse</dt><dd><?php echo esc_html($customer->address ?: '—'); ?></dd></div><div><dt>Postnr. / sted</dt><dd><?php echo esc_html(trim(($customer->postal_code ?: '').' '.($customer->city ?: '')) ?: '—'); ?></dd></div><div><dt>Org.nr.</dt><dd><?php echo esc_html($customer->organization_number ?: '—'); ?></dd></div><div><dt>Fakturaadresse</dt><dd><?php echo !empty($customer->billing_same_as_customer)?'Samme som kundeadresse':esc_html(trim(($customer->billing_address ?: '').', '.($customer->billing_postal_code ?: '').' '.($customer->billing_city ?: ''),', ')); ?></dd></div></dl></section>                    </div>

                <?php $ch=NLS1_Fotoportal_Admin::customer_hero_settings($customer_id);$chi=NLS1_Fotoportal_Admin::customer_hero_images($customer_id);$ps=NLS1_Fotoportal_Admin::photographer_portal_settings();$churl=NLS1_Fotoportal_Admin::hero_image_url($ch,$chi,$ps['cover_image_url']??''); ?>
                <section class="aurora-workspace-card aurora-hero-designer-card"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">HERO DESIGNER</span><h2>Kundeportal</h2><p>Tilpass coveret kunden ser på sin faste hovedportal.</p></div></div>
                <div class="aurora-hero-editor-preview size-<?php echo esc_attr($ch['size']); ?>" style="background-image:url('<?php echo esc_url($churl); ?>');background-position:<?php echo (int)$ch['focal_x']; ?>% <?php echo (int)$ch['focal_y']; ?>%"><span class="aurora-hero-editor-overlay" style="background:<?php echo esc_attr($ch['overlay_color']); ?>;opacity:<?php echo esc_attr($ch['overlay_opacity']/100); ?>"></span><div class="aurora-hero-editor-copy"><strong><?php echo esc_html($customer->client_name); ?></strong><span>Velkommen til din bildeportal</span></div></div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-hero-controls"><input type="hidden" name="action" value="9ls1_fotoportal_save_customer_hero"><input type="hidden" name="client_id" value="<?php echo (int)$customer_id; ?>"><?php wp_nonce_field('9ls1_fotoportal_save_customer_hero'); ?>
                <label>Størrelse<select name="hero_size"><option value="small" <?php selected($ch['size'],'small'); ?>>Small</option><option value="medium" <?php selected($ch['size'],'medium'); ?>>Medium</option><option value="large" <?php selected($ch['size'],'large'); ?>>Large</option></select></label><label>Overlay<input type="color" name="overlay_color" value="<?php echo esc_attr($ch['overlay_color']); ?>"></label><label>Transparens <b><?php echo (int)$ch['overlay_opacity']; ?>%</b><input type="range" name="overlay_opacity" min="0" max="80" value="<?php echo (int)$ch['overlay_opacity']; ?>"></label><label>Focal X<input type="range" name="focal_x" min="0" max="100" value="<?php echo (int)$ch['focal_x']; ?>"></label><label>Focal Y<input type="range" name="focal_y" min="0" max="100" value="<?php echo (int)$ch['focal_y']; ?>"></label><button class="aurora-primary-action" type="submit">Lagre Hero</button>
                <div class="aurora-hero-image-picker"><label class="is-default"><input type="radio" name="hero_image_id" value="0" <?php checked((int)$ch['image_id'],0); ?>><span>Fotografens cover</span></label><?php foreach($chi as $im):$u=$im->thumbnail_url?:$im->preview_url;if(!$u)continue;?><label><input type="radio" name="hero_image_id" value="<?php echo (int)$im->id; ?>" <?php checked((int)$ch['image_id'],(int)$im->id); ?>><img src="<?php echo esc_url($u); ?>" alt=""></label><?php endforeach;?></div></form>
                <div style="border-top:1px solid #ece7f0;margin-top:22px;padding-top:20px"><div class="aurora-workspace-cardhead" style="margin-bottom:12px"><div><span class="aurora-workspace-eyebrow">KUNDENS FASTE PORTAL</span><h3 style="margin:3px 0 4px">Portal og innloggingslenke</h3><p>Samme sikre portal brukes også ved fremtidige oppdrag.</p></div><a class="aurora-secondary-action" href="<?php echo esc_url($cpurl); ?>" target="_blank">Åpne</a></div><div class="aurora-gallery-url-row"><input readonly value="<?php echo esc_attr($cpurl); ?>"><button type="button" class="aurora-secondary-action" data-copy-gallery-url="<?php echo esc_attr($cpurl); ?>">Kopier URL</button></div></div>
                </section>
<section class="aurora-workspace-card aurora-workspace-card-wide">
                            <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">PROSJEKTER</span><h2>Kundens prosjekter</h2></div><strong class="aurora-count-badge"><?php echo count($customer_projects); ?></strong></div>
                            <?php if ($customer_projects) : ?>
                                <div class="aurora-project-mini-list">
                                    <?php foreach ($customer_projects as $project) : ?>
                                        <div>
                                            <span class="dashicons dashicons-portfolio"></span>
                                            <div><strong><?php echo esc_html($project->project_name); ?></strong><small><?php echo esc_html($project->project_number); ?><?php if ($project->project_date) echo ' · ' . esc_html(date_i18n('d.m.Y', strtotime($project->project_date))); ?></small></div>
                                            <span class="aurora-project-health <?php echo esc_attr($status_class); ?>" title="<?php echo esc_attr($status_help); ?>"><span class="aurora-health-dot"></span><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span>
                                            <a class="aurora-icon-link" title="Åpne prosjekt" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>(int)$project->id])); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="aurora-empty-state"><span class="dashicons dashicons-portfolio"></span><strong>Ingen prosjekter ennå</strong><p>Opprett første fotooppdrag for denne kunden.</p></div>
                            <?php endif; ?>
                        </section>


                <?php else : ?>
                    <?php
                    $search = sanitize_text_field($_GET['s'] ?? '');
                    $group=sanitize_text_field($_GET['group']??''); $type=sanitize_key($_GET['ctype']??''); $sort=sanitize_key($_GET['sort']??'created'); $order=strtolower(sanitize_key($_GET['order']??'desc'))==='asc'?'asc':'desc';
                    $customers=NLS1_Fotoportal_Admin::get_clients(true,$search,$group,$type,$sort,$order);
                    $sort_url=function($key)use($sort,$order){return add_query_arg(['sort'=>$key,'order'=>(($sort===$key&&$order==='asc')?'desc':'asc')]);}; $sort_arrow=function($key)use($sort,$order){return $sort===$key?($order==='asc'?' ↑':' ↓'):' ↕';};
                    ?>
                    <section class="aurora-workspace-card aurora-customers-toolbar">
                        <form method="get" class="aurora-customer-filters">
                            <input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Photographer_Workspace::PAGE_SLUG); ?>">
                            <input type="hidden" name="workspace_view" value="customers">
                            <label class="aurora-search-field"><span class="dashicons dashicons-search"></span><input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Søk etter kunde, e-post eller telefon"></label>
                            <select name="group">
                                <option value="">Alle grupper</option>
                                <?php foreach (array_keys(NLS1_Fotoportal_Admin::$project_types) as $g) : ?><option value="<?php echo esc_attr($g); ?>" <?php selected($group, $g); ?>><?php echo esc_html($g); ?></option><?php endforeach; ?>
                            </select>
                            <select name="ctype">
                                <option value="">Alle kundetyper</option>
                                <option value="private" <?php selected($type,'private'); ?>>Privat</option>
                                <option value="business" <?php selected($type,'business'); ?>>Bedrift</option>
                                <option value="artist" <?php selected($type,'artist'); ?>>Artist/Band</option>
                                <option value="organization" <?php selected($type,'organization'); ?>>Organisasjon</option>
                            </select>
                            <button class="aurora-secondary-action" type="submit">Filtrer</button>
                            <?php if ($search || $group || $type) : ?><a class="aurora-text-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers')); ?>">Nullstill</a><?php endif; ?>
                        </form>
                    </section>

                    <section class="aurora-workspace-card aurora-customer-list-card">
                        <div class="aurora-workspace-cardhead">
                            <div><span class="aurora-workspace-eyebrow">KUNDEREGISTER</span><h2><?php echo count($customers); ?> kunder</h2></div>
                        </div>
                        <?php if ($customers) : ?>
                            <div class="aurora-customer-table-wrap">
                                <table class="aurora-customer-table">
                                    <thead><tr>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url('customer')); ?>">Kunde<?php echo esc_html($sort_arrow('customer')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url('contact')); ?>">Kontakt<?php echo esc_html($sort_arrow('contact')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url('type')); ?>">Type<?php echo esc_html($sort_arrow('type')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url('city')); ?>">Sted<?php echo esc_html($sort_arrow('city')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url('status')); ?>">Status<?php echo esc_html($sort_arrow('status')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url('created')); ?>">Registrert<?php echo esc_html($sort_arrow('created')); ?></a></th>
<th></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($customers as $row) : ?>
                                        <?php $primary = NLS1_Fotoportal_Admin::get_primary_contact($row->id); ?>
                                        <tr>
                                            <td>
                                                <a class="aurora-customer-name" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers', ['customer_id'=>$row->id])); ?>">
                                                    <span class="aurora-customer-avatar is-small"><?php echo esc_html(strtoupper(substr($row->client_name,0,1))); ?></span>
                                                    <span><strong><?php echo esc_html($row->client_name); ?></strong><small><?php echo esc_html($row->customer_number ?: '—'); ?></small></span>
                                                </a>
                                            </td>
                                            <td><strong><?php echo esc_html($primary ? NLS1_Fotoportal_Admin::format_contact_name($primary) : '—'); ?></strong><small><?php echo esc_html($row->email ?: ''); ?></small></td>
                                            <td><?php echo esc_html(NLS1_Fotoportal_Admin::client_type_label($row->client_type)); ?></td>
                                            <td><?php echo esc_html($row->city ?: '—'); ?></td>
                                            <td>
                                                <?php if (!empty($row->is_test)) : ?>
                                                    <span class="aurora-status-pill is-test">Test</span>
                                                <?php else : ?>
                                                    <span class="aurora-status-pill is-active">Aktiv</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($row->created_at) ? esc_html(date_i18n('d.m.Y', strtotime($row->created_at))) : '—'; ?></td>
                                            <td class="aurora-row-actions">
                                                <?php if ($row->email) : ?><a class="aurora-icon-link" title="Send e-post" href="mailto:<?php echo esc_attr($row->email); ?>"><span class="dashicons dashicons-email"></span></a><?php endif; ?>
                                                <a class="aurora-icon-link" title="Åpne kunde" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers', ['customer_id'=>$row->id])); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="aurora-empty-state"><span class="dashicons dashicons-groups"></span><strong>Ingen kunder funnet</strong><p>Opprett en ny kunde eller juster søket.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>">+ Ny kunde</a></div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

            <?php elseif ($view === 'new') : ?>
                <?php if (isset($_GET['message']) && $_GET['message'] === 'missing_fields') : ?>
                    <div class="aurora-workspace-alert is-error"><span class="dashicons dashicons-warning"></span><div><strong>Mangler obligatoriske felt</strong><p>Kontroller kunde- og prosjektinformasjonen.</p></div></div>
                <?php endif; ?>

                <section class="aurora-workspace-card aurora-native-wizard">
                    <div class="aurora-create-intro">
                        <span class="aurora-workspace-eyebrow">NYTT FOTOOPPDRAG</span>
                        <h2>Opprett kunde og prosjekt</h2>
                        <p>Kundeopplysninger først, deretter selve fotooppdraget.</p>
                    </div>

                    <div class="nls1-wizard aurora-real-wizard" data-current-step="1">
                        <div class="nls1-step active" data-step-indicator="1"><span>1</span><strong>Kunde</strong><small>Kontakt og kundeinfo</small></div>
                        <div class="nls1-step" data-step-indicator="2"><span>2</span><strong>Prosjekt</strong><small>Selve fotooppdraget</small></div>
                        <div class="nls1-step" data-step-indicator="3"><span>3</span><strong>Bekreft</strong><small>Kontroller og opprett</small></div>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form aurora-step-form" id="aurora-new-project-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="9ls1_fotoportal_save_client_project">
                        <input type="hidden" name="aurora_workspace" value="1">
                        <?php wp_nonce_field('9ls1_fotoportal_save_client_project'); ?>

                        <section class="aurora-form-step is-active" data-step="1">
                            <div class="aurora-form-step-head"><div><span class="aurora-step-label">STEG 1 AV 3</span><h2>Kunde</h2><p>Hvem er kunden? Bruk personnavn, familienavn eller firmanavn.</p></div></div>
                            <div class="nls1-form-grid">
                                <label>Kundenavn *<input type="text" name="client_name" required placeholder="f.eks. Ola Hansen eller Hansen-familien"></label>
                                <input type="hidden" name="client_group" value="">
                                <label>Kundetype<select name="client_type"><option value="private">Privat</option><option value="business">Bedrift</option><option value="artist">Artist/Band</option><option value="organization">Organisasjon</option></select></label>
                                <div class="aurora-form-hint"><strong>Kundenavn</strong><span>Eksempel: «Ola Hansen». Prosjektnavn som «Bryllup Hansen 2027» legges inn i neste steg.</span></div>
                                <label>Hovedkontakt fornavn *<input type="text" name="first_name" required placeholder="f.eks. Ola"></label>
                                <label>Hovedkontakt etternavn<input type="text" name="last_name" placeholder="f.eks. Hansen"></label>
                                <label>E-post *<input type="email" name="email" required placeholder="ola@eksempel.no"></label>
                                <label>Telefon<input type="text" name="phone" placeholder="+47 ..."></label>
                                <label>Adresse<input type="text" name="address" placeholder="Gateadresse"></label><label>Postnummer<input type="text" name="postal_code" placeholder="1540"></label><label>Sted/by<input type="text" name="city" placeholder="f.eks. Vestby"></label><label>Organisasjonsnummer<input type="text" name="organization_number" placeholder="Valgfritt"></label><div class="nls1-full aurora-billing-box"><strong>Fakturainformasjon</strong><p>Bruk kundeadressen eller oppgi egen fakturaadresse.</p><label class="nls1-checkbox"><input type="checkbox" name="billing_same_as_customer" value="1" checked data-billing-toggle> Bruk samme som kundeadresse</label><div class="aurora-billing-fields" data-billing-fields hidden><label>Fakturanavn<input type="text" name="billing_name"></label><label>Fakturaadresse<input type="text" name="billing_address"></label><label>Postnummer<input type="text" name="billing_postal_code"></label><label>Sted<input type="text" name="billing_city"></label></div></div>
                            </div>
                            <div class="aurora-step-actions"><span></span><button type="button" class="aurora-primary-action aurora-next-step" data-next="2">Neste: Prosjekt →</button></div>
                        </section>

                        <section class="aurora-form-step" data-step="2" hidden>
                            <div class="aurora-form-step-head"><div><span class="aurora-step-label">STEG 2 AV 3</span><h2>Prosjekt</h2><p>Beskriv selve fotograferingen. Prosjektdata holdes adskilt fra kunden.</p></div></div>
                            <div class="nls1-form-grid">
                                <label>Prosjektnavn *<input type="text" name="project_name" required placeholder="f.eks. Bryllup Hansen 2027"></label>
                                <label>Prosjekttype *<select name="project_type" required><option value="">Velg type</option><?php foreach (NLS1_Fotoportal_Admin::$project_types as $ptype => $prefix): ?><option value="<?php echo esc_attr($ptype); ?>"><?php echo esc_html($ptype); ?></option><?php endforeach; ?></select></label>
                                <label>Dato<input type="date" name="project_date"></label>
                                <label>Lokasjon<input type="text" name="location" placeholder="f.eks. Son Spa / Vestby"></label>
                                <label class="nls1-full">Notater<textarea name="description" rows="5" placeholder="Praktiske notater, ønsker, tidspunkt eller annen prosjektinformasjon."></textarea></label>
                                <div class="nls1-full aurora-optional-document"><span class="aurora-workspace-eyebrow">VALGFRITT</span><h3>Dokument</h3><p>Har du et dokument klart, kan det legges til nå. Du kan også gjøre dette senere.</p><div class="nls1-form-grid"><label>Tittel<input type="text" name="project_document_title"></label><label>Type<select name="project_document_type"><?php foreach(NLS1_Fotoportal_Admin::$document_types as $dtype):?><option><?php echo esc_html($dtype);?></option><?php endforeach;?></select></label><label class="nls1-full">Fil<input type="file" name="project_document"></label></div></div><label class="nls1-checkbox nls1-full"><input type="checkbox" name="is_test" value="1"> Merk som testdata</label>
                            </div>
                            <div class="aurora-step-actions"><button type="button" class="aurora-secondary-action aurora-prev-step" data-prev="1">← Tilbake</button><button type="button" class="aurora-primary-action aurora-next-step" data-next="3">Neste: Bekreft →</button></div>
                        </section>

                        <section class="aurora-form-step" data-step="3" hidden>
                            <div class="aurora-form-step-head"><div><span class="aurora-step-label">STEG 3 AV 3</span><h2>Bekreft</h2><p>Kontroller opplysningene før kunden og prosjektet opprettes.</p></div></div>
                            <div class="aurora-review-grid">
                                <div class="aurora-review-card"><span>KUNDE</span><strong data-review="client_name">—</strong><small data-review="contact">—</small><small data-review="email">—</small></div>
                                <div class="aurora-review-card"><span>PROSJEKT</span><strong data-review="project_name">—</strong><small data-review="project_type">—</small><small data-review="project_date">—</small></div>
                            </div>
                            <div class="aurora-after-create"><strong>Etter opprettelse</strong><p>Du kommer direkte til kundens nye Aurora-profil.</p></div>
                            <div class="aurora-step-actions"><button type="button" class="aurora-secondary-action aurora-prev-step" data-prev="2">← Tilbake</button><button type="submit" class="aurora-primary-action">Opprett kunde og prosjekt</button></div>
                        </section>
                    </form>
                </section>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('aurora-new-project-form');
                    if (!form) return;
                    const steps = Array.from(form.querySelectorAll('.aurora-form-step'));
                    const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
                    function showStep(n) {
                        steps.forEach(el => { const active=Number(el.dataset.step)===n; el.hidden=!active; el.classList.toggle('is-active',active); });
                        indicators.forEach(el => { const step=Number(el.dataset.stepIndicator); el.classList.toggle('active',step<=n); el.classList.toggle('is-current',step===n); });
                        if(n===3) updateReview();
                        window.scrollTo({top:Math.max(0,form.getBoundingClientRect().top+window.scrollY-150),behavior:'smooth'});
                    }
                    function validStep(n) {
                        const step=form.querySelector('.aurora-form-step[data-step="'+n+'"]');
                        for(const field of Array.from(step.querySelectorAll('[required]'))) {
                            if(!field.checkValidity()){ field.reportValidity(); return false; }
                        }
                        return true;
                    }
                    function value(name){ const el=form.elements[name]; return el ? (el.value||'').trim() : ''; }
                    function updateReview(){
                        const set=(key,val)=>{const el=form.querySelector('[data-review="'+key+'"]');if(el)el.textContent=val||'—';};
                        set('client_name',value('client_name'));
                        set('contact',[value('first_name'),value('last_name')].filter(Boolean).join(' '));
                        set('email',value('email'));
                        set('project_name',value('project_name'));
                        set('project_type',value('project_type'));
                        set('project_date',value('project_date'));
                    }
                    form.querySelectorAll('.aurora-next-step').forEach(btn=>btn.addEventListener('click',()=>{const current=Number(btn.closest('.aurora-form-step').dataset.step);if(validStep(current))showStep(Number(btn.dataset.next));}));
                    form.querySelectorAll('.aurora-prev-step').forEach(btn=>btn.addEventListener('click',()=>showStep(Number(btn.dataset.prev))));
                    showStep(1);
                });
                </script>


            <?php elseif ($view === 'projects') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
                ?>

                <?php if ($project_id && $project) : ?>
                    <?php
                    $project_client = NLS1_Fotoportal_Admin::get_client($project->client_id);
                    $contracts = NLS1_Fotoportal_Admin::get_project_contracts($project_id);
                    $documents = NLS1_Fotoportal_Admin::get_documents($project_id, true);
                    $galleries = NLS1_Fotoportal_Admin::get_galleries($project_id, true);
                    $has_signed_contract = NLS1_Fotoportal_Admin::has_signed_contract($project_id);
                    $gallery_ready = false;
                    foreach ($galleries as $rg) {
                        if (in_array($rg->status, ['preview_generated','ready'], true)) { $gallery_ready = true; break; }
                    }
                    $status_class = 'is-neutral';
                    $status_help = 'Prosjektet er opprettet og ikke ferdigstilt.';
                    if ($project->status === 'delivered') { $status_class='is-complete'; $status_help='Prosjektet er markert som levert.'; }
                    elseif ($project->status === 'archived') { $status_class='is-muted'; $status_help='Prosjektet er arkivert.'; }
                    elseif ($has_signed_contract && $gallery_ready) { $status_class='is-ready'; $status_help='Kontrakt er signert og minst ett galleri er klart. Betaling spores ikke i Aurora ennå.'; }
                    elseif (in_array($project->status,['contract_signed','images_uploaded','client_selecting','delivery_ready'],true)) { $status_class='is-progress'; $status_help='Prosjektet er i aktiv produksjon/leveranse.'; }
                    elseif (in_array($project->status,['contract_created','contract_sent','shoot_done'],true)) { $status_class='is-waiting'; $status_help='Prosjektet venter på neste steg.'; }
                    ?>
                    <div class="aurora-customer-profile-head">
                        <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects')); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Alle prosjekter</a>
                        <div class="aurora-project-profile-card">
                            <div class="aurora-project-mark"><span class="dashicons dashicons-portfolio"></span></div>
                            <div class="aurora-customer-profile-main">
                                <span class="aurora-workspace-eyebrow"><?php echo esc_html($project->project_number ?: 'PROSJEKT'); ?></span>
                                <h2><?php echo esc_html($project->project_name); ?></h2>
                                <div class="aurora-customer-meta">
                                    <span><?php echo esc_html($project->project_type ?: '—'); ?></span>
                                    <?php if ($project_client) : ?><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers',['customer_id'=>$project_client->id])); ?>"><?php echo esc_html($project_client->client_name); ?></a><?php endif; ?>
                                    <span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span>
                                    <?php if (!empty($project->is_test)) : ?><span class="aurora-status-pill is-test">Test</span><?php endif; ?>
                                </div>
                            </div>
                            <div class="aurora-customer-profile-actions">
                                <a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers',['customer_id'=>$project->client_id])); ?>">Kunde</a>
                            </div>
                        </div>
                    </div>

                    <section class="aurora-workspace-card aurora-project-summary">
                        <div class="aurora-project-summary-grid">
                            <div><span>Dato</span><strong><?php echo $project->project_date ? esc_html(date_i18n('d.m.Y', strtotime($project->project_date))) : '—'; ?></strong></div>
                            <div><span>Lokasjon</span><strong><?php echo esc_html($project->location ?: '—'); ?></strong></div>
                            <div><span>Status</span><strong><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></strong></div>
                            <div><span>Kunde</span><strong><?php echo esc_html($project_client ? $project_client->client_name : '—'); ?></strong></div>
                        </div>
                        <?php if (!empty($project->description)) : ?><div class="aurora-project-notes"><span>NOTATER</span><p><?php echo nl2br(esc_html($project->description)); ?></p></div><?php endif; ?>
                    </section>

                    <section class="aurora-workspace-card aurora-project-workflow">
                        <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">PROSJEKTFLYT</span><h2>Fra prosjekt til leveranse</h2></div></div>
                        <?php $flow=NLS1_Fotoportal_Admin::project_delivery_state($project_id); ?>
                        <div class="aurora-project-steps aurora-project-steps-six">
                            <div class="is-current is-complete"><span>1</span><strong>Prosjekt</strong><small>Opprettet ✓</small></div>
                            <a class="<?php echo $flow['contract_registered']?'is-complete':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>"><span>2</span><strong>Kontrakt registrert</strong><small><?php echo $flow['contract_registered']?'Registrert ✓':'Mangler'; ?></small></a>
                            <a class="<?php echo $flow['contract_signed']?'is-complete':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>"><span>3</span><strong>Kontrakt signert</strong><small><?php echo $flow['contract_signed']?'Signert ✓':'Venter'; ?></small></a>
                            <a class="<?php echo $flow['documents']?'is-complete':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id])); ?>"><span>4</span><strong>Dokumenter</strong><small><?php echo $flow['documents']?count($documents).' filer ✓':'Valgfritt'; ?></small></a>
                            <a class="<?php echo $flow['gallery']?'is-complete':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span>5</span><strong>Galleri</strong><small><?php echo $flow['gallery']?count($galleries).' gallerier ✓':'Mangler'; ?></small></a>
                            <a class="<?php echo $flow['paid']?'is-complete':''; ?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>$project_id])); ?>"><span>6</span><strong>Leveranse</strong><small><?php echo $flow['paid']?'Faktura betalt ✓':'Venter på betaling'; ?></small></a>
                        </div>
                        <div class="aurora-status-legend">
                            <span><i class="is-green"></i>Grønn: fullført</span>
                            <span><i class="is-blue"></i>Lilla: aktivt steg</span>
                            <span><i class="is-gray"></i>Hvit/grå: venter eller valgfritt</span>
                        </div>
                    </section>

                <?php else : ?>
                    <?php
                    $search = sanitize_text_field($_GET['s'] ?? '');
                    $ptype = sanitize_text_field($_GET['ptype'] ?? '');
                    $status = sanitize_key($_GET['status'] ?? '');
                    $sort = sanitize_key($_GET['sort'] ?? 'created');
                    $order = strtolower(sanitize_key($_GET['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
                    $projects = NLS1_Fotoportal_Admin::get_projects(true, $search, $ptype, $status, $sort, $order);
                    $project_sort_url = function($key) use ($sort,$order) {
                        return add_query_arg(['sort'=>$key,'order'=>(($sort===$key && $order==='asc')?'desc':'asc')]);
                    };
                    $project_sort_arrow = function($key) use ($sort,$order) {
                        return $sort===$key ? ($order==='asc' ? ' ↑' : ' ↓') : ' ↕';
                    };
                    ?>
                    <section class="aurora-workspace-card aurora-customers-toolbar">
                        <form method="get" class="aurora-customer-filters">
                            <input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Photographer_Workspace::PAGE_SLUG); ?>">
                            <input type="hidden" name="workspace_view" value="projects">
                            <label class="aurora-search-field"><span class="dashicons dashicons-search"></span><input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Søk etter prosjekt, prosjektnummer eller kunde"></label>
                            <select name="ptype">
                                <option value="">Alle prosjekttyper</option>
                                <?php foreach (NLS1_Fotoportal_Admin::$project_types as $type_name => $prefix) : ?><option value="<?php echo esc_attr($type_name); ?>" <?php selected($ptype,$type_name); ?>><?php echo esc_html($type_name); ?></option><?php endforeach; ?>
                            </select>
                            <select name="status">
                                <option value="">Alle statuser</option>
                                <?php foreach (NLS1_Fotoportal_Admin::$project_statuses as $status_key => $status_label) : ?><option value="<?php echo esc_attr($status_key); ?>" <?php selected($status,$status_key); ?>><?php echo esc_html($status_label); ?></option><?php endforeach; ?>
                            </select>
                            <button class="aurora-secondary-action" type="submit">Filtrer</button>
                            <?php if ($search || $ptype || $status) : ?><a class="aurora-text-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects')); ?>">Nullstill</a><?php endif; ?>
                        </form>
                    </section>

                    <section class="aurora-workspace-card aurora-customer-list-card">
                        <div class="aurora-workspace-cardhead">
                            <div><span class="aurora-workspace-eyebrow">PROSJEKTREGISTER</span><h2><?php echo count($projects); ?> prosjekter</h2></div>
                        </div>

                        <?php if ($projects) : ?>
                            <div class="aurora-customer-table-wrap">
                                <table class="aurora-customer-table aurora-project-table">
                                    <thead><tr>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($project_sort_url('project')); ?>">Prosjekt<?php echo esc_html($project_sort_arrow('project')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($project_sort_url('customer')); ?>">Kunde<?php echo esc_html($project_sort_arrow('customer')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($project_sort_url('type')); ?>">Type<?php echo esc_html($project_sort_arrow('type')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($project_sort_url('date')); ?>">Dato<?php echo esc_html($project_sort_arrow('date')); ?></a></th>
<th><a class="aurora-sort-heading" href="<?php echo esc_url($project_sort_url('status')); ?>">Status<?php echo esc_html($project_sort_arrow('status')); ?></a></th>
<th></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($projects as $row) : ?>
                                        <tr>
                                            <td>
                                                <a class="aurora-customer-name" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$row->id])); ?>">
                                                    <span class="aurora-customer-avatar is-small"><span class="dashicons dashicons-portfolio"></span></span>
                                                    <span><strong><?php echo esc_html($row->project_name); ?></strong><small><?php echo esc_html($row->project_number ?: '—'); ?></small></span>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html($row->client_name ?: '—'); ?></td>
                                            <td><?php echo esc_html($row->project_type ?: '—'); ?></td>
                                            <td><?php echo $row->project_date ? esc_html(date_i18n('d.m.Y', strtotime($row->project_date))) : '—'; ?></td>
                                            <td>
                                                <span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($row->status)); ?></span>
                                                <?php if (!empty($row->is_test)) : ?><span class="aurora-status-pill is-test">Test</span><?php endif; ?>
                                            </td>
                                            <td class="aurora-row-actions"><a class="aurora-icon-link" title="Åpne prosjekt" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$row->id])); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="aurora-empty-state"><span class="dashicons dashicons-portfolio"></span><strong>Ingen prosjekter funnet</strong><p>Opprett et nytt prosjekt eller juster søket.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>">+ Nytt prosjekt</a></div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>


            <?php elseif ($view === 'contracts') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
                $contracts = $project ? NLS1_Fotoportal_Admin::get_project_contracts($project_id) : [];
                ?>

                <?php if ($project) : ?>
                    <div class="aurora-contract-context">
                        <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Tilbake til prosjekt</a>
                        <div class="aurora-project-profile-card">
                            <div class="aurora-project-mark"><span class="dashicons dashicons-media-document"></span></div>
                            <div class="aurora-customer-profile-main">
                                <span class="aurora-workspace-eyebrow"><?php echo esc_html($project->project_number); ?></span>
                                <h2><?php echo esc_html($project->project_name); ?></h2>
                                <div class="aurora-customer-meta"><span><?php echo esc_html($project->client_name ?: '—'); ?></span><span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="aurora-contract-grid">
                        <section class="aurora-workspace-card aurora-workspace-card-wide">
                            <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">KONTRAKTER</span><h2>Avtaler for prosjektet</h2></div><span class="aurora-count-badge"><?php echo count($contracts); ?></span></div>

                            <?php if ($contracts) : ?>
                                <div class="aurora-contract-list">
                                    <?php foreach ($contracts as $contract) : ?>
                                        <div class="aurora-contract-item">
                                            <div class="aurora-contract-icon"><span class="dashicons dashicons-media-document"></span></div>
                                            <div class="aurora-contract-main">
                                                <strong><?php echo esc_html($contract->contract_name ?: 'Kontrakt'); ?></strong>
                                                <small>Opprettet <?php echo !empty($contract->created_at) ? esc_html(date_i18n('d.m.Y', strtotime($contract->created_at))) : '—'; ?></small>
                                            </div>
                                            <div class="aurora-contract-status">
                                                <?php
                                                $cstatus = sanitize_key($contract->status ?? 'draft');
                                                $labels = ['draft'=>'Utkast','sent'=>'Sendt','signed'=>'Signert','cancelled'=>'Kansellert'];
                                                $label = $labels[$cstatus] ?? ucfirst($cstatus);
                                                ?>
                                                <span class="aurora-status-pill <?php echo $cstatus === 'signed' ? 'is-active' : ($cstatus === 'draft' ? 'is-test' : ''); ?>"><?php echo esc_html($label); ?></span>
                                                <?php if ($cstatus === 'signed' && !empty($contract->signed_at)) : ?><small><?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($contract->signed_at))); ?></small><?php endif; ?>
                                            </div>
                                            <div class="aurora-row-actions">
                                                <?php if (!empty($contract->file_url)) : ?>
                                                    <a class="aurora-icon-link" href="<?php echo esc_url($contract->file_url); ?>" target="_blank" rel="noopener" title="Se opplastet kontrakt"><span class="dashicons dashicons-visibility"></span></a>
                                                <?php else : ?>
                                                    <details class="aurora-contract-preview"><summary class="aurora-icon-link" title="Se kontrakt"><span class="dashicons dashicons-visibility"></span></summary>
                                                        <div class="aurora-contract-preview-panel"><strong><?php echo esc_html($contract->contract_name); ?></strong><div><?php echo wp_kses_post(wpautop($contract->contract_text)); ?></div><?php if (!empty($contract->signer_email)) : ?><small>Signerer: <?php echo esc_html($contract->signer_email); ?></small><?php endif; ?></div>
                                                    </details>
                                                <?php endif; ?>
                                                <?php if ($cstatus === 'draft' && (($contract->contract_source ?? 'aurora') !== 'upload')) : ?>
                                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                        <input type="hidden" name="action" value="9ls1_fotoportal_mark_contract_sent">
                                                        <input type="hidden" name="contract_id" value="<?php echo (int)$contract->id; ?>">
                                                        <input type="hidden" name="aurora_workspace" value="1">
                                                        <?php wp_nonce_field('9ls1_fotoportal_mark_contract_sent_' . (int)$contract->id); ?>
                                                        <button class="aurora-secondary-action" type="submit">Marker sendt</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="aurora-empty-state"><span class="dashicons dashicons-media-document"></span><strong>Ingen kontrakter ennå</strong><p>Opprett første kontrakt for dette prosjektet.</p></div>
                            <?php endif; ?>
                        </section>

                        <section class="aurora-workspace-card">
                            <span class="aurora-workspace-eyebrow">NY KONTRAKT</span><h2>Opprett avtale</h2>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-contract-form" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="9ls1_fotoportal_create_contract">
                                <input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>">
                                <input type="hidden" name="aurora_workspace" value="1">
                                <?php wp_nonce_field('9ls1_fotoportal_create_contract'); ?>
                                <div class="aurora-ads-badge"><strong>Aurora Digital Signering (ADS)</strong><span>Alle kontrakter signeres digitalt via unik signeringslenke.</span></div>
                                <label>Tittel<input type="text" name="contract_name" value="Kontrakt" required></label>
                                <label>Signerer<input type="text" name="signer_name" placeholder="Navn"></label>
                                <label>E-post til signerer<input type="email" name="signer_email" placeholder="kunde@epost.no" required></label>
                                <label>Kontraktstekst<textarea name="contract_text" rows="12" required><?php echo esc_textarea(NLS1_Fotoportal_Admin::standard_contract_text()); ?></textarea></label>
                                <label>Vedlegg til kontrakten <span class="aurora-optional-label">Valgfritt</span><input type="file" name="contract_file" accept=".pdf,.doc,.docx,.odt,.txt"></label>
                                <label>Internt notat <span class="aurora-optional-label">Valgfritt</span><textarea name="notes" rows="4" placeholder="Kun synlig for fotografen."></textarea></label>
                                <p class="aurora-form-help">Når kontrakten opprettes genererer ADS en unik signeringslenke. Når kunden signerer registreres kontrakten automatisk som signert.</p>
                                <button class="aurora-primary-action" type="submit"><span class="dashicons dashicons-plus-alt2"></span>Opprett kontrakt</button>
                            </form>
                        </section>
                    </div>

                    <section class="aurora-workspace-card aurora-project-workflow">
                        <div class="aurora-project-steps">
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span>1</span><strong>Prosjekt</strong><small>Detaljer og status</small></a>
                            <div class="is-current"><span>2</span><strong>Kontrakt</strong><small><?php echo count($contracts); ?> registrert</small></div>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id])); ?>"><span>3</span><strong>Dokumenter</strong><small>Neste steg</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span>4</span><strong>Galleri</strong><small>Senere</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>$project_id])); ?>"><span>5</span><strong>Leveranse</strong><small>Sluttlevering</small></a>
                        </div>
                    </section>

                <?php else : ?>
                    <section class="aurora-workspace-card">
                        <?php
                        $search=sanitize_text_field($_GET['s']??''); $status=sanitize_key($_GET['status']??''); $source=sanitize_key($_GET['source']??''); $sort=sanitize_key($_GET['sort']??'created'); $order=strtolower(sanitize_key($_GET['order']??'desc'))==='asc'?'asc':'desc';
                        $contracts=NLS1_Fotoportal_Admin::get_contracts(true,$search,$status,$source,$sort,$order); $clabels=['draft'=>'Utkast','sent'=>'Sendt','signed'=>'Signert','cancelled'=>'Kansellert'];
                        $sort_url=function($key)use($sort,$order){return add_query_arg(['sort'=>$key,'order'=>(($sort===$key&&$order==='asc')?'desc':'asc')]);}; $sort_arrow=function($key)use($sort,$order){return $sort===$key?($order==='asc'?' ↑':' ↓'):' ↕';}; ?>
                        <form method="get" class="aurora-customer-filters aurora-overview-filters"><input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Photographer_Workspace::PAGE_SLUG); ?>"><input type="hidden" name="workspace_view" value="contracts"><label class="aurora-search-field"><span class="dashicons dashicons-search"></span><input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Søk etter kontrakt, prosjekt eller kunde"></label><select name="status"><option value="">Alle statuser</option><?php foreach($clabels as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($status,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select><select name="source"><option value="">Alle typer</option><option value="aurora" <?php selected($source,'aurora'); ?>>Aurora digital</option><option value="upload" <?php selected($source,'upload'); ?>>Opplastet</option></select><button class="aurora-secondary-action" type="submit">Filtrer</button></form>
                        <div class="aurora-workspace-cardhead aurora-overview-head"><div><span class="aurora-workspace-eyebrow">KONTRAKTREGISTER</span><h2><?php echo count($contracts); ?> kontrakter</h2></div></div>
                        <?php if($contracts): ?><div class="aurora-customer-table-wrap"><table class="aurora-customer-table"><thead><tr><?php foreach(['contract'=>'Kontrakt','project'=>'Prosjekt','customer'=>'Kunde','source'=>'Type','created'=>'Opprettet','status'=>'Status'] as $k=>$v): ?><th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url($k)); ?>"><?php echo esc_html($v.$sort_arrow($k)); ?></a></th><?php endforeach; ?><th></th></tr></thead><tbody><?php foreach($contracts as $row): $cs=sanitize_key($row->status??'draft'); ?><tr><td><strong><?php echo esc_html($row->contract_name?:'Kontrakt'); ?></strong><small><?php echo esc_html($row->signer_email??''); ?></small></td><td><strong><?php echo esc_html($row->project_name?:'—'); ?></strong><small><?php echo esc_html($row->project_number?:''); ?></small></td><td><?php echo esc_html($row->client_name?:'—'); ?></td><td><?php echo (($row->contract_source??'aurora')==='upload')?'Opplastet':'Aurora digital'; ?></td><td><?php echo !empty($row->created_at)?esc_html(date_i18n('d.m.Y',strtotime($row->created_at))):'—'; ?></td><td><span class="aurora-status-pill <?php echo $cs==='signed'?'is-active':($cs==='draft'?'is-test':''); ?>"><?php echo esc_html($clabels[$cs]??ucfirst($cs)); ?></span></td><td class="aurora-row-actions"><a class="aurora-icon-link" title="Åpne kontrakt i prosjekt" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>(int)$row->project_id])); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></a></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="aurora-empty-state"><strong>Ingen kontrakter funnet</strong><p>Kontrakter fra prosjektene dine vises her.</p></div><?php endif; ?>
                    </section>
                <?php endif; ?>

            
            <?php elseif ($view === 'documents') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
                $documents = $project ? NLS1_Fotoportal_Admin::get_documents($project_id, true) : [];
                ?>
                <?php if ($project) : ?>
                    <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Tilbake til prosjekt</a>
                    <div class="aurora-project-profile-card">
                        <div class="aurora-project-mark"><span class="dashicons dashicons-media-document"></span></div>
                        <div class="aurora-customer-profile-main">
                            <span class="aurora-workspace-eyebrow"><?php echo esc_html($project->project_number); ?></span>
                            <h2><?php echo esc_html($project->project_name); ?></h2>
                            <div class="aurora-customer-meta"><span><?php echo esc_html($project->client_name ?: '—'); ?></span><span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span></div>
                        </div>
                    </div>

                    <?php if (!empty($_GET['message']) && $_GET['message']==='document_added') : ?>
                        <div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong>Dokument lagt til</strong><p>Dokumentet er registrert på prosjektet.</p></div></div>
                    <?php elseif (!empty($_GET['message']) && $_GET['message']==='document_deleted') : ?>
                        <div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong>Dokument slettet</strong></div></div>
                    <?php endif; ?>

                    <div class="aurora-document-grid">
                        <section class="aurora-workspace-card aurora-workspace-card-wide">
                            <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">DOKUMENTER</span><h2>Prosjektdokumenter</h2></div><span class="aurora-count-badge"><?php echo count($documents); ?></span></div>
                            <?php if ($documents) : ?>
                                <div class="aurora-document-list">
                                <?php foreach ($documents as $doc) : ?>
                                    <div class="aurora-document-item">
                                        <div class="aurora-contract-icon"><span class="dashicons dashicons-media-document"></span></div>
                                        <div class="aurora-contract-main"><strong><?php echo esc_html($doc->document_title); ?></strong><small><?php echo esc_html($doc->document_type ?: 'Dokument'); ?> · <?php echo !empty($doc->created_at) ? esc_html(date_i18n('d.m.Y',strtotime($doc->created_at))) : '—'; ?></small></div>
                                        <span class="aurora-status-pill is-active">Aktiv</span>
                                        <div class="aurora-row-actions">
                                            <?php if (!empty($doc->file_url)) : ?><a class="aurora-icon-link" href="<?php echo esc_url($doc->file_url); ?>" target="_blank" rel="noopener" title="Åpne dokument"><span class="dashicons dashicons-external"></span></a><?php endif; ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-inline-form" onsubmit="return confirm('Slette dokumentet?');">
                                                <input type="hidden" name="action" value="9ls1_fotoportal_delete_document"><input type="hidden" name="document_id" value="<?php echo (int)$doc->id; ?>"><input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_delete_document'); ?>
                                                <button class="aurora-icon-link is-danger" type="submit" title="Slett"><span class="dashicons dashicons-trash"></span></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="aurora-empty-state"><span class="dashicons dashicons-media-document"></span><strong>Ingen dokumenter ennå</strong><p>Legg til første dokument for prosjektet.</p></div>
                            <?php endif; ?>
                        </section>

                        <section class="aurora-workspace-card">
                            <span class="aurora-workspace-eyebrow">NYTT DOKUMENT</span><h2>Legg til dokument</h2>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-contract-form" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="9ls1_fotoportal_add_document"><input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_add_document'); ?>
                                <label>Tittel<input type="text" name="document_title" placeholder="f.eks. Shot List"></label>
                                <label>Type<select name="document_type"><?php foreach (NLS1_Fotoportal_Admin::$document_types as $dtype): ?><option><?php echo esc_html($dtype); ?></option><?php endforeach; ?></select></label>
                                <label>Last opp fil<input type="file" name="document_file"></label>
                                <div class="aurora-or-divider"><span>eller</span></div>
                                <label>Fil-URL<input type="url" name="file_url" placeholder="https://..."></label>
                                <label>Notater<textarea name="notes" rows="5"></textarea></label>
                                <button class="aurora-primary-action" type="submit"><span class="dashicons dashicons-plus-alt2"></span>Legg til dokument</button>
                            </form>
                        </section>
                    </div>

                    <section class="aurora-workspace-card aurora-project-workflow">
                        <div class="aurora-project-steps">
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span>1</span><strong>Prosjekt</strong><small>Detaljer og status</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>"><span>2</span><strong>Kontrakt</strong><small>Avtaler</small></a>
                            <div class="is-current"><span>3</span><strong>Dokumenter</strong><small><?php echo count($documents); ?> registrert</small></div>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span>4</span><strong>Galleri</strong><small>Neste steg</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>$project_id])); ?>"><span>5</span><strong>Leveranse</strong><small>Sluttlevering</small></a>
                        </div>
                    </section>
                <?php else : ?>
                    <section class="aurora-workspace-card"><?php
                        $search=sanitize_text_field($_GET['s']??''); $dtype=sanitize_text_field($_GET['dtype']??''); $sort=sanitize_key($_GET['sort']??'created'); $order=strtolower(sanitize_key($_GET['order']??'desc'))==='asc'?'asc':'desc';
                        $documents=NLS1_Fotoportal_Admin::get_documents(0,true,$search,$dtype,$sort,$order); $sort_url=function($key)use($sort,$order){return add_query_arg(['sort'=>$key,'order'=>(($sort===$key&&$order==='asc')?'desc':'asc')]);}; $sort_arrow=function($key)use($sort,$order){return $sort===$key?($order==='asc'?' ↑':' ↓'):' ↕';}; ?>
                        <form method="get" class="aurora-customer-filters aurora-overview-filters"><input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Photographer_Workspace::PAGE_SLUG); ?>"><input type="hidden" name="workspace_view" value="documents"><label class="aurora-search-field"><span class="dashicons dashicons-search"></span><input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Søk etter dokument, prosjekt eller kunde"></label><select name="dtype"><option value="">Alle dokumenttyper</option><?php foreach(NLS1_Fotoportal_Admin::$document_types as $dt): ?><option value="<?php echo esc_attr($dt); ?>" <?php selected($dtype,$dt); ?>><?php echo esc_html($dt); ?></option><?php endforeach; ?></select><button class="aurora-secondary-action" type="submit">Filtrer</button></form>
                        <div class="aurora-workspace-cardhead aurora-overview-head"><div><span class="aurora-workspace-eyebrow">DOKUMENTREGISTER</span><h2><?php echo count($documents); ?> dokumenter</h2></div></div>
                        <?php if($documents): ?><div class="aurora-customer-table-wrap"><table class="aurora-customer-table"><thead><tr><?php foreach(['document'=>'Dokument','project'=>'Prosjekt','customer'=>'Kunde','type'=>'Type','created'=>'Opprettet'] as $k=>$v): ?><th><a class="aurora-sort-heading" href="<?php echo esc_url($sort_url($k)); ?>"><?php echo esc_html($v.$sort_arrow($k)); ?></a></th><?php endforeach; ?><th>Status</th><th></th></tr></thead><tbody><?php foreach($documents as $row): ?><tr><td><strong><?php echo esc_html($row->document_title?:'Dokument'); ?></strong></td><td><strong><?php echo esc_html($row->project_name?:'—'); ?></strong><small><?php echo esc_html($row->project_number?:''); ?></small></td><td><?php echo esc_html($row->client_name?:'—'); ?></td><td><?php echo esc_html($row->document_type?:'Dokument'); ?></td><td><?php echo !empty($row->created_at)?esc_html(date_i18n('d.m.Y',strtotime($row->created_at))):'—'; ?></td><td><span class="aurora-status-pill is-active">Aktiv</span></td><td class="aurora-row-actions"><?php if(!empty($row->file_url)): ?><a class="aurora-icon-link" title="Åpne dokument" target="_blank" rel="noopener" href="<?php echo esc_url($row->file_url); ?>"><span class="dashicons dashicons-external"></span></a><?php endif; ?><a class="aurora-icon-link" title="Åpne dokumenter i prosjekt" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('documents',['project_id'=>(int)$row->project_id])); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></a></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="aurora-empty-state"><strong>Ingen dokumenter funnet</strong><p>Dokumenter fra prosjektene dine vises her.</p></div><?php endif; ?></section>
                <?php endif; ?>


            <?php elseif ($view === 'galleries') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
                $gallery_id = absint($_GET['gallery_id'] ?? 0);
                $detail_gallery = $gallery_id ? NLS1_Fotoportal_Admin::get_gallery($gallery_id) : null;
                if ($detail_gallery && !$project_id) {
                    $project_id = (int)$detail_gallery->project_id;
                    $project = NLS1_Fotoportal_Admin::get_project($project_id);
                }
                if ($detail_gallery && (int)$detail_gallery->project_id !== (int)$project_id) $detail_gallery = null;
                $galleries = $project ? NLS1_Fotoportal_Admin::get_galleries($project_id, true) : NLS1_Fotoportal_Admin::get_galleries(0, true);
                $gallery_unlocked = $project ? NLS1_Fotoportal_Admin::has_signed_contract($project_id) : false;
                ?>

                <?php if ($project) : ?>
                    <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Tilbake til prosjekt</a>
                    <div class="aurora-project-profile-card">
                        <div class="aurora-project-mark"><span class="dashicons dashicons-format-gallery"></span></div>
                        <div class="aurora-customer-profile-main">
                            <span class="aurora-workspace-eyebrow"><?php echo esc_html($project->project_number); ?></span>
                            <h2><?php echo esc_html($project->project_name); ?></h2>
                            <div class="aurora-customer-meta"><span><?php echo esc_html($project->client_name ?: '—'); ?></span><span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                $gallery_message = sanitize_key($_GET['message'] ?? '');
                $message_map = [
                    'gallery_uploaded' => ['Galleri lastet opp', 'ZIP-filen er pakket ut og preview/thumbnails er generert.'],
                    'gallery_deleted' => ['Galleri slettet', 'Galleriet er fjernet.'],
                    'gallery_regenerated' => ['Galleri regenerert', 'Preview og thumbnails er generert på nytt.'],
                    'proof_pdf_generated' => ['PDF generert', 'Premium Proof PDF er generert.'],
                    'gallery_updated' => ['Galleri oppdatert', 'Navn og beskrivelse er lagret.'],
                ];
                if (isset($message_map[$gallery_message])) :
                ?>
                    <div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong><?php echo esc_html($message_map[$gallery_message][0]); ?></strong><p><?php echo esc_html($message_map[$gallery_message][1]); ?></p></div></div>
                <?php elseif ($gallery_message === 'gallery_contract_required') : ?>
                    <div class="aurora-workspace-alert"><span class="dashicons dashicons-lock"></span><div><strong>Signert kontrakt kreves</strong><p>Galleri kan først opprettes når prosjektet har minst én signert kontrakt.</p></div></div>
                <?php endif; ?>

                <?php if ($detail_gallery) :
                    $detail_images = NLS1_Fotoportal_Admin::get_gallery_images((int)$detail_gallery->id, 10000);
                    $customer_gallery_url = NLS1_Fotoportal_Admin::gallery_public_url($detail_gallery);
                    $gallery_activity = NLS1_Fotoportal_Admin::gallery_interaction_counts((int)$detail_gallery->id,(int)$detail_gallery->account_id);
                ?>
                    <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Tilbake til gallerier</a>
                    <section class="aurora-workspace-card aurora-gallery-detail-head">
                        <div>
                            <span class="aurora-workspace-eyebrow">GALLERI</span>
                            <h2><?php echo esc_html($detail_gallery->gallery_title); ?></h2>
                            <p><?php echo (int)$detail_gallery->original_count; ?> bilder · <?php echo esc_html($detail_gallery->gallery_number); ?></p><?php if(!empty($detail_gallery->gallery_description)): ?><p class="aurora-gallery-description"><?php echo esc_html($detail_gallery->gallery_description); ?></p><?php endif; ?><div class="aurora-gallery-activity"><span>♡ <b><?php echo (int)$gallery_activity['favorites']; ?></b> favoritter</span><span>✓ <b><?php echo (int)$gallery_activity['approved']; ?></b> valgt</span><span>💬 <b><?php echo (int)$gallery_activity['comments']; ?></b> kommentarer</span></div>
                        </div>
                        <div class="aurora-gallery-detail-actions">
                            <a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id,'add_images'=>(int)$detail_gallery->id])); ?>"><span class="dashicons dashicons-images-alt2"></span>Legg til bilder</a>
                            <a class="aurora-primary-action" href="<?php echo esc_url($customer_gallery_url); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-external"></span>Se kundegalleri</a><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-inline-form"><input type="hidden" name="action" value="9ls1_fotoportal_send_customer_portal"><input type="hidden" name="gallery_id" value="<?php echo (int)$detail_gallery->id; ?>"><?php wp_nonce_field('9ls1_fotoportal_send_customer_portal'); ?><button class="aurora-secondary-action" type="submit"><span class="dashicons dashicons-email-alt"></span>Send URL til kunde</button></form>
                        </div>
                    </section>

                    <section class="aurora-workspace-card aurora-gallery-edit-card"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">GALLERIINFO</span><h2>Navn og beskrivelse</h2><p>Dette er teksten kunden ser i galleriet. Kundens navn brukes ikke som beskrivelse.</p></div></div><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-gallery-edit-form"><input type="hidden" name="action" value="9ls1_fotoportal_update_gallery_details"><input type="hidden" name="gallery_id" value="<?php echo (int)$detail_gallery->id; ?>"><?php wp_nonce_field('9ls1_fotoportal_update_gallery_details'); ?><label>Gallerinavn<input type="text" name="gallery_title" value="<?php echo esc_attr($detail_gallery->gallery_title); ?>" required></label><label>Beskrivelse<textarea name="gallery_description" rows="3" placeholder="Skriv en kort beskrivelse av galleriet …"><?php echo esc_textarea($detail_gallery->gallery_description??''); ?></textarea></label><button class="aurora-primary-action" type="submit">Lagre galleriinfo</button></form></section>

                    <section class="aurora-workspace-card aurora-gallery-share-card">
                        <div><span class="aurora-workspace-eyebrow">KUNDEGALLERI</span><strong>Delbar URL</strong><p>Denne lenken åpner kundens rene galleri uten Aurora-administrasjonen.</p></div>
                        <?php $customer_portal_url=NLS1_Fotoportal_Admin::customer_portal_url((int)$detail_gallery->client_id); ?>
                        <div class="aurora-gallery-url-row">
                            <input type="text" readonly value="<?php echo esc_attr($customer_gallery_url); ?>" aria-label="URL til kundegalleri">
                            <button type="button" class="aurora-secondary-action" data-copy-gallery-url="<?php echo esc_attr($customer_gallery_url); ?>"><span class="dashicons dashicons-admin-page"></span>Kopier</button>
                        </div><div class="aurora-gallery-url-row aurora-customer-portal-row"><input type="text" readonly value="<?php echo esc_attr($customer_portal_url); ?>"><button type="button" class="aurora-secondary-action" data-copy-gallery-url="<?php echo esc_attr($customer_portal_url); ?>">Kopier hovedportal</button></div>
                    </section>

                    <?php $gh=NLS1_Fotoportal_Admin::gallery_hero_settings((int)$detail_gallery->id);$fallback=!empty($detail_images)?($detail_images[0]->preview_url?:$detail_images[0]->thumbnail_url):'';$ghurl=NLS1_Fotoportal_Admin::hero_image_url($gh,$detail_images,$fallback); ?>
                    <section class="aurora-workspace-card aurora-hero-designer-card"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">HERO DESIGNER</span><h2>Galleri-cover</h2><p>Velg bilde, focal point, størrelse og overlay direkte for kundegalleriet.</p></div></div>
                    <div class="aurora-hero-editor-preview size-<?php echo esc_attr($gh['size']); ?>" style="background-image:url('<?php echo esc_url($ghurl); ?>');background-position:<?php echo (int)$gh['focal_x']; ?>% <?php echo (int)$gh['focal_y']; ?>%"><span class="aurora-hero-editor-overlay" style="background:<?php echo esc_attr($gh['overlay_color']); ?>;opacity:<?php echo esc_attr($gh['overlay_opacity']/100); ?>"></span><div class="aurora-hero-editor-copy"><strong><?php echo esc_html($detail_gallery->gallery_title); ?></strong><span><?php echo esc_html($detail_gallery->gallery_description??''); ?></span></div><span class="aurora-focal-dot" style="left:<?php echo (int)$gh['focal_x']; ?>%;top:<?php echo (int)$gh['focal_y']; ?>%"></span></div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-hero-controls"><input type="hidden" name="action" value="9ls1_fotoportal_save_gallery_hero"><input type="hidden" name="gallery_id" value="<?php echo (int)$detail_gallery->id; ?>"><?php wp_nonce_field('9ls1_fotoportal_save_gallery_hero'); ?><label>Størrelse<select name="hero_size"><option value="small" <?php selected($gh['size'],'small'); ?>>Small</option><option value="medium" <?php selected($gh['size'],'medium'); ?>>Medium</option><option value="large" <?php selected($gh['size'],'large'); ?>>Large</option></select></label><label>Overlay<input type="color" name="overlay_color" value="<?php echo esc_attr($gh['overlay_color']); ?>"></label><label>Transparens <b><?php echo (int)$gh['overlay_opacity']; ?>%</b><input type="range" name="overlay_opacity" min="0" max="80" value="<?php echo (int)$gh['overlay_opacity']; ?>"></label><label>Focal X<input type="range" name="focal_x" min="0" max="100" value="<?php echo (int)$gh['focal_x']; ?>"></label><label>Focal Y<input type="range" name="focal_y" min="0" max="100" value="<?php echo (int)$gh['focal_y']; ?>"></label><button class="aurora-primary-action" type="submit">Lagre Hero</button><div class="aurora-hero-image-picker"><?php foreach($detail_images as $im):$u=$im->thumbnail_url?:$im->preview_url;if(!$u)continue;?><label><input type="radio" name="hero_image_id" value="<?php echo (int)$im->id; ?>" <?php checked((int)$gh['image_id'],(int)$im->id); ?>><img src="<?php echo esc_url($u); ?>" alt=""></label><?php endforeach;?></div></form></section>

                    <section class="aurora-gallery-masonry-card">
                        <?php if ($detail_images) : ?>
                            <div class="aurora-gallery-masonry">
                                <?php foreach ($detail_images as $image) :
                                    $display_url = $image->preview_url ?: ($image->thumbnail_url ?: '');
                                    if (!$display_url) continue;
                                ?>
                                    <button type="button" class="aurora-masonry-item" data-gallery-image="<?php echo esc_url($display_url); ?>" aria-label="Åpne bilde">
                                        <img loading="lazy" src="<?php echo esc_url($display_url); ?>" alt="">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="aurora-empty-state"><span class="dashicons dashicons-format-gallery"></span><strong>Ingen bilder å vise</strong><p>Legg til bilder i galleriet først.</p></div>
                        <?php endif; ?>
                    </section>

                    <div class="aurora-gallery-lightbox" data-gallery-lightbox>
                        <button type="button" class="aurora-gallery-lightbox-close" aria-label="Lukk">×</button>
                        <button type="button" class="aurora-gallery-lightbox-nav is-prev" aria-label="Forrige bilde">‹</button>
                        <img src="" alt="">
                        <button type="button" class="aurora-gallery-lightbox-nav is-next" aria-label="Neste bilde">›</button>
                    </div>
                <?php else : ?>

                <section class="aurora-workspace-card aurora-gallery-toolbar">
                    <div class="aurora-workspace-cardhead">
                        <div><span class="aurora-workspace-eyebrow">GALLERIER</span><h2><?php echo $project ? 'Prosjektgallerier' : 'Alle gallerier'; ?></h2></div>
                        <?php if ($project && $gallery_unlocked) : ?><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id,'new_gallery'=>1])); ?>"><span class="dashicons dashicons-plus-alt2"></span>Nytt galleri</a><?php endif; ?>
                    </div>
                    <?php if (!$project) : ?><p>Velg et prosjekt for å opprette et nytt galleri. Oversikten nedenfor viser gallerier på denne fotografkontoen.</p><?php endif; ?>
                </section>

                <section class="aurora-workspace-card">
                    <?php if ($galleries) : ?>
                        <div class="aurora-gallery-list">
                            <?php foreach ($galleries as $gal) :
                                $thumbs = NLS1_Fotoportal_Admin::get_gallery_images((int)$gal->id, 1);
                                $thumb = !empty($thumbs[0]->thumbnail_url) ? $thumbs[0]->thumbnail_url : '';
                                $gal_project_id = (int)$gal->project_id;
                                $remaining = '';
                                if (!empty($gal->downloadable_until)) {
                                    $days = (int)floor((strtotime($gal->downloadable_until . ' 23:59:59') - current_time('timestamp')) / DAY_IN_SECONDS);
                                    $remaining = $days >= 0 ? $days . ' dager igjen' : 'Utløpt';
                                }
                                $status_label = $gal->status === 'preview_generated' ? 'Klar' : ($gal->status === 'uploaded' ? 'Lastet opp' : ucfirst(str_replace('_',' ',$gal->status)));
                                $gallery_pdfs = NLS1_Fotoportal_Admin::get_gallery_export_pdfs($gal);
                                $latest_pdf = $gallery_pdfs ? end($gallery_pdfs) : null;
                            ?>
                                <article class="aurora-gallery-row">
                                    <div class="aurora-gallery-thumb">
                                        <?php if ($thumb) : ?><img src="<?php echo esc_url($thumb); ?>" alt=""><?php else : ?><span class="dashicons dashicons-format-gallery"></span><?php endif; ?>
                                    </div>
                                    <div class="aurora-gallery-main">
                                        <div class="aurora-gallery-titleline"><strong><?php echo esc_html($gal->gallery_title); ?></strong><?php if (!empty($gal->is_test)) : ?><span class="aurora-test-badge">Test</span><?php endif; ?></div>
                                        <small><?php echo esc_html($gal->gallery_number); ?> · <?php echo (int)$gal->original_count; ?> bilder</small>
                                        <?php if (!$project) : ?><a class="aurora-gallery-projectlink" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$gal_project_id])); ?>"><?php echo esc_html(($gal->project_number ?? '') . ' · ' . ($gal->project_name ?? '')); ?></a><?php endif; ?>
                                    </div>
                                    <div class="aurora-gallery-meta"><span>Nedlastbar til</span><strong><?php echo !empty($gal->downloadable_until) ? esc_html(date_i18n('d.m.Y',strtotime($gal->downloadable_until))) : 'Ikke satt'; ?></strong><small><?php echo esc_html($remaining); ?></small></div>
                                    <div class="aurora-gallery-meta"><span>Status</span><strong class="aurora-status-pill is-active"><?php echo esc_html($status_label); ?></strong><small><?php echo (int)$gal->preview_count; ?> preview · <?php echo (int)$gal->thumbnail_count; ?> thumbnails</small></div>
                                    <div class="aurora-row-actions aurora-gallery-actions">
                                        <a class="aurora-icon-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$gal_project_id,'gallery_id'=>(int)$gal->id])); ?>" title="Åpne galleri"><span class="dashicons dashicons-visibility"></span></a>
                                        <a class="aurora-icon-link is-add-images" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$gal_project_id,'add_images'=>(int)$gal->id])); ?>" title="Legg til flere bilder"><span class="dashicons dashicons-images-alt2"></span></a>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-inline-form">
                                            <input type="hidden" name="action" value="9ls1_fotoportal_regenerate_gallery"><input type="hidden" name="gallery_id" value="<?php echo (int)$gal->id; ?>"><input type="hidden" name="project_id" value="<?php echo $gal_project_id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_regenerate_gallery'); ?>
                                            <button class="aurora-icon-link" type="submit" title="Regenerer preview"><span class="dashicons dashicons-update"></span></button>
                                        </form>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-inline-form">
                                            <input type="hidden" name="action" value="9ls1_fotoportal_generate_proof_pdf"><input type="hidden" name="gallery_id" value="<?php echo (int)$gal->id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_generate_proof_pdf'); ?>
                                            <button class="aurora-icon-link" type="submit" title="Generer Premium Proof PDF"><span class="dashicons dashicons-pdf"></span></button>
                                        </form>
                                        <?php if ($latest_pdf) : ?><a class="aurora-icon-link is-pdf-ready" href="<?php echo esc_url($latest_pdf['url']); ?>" target="_blank" rel="noopener" title="Se sist genererte Premium Proof PDF"><span class="dashicons dashicons-pdf"></span></a><?php endif; ?>
                                        <details class="aurora-more-actions"><summary class="aurora-icon-link" title="Flere handlinger"><span class="dashicons dashicons-ellipsis"></span></summary>
                                            <div class="aurora-more-menu">
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette galleriet?');">
                                                    <input type="hidden" name="action" value="9ls1_fotoportal_delete_gallery"><input type="hidden" name="gallery_id" value="<?php echo (int)$gal->id; ?>"><input type="hidden" name="project_id" value="<?php echo $gal_project_id; ?>"><input type="hidden" name="delete_files" value="0"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_delete_gallery'); ?>
                                                    <button type="submit" class="is-danger">Slett galleri</button>
                                                </form>
                                            </div>
                                        </details>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="aurora-empty-state"><span class="dashicons dashicons-format-gallery"></span><strong>Ingen gallerier ennå</strong><p><?php echo $project ? 'Opprett prosjektets første galleri når kontrakten er signert.' : 'Ingen gallerier er registrert på fotografkontoen.'; ?></p></div>
                    <?php endif; ?>
                </section>

                <?php if ($project) : ?>
                    <?php
                    $append_gallery=null;
                    $append_gallery_id=(int)($_GET['add_images']??0);
                    if($append_gallery_id){
                        $candidate_gallery=NLS1_Fotoportal_Admin::get_gallery($append_gallery_id);
                        if($candidate_gallery && (int)$candidate_gallery->project_id===(int)$project_id) $append_gallery=$candidate_gallery;
                    }
                    ?>
                    <?php if ($gallery_unlocked && $append_gallery) : ?>
                        <section id="aurora-add-gallery-images" class="aurora-workspace-card aurora-gallery-upload-card">
                            <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">LEGG TIL BILDER</span><h2><?php echo esc_html($append_gallery->gallery_title); ?></h2><p>Eksisterende bilder beholdes. Nye filer legges til i samme galleri og behandles av Aurora.</p></div><span class="aurora-status-pill is-active"><?php echo (int)$append_gallery->original_count; ?> bilder</span></div>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="aurora-gallery-upload-form">
                                <input type="hidden" name="action" value="9ls1_fotoportal_add_gallery_images"><input type="hidden" name="gallery_id" value="<?php echo (int)$append_gallery->id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_add_gallery_images'); ?>
                                <div class="aurora-form-grid">
                                    <label>ZIP-fil <small>Valgfritt</small><input type="file" name="gallery_zip" accept=".zip"></label>
                                    <label>Enkeltbilder <small>Velg flere samtidig</small><input type="file" name="gallery_images[]" accept=".jpg,.jpeg,.png,.webp,.tif,.tiff,image/jpeg,image/png,image/webp,image/tiff" multiple></label>
                                </div>
                                <p class="aurora-form-help">Velg ZIP, flere enkeltbilder eller begge deler. Eksisterende originaler slettes ikke, og like filnavn får automatisk et unikt navn.</p>
                                <div class="aurora-step-actions"><button class="aurora-primary-action" type="submit"><span class="dashicons dashicons-upload"></span>Legg til bilder</button><a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>">Avbryt</a></div>
                            </form>
                        </section>
                    <?php elseif ($gallery_unlocked && !empty($_GET['new_gallery'])) : ?>
                        <section id="aurora-new-gallery" class="aurora-workspace-card aurora-gallery-upload-card">
                            <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">NYTT GALLERI</span><h2>Last opp bilder</h2></div><span class="aurora-status-pill is-active">Kontrakt signert</span></div>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="aurora-gallery-upload-form">
                                <input type="hidden" name="action" value="9ls1_fotoportal_upload_gallery_zip"><input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_upload_gallery_zip'); ?>
                                <div class="aurora-form-grid">
                                    <label>Gallerinavn<input type="text" name="gallery_title" placeholder="f.eks. Hovedgalleri"></label>
                                    <label>ZIP-fil<input type="file" name="gallery_zip" accept=".zip" required></label>
                                    <label>Nedlastbar til<input type="date" name="downloadable_until"></label>
                                    <label>Automatisk slettedato<input type="date" name="auto_delete_at"></label>
                                </div>
                                <div class="aurora-gallery-options">
                                    <label><input type="checkbox" name="local_backup_confirmed" value="1">Lokal backup er bekreftet</label>
                                    <label><input type="checkbox" name="watermark_enabled" value="1" checked>Vannmerke på preview</label>
                                    <label><input type="checkbox" name="download_enabled" value="1">Aktiver nedlasting</label>
                                </div>
                                <p class="aurora-form-help">Aurora oppretter original, preview, thumbnails, ZIP og export automatisk.</p>
                                <button class="aurora-primary-action" type="submit"><span class="dashicons dashicons-upload"></span>Last opp og opprett galleri</button>
                            </form>
                        </section>
                    <?php elseif (!$gallery_unlocked) : ?>
                        <section class="aurora-workspace-card aurora-gallery-locked">
                            <span class="dashicons dashicons-lock"></span><div><span class="aurora-workspace-eyebrow">GALLERI LÅST</span><h2>Kontrakten må være signert først</h2><p>Dette er håndhevet både i arbeidsflaten og i opplastingshandleren.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>">Gå til Kontrakt</a></div>
                        </section>
                    <?php endif; ?>

                <?php endif; // gallery detail/list ?>

                    <section class="aurora-workspace-card aurora-project-workflow">
                        <div class="aurora-project-steps">
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span>1</span><strong>Prosjekt</strong><small>Detaljer og status</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>"><span>2</span><strong>Kontrakt</strong><small>Avtaler</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id])); ?>"><span>3</span><strong>Dokumenter</strong><small>Underlag</small></a>
                            <div class="is-current"><span>4</span><strong>Galleri</strong><small><?php echo count($galleries); ?> gallerier</small></div>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>$project_id])); ?>"><span>5</span><strong>Leveranse</strong><small>Neste steg</small></a>
                        </div>
                    </section>
                <?php endif; ?>


            <?php elseif ($view === 'selections') : ?>
                <?php
                $selection_items = NLS1_Fotoportal_Admin::photographer_selection_items((int)$account->id);
                $selection_counts = ['all'=>count($selection_items),'favorites'=>0,'approved'=>0,'comments'=>0];
                $selection_clients=[];$selection_projects=[];$selection_galleries=[];
                foreach($selection_items as $si){
                    if(!empty($si->is_favorite))$selection_counts['favorites']++;
                    if(!empty($si->is_selected))$selection_counts['approved']++;
                    if((int)$si->comment_count>0)$selection_counts['comments']++;
                    if($si->client_id)$selection_clients[(int)$si->client_id]=$si->client_name;
                    $selection_projects[(int)$si->project_id]=$si->project_name;
                    $selection_galleries[(int)$si->gallery_id]=$si->gallery_title;
                }
                ?>
                <section class="aurora-workspace-card aurora-selection-overview">
                    <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">KUNDEAKTIVITET</span><h2>Bildevalg på tvers av gallerier</h2><p>Se raskt hvilke bilder kundene har favorisert, valgt eller kommentert.</p></div><strong class="aurora-selection-total"><?php echo (int)$selection_counts['all']; ?> bilder</strong></div>
                    <div class="aurora-selection-filters" data-selection-filters>
                        <button type="button" class="is-active" data-selection-kind="all">Alle <b><?php echo (int)$selection_counts['all']; ?></b></button>
                        <button type="button" data-selection-kind="favorite">♡ Favoritter <b><?php echo (int)$selection_counts['favorites']; ?></b></button>
                        <button type="button" data-selection-kind="approved">✓ Valgte <b><?php echo (int)$selection_counts['approved']; ?></b></button>
                        <button type="button" data-selection-kind="comment">💬 Redigeringsønsker <b><?php echo (int)$selection_counts['comments']; ?></b></button>
                    </div>
                    <div class="aurora-selection-selects">
                        <label>Kunde<select data-selection-client><option value="">Alle kunder</option><?php foreach($selection_clients as $id=>$name): ?><option value="<?php echo (int)$id; ?>"><?php echo esc_html($name); ?></option><?php endforeach; ?></select></label>
                        <label>Prosjekt<select data-selection-project><option value="">Alle prosjekter</option><?php foreach($selection_projects as $id=>$name): ?><option value="<?php echo (int)$id; ?>"><?php echo esc_html($name); ?></option><?php endforeach; ?></select></label>
                        <label>Galleri<select data-selection-gallery><option value="">Alle gallerier</option><?php foreach($selection_galleries as $id=>$name): ?><option value="<?php echo (int)$id; ?>"><?php echo esc_html($name); ?></option><?php endforeach; ?></select></label><label>Status<select data-selection-status><option value="">Alle statuser</option><option value="open">Ingen forespørsel</option><option value="submitted">Redigeringsønske</option><option value="processing">Under behandling</option><option value="ready">Ferdig behandlet</option></select></label>
                    </div>
                </section>
                <?php if($selection_items): ?><div class="aurora-selection-grid" data-selection-grid><?php foreach($selection_items as $si): $img=$si->thumbnail_url?:$si->preview_url; if(!$img)continue; ?>
                    <article class="aurora-selection-card" data-favorite="<?php echo !empty($si->is_favorite)?'1':'0'; ?>" data-approved="<?php echo !empty($si->is_selected)?'1':'0'; ?>" data-comment="<?php echo (int)$si->comment_count>0?'1':'0'; ?>" data-client="<?php echo (int)$si->client_id; ?>" data-project="<?php echo (int)$si->project_id; ?>" data-gallery="<?php echo (int)$si->gallery_id; ?>" data-status="<?php echo esc_attr($si->selection_status?:'open'); ?>">
                        <button type="button" class="aurora-selection-image" data-gallery-image="<?php echo esc_url($si->preview_url?:$img); ?>"><img loading="lazy" src="<?php echo esc_url($img); ?>" alt=""><span><?php if(!empty($si->is_favorite)): ?><i>♡</i><?php endif; ?><?php if(!empty($si->is_selected)): ?><i>✓</i><?php endif; ?><?php if((int)$si->comment_count): ?><i>💬 <?php echo (int)$si->comment_count; ?></i><?php endif; ?></span></button>
                        <div class="aurora-selection-body"><strong><?php echo esc_html($si->original_filename); ?></strong><small><?php echo esc_html($si->client_name?:'Ukjent kunde'); ?> · <?php echo esc_html($si->project_name); ?></small><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>(int)$si->project_id,'gallery_id'=>(int)$si->gallery_id])); ?>"><?php echo esc_html($si->gallery_title); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-selection-status-form"><input type="hidden" name="action" value="9ls1_fotoportal_update_selection_status"><input type="hidden" name="gallery_id" value="<?php echo (int)$si->gallery_id; ?>"><?php wp_nonce_field('9ls1_fotoportal_update_selection_status'); ?><select name="selection_status" onchange="this.form.submit()"><option value="open" <?php selected($si->selection_status?:'open','open'); ?>>Ingen forespørsel</option><option value="submitted" <?php selected($si->selection_status,'submitted'); ?>>Redigeringsønske</option><option value="processing" <?php selected($si->selection_status,'processing'); ?>>Under behandling</option><option value="ready" <?php selected($si->selection_status,'ready'); ?>>Ferdig behandlet</option></select></form><?php if(!empty($si->latest_comment)): ?><div class="aurora-selection-comment"><span class="dashicons dashicons-format-chat"></span><p><?php echo esc_html($si->latest_comment); ?></p></div><?php endif; ?></div>
                    </article>
                <?php endforeach; ?></div><div class="aurora-selection-empty-filter" data-selection-empty hidden>Ingen bilder matcher filteret.</div><?php else: ?><div class="aurora-empty-state"><span class="dashicons dashicons-yes-alt"></span><strong>Ingen kundeaktivitet ennå</strong><p>Favoritter, valgte bilder og kommentarer vil automatisk vises her.</p></div><?php endif; ?>
                <div class="aurora-gallery-lightbox" data-gallery-lightbox><button type="button" class="aurora-gallery-lightbox-close" aria-label="Lukk">×</button><button type="button" class="aurora-gallery-lightbox-nav is-prev" aria-label="Forrige bilde">‹</button><img src="" alt=""><button type="button" class="aurora-gallery-lightbox-nav is-next" aria-label="Neste bilde">›</button></div>

            <?php elseif ($view === 'hq_delivery') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
                $project_galleries = $project ? NLS1_Fotoportal_Admin::get_galleries($project_id, true) : [];
                $signed = $project ? NLS1_Fotoportal_Admin::has_signed_contract($project_id) : false;
                $delivery_flow = $project ? NLS1_Fotoportal_Admin::project_delivery_state($project_id) : [];
                $paid = !empty($delivery_flow['paid']);
                $ready_galleries = 0; $download_galleries = 0; $image_total = 0;
                foreach ($project_galleries as $delivery_gallery) {
                    if (in_array($delivery_gallery->status, ['preview_generated','ready'], true)) $ready_galleries++;
                    if (!empty($delivery_gallery->download_enabled)) $download_galleries++;
                    $image_total += (int)$delivery_gallery->original_count;
                }
                ?>
                <?php if ($project) : ?>
                    <a class="aurora-back-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span>Tilbake til prosjekt</a>
                    <div class="aurora-project-profile-card">
                        <div class="aurora-project-mark"><span class="dashicons dashicons-download"></span></div>
                        <div class="aurora-customer-profile-main">
                            <span class="aurora-workspace-eyebrow"><?php echo esc_html($project->project_number); ?></span>
                            <h2><?php echo esc_html($project->project_name); ?></h2>
                            <div class="aurora-customer-meta"><span><?php echo esc_html($project->client_name ?: '—'); ?></span><span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span></div>
                        </div>
                    </div>

                    <div class="aurora-delivery-summary-grid">
                        <section class="aurora-workspace-card aurora-delivery-summary"><span class="dashicons dashicons-format-gallery"></span><div><small>Gallerier</small><strong><?php echo count($project_galleries); ?></strong><p><?php echo (int)$image_total; ?> bilder totalt</p></div></section>
                        <section class="aurora-workspace-card aurora-delivery-summary"><span class="dashicons dashicons-yes-alt"></span><div><small>Preview klare</small><strong><?php echo (int)$ready_galleries; ?> / <?php echo count($project_galleries); ?></strong><p>Gallerier med generert preview</p></div></section>
                        <section class="aurora-workspace-card aurora-delivery-summary"><span class="dashicons dashicons-download"></span><div><small>Nedlasting</small><strong><?php echo (int)$download_galleries; ?></strong><p>Gallerier med nedlasting aktivert</p></div></section>
                        <section class="aurora-workspace-card aurora-delivery-summary"><span class="dashicons dashicons-media-document"></span><div><small>Kontrakt</small><strong><?php echo $signed ? 'Signert' : 'Mangler'; ?></strong><p><?php echo $signed ? 'Prosjektet er godkjent for galleri.' : 'Signering må fullføres.'; ?></p></div></section>
                    </div>

                    <section class="aurora-workspace-card">
                        <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">STEG 5</span><h2>Leveranse</h2><p>Klargjør prosjektet for kundeportal, godkjenning og endelig levering.</p></div><span class="aurora-status-pill <?php echo $project->status === 'delivered' ? 'is-active' : ''; ?>"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span></div>
                        <?php if ($project_galleries) : ?>
                            <div class="aurora-delivery-gallery-list">
                            <?php foreach ($project_galleries as $gal) :
                                $delivery_ready = in_array($gal->status, ['preview_generated','ready'], true);
                            ?>
                                <div class="aurora-delivery-gallery-row">
                                    <span class="aurora-delivery-gallery-icon dashicons dashicons-format-gallery"></span>
                                    <div><strong><?php echo esc_html($gal->gallery_title); ?></strong><small><?php echo esc_html($gal->gallery_number); ?> · <?php echo (int)$gal->original_count; ?> bilder</small></div>
                                    <div><span>Preview</span><strong><?php echo $delivery_ready ? 'Klar' : 'Ikke klar'; ?></strong></div>
                                    <div><span>Nedlasting</span><strong><?php echo !empty($gal->download_enabled) ? 'Aktivert' : 'Av'; ?></strong></div>
                                    <div><span>Tilgjengelig til</span><strong><?php echo !empty($gal->downloadable_until) ? esc_html(date_i18n('d.m.Y',strtotime($gal->downloadable_until))) : 'Ikke satt'; ?></strong></div>
                                    <a class="aurora-icon-link" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>" title="Administrer galleri"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="aurora-empty-state"><span class="dashicons dashicons-format-gallery"></span><strong>Ingen gallerier klare for levering</strong><p>Opprett og klargjør et galleri før prosjektet leveres.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>">Gå til Galleri</a></div>
                        <?php endif; ?>
                    </section>

                    <section class="aurora-workspace-card aurora-delivery-status-card">
                        <div><span class="aurora-workspace-eyebrow">BETALING OG PORTALTILGANG</span><h2><?php echo $paid?'Faktura er betalt':'Venter på betaling'; ?></h2><p>Når kontrakten er signert, minst ett galleri finnes og faktura markeres som betalt, frigir Aurora kundeportalen. Kunden får e-post med innloggingslenke til sin faste hovedportal.</p></div>
                        <div class="aurora-delivery-gates">
                            <span class="<?php echo $signed?'is-ok':''; ?>">Kontrakt <?php echo $signed?'✓':'mangler'; ?></span>
                            <span class="<?php echo !empty($project_galleries)?'is-ok':''; ?>">Galleri <?php echo !empty($project_galleries)?'✓':'mangler'; ?></span>
                            <span class="<?php echo $paid?'is-ok':''; ?>">Faktura <?php echo $paid?'betalt ✓':'ubetalt'; ?></span>
                        </div>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-delivery-status-form">
                            <input type="hidden" name="action" value="9ls1_fotoportal_update_payment_status"><input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>"><?php wp_nonce_field('9ls1_fotoportal_update_payment_status'); ?>
                            <input type="hidden" name="payment_status" value="<?php echo $paid?'unpaid':'paid'; ?>">
                            <button class="<?php echo $paid?'aurora-secondary-action':'aurora-primary-action'; ?>" type="submit"><?php echo $paid?'Marker som ikke betalt':'Marker faktura som betalt'; ?></button>
                        </form>
                        <?php if($paid && !empty($delivery_flow['portal_ready'])): ?><p class="aurora-delivery-ready-note">✓ Kundeportalen er frigitt. Kunden må logge inn med e-postkontoen som er knyttet til kunden.</p><?php elseif($paid): ?><p class="aurora-form-help">Betaling er registrert, men portalen åpnes først når kontrakten er signert og galleri finnes.</p><?php endif; ?>
                    </section>

                    <section class="aurora-workspace-card aurora-project-workflow">
                        <div class="aurora-project-steps">
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects',['project_id'=>$project_id])); ?>"><span>1</span><strong>Prosjekt</strong><small>Detaljer og status</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>"><span>2</span><strong>Kontrakt</strong><small>Avtaler</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id])); ?>"><span>3</span><strong>Dokumenter</strong><small>Underlag</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span>4</span><strong>Galleri</strong><small><?php echo count($project_galleries); ?> gallerier</small></a>
                            <div class="is-current"><span>5</span><strong>Leveranse</strong><small>Sluttlevering</small></div>
                        </div>
                    </section>
                <?php else : ?>
                    <?php $delivery_projects=NLS1_Fotoportal_Admin::get_projects(false); $delivery_filter=sanitize_key($_GET['filter']??'all'); ?>
                    <section class="aurora-workspace-card"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">LEVERANSER</span><h2>Alle prosjekter og leveransestatus</h2><p>Åpne et prosjekt for betaling, portalfrigivelse og endelig levering.</p></div></div>
                    <div class="aurora-resource-filters"><a class="<?php echo $delivery_filter==='all'?'is-active':'';?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery'));?>">Alle</a><a class="<?php echo $delivery_filter==='unpaid'?'is-active':'';?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['filter'=>'unpaid']));?>">Mangler betaling</a><a class="<?php echo $delivery_filter==='ready'?'is-active':'';?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['filter'=>'ready']));?>">Klar til levering</a></div>
                    <div class="aurora-resource-list aurora-delivery-overview-list"><?php $shown=0; foreach($delivery_projects as $dp): $ds=NLS1_Fotoportal_Admin::project_delivery_state((int)$dp->id); $is_unpaid=empty($ds['paid']); $is_ready=!empty($ds['portal_ready']); if($delivery_filter==='unpaid'&&!$is_unpaid)continue; if($delivery_filter==='ready'&&!$is_ready)continue; $shown++; ?><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>(int)$dp->id]));?>"><span class="dashicons dashicons-download"></span><div><strong><?php echo esc_html($dp->project_name);?></strong><small><?php echo esc_html($dp->client_name?:'—');?> · <?php echo esc_html(NLS1_Fotoportal_Admin::status_label($dp->status));?> · <?php echo !empty($ds['paid'])?'Betalt':'Mangler betaling';?></small></div><span class="dashicons dashicons-arrow-right-alt2"></span></a><?php endforeach; if(!$shown):?><div class="aurora-empty-state"><strong>Ingen prosjekter i dette filteret</strong></div><?php endif;?></div></section>
                <?php endif; ?>

<?php elseif ($view === 'settings') : ?>
<?php $ps=NLS1_Fotoportal_Admin::photographer_portal_settings(); $editing=!empty($_GET['edit_profile']); ?>
<?php if($editing): ?>
<section class="aurora-workspace-card aurora-profile-edit-full"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">FOTOGRAFKONTO</span><h2>Rediger profil</h2></div><a class="aurora-text-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings')); ?>">Avbryt</a></div>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="aurora-portal-settings-form"><input type="hidden" name="action" value="9ls1_fotoportal_save_portal_settings"><?php wp_nonce_field('9ls1_fotoportal_save_portal_settings'); ?>
<input type="hidden" name="watermark_position" value="<?php echo esc_attr($ps['watermark_position']); ?>"><input type="hidden" name="watermark_size" value="<?php echo (int)$ps['watermark_size']; ?>"><input type="hidden" name="watermark_opacity" value="<?php echo (int)$ps['watermark_opacity']; ?>">
<div class="aurora-form-grid"><label>Studio / firmanavn<input name="studio_name" value="<?php echo esc_attr($ps['studio_name']); ?>"></label><label>Fotografens navn<input name="photographer_name" value="<?php echo esc_attr($ps['photographer_name']); ?>"></label><label>Konto- og innloggings-e-post<input type="email" value="<?php echo esc_attr($account->contact_email); ?>" readonly disabled><small>Administreres av Aurora Admin og brukes til innlogging og kontokommunikasjon.</small></label><label>Kunde-e-post <em>(valgfritt)</em><input type="email" name="portal_email" value="<?php echo esc_attr($ps['email']); ?>" placeholder="<?php echo esc_attr($account->contact_email); ?>"><small>Hvis tomt brukes konto-e-posten som kontaktadresse mot kunden.</small></label><label>Telefon<input name="portal_phone" value="<?php echo esc_attr($ps['phone']); ?>"></label><label>Nettside<input name="portal_website" value="<?php echo esc_attr($ps['website']); ?>"></label><label>Profilfarge<input type="color" name="accent_color" value="<?php echo esc_attr($ps['accent_color']); ?>"></label><label class="aurora-span-2">Adresse<textarea name="portal_address"><?php echo esc_textarea($ps['address']); ?></textarea></label><label class="aurora-span-2">Kort presentasjon<textarea name="portal_about"><?php echo esc_textarea($ps['about']); ?></textarea></label><label>Logo<input type="file" name="portal_logo" accept="image/*"></label><label>Profilbilde<input type="file" name="portal_profile_image" accept="image/*"></label><label class="aurora-span-2">Cover / hero-bilde<input type="file" name="portal_cover_image" accept="image/*"></label></div>
<hr><h3>E-postmal – Send URL til kunde</h3><label>E-postemne<input name="portal_email_subject" value="<?php echo esc_attr($ps['email_subject']); ?>"></label><label>E-posttekst<textarea name="portal_email_body" rows="8"><?php echo esc_textarea($ps['email_body']); ?></textarea></label><p><button class="aurora-primary-action" type="submit">Lagre profil</button></p></form></section>
<?php else: ?>
<section class="aurora-workspace-card aurora-profile-settings-summary aurora-settings-profile-card"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">FOTOGRAFKONTO</span><h2><?php echo esc_html($ps['studio_name']?:$account->account_name);?></h2><p><?php echo esc_html($ps['photographer_name']);?><?php if($ps['email']):?> · <?php echo esc_html($ps['email']);?><?php endif;?></p></div><a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('settings',['edit_profile'=>1]));?>"><span class="dashicons dashicons-edit"></span>Rediger profil, branding og e-post</a></div><div class="aurora-settings-profile-mini"><?php if($ps['logo_url']):?><img src="<?php echo esc_url($ps['logo_url']);?>" alt="Logo"><?php endif;?><?php if($ps['profile_image_url']):?><img src="<?php echo esc_url($ps['profile_image_url']);?>" alt="Profilbilde"><?php endif;?><span style="background:<?php echo esc_attr($ps['accent_color']);?>"></span><small>Logo · profilbilde · hero · kontaktinformasjon · profilfarge · e-postmaler</small></div></section>

<?php if (current_user_can('aurora_fotoportal_photographer') && !$support_mode) : ?>
<section class="aurora-workspace-card aurora-photographer-support-card">
    <div class="aurora-workspace-cardhead">
        <div>
            <span class="aurora-workspace-eyebrow">SUPPORT OG PERSONVERN</span>
            <h2>Supporttilgang til Aurora/9Ls1 Digital</h2>
            <p>Du bestemmer selv om Aurora-support kan åpne din Photographer Workspace ved behov.</p>
        </div>
        <span class="aurora-support-state <?php echo !empty($account->support_access_enabled)?'is-on':'is-off'; ?>"><?php echo !empty($account->support_access_enabled)?'Aktiv':'Av'; ?></span>
    </div>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-support-consent-form">
        <input type="hidden" name="action" value="aurora_toggle_support_access">
        <?php wp_nonce_field('aurora_toggle_support_access'); ?>
        <label class="aurora-support-switch">
            <input type="checkbox" name="support_access_enabled" value="1" <?php checked(!empty($account->support_access_enabled)); ?>>
            <span></span>
            <div><strong>Tillat Aurora Administrator å åpne min Photographer Workspace</strong><small>Support bruker aldri passordet ditt. Tilgangen kan slås av når som helst, og alle supportøkter logges.</small></div>
        </label>
        <div class="aurora-support-consent-footer">
            <?php if (!empty($account->support_access_granted_at)) : ?><small>Sist aktivert <?php echo esc_html(wp_date(get_option('date_format').' H:i', strtotime($account->support_access_granted_at))); ?></small><?php else : ?><small>Tilgangen er ikke aktivert.</small><?php endif; ?>
            <button class="aurora-primary-action" type="submit">Lagre supporttilgang</button>
        </div>
    </form>
</section>
<?php endif; ?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="aurora-watermark-page-form">
<input type="hidden" name="action" value="9ls1_fotoportal_save_portal_settings"><?php wp_nonce_field('9ls1_fotoportal_save_portal_settings'); ?>
<input type="hidden" name="studio_name" value="<?php echo esc_attr($ps['studio_name']); ?>"><input type="hidden" name="photographer_name" value="<?php echo esc_attr($ps['photographer_name']); ?>"><input type="hidden" name="portal_email" value="<?php echo esc_attr($ps['email']); ?>"><input type="hidden" name="portal_phone" value="<?php echo esc_attr($ps['phone']); ?>"><input type="hidden" name="portal_website" value="<?php echo esc_attr($ps['website']); ?>"><input type="hidden" name="portal_address" value="<?php echo esc_attr($ps['address']); ?>"><input type="hidden" name="portal_about" value="<?php echo esc_attr($ps['about']); ?>"><input type="hidden" name="accent_color" value="<?php echo esc_attr($ps['accent_color']); ?>"><input type="hidden" name="portal_email_subject" value="<?php echo esc_attr($ps['email_subject']); ?>"><input type="hidden" name="portal_email_body" value="<?php echo esc_attr($ps['email_body']); ?>">
<div class="aurora-watermark-layout">
    <section class="aurora-workspace-card aurora-watermark-editor-card">
        <span class="aurora-workspace-eyebrow">VANNMERKE</span><h2>Vannmerke</h2><p>Last opp ditt vannmerke og tilpass hvordan det vises på bildene.</p>
        <div class="aurora-watermark-upload-box">
            <strong>1. Last opp vannmerke</strong>
            <div class="aurora-watermark-logo-preview"><?php if(!empty($ps['watermark_url'])):?><img src="<?php echo esc_url($ps['watermark_url']); ?>" alt="Vannmerke"><?php else:?><span>DITT<br>VANNMERKE</span><?php endif;?></div>
            <label class="aurora-watermark-upload-button"><span class="dashicons dashicons-upload"></span>Bytt vannmerke<input type="file" name="portal_watermark" accept="image/png,image/webp,image/jpeg" hidden></label>
            <small>Anbefalt format: PNG eller WebP med transparent bakgrunn.</small>
        </div>
        <div class="aurora-watermark-control"><strong>2. Plassering</strong><div class="aurora-position-grid">
            <?php foreach(['top_left'=>'Topp venstre','top_center'=>'Topp senter','top_right'=>'Topp høyre','bottom_left'=>'Bunn venstre','bottom_center'=>'Bunn senter','bottom_right'=>'Bunn høyre','center'=>'Midt'] as $val=>$label):?><label class="aurora-position-option aurora-position-<?php echo esc_attr($val);?> <?php echo $ps['watermark_position']===$val?'is-active':'';?>"><input type="radio" name="watermark_position" value="<?php echo esc_attr($val);?>" <?php checked($ps['watermark_position'],$val);?> data-watermark-position-radio><span></span><b><?php echo esc_html($label);?></b></label><?php endforeach;?>
        </div></div>
        <div class="aurora-watermark-control"><div class="aurora-range-head"><strong>3. Størrelse</strong><output data-watermark-size-value><?php echo (int)$ps['watermark_size'];?> %</output></div><input type="range" min="5" max="70" name="watermark_size" value="<?php echo (int)$ps['watermark_size'];?>" data-watermark-size></div>
        <div class="aurora-watermark-control"><div class="aurora-range-head"><strong>4. Transparens</strong><output data-watermark-opacity-value><?php echo (int)$ps['watermark_opacity'];?> %</output></div><input type="range" min="5" max="95" name="watermark_opacity" value="<?php echo (int)$ps['watermark_opacity'];?>" data-watermark-opacity></div>
        <div class="aurora-watermark-note"><span class="dashicons dashicons-info-outline"></span>Vannmerket brukes kun på forhåndsvisningsbilder. Originalbilder leveres uten vannmerke.</div>
    </section>
    <section class="aurora-workspace-card aurora-watermark-customer-card">
        <span class="aurora-workspace-eyebrow">KUNDEOPPLEVELSE</span><h2>Kundeopplevelse</h2><p>Slik vil vannmerket vises for kundene i galleriene.</p>
        <div class="aurora-watermark-preview" data-watermark-preview data-position="<?php echo esc_attr($ps['watermark_position']);?>" style="--wm-size:<?php echo (int)$ps['watermark_size'];?>%;--wm-opacity:<?php echo ((int)$ps['watermark_opacity'])/100;?>"><img class="aurora-watermark-preview-bg" src="<?php echo esc_url($branding['watermark_preview_url']); ?>" alt="Aurora testbilde"><?php if(!empty($ps['watermark_url'])):?><img class="aurora-watermark-preview-mark" src="<?php echo esc_url($ps['watermark_url']);?>" alt="Vannmerke"><?php else:?><div class="aurora-watermark-preview-placeholder">DITT VANNMERKE</div><?php endif;?></div>
        <small class="aurora-preview-caption"><span class="dashicons dashicons-info-outline"></span>Dette er et eksempelbilde. Vannmerket plasseres på alle preview-bilder i galleriet.</small>
        <div class="aurora-watermark-tip"><strong>Tips</strong><p>Juster størrelse og transparens til vannmerket er synlig, men ikke forstyrrende.</p></div>
    </section>
</div>
<div class="aurora-watermark-savebar"><button class="aurora-primary-action" type="submit"><span class="dashicons dashicons-saved"></span>Lagre endringer</button></div>
</form>

<?php endif; ?>

            <?php elseif ($view === 'resources') : ?>
                <?php $resources=NLS1_Fotoportal_Admin::photographer_resources((int)$account->id); $resource_category=sanitize_key($_GET['category']??'all'); if($resource_category!=='all')$resources=array_values(array_filter($resources,function($r)use($resource_category){return ($r['category']??'annet')===$resource_category;})); ?>
                <?php if(($_GET['message']??'')==='resource_uploaded'):?><div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong>Ressurs lastet opp</strong><p>Filen er lagt til fotografkontoens ressurser.</p></div></div><?php endif;?>
                <div class="aurora-resource-filters"><a class="<?php echo $resource_category==='all'?'is-active':'';?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources'));?>">Alle</a><?php foreach(['kontraktmal'=>'Kontraktmaler','dokumentmal'=>'Dokumentmaler','epostmal'=>'E-postmaler','shotlist'=>'Fotoplan / Shotlists','annet'=>'Andre'] as $rk=>$rl):?><a class="<?php echo $resource_category===$rk?'is-active':'';?>" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('resources',['category'=>$rk]));?>"><?php echo esc_html($rl);?></a><?php endforeach;?></div>
                <div class="aurora-workspace-grid">
                    <section class="aurora-workspace-card aurora-workspace-card-wide"><div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">RESSURSER</span><h2>Maler og filer</h2><p>Last opp kontraktmaler, guider, shotlists og andre filer du bruker i arbeidet.</p></div></div><?php if($resources):?><div class="aurora-resource-list"><?php foreach(array_reverse($resources) as $r):?><a href="<?php echo esc_url($r['url']);?>" target="_blank" rel="noopener"><span class="dashicons dashicons-media-document"></span><div><strong><?php echo esc_html($r['title']);?></strong><small><?php echo esc_html(ucfirst($r['category']));?> · <?php echo esc_html($r['filename']);?></small></div><span class="dashicons dashicons-external"></span></a><?php endforeach;?></div><?php else:?><div class="aurora-empty-state"><strong>Ingen ressurser lastet opp ennå</strong><p>Bruk skjemaet til høyre for å legge til den første.</p></div><?php endif;?></section>
                    <section class="aurora-workspace-card"><span class="aurora-workspace-eyebrow">LAST OPP</span><h2>Ny ressurs</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" enctype="multipart/form-data" class="aurora-resource-upload-form"><input type="hidden" name="action" value="9ls1_fotoportal_upload_resource"><?php wp_nonce_field('9ls1_fotoportal_upload_resource');?><label>Tittel<input type="text" name="resource_title" placeholder="F.eks. Bryllup shotlist"></label><label>Kategori<select name="resource_category"><option value="kontraktmal">Kontraktmal</option><option value="dokumentmal">Dokumentmal</option><option value="epostmal">E-postmal</option><option value="shotlist">Fotoplan / Shotlist</option><option value="kundeguide">Kundeguide</option><option value="annet">Annet</option></select></label><label>Fil<input type="file" name="resource_file" required></label><button class="aurora-primary-action" type="submit">Last opp ressurs</button></form></section>
                </div>
            <?php else : ?>
                <section class="aurora-workspace-card aurora-module-placeholder">
                    <span class="aurora-workspace-eyebrow">WORKSPACE MODULE</span>
                    <h2><?php echo esc_html($title[0]); ?></h2>
                    <p>Den nye Aurora-visningen for denne modulen bygges i neste trinn. Eksisterende funksjonalitet er fortsatt bevart og kan åpnes midlertidig under utviklingen.</p>
                    <?php if (!empty($legacy_links[$view])) : ?>
                        <a class="aurora-secondary-action" href="<?php echo esc_url($legacy_links[$view]); ?>">Åpne eksisterende <?php echo esc_html(strtolower($title[0])); ?>-visning <span class="dashicons dashicons-arrow-right-alt2"></span></a>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    var preview=document.querySelector('[data-watermark-preview]');
    if(!preview)return;
    var size=document.querySelector('[data-watermark-size]'), opacity=document.querySelector('[data-watermark-opacity]');
    var sizeOut=document.querySelector('[data-watermark-size-value]'), opacityOut=document.querySelector('[data-watermark-opacity-value]');
    function sync(){ if(size){preview.style.setProperty('--wm-size',size.value+'%'); if(sizeOut)sizeOut.textContent=size.value+' %';} if(opacity){preview.style.setProperty('--wm-opacity',(opacity.value/100)); if(opacityOut)opacityOut.textContent=opacity.value+' %';} }
    if(size)size.addEventListener('input',sync); if(opacity)opacity.addEventListener('input',sync);
    document.querySelectorAll('[data-watermark-position-radio]').forEach(function(r){r.addEventListener('change',function(){preview.setAttribute('data-position',this.value);document.querySelectorAll('.aurora-position-option').forEach(function(x){x.classList.remove('is-active');});this.closest('.aurora-position-option').classList.add('is-active');});});
    var file=document.querySelector('input[name=portal_watermark]');
    if(file)file.addEventListener('change',function(){var f=this.files&&this.files[0];if(!f)return;var u=URL.createObjectURL(f), mark=preview.querySelector('.aurora-watermark-preview-mark'), ph=preview.querySelector('.aurora-watermark-preview-placeholder');if(!mark){mark=document.createElement('img');mark.className='aurora-watermark-preview-mark';preview.appendChild(mark);}mark.src=u;if(ph)ph.remove();});
    sync();
});
</script>

<script>
document.addEventListener('DOMContentLoaded',function(){
    function bindBilling(toggleSelector,fieldsSelector){
        var t=document.querySelector(toggleSelector),f=document.querySelector(fieldsSelector);
        if(!t||!f)return;
        function sync(){ if(t.checked){f.setAttribute('hidden','hidden');}else{f.removeAttribute('hidden');} }
        t.addEventListener('change',sync); sync();
    }
    bindBilling('[data-billing-toggle]','[data-billing-fields]');
    bindBilling('[data-billing-toggle-edit]','[data-billing-fields-edit]');
});
</script>

<script>
document.addEventListener('DOMContentLoaded',function(){
    var source=document.querySelector('[data-contract-source]');
    var digital=document.querySelector('[data-contract-digital]');
    var upload=document.querySelector('[data-contract-upload]');
    if(source&&digital&&upload){
        function syncContract(){
            var own=source.value==='upload';
            digital.hidden=own;
            upload.hidden=!own;
        }
        source.addEventListener('change',syncContract);
        syncContract();
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded',function(){
    var workspace=document.querySelector('.aurora-workspace');
    var sidebar=document.querySelector('.aurora-workspace-sidebar');
    var openButton=document.querySelector('[data-aurora-menu-open]');
    var closeButtons=document.querySelectorAll('[data-aurora-menu-close]');
    if(!workspace||!sidebar||!openButton)return;

    function openMenu(){
        workspace.classList.add('is-mobile-menu-open');
        document.documentElement.classList.add('aurora-menu-lock');
        openButton.setAttribute('aria-expanded','true');
    }
    function closeMenu(){
        workspace.classList.remove('is-mobile-menu-open');
        document.documentElement.classList.remove('aurora-menu-lock');
        openButton.setAttribute('aria-expanded','false');
    }

    openButton.addEventListener('click',openMenu);
    closeButtons.forEach(function(button){button.addEventListener('click',closeMenu);});
    sidebar.querySelectorAll('a').forEach(function(link){link.addEventListener('click',closeMenu);});
    document.addEventListener('keydown',function(event){if(event.key==='Escape')closeMenu();});
    window.addEventListener('resize',function(){if(window.innerWidth>782)closeMenu();});
});
</script>

<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('[data-copy-gallery-url]').forEach(function(button){
        button.addEventListener('click',function(){
            var url=button.getAttribute('data-copy-gallery-url')||'';
            if(navigator.clipboard&&url){navigator.clipboard.writeText(url).then(function(){var old=button.innerHTML;button.textContent='Kopiert';setTimeout(function(){button.innerHTML=old;},1400);});}
        });
    });
    var lightbox=document.querySelector('[data-gallery-lightbox]');
    if(lightbox){
        var image=lightbox.querySelector('img');
        var galleryItems=Array.prototype.slice.call(document.querySelectorAll('[data-gallery-image]'));
        var galleryIndex=0;
        function showGalleryImage(index){
            if(!galleryItems.length)return;
            galleryIndex=(index+galleryItems.length)%galleryItems.length;
            image.src=galleryItems[galleryIndex].getAttribute('data-gallery-image');
            lightbox.classList.add('is-open');
        }
        function closeLightbox(){lightbox.classList.remove('is-open');image.src='';}
        galleryItems.forEach(function(item,index){
            item.addEventListener('click',function(){showGalleryImage(index);});
        });
        var prev=lightbox.querySelector('.is-prev');
        var next=lightbox.querySelector('.is-next');
        if(prev)prev.addEventListener('click',function(event){event.stopPropagation();showGalleryImage(galleryIndex-1);});
        if(next)next.addEventListener('click',function(event){event.stopPropagation();showGalleryImage(galleryIndex+1);});
        lightbox.addEventListener('click',function(event){if(event.target===lightbox||event.target.classList.contains('aurora-gallery-lightbox-close'))closeLightbox();});
        document.addEventListener('keydown',function(event){
            if(!lightbox.classList.contains('is-open'))return;
            if(event.key==='Escape')closeLightbox();
            if(event.key==='ArrowLeft')showGalleryImage(galleryIndex-1);
            if(event.key==='ArrowRight')showGalleryImage(galleryIndex+1);
        });
    }
});
</script>
<script>
(function heroDesignerLivePreview(){document.querySelectorAll('.aurora-hero-controls').forEach(function(form){var preview=form.previousElementSibling;if(!preview||!preview.classList.contains('aurora-hero-editor-preview'))return;var overlay=preview.querySelector('.aurora-hero-editor-overlay'),dot=preview.querySelector('.aurora-focal-dot');function update(){var size=form.querySelector('[name=hero_size]'),x=form.querySelector('[name=focal_x]'),y=form.querySelector('[name=focal_y]'),op=form.querySelector('[name=overlay_opacity]'),col=form.querySelector('[name=overlay_color]'),img=form.querySelector('[name=hero_image_id]:checked');preview.classList.remove('size-small','size-medium','size-large');preview.classList.add('size-'+size.value);preview.style.backgroundPosition=x.value+'% '+y.value+'%';if(overlay){overlay.style.background=col.value;overlay.style.opacity=(parseInt(op.value||0,10)/100);}if(dot){dot.style.left=x.value+'%';dot.style.top=y.value+'%';}if(img&&img.parentElement.querySelector('img'))preview.style.backgroundImage='url("'+img.parentElement.querySelector('img').src+'")';var b=op.parentElement.querySelector('b');if(b)b.textContent=op.value+'%';}form.querySelectorAll('input,select').forEach(function(el){el.addEventListener('input',update);el.addEventListener('change',update);});});})();
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){var t=document.querySelector('[data-aurora-notification-toggle]'),d=document.querySelector('[data-aurora-notification-dropdown]');if(!t||!d)return;function close(){d.hidden=true;t.setAttribute('aria-expanded','false')}t.addEventListener('click',function(e){e.stopPropagation();d.hidden=!d.hidden;t.setAttribute('aria-expanded',d.hidden?'false':'true')});d.addEventListener('click',function(e){e.stopPropagation()});document.addEventListener('click',close);document.addEventListener('keydown',function(e){if(e.key==='Escape')close()})});
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var trigger=document.querySelector('[data-aurora-profile-toggle]');
    var dropdown=document.querySelector('[data-aurora-profile-dropdown]');
    if(!trigger||!dropdown)return;
    function close(){dropdown.hidden=true;trigger.setAttribute('aria-expanded','false');}
    trigger.addEventListener('click',function(e){e.stopPropagation();dropdown.hidden=!dropdown.hidden;trigger.setAttribute('aria-expanded',dropdown.hidden?'false':'true');});
    dropdown.addEventListener('click',function(e){e.stopPropagation();});
    document.addEventListener('click',close);
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
});
</script>

<script>
document.addEventListener('DOMContentLoaded',function(){
 var root=document.querySelector('[data-selection-grid]'); if(!root)return;
 var cards=[].slice.call(root.querySelectorAll('.aurora-selection-card')), kind='all';
 var c=document.querySelector('[data-selection-client]'),p=document.querySelector('[data-selection-project]'),g=document.querySelector('[data-selection-gallery]'),st=document.querySelector('[data-selection-status]'),empty=document.querySelector('[data-selection-empty]');
 function apply(){var shown=0;cards.forEach(function(x){var ok=(kind==='all'||x.dataset[kind]==='1')&&(!c.value||x.dataset.client===c.value)&&(!p.value||x.dataset.project===p.value)&&(!g.value||x.dataset.gallery===g.value)&&(!st||!st.value||x.dataset.status===st.value);x.hidden=!ok;if(ok)shown++;});if(empty)empty.hidden=shown!==0;}
 document.querySelectorAll('[data-selection-kind]').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('[data-selection-kind]').forEach(function(z){z.classList.remove('is-active')});b.classList.add('is-active');kind=b.dataset.selectionKind;apply();});});
 [c,p,g,st].forEach(function(x){if(x)x.addEventListener('change',apply)});
});
</script>
