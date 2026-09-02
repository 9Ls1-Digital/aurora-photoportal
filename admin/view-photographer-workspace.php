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
                <?php elseif ($view === 'projects') : ?>
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
                                <label>Kundetype<select name="client_type"><option value="private" <?php selected($customer->client_type,'private'); ?>>Privat</option><option value="business" <?php selected($customer->client_type,'business'); ?>>Bedrift</option><option value="artist" <?php selected($customer->client_type,'artist'); ?>>Artist/Band</option><option value="organization" <?php selected($customer->client_type,'organization'); ?>>Organisasjon</option></select></label>
                                <label>Hovedkontakt fornavn<input type="text" name="first_name" value="<?php echo esc_attr($contact->first_name ?? ''); ?>"></label>
                                <label>Hovedkontakt etternavn<input type="text" name="last_name" value="<?php echo esc_attr($contact->last_name ?? ''); ?>"></label>
                                <label>E-post<input type="email" name="email" value="<?php echo esc_attr($customer->email); ?>"></label>
                                <label>Telefon<input type="text" name="phone" value="<?php echo esc_attr($customer->phone); ?>"></label>
                                <label>Adresse<input type="text" name="address" value="<?php echo esc_attr($customer->address); ?>"></label>
                                <label>Postnummer<input type="text" name="postal_code" value="<?php echo esc_attr($customer->postal_code); ?>"></label>
                                <label>Sted/by<input type="text" name="city" value="<?php echo esc_attr($customer->city); ?>"></label>
                                <label>Organisasjonsnummer<input type="text" name="organization_number" value="<?php echo esc_attr($customer->organization_number); ?>"></label>
                                <div class="nls1-full aurora-billing-box">
                                    <strong>Fakturainformasjon</strong><p>Bruk kundeadressen eller oppgi egen fakturaadresse.</p>
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
                    <section class="aurora-workspace-card aurora-workspace-card-wide">
                        <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">ARBEIDSFLYT</span><h2>Kom raskt i gang</h2></div></div>
                        <div class="aurora-workspace-shortcuts">
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('new')); ?>"><span class="dashicons dashicons-plus-alt2"></span><strong>Ny kunde / prosjekt</strong><small>Start ny fotografering</small></a>
                            <?php if (!empty($enabled['contracts'])) : ?><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts')); ?>"><span class="dashicons dashicons-media-document"></span><strong>Kontrakter</strong><small>Se avtaler og status</small></a><?php endif; ?>
                            <?php if (!empty($enabled['galleries'])) : ?><a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span class="dashicons dashicons-format-gallery"></span><strong>Gallerier</strong><small>Administrer bilder</small></a><?php endif; ?>
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
                                <a class="aurora-secondary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('customers',['customer_id'=>$customer_id,'edit'=>1])); ?>"><span class="dashicons dashicons-edit"></span>Rediger</a>
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
                                <div><dt>Telefon</dt><dd><?php echo esc_html($customer->phone ?: '—'); ?></dd></div>
                                <div><dt>Sted/by</dt><dd><?php echo esc_html($customer->city ?: '—'); ?></dd></div><div><dt>Registrert</dt><dd><?php echo !empty($customer->created_at) ? esc_html(date_i18n('d.m.Y', strtotime($customer->created_at))) : '—'; ?></dd></div>
                            </dl>
                        </section>
                        <section class="aurora-workspace-card"><span class="aurora-workspace-eyebrow">ADRESSE & FAKTURA</span><h2>Kundeinformasjon</h2><dl class="aurora-customer-details">
<div><dt>Adresse</dt><dd><?php echo esc_html($customer->address ?: '—'); ?></dd></div><div><dt>Postnr. / sted</dt><dd><?php echo esc_html(trim(($customer->postal_code ?: '').' '.($customer->city ?: '')) ?: '—'); ?></dd></div><div><dt>Org.nr.</dt><dd><?php echo esc_html($customer->organization_number ?: '—'); ?></dd></div><div><dt>Fakturaadresse</dt><dd><?php echo !empty($customer->billing_same_as_customer)?'Samme som kundeadresse':esc_html(trim(($customer->billing_address ?: '').', '.($customer->billing_postal_code ?: '').' '.($customer->billing_city ?: ''),', ')); ?></dd></div></dl></section> <section class="aurora-workspace-card aurora-workspace-card-wide">
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
                    </div>

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
                        <?php if (!empty($project->description)) : ?><div class="aurora-status-legend">
                            <span><i class="is-green"></i>Grønn: leveringsklar/levert</span>
                            <span><i class="is-blue"></i>Blå/lilla: aktiv produksjon</span>
                            <span><i class="is-yellow"></i>Gul: venter på neste steg</span>
                            <span><i class="is-gray"></i>Grå: opprettet/arkivert</span>
                        </div>
                        <div class="aurora-project-notes"><span>NOTATER</span><p><?php echo nl2br(esc_html($project->description)); ?></p></div><?php endif; ?>
                    </section>

                    <section class="aurora-workspace-card aurora-project-workflow">
                        <div class="aurora-workspace-cardhead"><div><span class="aurora-workspace-eyebrow">PROSJEKTFLYT</span><h2>Fra prosjekt til leveranse</h2></div></div>
                        <div class="aurora-project-steps">
                            <div class="is-current"><span>1</span><strong>Prosjekt</strong><small>Detaljer og status</small></div>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('contracts',['project_id'=>$project_id])); ?>"><span>2</span><strong>Kontrakt</strong><small><?php echo count($contracts); ?> registrert</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('documents',['project_id'=>$project_id])); ?>"><span>3</span><strong>Dokumenter</strong><small><?php echo count($documents); ?> filer</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('galleries',['project_id'=>$project_id])); ?>"><span>4</span><strong>Galleri</strong><small><?php echo count($galleries); ?> gallerier</small></a>
                            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('hq_delivery',['project_id'=>$project_id])); ?>"><span>5</span><strong>Leveranse</strong><small>Sluttlevering</small></a>
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
                                <label>Type avtale<select name="contract_source" data-contract-source>
                                    <option value="aurora">Aurora digital signering</option>
                                    <option value="upload">Last opp egen kontrakt</option>
                                </select></label>
                                <label>Tittel<input type="text" name="contract_name" value="Kontrakt" required></label>
                                <div data-contract-digital>
                                    <label>Signerer<input type="text" name="signer_name" placeholder="Navn"></label>
                                    <label>E-post til signerer<input type="email" name="signer_email" placeholder="kunde@epost.no"></label>
                                    <label>Kontraktstekst<textarea name="contract_text" rows="8" placeholder="Skriv eller lim inn kontraktsteksten som kunden skal signere..."></textarea></label>
                                    <p class="aurora-form-help">Aurora oppretter en signeringslenke som kan sendes til kunden.</p>
                                </div>
                                <div data-contract-upload hidden>
                                    <label>Kontraktfil<input type="file" name="contract_file" accept=".pdf,.doc,.docx,.odt,.txt"></label>
                                    <label>Notat<textarea name="notes" rows="5" placeholder="Valgfritt notat om den opplastede kontrakten."></textarea></label>
                                </div>
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
                        <div class="aurora-empty-state"><span class="dashicons dashicons-media-document"></span><strong>Velg et prosjekt først</strong><p>Kontrakter administreres nå fra prosjektets arbeidsflyt.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects')); ?>">Gå til Prosjekter</a></div>
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
                    <section class="aurora-workspace-card"><div class="aurora-empty-state"><span class="dashicons dashicons-media-document"></span><strong>Velg et prosjekt først</strong><p>Dokumenter administreres fra prosjektets arbeidsflyt.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects')); ?>">Gå til Prosjekter</a></div></section>
                <?php endif; ?>


            <?php elseif ($view === 'galleries') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
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
                ];
                if (isset($message_map[$gallery_message])) :
                ?>
                    <div class="aurora-workspace-alert is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong><?php echo esc_html($message_map[$gallery_message][0]); ?></strong><p><?php echo esc_html($message_map[$gallery_message][1]); ?></p></div></div>
                <?php elseif ($gallery_message === 'gallery_contract_required') : ?>
                    <div class="aurora-workspace-alert"><span class="dashicons dashicons-lock"></span><div><strong>Signert kontrakt kreves</strong><p>Galleri kan først opprettes når prosjektet har minst én signert kontrakt.</p></div></div>
                <?php endif; ?>

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
                                        <?php if ($thumb) : ?><a class="aurora-icon-link" href="<?php echo esc_url($thumbs[0]->preview_url ?: $thumb); ?>" target="_blank" rel="noopener" title="Vis preview"><span class="dashicons dashicons-visibility"></span></a><?php endif; ?>
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
                    <?php if ($gallery_unlocked && !empty($_GET['new_gallery'])) : ?>
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


            <?php elseif ($view === 'hq_delivery') : ?>
                <?php
                $project_id = absint($_GET['project_id'] ?? 0);
                $project = $project_id ? NLS1_Fotoportal_Admin::get_project($project_id) : null;
                $project_galleries = $project ? NLS1_Fotoportal_Admin::get_galleries($project_id, true) : [];
                $signed = $project ? NLS1_Fotoportal_Admin::has_signed_contract($project_id) : false;
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
                        <div><span class="aurora-workspace-eyebrow">PROSJEKTSTATUS</span><h2>Marker fremdrift</h2><p>Leveranse bruker den eksisterende prosjektstatusen. Kundeportal og automatisk sluttlevering kobles på i et senere modulsteg.</p></div>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-delivery-status-form">
                            <input type="hidden" name="action" value="9ls1_fotoportal_update_project_status"><input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>"><input type="hidden" name="aurora_workspace" value="1"><?php wp_nonce_field('9ls1_fotoportal_update_project_status'); ?>
                            <select name="status">
                                <?php foreach (NLS1_Fotoportal_Admin::$project_statuses as $status_key=>$status_name) : ?>
                                    <option value="<?php echo esc_attr($status_key); ?>" <?php selected($project->status,$status_key); ?>><?php echo esc_html($status_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="aurora-primary-action" type="submit">Oppdater status</button>
                        </form>
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
                    <section class="aurora-workspace-card"><div class="aurora-empty-state"><span class="dashicons dashicons-download"></span><strong>Velg et prosjekt først</strong><p>Leveranse administreres fra prosjektets arbeidsflyt.</p><a class="aurora-primary-action" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('projects')); ?>">Gå til Prosjekter</a></div></section>
                <?php endif; ?>

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
