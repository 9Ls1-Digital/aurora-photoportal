<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap nls1-fotoportal-admin">
    <div class="aurora-external-notices" aria-live="polite"></div>
    <header class="aurora-module-header">
        <div>
            <div class="nls1-aurora-eyebrow">Aurora / Fotoportal</div>
            <h1>Aurora Fotoportal</h1>
            <p class="description">Kunder, prosjekter, kontrakter, gallerier og leveranser samlet i én arbeidsflate.</p>
        </div>
        <div class="aurora-header-actions">
            <a class="button" href="<?php echo esc_url(NLS1_Fotoportal_Admin::dashboard_url()); ?>">Aurora-oversikt</a>
            <a class="button button-primary" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('wizard')); ?>">+ Ny kunde / prosjekt</a>
        </div>
    </header>

    <?php
    $nav_groups = [
        'Arbeid' => ['dashboard'=>'Dashboard','wizard'=>'Ny kunde/prosjekt','clients'=>'Kunder','projects'=>'Prosjekter'],
        'Produksjon' => ['contracts'=>'Kontrakter','documents'=>'Dokumenter','galleries'=>'Gallerier','deliveries'=>'Leveranser'],
        'System' => ['resources'=>'Ressurser','shop'=>'Nettbutikk','settings'=>'Innstillinger'],
    ];
    ?>
    <div class="aurora-module-nav" aria-label="Fotoportal navigasjon">
        <?php foreach ($nav_groups as $group_label => $items): ?>
            <div class="aurora-nav-group">
                <span class="aurora-nav-group-label"><?php echo esc_html($group_label); ?></span>
                <div class="aurora-nav-items">
                    <?php foreach ($items as $key => $label): ?>
                        <a class="aurora-nav-link<?php echo $tab === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url($key)); ?>"><?php echo esc_html($label); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'client_updated') : ?><div class="notice notice-success"><p>Kundeinformasjon er oppdatert.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'project_updated') : ?><div class="notice notice-success"><p>Prosjektinformasjon er oppdatert.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'test_item_deleted') : ?><div class="notice notice-success"><p>Testelement er slettet.</p></div><?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'contract_created') : ?><div class="notice notice-success"><p>Kontrakt er opprettet.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'contract_sent') : ?><div class="notice notice-success"><p>Kontrakt er markert som sendt.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'contract_missing') : ?><div class="notice notice-error"><p>Fyll ut kontraktsnavn, tekst og e-post.</p></div><?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'document_added') : ?><div class="notice notice-success"><p>Dokument er lagt til.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'document_deleted') : ?><div class="notice notice-success"><p>Dokument er slettet.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'document_missing') : ?><div class="notice notice-error"><p>Dokumentnavn og fil må fylles ut.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'template_saved') : ?><div class="notice notice-success"><p>Kontraktmal er lagret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'template_deleted') : ?><div class="notice notice-success"><p>Kontraktmal er slettet.</p></div><?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_uploaded') : ?><div class="notice notice-success"><p>Galleri er lastet opp og pakket ut.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_deleted') : ?><div class="notice notice-success"><p>Galleri er slettet.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_zip_missing') : ?><div class="notice notice-error"><p>Velg en ZIP-fil.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_not_zip') : ?><div class="notice notice-error"><p>Filen må være en ZIP-fil.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_upload_failed') : ?><div class="notice notice-error"><p>Opplasting feilet.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_dir_failed') : ?><div class="notice notice-error"><p>Kunne ikke opprette mappestruktur.</p></div><?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'branding_saved') : ?><div class="notice notice-success"><p>Branding og vannmerkeinnstillinger er lagret.</p></div><?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'gallery_regenerated') : ?><div class="notice notice-success"><p>Preview og thumbnails er regenerert.</p></div><?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'proof_pdf_generated') : ?><div class="notice notice-success"><p>PDF Preview Sheet er generert.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'proof_pdf_failed') : ?><div class="notice notice-error"><p>PDF kunne ikke genereres.</p></div><?php endif; ?>

    <?php if ($tab === 'dashboard') : ?>
        <section class="aurora-section-heading">
            <div><span class="aurora-kicker">OVERSIKT</span><h2>Arbeidsstatus</h2><p>Et raskt bilde av Fotoportal akkurat nå.</p></div>
        </section>
        <div class="nls1-card-grid aurora-stat-grid">
            <a class="nls1-card aurora-stat-card" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('clients')); ?>"><span class="aurora-stat-label">Kunder</span><p><?php echo esc_html(NLS1_Fotoportal_Admin::count_rows('clients', 'is_test = 0')); ?></p><small>Åpne kunderegister →</small></a>
            <a class="nls1-card aurora-stat-card" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('projects')); ?>"><span class="aurora-stat-label">Prosjekter</span><p><?php echo esc_html(NLS1_Fotoportal_Admin::count_rows('projects', 'is_test = 0')); ?></p><small>Se alle prosjekter →</small></a>
            <a class="nls1-card aurora-stat-card" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('galleries')); ?>"><span class="aurora-stat-label">Gallerier</span><p><?php echo esc_html(NLS1_Fotoportal_Admin::count_rows('galleries')); ?></p><small>Administrer gallerier →</small></a>
            <a class="nls1-card aurora-stat-card" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('contracts')); ?>"><span class="aurora-stat-label">Kontrakter</span><p><?php echo esc_html(NLS1_Fotoportal_Admin::count_rows('contracts')); ?></p><small>Åpne kontrakter →</small></a>
        </div>


        <section class="aurora-workflow">
            <div class="aurora-section-heading aurora-workflow-heading">
                <div><span class="aurora-kicker">ARBEIDSFLYT</span><h2>Fra kunde til leveranse</h2><p>Fire steg gjennom et fotooppdrag – bygget på funksjonene som allerede finnes i Fotoportal.</p></div>
                <span class="aurora-workflow-badge">Aurora Workflow</span>
            </div>
            <div class="aurora-workflow-grid">
                <a class="aurora-workflow-step" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('clients')); ?>">
                    <span class="aurora-workflow-number">01</span><span class="aurora-workflow-content"><strong>Kunde</strong><span>Opprett eller åpne kunden som fotograferingen tilhører.</span></span><span class="aurora-workflow-arrow">→</span>
                </a>
                <a class="aurora-workflow-step" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('projects')); ?>">
                    <span class="aurora-workflow-number">02</span><span class="aurora-workflow-content"><strong>Prosjekt</strong><span>Planlegg oppdraget, prosjektdata og avtaler.</span></span><span class="aurora-workflow-arrow">→</span>
                </a>
                <a class="aurora-workflow-step" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('galleries')); ?>">
                    <span class="aurora-workflow-number">03</span><span class="aurora-workflow-content"><strong>Galleri</strong><span>Last opp bilder og bygg preview/proof for kunden.</span></span><span class="aurora-workflow-arrow">→</span>
                </a>
                <a class="aurora-workflow-step" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('deliveries')); ?>">
                    <span class="aurora-workflow-number">04</span><span class="aurora-workflow-content"><strong>Leveranse</strong><span>Klargjør godkjent materiale og kundeleveranse.</span></span>
                </a>
            </div>
            <div class="aurora-workflow-note"><strong>Neste Aurora-lag:</strong> Customer App og Portal/API kobles senere mot denne arbeidsflyten via stabile tjenester, uten å gjøre Aurora Core til en hard avhengighet.</div>
        </section>

        <div class="aurora-dashboard-columns">
            <section class="nls1-panel aurora-command-panel">
                <span class="aurora-kicker">HURTIGHANDLINGER</span>
                <h2>Hva vil du gjøre?</h2>
                <div class="aurora-quick-actions">
                    <a href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('wizard')); ?>"><strong>Ny kunde / prosjekt</strong><span>Start ny fotografering og opprett kunden samtidig.</span></a>
                    <a href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('galleries')); ?>"><strong>Gallerier</strong><span>Last opp, regenerer preview og lag proof-PDF.</span></a>
                    <a href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('documents')); ?>"><strong>Dokumenter</strong><span>Finn kontrakter, avtaler og prosjektfiler.</span></a>
                    <a href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('settings')); ?>"><strong>Branding & innstillinger</strong><span>Logo, vannmerke og Premium Proof-oppsett.</span></a>
                </div>
            </section>
            <section class="nls1-panel aurora-roadmap-panel">
                <span class="aurora-kicker">AURORA READY</span>
                <h2>Kundeportalen kommer inn som modul</h2>
                <p>Fotoportal er fortsatt selvstendig, men nye kundevendte funksjoner bygges mot et stabilt API-lag slik at Aurora senere kan levere felles tjenester.</p>
                <div class="aurora-roadmap-steps">
                    <span class="is-current">Admin UX</span><span>Customer App</span><span>Portal/API</span><span>Levering</span>
                </div>
            </section>
        </div>

    <?php elseif ($tab === 'wizard') : ?>
        <?php if (isset($_GET['message']) && $_GET['message'] === 'missing_fields') : ?><div class="notice notice-error"><p>Fyll ut alle obligatoriske felt.</p></div><?php endif; ?>
        <div class="nls1-wizard"><div class="nls1-step active"><span>1</span>Kunde</div><div class="nls1-step active"><span>2</span>Prosjekt</div><div class="nls1-step"><span>3</span>Kontrakt</div><div class="nls1-step"><span>4</span>Invitasjon</div><div class="nls1-step"><span>5</span>Ferdig</div></div>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
            <input type="hidden" name="action" value="9ls1_fotoportal_save_client_project"><?php wp_nonce_field('9ls1_fotoportal_save_client_project'); ?>
            <div class="nls1-panel"><h2>Kunde</h2><div class="nls1-form-grid">
                <label>Kundenavn *<input type="text" name="client_name" required placeholder="f.eks. Hansen Bryllup"></label>
                <label>Kundegruppe *<select name="client_group" required><option value="">Velg gruppe</option><?php foreach(array_keys(NLS1_Fotoportal_Admin::$project_types) as $type): ?><option><?php echo esc_html($type); ?></option><?php endforeach; ?></select></label>
                <label>Kundetype<select name="client_type"><option value="private">Privat</option><option value="business">Bedrift</option><option value="artist">Artist/Band</option><option value="organization">Organisasjon</option></select></label>
                <label>Hovedkontakt fornavn *<input type="text" name="first_name" required></label>
                <label>Hovedkontakt etternavn<input type="text" name="last_name"></label>
                <label>E-post *<input type="email" name="email" required></label>
                <label>Telefon<input type="text" name="phone"></label>
                <label>Sted/by<input type="text" name="city"></label>
            </div></div>
            <div class="nls1-panel"><h2>Prosjekt</h2><div class="nls1-form-grid">
                <label>Prosjektnavn *<input type="text" name="project_name" required placeholder="f.eks. Bryllup Hansen 2027"></label>
                <label>Prosjekttype *<select name="project_type" required><option value="">Velg type</option><?php foreach(NLS1_Fotoportal_Admin::$project_types as $type=>$prefix): ?><option><?php echo esc_html($type); ?></option><?php endforeach; ?></select></label>
                <label>Dato<input type="date" name="project_date"></label><label>Lokasjon<input type="text" name="location"></label>
                <label class="nls1-full">Notater<textarea name="description" rows="4"></textarea></label><label class="nls1-checkbox"><input type="checkbox" name="is_test" value="1"> Merk som testdata</label>
            </div><p><button class="button button-primary">Opprett kunde og prosjekt</button></p></div>
        </form>

    <?php elseif ($tab === 'clients') : ?>
        <?php $search=sanitize_text_field($_GET['s']??''); $group=sanitize_text_field($_GET['group']??''); $type=sanitize_key($_GET['ctype']??''); $clients=NLS1_Fotoportal_Admin::get_clients(true,$search,$group,$type); ?>
        <div class="nls1-actions"><a class="button button-primary" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url('wizard')); ?>">Ny kunde/prosjekt</a></div>
        <div class="nls1-panel nls1-wide"><h2>Kunder</h2>
        <form method="get" class="nls1-filter"><input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Fotoportal_Admin::MENU_SLUG); ?>"><input type="hidden" name="module" value="fotoportal"><input type="hidden" name="tab" value="clients">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Søk kunde...">
            <select name="group"><option value="">Alle grupper</option><?php foreach(array_keys(NLS1_Fotoportal_Admin::$project_types) as $g): ?><option <?php selected($group,$g); ?>><?php echo esc_html($g); ?></option><?php endforeach; ?></select>
            <select name="ctype"><option value="">Alle kundetyper</option><option value="private" <?php selected($type,'private'); ?>>Privat</option><option value="business" <?php selected($type,'business'); ?>>Bedrift</option><option value="artist" <?php selected($type,'artist'); ?>>Artist/Band</option><option value="organization" <?php selected($type,'organization'); ?>>Organisasjon</option></select>
            <button class="button">Filtrer</button>
        </form>
        <table class="widefat striped"><thead><tr><th>Kundenr.</th><th>Kunde</th><th>Gruppe</th><th>Hovedkontakt</th><th>E-post</th><th>Telefon</th><th>Type</th><th>Status</th><th></th></tr></thead><tbody>
        <?php if($clients): foreach($clients as $client): $contact=NLS1_Fotoportal_Admin::get_primary_contact($client->id); ?>
            <tr><td><?php echo esc_html($client->customer_number); ?></td><td><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::client_url($client->id)); ?>"><?php echo $client->is_test?'<strong>TEST</strong> - ':''; ?><?php echo esc_html($client->client_name); ?></a></td><td><?php echo esc_html($client->client_group); ?></td><td><?php echo esc_html(NLS1_Fotoportal_Admin::format_contact_name($contact)); ?></td><td><?php echo esc_html($client->email); ?></td><td><?php echo esc_html($client->phone); ?></td><td><?php echo esc_html(NLS1_Fotoportal_Admin::client_type_label($client->client_type)); ?></td><td><?php echo esc_html($client->status); ?></td><td><?php if ((int)$client->is_test === 1): ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette denne testkunden?');"><input type="hidden" name="action" value="9ls1_fotoportal_delete_test_item"><input type="hidden" name="item_type" value="client"><input type="hidden" name="item_id" value="<?php echo esc_attr($client->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_delete_test_item'); ?><button class="button button-small">Slett test</button></form><?php endif; ?></td></tr>
        <?php endforeach; else: ?><tr><td colspan="9">Ingen kunder.</td></tr><?php endif; ?></tbody></table></div>

    <?php elseif ($tab === 'projects') : ?>
        <?php $search=sanitize_text_field($_GET['s']??''); $ptype=sanitize_text_field($_GET['ptype']??''); $status=sanitize_key($_GET['pstatus']??''); $projects=NLS1_Fotoportal_Admin::get_projects(true,$search,$ptype,$status); ?>
        <div class="nls1-panel nls1-wide"><h2>Prosjekter</h2>
        <form method="get" class="nls1-filter"><input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Fotoportal_Admin::MENU_SLUG); ?>"><input type="hidden" name="module" value="fotoportal"><input type="hidden" name="tab" value="projects">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Søk prosjekt/kunde...">
            <select name="ptype"><option value="">Alle typer</option><?php foreach(array_keys(NLS1_Fotoportal_Admin::$project_types) as $g): ?><option <?php selected($ptype,$g); ?>><?php echo esc_html($g); ?></option><?php endforeach; ?></select>
            <select name="pstatus"><option value="">Alle statuser</option><?php foreach(NLS1_Fotoportal_Admin::$project_statuses as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($status,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select>
            <button class="button">Filtrer</button>
        </form>
        <table class="widefat striped"><thead><tr><th>Prosjektnr.</th><th>Prosjekt</th><th>Kunde</th><th>Type</th><th>Dato</th><th>Status</th><th></th></tr></thead><tbody>
        <?php if($projects): foreach($projects as $project): ?><tr><td><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($project->id)); ?>"><?php echo esc_html($project->project_number); ?></a></td><td><?php echo $project->is_test?'<strong>TEST</strong> - ':''; ?><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($project->id)); ?>"><?php echo esc_html($project->project_name); ?></a></td><td><?php echo esc_html($project->client_name); ?></td><td><?php echo esc_html($project->project_type); ?></td><td><?php echo esc_html($project->project_date); ?></td><td><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></td><td><?php if ((int)$project->is_test === 1): ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette dette testprosjektet?');"><input type="hidden" name="action" value="9ls1_fotoportal_delete_test_item"><input type="hidden" name="item_type" value="project"><input type="hidden" name="item_id" value="<?php echo esc_attr($project->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_delete_test_item'); ?><button class="button button-small">Slett test</button></form><?php endif; ?></td></tr><?php endforeach; else: ?><tr><td colspan="7">Ingen prosjekter.</td></tr><?php endif; ?></tbody></table></div>

    <?php elseif ($tab === 'client_profile') : ?>
        <?php $client=NLS1_Fotoportal_Admin::get_client((int)($_GET['client_id']??0)); if(!$client){echo '<div class="notice notice-error"><p>Kunde ikke funnet.</p></div>'; return;} $contacts=NLS1_Fotoportal_Admin::get_contacts($client->id); $projects=NLS1_Fotoportal_Admin::get_client_projects($client->id); $logs=NLS1_Fotoportal_Admin::get_logs($client->id); ?>
        <div class="nls1-profile-header"><div><h2><?php echo esc_html($client->client_name); ?></h2><p><?php echo esc_html($client->customer_number); ?> · <?php echo esc_html($client->client_group); ?> · <?php echo esc_html(NLS1_Fotoportal_Admin::client_type_label($client->client_type)); ?></p></div><span class="nls1-status-badge"><?php echo esc_html($client->status); ?></span></div>
        <div class="nls1-panel">
            <h3>Kundeinformasjon</h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
                <input type="hidden" name="action" value="9ls1_fotoportal_update_client">
                <input type="hidden" name="client_id" value="<?php echo esc_attr($client->id); ?>">
                <?php wp_nonce_field('9ls1_fotoportal_update_client'); ?>
                <div class="nls1-form-grid">
                    <label>Kundenavn<input type="text" name="client_name" value="<?php echo esc_attr($client->client_name); ?>" required></label>
                    <label>Kundegruppe<select name="client_group" required><?php foreach(array_keys(NLS1_Fotoportal_Admin::$project_types) as $g): ?><option <?php selected($client->client_group,$g); ?>><?php echo esc_html($g); ?></option><?php endforeach; ?></select></label>
                    <label>Kundetype<select name="client_type"><option value="private" <?php selected($client->client_type,'private'); ?>>Privat</option><option value="business" <?php selected($client->client_type,'business'); ?>>Bedrift</option><option value="artist" <?php selected($client->client_type,'artist'); ?>>Artist/Band</option><option value="organization" <?php selected($client->client_type,'organization'); ?>>Organisasjon</option></select></label>
                    <label>E-post<input type="email" name="email" value="<?php echo esc_attr($client->email); ?>"></label>
                    <label>Telefon<input type="text" name="phone" value="<?php echo esc_attr($client->phone); ?>"></label>
                    <label>Sted/by<input type="text" name="city" value="<?php echo esc_attr($client->city); ?>"></label>
                    <label class="nls1-full">Notater<textarea name="notes" rows="3"><?php echo esc_textarea($client->notes); ?></textarea></label>
                </div>
                <p><button class="button button-primary">Lagre kunde</button></p>
            </form>
        </div>
        <div class="nls1-profile-grid"><div class="nls1-panel"><h3>Kontakter</h3><table class="widefat striped"><tbody><?php foreach($contacts as $c): ?><tr><td><?php echo $c->is_primary?'<strong>Hovedkontakt</strong><br>':''; ?><?php echo esc_html(NLS1_Fotoportal_Admin::format_contact_name($c)); ?></td><td><?php echo esc_html($c->email); ?></td><td><?php echo esc_html($c->phone); ?></td></tr><?php endforeach; ?></tbody></table>
        <h4>Legg til kontakt</h4><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-mini-form"><input type="hidden" name="action" value="9ls1_fotoportal_add_contact"><input type="hidden" name="client_id" value="<?php echo esc_attr($client->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_add_contact'); ?><input name="first_name" placeholder="Fornavn" required><input name="last_name" placeholder="Etternavn"><input name="email" type="email" placeholder="E-post" required><input name="phone" placeholder="Telefon"><input name="contact_role" placeholder="Rolle"><button class="button">Legg til</button></form></div>
        <div class="nls1-panel"><h3>Prosjekter</h3><table class="widefat striped"><tbody><?php foreach($projects as $p): ?><tr><td><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($p->id)); ?>"><?php echo esc_html($p->project_number); ?></a></td><td><?php echo esc_html($p->project_name); ?></td><td><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($p->status)); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        <div class="nls1-panel"><h3>Logg</h3><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="9ls1_fotoportal_add_log"><input type="hidden" name="client_id" value="<?php echo esc_attr($client->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_add_log'); ?><textarea name="message" rows="3" placeholder="Skriv notat..."></textarea><p><button class="button">Legg til notat</button></p></form><ul class="nls1-log"><?php foreach($logs as $l): ?><li><strong><?php echo esc_html($l->created_at); ?></strong> — <?php echo esc_html($l->message); ?></li><?php endforeach; ?></ul></div>

    <?php elseif ($tab === 'project_profile') : ?>
        <?php $project=NLS1_Fotoportal_Admin::get_project((int)($_GET['project_id']??0)); if(!$project){echo '<div class="notice notice-error"><p>Prosjekt ikke funnet.</p></div>'; return;} $logs=NLS1_Fotoportal_Admin::get_logs(0,$project->id); ?>
        <div class="nls1-profile-header"><div><h2><?php echo esc_html($project->project_name); ?></h2><p><?php echo esc_html($project->project_number); ?> · <a href="<?php echo esc_url(NLS1_Fotoportal_Admin::client_url($project->client_id)); ?>"><?php echo esc_html($project->client_name); ?></a> · <?php echo esc_html($project->project_type); ?></p></div><span class="nls1-status-badge"><?php echo esc_html(NLS1_Fotoportal_Admin::status_label($project->status)); ?></span></div>
        <div class="nls1-panel">
            <h3>Rediger prosjekt</h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
                <input type="hidden" name="action" value="9ls1_fotoportal_update_project">
                <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                <?php wp_nonce_field('9ls1_fotoportal_update_project'); ?>
                <div class="nls1-form-grid">
                    <label>Prosjektnavn<input type="text" name="project_name" value="<?php echo esc_attr($project->project_name); ?>" required></label>
                    <label>Prosjekttype<select name="project_type" required><?php foreach(array_keys(NLS1_Fotoportal_Admin::$project_types) as $type): ?><option <?php selected($project->project_type,$type); ?>><?php echo esc_html($type); ?></option><?php endforeach; ?></select></label>
                    <label>Dato<input type="date" name="project_date" value="<?php echo esc_attr($project->project_date); ?>"></label>
                    <label>Lokasjon<input type="text" name="location" value="<?php echo esc_attr($project->location); ?>"></label>
                    <label class="nls1-full">Notater<textarea name="description" rows="3"><?php echo esc_textarea($project->description); ?></textarea></label>
                </div>
                <p><button class="button button-primary">Lagre prosjekt</button></p>
            </form>
        </div>
        <div class="nls1-profile-grid"><div class="nls1-panel"><h3>Prosjektdetaljer</h3><p><strong>Dato:</strong> <?php echo esc_html($project->project_date); ?></p><p><strong>Lokasjon:</strong> <?php echo esc_html($project->location); ?></p><p><?php echo nl2br(esc_html($project->description)); ?></p></div>
        <div class="nls1-panel"><h3>Status</h3><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="9ls1_fotoportal_update_project_status"><input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_update_project_status'); ?><select name="status"><?php foreach(NLS1_Fotoportal_Admin::$project_statuses as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($project->status,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select><button class="button button-primary">Oppdater status</button></form></div></div>
        <?php $project_galleries = NLS1_Fotoportal_Admin::get_galleries($project->id); ?>
        <div class="nls1-panel nls1-wide">
            <h3>Gallerier</h3>
            <table class="widefat striped">
                <thead><tr><th>Galleri</th><th>Bilder</th><th>Nedlastbar til</th><th>Auto-slett</th><th>Backup</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php if($project_galleries): foreach($project_galleries as $gal): ?>
                    <tr>
                        <td><?php echo esc_html($gal->gallery_title); ?><br><small><?php echo esc_html($gal->gallery_number); ?></small></td>
                        <td><?php echo esc_html($gal->original_count); ?> originaler<br><small><?php echo esc_html($gal->preview_count); ?> preview · <?php echo esc_html($gal->thumbnail_count); ?> thumbnails</small></td>
                        <td><?php echo esc_html($gal->downloadable_until); ?></td>
                        <td><?php echo esc_html($gal->auto_delete_at); ?></td>
                        <td><?php echo $gal->local_backup_confirmed ? 'Bekreftet' : '<strong>Mangler</strong>'; ?></td>
                        <td><?php echo esc_html($gal->status); ?></td>
                        <td>
                            <details>
                                <summary>Handling</summary>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:10px;">
                                    <input type="hidden" name="action" value="9ls1_fotoportal_regenerate_gallery">
                                    <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gal->id); ?>">
                                    <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                                    <?php wp_nonce_field('9ls1_fotoportal_regenerate_gallery'); ?>
                                    <button class="button">Regenerer preview/thumbnails</button>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:10px;">
                                    <input type="hidden" name="action" value="9ls1_fotoportal_generate_proof_pdf">
                                    <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gal->id); ?>">
                                    <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                                    <?php wp_nonce_field('9ls1_fotoportal_generate_proof_pdf'); ?>
                                    <button class="button button-primary">Generer PDF Preview</button>
                                </form>
                                <?php $pdfs = NLS1_Fotoportal_Admin::get_gallery_export_pdfs($gal); if($pdfs): ?>
                                    <div class="nls1-export-links">
                                    <?php foreach($pdfs as $pdf): ?>
                                        <a class="button" href="<?php echo esc_url($pdf['url']); ?>" target="_blank">Last ned <?php echo esc_html($pdf['name']); ?></a>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette galleri?');">
                                    <input type="hidden" name="action" value="9ls1_fotoportal_delete_gallery">
                                    <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gal->id); ?>">
                                    <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                                    <?php wp_nonce_field('9ls1_fotoportal_delete_gallery'); ?>
                                    <label><input type="checkbox" name="delete_files" value="1"> Slett også filer fra server</label>
                                    <button class="button">Slett galleri</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                    <?php $imgs = NLS1_Fotoportal_Admin::get_gallery_images($gal->id, 12); if($imgs): ?>
                    <tr><td colspan="7">
                        <div class="nls1-image-preview-grid">
                            <?php foreach($imgs as $img): ?>
                                <div class="nls1-image-preview">
                                    <?php $display_url = $img->thumbnail_url ?: ($img->preview_url ?: $img->original_url); if($display_url): ?><img src="<?php echo esc_url($display_url); ?>" loading="lazy"><?php endif; ?>
                                    <small><?php echo esc_html($img->original_filename); ?></small>
                                    <small class="nls1-file-source"><?php echo $img->thumbnail_url ? 'thumbnail' : ($img->preview_url ? 'preview' : 'original'); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                <?php endforeach; else: ?><tr><td colspan="8">Ingen gallerier ennå.</td></tr><?php endif; ?>
                </tbody>
            </table>

            <h4>Last opp ZIP-galleri</h4>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="9ls1_fotoportal_upload_gallery_zip">
                <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                <?php wp_nonce_field('9ls1_fotoportal_upload_gallery_zip'); ?>
                <div class="nls1-form-grid">
                    <label>Gallerinavn<input type="text" name="gallery_title" placeholder="f.eks. Hovedgalleri"></label>
                    <label>ZIP-fil<input type="file" name="gallery_zip" accept=".zip" required></label>
                    <label>Nedlastbar til<input type="date" name="downloadable_until"></label>
                    <label>Automatisk slettedato<input type="date" name="auto_delete_at"></label>
                    <label class="nls1-checkbox"><input type="checkbox" name="local_backup_confirmed" value="1"> Lokal backup er bekreftet</label>
                    <label class="nls1-checkbox"><input type="checkbox" name="watermark_enabled" value="1" checked> Vannmerke på preview senere</label>
                    <label class="nls1-checkbox"><input type="checkbox" name="download_enabled" value="1"> Aktiver nedlasting</label>
                </div>
                <p class="description">Mappestruktur opprettes automatisk: original, preview, thumbnails, zip og export.</p>
                <p><button class="button button-primary">Last opp og pakk ut ZIP</button></p>
            </form>
        </div>

        <?php $project_documents = NLS1_Fotoportal_Admin::get_documents($project->id); ?>
        <div class="nls1-panel nls1-wide">
            <h3>Dokumenter</h3>
            <table class="widefat striped">
                <thead><tr><th>Dokument</th><th>Type</th><th>Dato</th><th>Handling</th><th></th></tr></thead>
                <tbody>
                <?php if($project_documents): foreach($project_documents as $doc): ?>
                    <tr>
                        <td><?php echo esc_html($doc->document_title); ?><br><small><?php echo esc_html($doc->notes); ?></small></td>
                        <td><?php echo esc_html($doc->document_type); ?></td>
                        <td><?php echo esc_html($doc->created_at); ?></td>
                        <td><a class="button" href="<?php echo esc_url($doc->file_url); ?>" target="_blank">Vis / Last ned</a></td>
                        <td><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette dokumentet fra prosjektet? Filen slettes ikke fra mediebiblioteket.');"><input type="hidden" name="action" value="9ls1_fotoportal_delete_document"><input type="hidden" name="document_id" value="<?php echo esc_attr($doc->id); ?>"><input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_delete_document'); ?><button class="button">Fjern</button></form></td>
                    </tr>
                <?php endforeach; else: ?><tr><td colspan="5">Ingen dokumenter ennå.</td></tr><?php endif; ?>
                </tbody>
            </table>

            <h4>Legg til dokument</h4>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form nls1-document-form">
                <input type="hidden" name="action" value="9ls1_fotoportal_add_document">
                <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                <input type="hidden" name="attachment_id" id="nls1_document_attachment_id" value="">
                <?php wp_nonce_field('9ls1_fotoportal_add_document'); ?>
                <div class="nls1-form-grid">
                    <label>Dokumentnavn<input type="text" name="document_title" required placeholder="f.eks. Fotograferingsavtale"></label>
                    <label>Dokumenttype<select name="document_type"><?php foreach(NLS1_Fotoportal_Admin::$document_types as $dt): ?><option><?php echo esc_html($dt); ?></option><?php endforeach; ?></select></label>
                    <label class="nls1-full">Fil-URL<input type="url" name="file_url" id="nls1_document_file_url" required placeholder="Velg fra mediebibliotek eller lim inn URL"></label>
                    <label class="nls1-full">Notat<textarea name="notes" rows="2"></textarea></label>
                </div>
                <p><button type="button" class="button" id="nls1_select_document">Velg fra mediebibliotek</button> <button class="button button-primary">Legg til dokument</button></p>
            </form>
        </div>

        <?php $project_contracts = NLS1_Fotoportal_Admin::get_project_contracts($project->id); ?>
        <div class="nls1-panel nls1-wide">
            <h3>Kontrakter</h3>
            <table class="widefat striped">
                <thead><tr><th>Kontrakt</th><th>Signerer</th><th>Status</th><th>Signeringslenke</th><th></th></tr></thead>
                <tbody>
                <?php if($project_contracts): foreach($project_contracts as $ct): ?>
                    <tr>
                        <td><?php echo esc_html($ct->contract_name); ?><br><small>v<?php echo esc_html($ct->contract_version); ?></small></td>
                        <td><?php echo esc_html($ct->signer_name); ?><br><small><?php echo esc_html($ct->signer_email); ?></small></td>
                        <td><?php echo esc_html(NLS1_Fotoportal_Admin::contract_status_label($ct->status)); ?></td>
                        <td><?php if($ct->status !== 'signed'): ?><input class="nls1-copy-input" readonly value="<?php echo esc_attr(NLS1_Fotoportal_Admin::signing_url($ct->id)); ?>"><?php else: ?>Signert <?php echo esc_html($ct->signed_at); ?><?php endif; ?></td>
                        <td><?php if($ct->status === 'draft'): ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="9ls1_fotoportal_mark_contract_sent"><input type="hidden" name="contract_id" value="<?php echo esc_attr($ct->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_mark_contract_sent'); ?><button class="button">Marker sendt</button></form><?php endif; ?></td>
                    </tr>
                <?php endforeach; else: ?><tr><td colspan="5">Ingen kontrakter ennå.</td></tr><?php endif; ?>
                </tbody>
            </table>

            <h4>Opprett ny kontrakt</h4>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
                <input type="hidden" name="action" value="9ls1_fotoportal_create_contract">
                <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                <?php wp_nonce_field('9ls1_fotoportal_create_contract'); ?>
                <div class="nls1-form-grid">
                    <label>Kontraktsnavn<input type="text" name="contract_name" value="Avtale - <?php echo esc_attr($project->project_name); ?>" required></label>
                    <label>Versjon<input type="text" name="contract_version" value="1.0"></label>
                    <label>Signerer navn<input type="text" name="signer_name" placeholder="Kundens navn"></label>
                    <label>Signerer e-post<input type="email" name="signer_email" placeholder="kunde@epost.no" required></label>
                    <label class="nls1-full">Kontrakttekst<textarea name="contract_text" rows="10" required>Avtale for <?php echo esc_html($project->project_name); ?>

Kunde: <?php echo esc_html($project->client_name); ?>
Prosjekt: <?php echo esc_html($project->project_number); ?>

Kunden bekrefter med dette at avtalen er lest og godkjent.</textarea></label>
                </div>
                <p><button class="button button-primary">Opprett kontrakt</button></p>
            </form>
        </div>

        <div class="nls1-panel"><h3>Logg</h3><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="9ls1_fotoportal_add_log"><input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_add_log'); ?><textarea name="message" rows="3" placeholder="Skriv prosjektnotat..."></textarea><p><button class="button">Legg til notat</button></p></form><ul class="nls1-log"><?php foreach($logs as $l): ?><li><strong><?php echo esc_html($l->created_at); ?></strong> — <?php echo esc_html($l->message); ?></li><?php endforeach; ?></ul></div>

    <?php elseif ($tab === 'contracts') : ?>
        <?php $contracts = NLS1_Fotoportal_Admin::get_contracts(true); ?>
        <div class="nls1-panel nls1-wide"><h2>Kontrakter</h2>
        <table class="widefat striped"><thead><tr><th>Kontrakt</th><th>Kunde</th><th>Prosjekt</th><th>Signerer</th><th>Status</th><th>Signeringslenke</th></tr></thead><tbody>
        <?php if($contracts): foreach($contracts as $ct): ?>
            <tr>
                <td><?php echo esc_html($ct->contract_name); ?><br><small>v<?php echo esc_html($ct->contract_version); ?></small></td>
                <td><?php echo esc_html($ct->client_name); ?></td>
                <td><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($ct->project_id)); ?>"><?php echo esc_html($ct->project_number . ' - ' . $ct->project_name); ?></a></td>
                <td><?php echo esc_html($ct->signer_name); ?><br><small><?php echo esc_html($ct->signer_email); ?></small></td>
                <td><?php echo esc_html(NLS1_Fotoportal_Admin::contract_status_label($ct->status)); ?></td>
                <td><?php if($ct->status !== 'signed'): ?><input class="nls1-copy-input" readonly value="<?php echo esc_attr(NLS1_Fotoportal_Admin::signing_url($ct->id)); ?>"><?php else: ?>Signert <?php echo esc_html($ct->signed_at); ?><?php endif; ?></td>
            </tr>
        <?php endforeach; else: ?><tr><td colspan="6">Ingen kontrakter ennå. Opprett kontrakt fra et prosjekt.</td></tr><?php endif; ?>
        </tbody></table></div>
    <?php elseif ($tab === 'documents') : ?>
        <?php $documents = NLS1_Fotoportal_Admin::get_documents(0, true); ?>
        <div class="nls1-panel nls1-wide"><h2>Dokumenter</h2>
        <p>Samlet dokumentoversikt for alle prosjekter.</p>
        <table class="widefat striped"><thead><tr><th>Dokument</th><th>Type</th><th>Kunde</th><th>Prosjekt</th><th>Handling</th></tr></thead><tbody>
        <?php if($documents): foreach($documents as $doc): ?>
            <tr>
                <td><?php echo esc_html($doc->document_title); ?></td>
                <td><?php echo esc_html($doc->document_type); ?></td>
                <td><?php echo esc_html($doc->client_name); ?></td>
                <td><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($doc->project_id)); ?>"><?php echo esc_html($doc->project_number . ' - ' . $doc->project_name); ?></a></td>
                <td><a class="button" href="<?php echo esc_url($doc->file_url); ?>" target="_blank">Vis / Last ned</a></td>
            </tr>
        <?php endforeach; else: ?><tr><td colspan="5">Ingen dokumenter ennå.</td></tr><?php endif; ?>
        </tbody></table></div>

    <?php elseif ($tab === 'galleries') : ?>
        <?php $galleries = NLS1_Fotoportal_Admin::get_galleries(0, true); ?>
        <div class="nls1-panel nls1-wide"><h2>Gallerier</h2>
        <p>Samlet oversikt over alle opplastede gallerier.</p>
        <table class="widefat striped"><thead><tr><th>Galleri</th><th>Kunde</th><th>Prosjekt</th><th>Bilder</th><th>Nedlastbar til</th><th>Auto-slett</th><th>Status</th><th></th></tr></thead><tbody>
        <?php if($galleries): foreach($galleries as $gal): ?>
            <tr>
                <td><?php echo esc_html($gal->gallery_title); ?><br><small><?php echo esc_html($gal->gallery_number); ?></small></td>
                <td><?php echo esc_html($gal->client_name); ?></td>
                <td><a href="<?php echo esc_url(NLS1_Fotoportal_Admin::project_url($gal->project_id)); ?>"><?php echo esc_html($gal->project_number . ' - ' . $gal->project_name); ?></a></td>
                <td><?php echo esc_html($gal->original_count); ?></td>
                <td><?php echo esc_html($gal->downloadable_until); ?></td>
                <td><?php echo esc_html($gal->auto_delete_at); ?></td>
                <td><?php echo esc_html($gal->status); ?></td>
                <td>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:8px;">
                        <input type="hidden" name="action" value="9ls1_fotoportal_regenerate_gallery">
                        <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gal->id); ?>">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr($gal->project_id); ?>">
                        <?php wp_nonce_field('9ls1_fotoportal_regenerate_gallery'); ?>
                        <button class="button">Regenerer</button>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:8px;">
                        <input type="hidden" name="action" value="9ls1_fotoportal_generate_proof_pdf">
                        <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gal->id); ?>">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr($gal->project_id); ?>">
                        <?php wp_nonce_field('9ls1_fotoportal_generate_proof_pdf'); ?>
                        <button class="button button-primary">PDF Preview</button>
                    </form>
                    <?php $pdfs = NLS1_Fotoportal_Admin::get_gallery_export_pdfs($gal); if($pdfs): foreach($pdfs as $pdf): ?>
                        <a class="button" href="<?php echo esc_url($pdf['url']); ?>" target="_blank">Last ned PDF</a>
                    <?php endforeach; endif; ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette galleri?');">
                        <input type="hidden" name="action" value="9ls1_fotoportal_delete_gallery">
                        <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gal->id); ?>">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr($gal->project_id); ?>">
                        <?php wp_nonce_field('9ls1_fotoportal_delete_gallery'); ?>
                        <label><input type="checkbox" name="delete_files" value="1"> Slett filer</label>
                        <button class="button">Slett</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; else: ?><tr><td colspan="8">Ingen gallerier ennå.</td></tr><?php endif; ?>
        </tbody></table></div>
    <?php elseif ($tab === 'deliveries') : ?><div class="nls1-panel"><h2>Leveranser</h2><p>Her kommer nedlastingsrettigheter, bruksrettgodkjenning og leveringslogg.</p></div>
    <?php elseif ($tab === 'resources') : ?><div class="nls1-panel"><h2>Ressurser</h2><p>Her kommer Wedding Pose Guide, familieguide, konfirmasjonsguide og andre kunderessurser.</p></div>
    <?php elseif ($tab === 'shop') : ?><div class="nls1-panel"><h2>Nettbutikk</h2><p>Her kommer WooCommerce-kobling, print, album og ekstra bilder.</p></div>
    <?php elseif ($tab === 'settings') : ?>
        <?php if (isset($_GET['message']) && $_GET['message'] === 'testdata_deleted') : ?><div class="notice notice-success"><p>Alle testdata er slettet.</p></div><?php endif; ?>
        <div class="nls1-panel"><h2>Testmodus</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block; margin-right:10px;"><input type="hidden" name="action" value="9ls1_fotoportal_create_testdata"><?php wp_nonce_field('9ls1_fotoportal_save_client_project'); ?><button class="button button-primary">Opprett testdata</button></form><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;" onsubmit="return confirm('Er du sikker på at du vil slette alle testdata?');"><input type="hidden" name="action" value="9ls1_fotoportal_delete_testdata"><?php wp_nonce_field('9ls1_fotoportal_delete_testdata'); ?><button class="button button-secondary">Slett alle testdata</button></form></div>
        <?php $branding = NLS1_Fotoportal_Admin::branding_settings(); ?>
        <div class="nls1-panel nls1-wide">
            <h2>Branding og vannmerke</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
                <input type="hidden" name="action" value="9ls1_fotoportal_save_branding">
                <input type="hidden" name="watermark_logo_id" id="nls1_watermark_logo_id" value="<?php echo esc_attr($branding['watermark_logo_id']); ?>">
                <input type="hidden" name="pdf_cover_image_id" id="nls1_pdf_cover_image_id" value="<?php echo esc_attr($branding['pdf_cover_image_id']); ?>">
                <input type="hidden" name="pdf_signature_image_id" id="nls1_pdf_signature_image_id" value="<?php echo esc_attr($branding['pdf_signature_image_id']); ?>">
                <?php wp_nonce_field('9ls1_fotoportal_save_branding'); ?>
                <div class="nls1-form-grid">
                    <label>Brand/navn<input type="text" name="brand_name" value="<?php echo esc_attr($branding['brand_name']); ?>"></label>
                    <label>Kontaktperson<input type="text" name="contact_name" value="<?php echo esc_attr($branding['contact_name']); ?>"></label>
                    <label>E-post<input type="email" name="contact_email" value="<?php echo esc_attr($branding['contact_email']); ?>"></label>
                    <label>Telefon<input type="text" name="contact_phone" value="<?php echo esc_attr($branding['contact_phone']); ?>"></label>
                    <label>Nettside<input type="url" name="website" value="<?php echo esc_attr($branding['website']); ?>"></label>
                    <label>Vannmerketype
                        <select name="watermark_type">
                            <option value="text" <?php selected($branding['watermark_type'],'text'); ?>>Ren tekst</option>
                            <option value="logo" <?php selected($branding['watermark_type'],'logo'); ?>>Logo</option>
                            <option value="text_logo" <?php selected($branding['watermark_type'],'text_logo'); ?>>Tekst + logo</option>
                        </select>
                    </label>
                    <label>Vannmerketekst<input type="text" name="watermark_text" value="<?php echo esc_attr($branding['watermark_text']); ?>"></label>
                    <label>Logo URL<input type="url" name="watermark_logo_url" id="nls1_watermark_logo_url" value="<?php echo esc_attr($branding['watermark_logo_url']); ?>"></label>
                    <label>Plassering
                        <select name="watermark_position">
                            <option value="bottom_right" <?php selected($branding['watermark_position'],'bottom_right'); ?>>Nederst høyre</option>
                            <option value="bottom_left" <?php selected($branding['watermark_position'],'bottom_left'); ?>>Nederst venstre</option>
                            <option value="center" <?php selected($branding['watermark_position'],'center'); ?>>Midtstilt</option>
                            <option value="center_large" <?php selected($branding['watermark_position'],'center_large'); ?>>Stort over bildet</option>
                            <option value="pattern" <?php selected($branding['watermark_position'],'pattern'); ?>>Mange små / mønster</option>
                        </select>
                    </label>
                    <label>Opasitet %<input type="number" name="watermark_opacity" value="<?php echo esc_attr($branding['watermark_opacity']); ?>" min="5" max="95"></label>
                    <label>Størrelse %<input type="number" name="watermark_size" value="<?php echo esc_attr($branding['watermark_size']); ?>" min="5" max="80"></label>
                    <label>Preview langside px<input type="number" name="preview_long_edge" value="<?php echo esc_attr($branding['preview_long_edge']); ?>" min="800" max="4000"></label>
                    <label>Thumbnail størrelse px<input type="number" name="thumbnail_size" value="<?php echo esc_attr($branding['thumbnail_size']); ?>" min="150" max="800"></label>
                    <label class="nls1-full"><strong>PDF Premium Design</strong></label>
                    <label>PDF forsidebilde URL<input type="url" name="pdf_cover_image_url" id="nls1_pdf_cover_image_url" value="<?php echo esc_attr($branding['pdf_cover_image_url']); ?>"></label>
                    <label>PDF signatur URL<input type="url" name="pdf_signature_image_url" id="nls1_pdf_signature_image_url" value="<?php echo esc_attr($branding['pdf_signature_image_url']); ?>"></label>
                    <label>PDF hovedfarge<input type="text" name="pdf_accent_color" value="<?php echo esc_attr($branding['pdf_accent_color']); ?>"></label>
                    <label>PDF lys bakgrunn<input type="text" name="pdf_secondary_color" value="<?php echo esc_attr($branding['pdf_secondary_color']); ?>"></label>
                    <label class="nls1-full">Fremtidig gallerilenke/QR<input type="url" name="pdf_gallery_url" value="<?php echo esc_attr($branding['pdf_gallery_url']); ?>" placeholder="https://trondenielsen.no/galleri/..."></label>
                </div>
                <p>
                    <button type="button" class="button" id="nls1_select_watermark_logo">Velg logo fra mediebibliotek</button>
                    <button type="button" class="button" id="nls1_select_pdf_cover">Velg PDF forsidebilde</button>
                    <button type="button" class="button" id="nls1_select_pdf_signature">Velg PDF signatur</button>
                    <button class="button button-primary">Lagre branding</button>
                </p>
                <p class="description"><strong>Viktig:</strong> Originalbildene blir aldri vannmerket. Vannmerke legges kun på preview-bildene. Preview lagres som <code>preview_IMG_0001.jpg</code> og thumbnails som <code>thumb_IMG_0001.jpg</code>. PNG-logo med transparent bakgrunn støttes. Etter endring av vannmerke kan du bruke «Regenerer preview/thumbnails» på galleriet.</p>
            </form>
        </div>

        <div class="nls1-panel nls1-wide">
            <h2>Kontraktmaler</h2>
            <p>Bruk variabler som {kunde_navn}, {prosjekt_navn}, {prosjekt_nr}, {prosjekt_dato}, {lokasjon}, {kontaktperson} og {fotograf}.</p>
            <?php $templates = NLS1_Fotoportal_Admin::get_document_templates(true); ?>
            <table class="widefat striped"><thead><tr><th>Mal</th><th>Prosjekttype</th><th>Dokumenttype</th><th>Status</th><th></th></tr></thead><tbody>
            <?php if($templates): foreach($templates as $tpl): ?>
                <tr>
                    <td><?php echo esc_html($tpl->template_name); ?></td>
                    <td><?php echo esc_html($tpl->project_type); ?></td>
                    <td><?php echo esc_html($tpl->document_type); ?></td>
                    <td><?php echo esc_html($tpl->status); ?></td>
                    <td><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Slette kontraktmal?');"><input type="hidden" name="action" value="9ls1_fotoportal_delete_template"><input type="hidden" name="template_id" value="<?php echo esc_attr($tpl->id); ?>"><?php wp_nonce_field('9ls1_fotoportal_delete_template'); ?><button class="button">Slett</button></form></td>
                </tr>
            <?php endforeach; else: ?><tr><td colspan="5">Ingen maler ennå.</td></tr><?php endif; ?>
            </tbody></table>

            <h3>Ny kontraktmal</h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nls1-form">
                <input type="hidden" name="action" value="9ls1_fotoportal_save_template">
                <?php wp_nonce_field('9ls1_fotoportal_save_template'); ?>
                <div class="nls1-form-grid">
                    <label>Malnavn<input type="text" name="template_name" required placeholder="f.eks. Standard Bryllup"></label>
                    <label>Prosjekttype<select name="project_type"><option value="">Alle</option><?php foreach(array_keys(NLS1_Fotoportal_Admin::$project_types) as $pt): ?><option><?php echo esc_html($pt); ?></option><?php endforeach; ?></select></label>
                    <label>Dokumenttype<select name="document_type"><?php foreach(NLS1_Fotoportal_Admin::$document_types as $dt): ?><option><?php echo esc_html($dt); ?></option><?php endforeach; ?></select></label>
                    <label class="nls1-checkbox"><input type="checkbox" name="is_test" value="1"> Testmal</label>
                    <label class="nls1-full">Maltekst<textarea name="template_content" rows="10" required>Avtale for {prosjekt_navn}

Kunde: {kunde_navn}
Prosjekt: {prosjekt_nr}
Dato: {prosjekt_dato}
Lokasjon: {lokasjon}
Kontaktperson: {kontaktperson}

Kunden bekrefter at avtalen er lest og godkjent.</textarea></label>
                </div>
                <p><button class="button button-primary">Lagre mal</button></p>
            </form>
        </div>
        <div class="nls1-panel"><h2>Design</h2><p>Her kommer logo, portalnavn, primærfarge, sekundærfarge, knappfarge og bakgrunn.</p></div>
    <?php endif; ?>
</div>

<script>
jQuery(function($){
    $('#nls1_select_document').on('click', function(e){
        e.preventDefault();
        var frame = wp.media({
            title: 'Velg dokument',
            button: { text: 'Bruk dette dokumentet' },
            multiple: false
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#nls1_document_file_url').val(attachment.url);
            $('#nls1_document_attachment_id').val(attachment.id);
            if (!$('input[name="document_title"]').val()) {
                $('input[name="document_title"]').val(attachment.title || attachment.filename);
            }
        });
        frame.open();
    });
});
</script>

<script>
jQuery(function($){
    $('#nls1_select_watermark_logo').on('click', function(e){
        e.preventDefault();
        var frame = wp.media({
            title: 'Velg vannmerkelogo',
            button: { text: 'Bruk denne logoen' },
            multiple: false
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#nls1_watermark_logo_url').val(attachment.url);
            $('#nls1_watermark_logo_id').val(attachment.id);
        });
        frame.open();
    });
});
</script>

<script>
jQuery(function($){
    $('#nls1_select_pdf_cover').on('click', function(e){
        e.preventDefault();
        var frame = wp.media({ title: 'Velg PDF forsidebilde', button: { text: 'Bruk som forsidebilde' }, multiple: false });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#nls1_pdf_cover_image_url').val(attachment.url);
            $('#nls1_pdf_cover_image_id').val(attachment.id);
        });
        frame.open();
    });
    $('#nls1_select_pdf_signature').on('click', function(e){
        e.preventDefault();
        var frame = wp.media({ title: 'Velg PDF signatur', button: { text: 'Bruk signatur' }, multiple: false });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#nls1_pdf_signature_image_url').val(attachment.url);
            $('#nls1_pdf_signature_image_id').val(attachment.id);
        });
        frame.open();
    });
});
</script>

<script>
(function(){
    function relocateAuroraAdminNotices(){
        var wrap = document.querySelector('.nls1-fotoportal-admin');
        if (!wrap) return;
        var target = wrap.querySelector('.aurora-external-notices');
        var header = wrap.querySelector('.aurora-module-header');
        if (!target || !header) return;

        header.querySelectorAll('.notice, .updated, .error, .update-nag').forEach(function(notice){
            target.appendChild(notice);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', relocateAuroraAdminNotices);
    } else {
        relocateAuroraAdminNotices();
    }

    var observer = new MutationObserver(relocateAuroraAdminNotices);
    observer.observe(document.body, { childList: true, subtree: true });
    window.setTimeout(function(){ observer.disconnect(); }, 5000);
})();
</script>
