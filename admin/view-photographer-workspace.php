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
    'projects' => NLS1_Fotoportal_Admin::fotoportal_url('projects'),
    'contracts' => NLS1_Fotoportal_Admin::fotoportal_url('contracts'),
    'documents' => NLS1_Fotoportal_Admin::fotoportal_url('documents'),
    'galleries' => NLS1_Fotoportal_Admin::fotoportal_url('galleries'),
    'hq_delivery' => NLS1_Fotoportal_Admin::fotoportal_url('galleries'),
];
?>
<div class="aurora-workspace">
    <aside class="aurora-workspace-sidebar">
        <div class="aurora-workspace-brand">
            <?php if (!empty($branding['logo_url'])) : ?>
                <span class="aurora-workspace-logo is-image"><img src="<?php echo esc_url($branding['logo_url']); ?>" alt="Aurora"></span>
            <?php else : ?>
                <span class="aurora-workspace-logo">A</span>
            <?php endif; ?>
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
            <div class="aurora-workspace-mobilebrand"><?php if (!empty($branding['logo_url'])) : ?><span class="aurora-workspace-logo is-image"><img src="<?php echo esc_url($branding['logo_url']); ?>" alt="Aurora"></span><?php else : ?><span class="aurora-workspace-logo">A</span><?php endif; ?><strong>Aurora Fotoportal</strong></div>
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
                <?php elseif ($view === 'customers') : ?>
                    <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Ny kunde</a>
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
                                <a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span>Nytt prosjekt</a>
                            </div>
                        </div>
                    </div>

                    <div class="aurora-workspace-grid">
                        <section class="aurora-workspace-card">
                            <span class="aurora-workspace-eyebrow">KONTAKT</span><h2>Kontaktinformasjon</h2>
                            <dl class="aurora-customer-details">
                                <div><dt>Hovedkontakt</dt><dd><?php echo esc_html($contact ? NLS1_Fotoportal_Admin::format_contact_name($contact) : '—'); ?></dd></div>
                                <div><dt>E-post</dt><dd><?php echo esc_html($customer->email ?: '—'); ?></dd></div>
                                <div><dt>Telefon</dt><dd><?php echo esc_html($customer->phone ?: '—'); ?></dd></div>
                                <div><dt>Sted/by</dt><dd><?php echo esc_html($customer->city ?: '—'); ?></dd></div>
                            </dl>
                        </section>
                        <section class="aurora-workspace-card aurora-workspace-card-wide">
                            <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">PROSJEKTER</span><h2>Kundens prosjekter</h2></div><strong class="aurora-count-badge"><?php echo count($customer_projects); ?></strong></div>
                            <?php if ($customer_projects) : ?>
                                <div class="aurora-project-mini-list">
                                    <?php foreach ($customer_projects as $project) : ?>
                                        <div>
                                            <span class="dashicons dashicons-portfolio"></span>
                                            <div><strong><?php echo esc_html($project->project_name); ?></strong><small><?php echo esc_html($project->project_number); ?><?php if ($project->project_date) echo ' · ' . esc_html(date_i18n('d.m.Y', strtotime($project->project_date))); ?></small></div>
                                            <span class="aurora-status-pill"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span>
                                            <a class="aurora-icon-link" title="Åpne prosjekt" href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($project->id)); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="aurora-empty-state"><span class="dashicons dashicons-portfolio"></span><strong>Ingen prosjekter ennå</strong><p>Opprett første fotooppdrag for denne kunden.</p></div>
                            <?php endif; ?>
                        </section>
                    </div>

                <?php else : ?>
                    <?php
                    $search = sanitize_text_field($_GET['s'] ?? '');
                    $group = sanitize_text_field($_GET['group'] ?? '');
                    $type = sanitize_key($_GET['ctype'] ?? '');
                    $customers = NLS1_Fotoportal_Admin::get_clients(true, $search, $group, $type);
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
                                    <thead><tr><th>Kunde</th><th>Kontakt</th><th>Type</th><th>Sted</th><th>Status</th><th></th></tr></thead>
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

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form aurora-step-form" id="aurora-new-project-form">
                        <input type="hidden" name="action" value="9ls1_fotoportal_save_client_project">
                        <input type="hidden" name="aurora_workspace" value="1">
                        <?php wp_nonce_field('9ls1_fotoportal_save_client_project'); ?>

                        <section class="aurora-form-step is-active" data-step="1">
                            <div class="aurora-form-step-head"><div><span class="aurora-step-label">STEG 1 AV 3</span><h2>Kunde</h2><p>Hvem er kunden? Bruk personnavn, familienavn eller firmanavn.</p></div></div>
                            <div class="nls1-form-grid">
                                <label>Kundenavn *<input type="text" name="client_name" required placeholder="f.eks. Ola Hansen eller Hansen-familien"></label>
                                <label>Kundegruppe *<select name="client_group" required><option value="">Velg gruppe</option><option>Privatkunde</option><option>Bedrift</option><option>Artist/Band</option><option>Organisasjon</option><option>Annet</option></select></label>
                                <label>Kundetype<select name="client_type"><option value="private">Privat</option><option value="business">Bedrift</option><option value="artist">Artist/Band</option><option value="organization">Organisasjon</option></select></label>
                                <div class="aurora-form-hint"><strong>Kundenavn</strong><span>Eksempel: «Ola Hansen». Prosjektnavn som «Bryllup Hansen 2027» legges inn i neste steg.</span></div>
                                <label>Hovedkontakt fornavn *<input type="text" name="first_name" required placeholder="f.eks. Ola"></label>
                                <label>Hovedkontakt etternavn<input type="text" name="last_name" placeholder="f.eks. Hansen"></label>
                                <label>E-post *<input type="email" name="email" required placeholder="ola@eksempel.no"></label>
                                <label>Telefon<input type="text" name="phone" placeholder="+47 ..."></label>
                                <label>Sted/by<input type="text" name="city" placeholder="f.eks. Vestby"></label>
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
                                <label class="nls1-checkbox nls1-full"><input type="checkbox" name="is_test" value="1"> Merk som testdata</label>
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
