<?php
if (!defined('ABSPATH')) exit;

$branding = NLS1_Aurora_Account_Platform::platform_branding();
$user = wp_get_current_user();
$initials = '';
foreach (preg_split('/\s+/', trim($user->display_name)) as $part) {
    if ($part !== '') $initials .= strtoupper(substr($part, 0, 1));
}
$initials = substr($initials ?: 'PF', 0, 2);

$view_titles = [
    'dashboard' => ['Dashboard', 'Oversikt over Fotoportal og det som trenger oppfølging.'],
    'new' => ['Ny kunde / prosjekt', 'Start en ny kunde- og prosjektflyt.'],
    'customers' => ['Kunder', 'Administrer dine fotokunder.'],
    'projects' => ['Prosjekter', 'Fotooppdrag, status og prosjektflyt.'],
    'contracts' => ['Kontrakter', 'Avtaler, utsending og signeringsstatus.'],
    'documents' => ['Dokumenter', 'Dokumenter og underlag knyttet til prosjektene.'],
    'galleries' => ['Gallerier', 'Bildegallerier, proof og kundeleveranser.'],
    'hq_delivery' => ['Leveranser', 'Ferdige leveranser og nedlastinger.'],
    'resources' => ['Ressurser', 'Maler, hjelp og arbeidsressurser.'],
    'shop' => ['Nettbutikk', 'Produkter og ordre.'],
    'settings' => ['Innstillinger', 'Din fotografkonto og Fotoportal-oppsett.'],
];
$title = $view_titles[$view] ?? $view_titles['dashboard'];

$legacy_links = [
    'new' => NLS1_Fotoportal_Admin::fotoportal_url('new', ['aurora_legacy'=>'1']),
    'customers' => NLS1_Fotoportal_Admin::fotoportal_url('clients', ['aurora_legacy'=>'1']),
    'projects' => NLS1_Fotoportal_Admin::fotoportal_url('projects', ['aurora_legacy'=>'1']),
    'contracts' => NLS1_Fotoportal_Admin::fotoportal_url('contracts', ['aurora_legacy'=>'1']),
    'documents' => NLS1_Fotoportal_Admin::fotoportal_url('documents', ['aurora_legacy'=>'1']),
    'galleries' => NLS1_Fotoportal_Admin::fotoportal_url('galleries', ['aurora_legacy'=>'1']),
    'hq_delivery' => NLS1_Fotoportal_Admin::fotoportal_url('galleries', ['aurora_legacy'=>'1']),
];
?>
<div class="aurora-workspace">
    <aside class="aurora-workspace-sidebar">
        <div class="aurora-workspace-brand">
            <span class="aurora-workspace-logo">A</span>
            <div><strong>Aurora</strong><small>FOTOPORTAL</small></div>
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

    <main class="aurora-workspace-main">
        <header class="aurora-workspace-topbar">
            <div class="aurora-workspace-mobilebrand"><span class="aurora-workspace-logo">A</span><strong>Aurora Fotoportal</strong></div>
            <div class="aurora-workspace-topactions">
                <button type="button" class="aurora-icon-button" title="Hjelp"><span class="dashicons dashicons-editor-help"></span></button>
                <button type="button" class="aurora-icon-button" title="Varsler"><span class="dashicons dashicons-bell"></span><i></i></button>
                <div class="aurora-workspace-user"><span><?php echo esc_html($initials); ?></span><div><strong><?php echo esc_html($user->display_name); ?></strong><small><?php echo esc_html($account->account_name); ?></small></div></div>
            </div>
        </header>

        <div class="aurora-workspace-content">
            <div class="aurora-workspace-titlebar">
                <div><span class="aurora-workspace-eyebrow">AURORA FOTOPORTAL</span><h1><?php echo esc_html($title[0]); ?></h1><p><?php echo esc_html($title[1]); ?></p></div>
                <?php if ($view === 'dashboard') : ?>
                    <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Nytt prosjekt</a>
                <?php endif; ?>
            </div>

            <?php if ($view === 'dashboard') : ?>
                <section class="aurora-workspace-kpis">
                    <div><span class="dashicons dashicons-portfolio"></span><div><small>Aktive prosjekter</small><strong>—</strong><em>Kobles til tenant-data</em></div></div>
                    <div><span class="dashicons dashicons-media-document"></span><div><small>Venter på signering</small><strong>—</strong><em>Kontrakter</em></div></div>
                    <div><span class="dashicons dashicons-format-gallery"></span><div><small>Aktive gallerier</small><strong>—</strong><em>Galleri</em></div></div>
                    <div><span class="dashicons dashicons-download"></span><div><small>Til levering</small><strong>—</strong><em>Leveranser</em></div></div>
                </section>

                <div class="aurora-workspace-grid">
                    <section class="aurora-workspace-card aurora-workspace-card-wide">
                        <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">ARBEIDSFLYT</span><h2>Kom raskt i gang</h2></div></div>
                        <div class="aurora-workspace-shortcuts">
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span><strong>Ny kunde / prosjekt</strong><small>Start ny fotografering</small></a>
                            <?php if (!empty($enabled['contracts'])) : ?><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts')); ?>"><span class="dashicons dashicons-media-document"></span><strong>Kontrakter</strong><small>Se avtaler og status</small></a><?php endif; ?>
                            <?php if (!empty($enabled['galleries'])) : ?><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries')); ?>"><span class="dashicons dashicons-format-gallery"></span><strong>Gallerier</strong><small>Administrer bilder</small></a><?php endif; ?>
                        </div>
                    </section>

                    <section class="aurora-workspace-card">
                        <span class="aurora-workspace-eyebrow">KONTO</span><h2><?php echo esc_html($account->account_name); ?></h2>
                        <div class="aurora-workspace-accountstatus"><span class="is-active">Aktiv</span><strong><?php echo esc_html($account->plan_name); ?></strong></div>
                        <p>Workspace-menyen styres allerede av modulene som er aktivert på fotografkontoen i Aurora Admin.</p>
                    </section>
                </div>

                <section class="aurora-workspace-card">
                    <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">NESTE FASE</span><h2>Photographer Workspace er opprettet</h2></div></div>
                    <div class="aurora-workspace-roadmap">
                        <span class="is-done">✓ Workspace shell</span><span class="is-done">✓ Fotografkonto</span><span class="is-done">✓ Modulstyrt meny</span><span>→ Tenant-migrering</span><span>→ Ekte dashboard-data</span><span>→ Nye modulvisninger</span>
                    </div>
                </section>

            <?php elseif ($view === 'settings') : ?>
                <div class="aurora-workspace-grid">
                    <section class="aurora-workspace-card aurora-workspace-card-wide">
                        <span class="aurora-workspace-eyebrow">FOTOGRAFKONTO</span><h2><?php echo esc_html($account->account_name); ?></h2>
                        <div class="aurora-workspace-settingsgrid">
                            <div><small>Kontostatus</small><strong><?php echo esc_html(ucfirst($account->status)); ?></strong></div>
                            <div><small>Plan</small><strong><?php echo esc_html($account->plan_name); ?></strong></div>
                            <div><small>Kontakt</small><strong><?php echo esc_html($account->contact_name ?: 'Ikke satt'); ?></strong></div>
                            <div><small>E-post</small><strong><?php echo esc_html($account->contact_email ?: 'Ikke satt'); ?></strong></div>
                        </div>
                    </section>
                    <section class="aurora-workspace-card">
                        <span class="aurora-workspace-eyebrow">MODULER</span><h2>Din tilgang</h2>
                        <div class="aurora-workspace-modulelist">
                            <?php foreach (NLS1_Aurora_Account_Platform::module_catalog() as $key=>$meta) : ?>
                                <div class="<?php echo !empty($enabled[$key])?'is-enabled':'is-disabled'; ?>"><span></span><strong><?php echo esc_html($meta[0]); ?></strong><small><?php echo !empty($enabled[$key])?'Aktiv':'Ikke i lisensen'; ?></small></div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

            <?php elseif ($view === 'resources') : ?>
                <section class="aurora-workspace-card">
                    <span class="aurora-workspace-eyebrow">RESSURSER</span><h2>Ressurssenter</h2>
                    <p>Her samler vi senere maler, guider, posekort, dokumentmaler og annet fotografinnhold. Selve Workspace-siden er klar for modulen.</p>
                </section>

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
