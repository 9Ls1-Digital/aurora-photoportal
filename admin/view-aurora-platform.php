<?php
if (!defined('ABSPATH')) exit;
$account_filters = [
    'search' => sanitize_text_field(wp_unslash($_GET['account_search'] ?? '')),
    'status' => sanitize_key($_GET['account_status'] ?? ''),
    'sort' => sanitize_key($_GET['account_sort'] ?? 'name'),
];
$accounts = NLS1_Aurora_Account_Platform::get_accounts(($section ?? '') === 'accounts' ? $account_filters : []);
$branding = NLS1_Aurora_Account_Platform::platform_branding();
$aurora_products = NLS1_Aurora_Account_Platform::installed_aurora_products();
?>
<div class="wrap nls1-fotoportal-admin aurora-platform-admin" style="--aurora-accent:<?php echo esc_attr($branding['accent']); ?>">
    <?php
    // Product administration must not inherit another product's header/navigation.
    $fotoportal_sections = ['fotoportal','accounts','modules','branding','system'];
    $is_fotoportal_admin = in_array($section, $fotoportal_sections, true);
    $is_platform_dashboard = ($section === 'dashboard');
    ?>

    <?php if ($is_platform_dashboard) : ?>
    <header class="aurora-platform-header aurora-control-center-header">
        <div>
            <span class="aurora-kicker">9LS1 DIGITAL / AURORA PLATFORM</span>
            <h1>Aurora Control Center</h1>
            <p>Samlet administrasjon av installerte Aurora-produkter og felles plattformtjenester.</p>
        </div>
        <div class="aurora-platform-identity">
            <?php if ($branding['logo_url']) : ?><img src="<?php echo esc_url($branding['logo_url']); ?>" alt=""><?php else : ?><span class="aurora-platform-mark">A</span><?php endif; ?>
            <div><strong><?php echo esc_html($branding['company_name']); ?></strong><small>Platform owner</small></div>
        </div>
    </header>
    <?php elseif ($is_fotoportal_admin) : ?>
    <header class="aurora-platform-header aurora-fotoportal-admin-header">
        <div>
            <span class="aurora-kicker">9LS1 DIGITAL / AURORA FOTOPORTAL</span>
            <h1>Aurora Fotoportal Admin</h1>
            <p>Administrer Fotoportal-produktet, fotografkontoer og produktinnstillinger.</p>
        </div>
        <div class="aurora-platform-identity">
            <?php if ($branding['logo_url']) : ?><img src="<?php echo esc_url($branding['logo_url']); ?>" alt=""><?php else : ?><span class="aurora-platform-mark">A</span><?php endif; ?>
            <div><strong><?php echo esc_html($branding['company_name']); ?></strong><small>Platform owner</small></div>
        </div>
    </header>

    <nav class="aurora-platform-nav aurora-fotoportal-admin-nav">
        <a class="<?php echo $section==='fotoportal'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('fotoportal')); ?>">Dashboard</a>
        <a class="<?php echo $section==='accounts'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>">Fotografkontoer</a>
        <a class="<?php echo $section==='modules'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('modules')); ?>">Moduler</a>
        <a class="<?php echo $section==='branding'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('branding')); ?>">Branding</a>
        <a class="<?php echo $section==='system'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('system')); ?>">System</a>
        <a class="aurora-nav-back" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url()); ?>">← Aurora Control Center</a>
    </nav>
    <?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message']==='account_created_mail_sent') : ?><div class="notice notice-success"><p>Fotografkonto og demo er opprettet. Velkomstmail med sikker lenke for å sette passord er sendt.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='account_created_mail_failed') : ?><div class="notice notice-error"><p>Fotografkonto og demo er opprettet, men invitasjonen kunne ikke sendes. <?php echo !empty($_GET['mail_error']) ? '<strong>'.esc_html(rawurldecode(wp_unslash($_GET['mail_error']))).'</strong>' : 'Kontroller WordPress sitt e-postoppsett.'; ?></p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='invitation_resent') : ?><div class="notice notice-success"><p>En ny invitasjon med fersk «sett passord»-lenke er sendt.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='invitation_failed') : ?><div class="notice notice-error"><p>Invitasjonen kunne ikke sendes. <?php echo !empty($_GET['mail_error']) ? '<strong>'.esc_html(rawurldecode(wp_unslash($_GET['mail_error']))).'</strong>' : 'Kontroller WordPress sitt e-postoppsett.'; ?></p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='account_saved') : ?><div class="notice notice-success"><p>Fotografkontoen er oppdatert.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='account_email_in_use') : ?><div class="notice notice-error"><p>Konto-/login-e-posten er allerede knyttet til en annen WordPress-bruker. Ingen kontoopplysninger ble endret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='account_missing') : ?><div class="notice notice-error"><p>Fotograf-/studionavn og konto-/login-e-post må fylles ut.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='modules_saved') : ?><div class="notice notice-success"><p>Modultilgang er lagret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='branding_saved') : ?><div class="notice notice-success"><p>Aurora-branding er lagret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='license_saved') : ?><div class="notice notice-success"><p>Lisensinnstillinger er lagret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='trial_extended') : ?><div class="notice notice-success"><p>Demoperioden er forlenget.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='trial_expired') : ?><div class="notice notice-warning"><p>Demoperioden er markert som utløpt.</p></div><?php endif; ?>

    <?php if ($section === 'dashboard') : ?>
        <section class="aurora-platform-stats">
            <a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>"><span>Fotografkontoer</span><strong><?php echo esc_html(NLS1_Aurora_Account_Platform::count_accounts()); ?></strong><small><?php echo esc_html(NLS1_Aurora_Account_Platform::count_accounts('active')); ?> aktive</small></a>
            <a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('licenses')); ?>"><span>Aktive lisenser</span><strong><?php echo esc_html(NLS1_Aurora_Account_Platform::count_accounts('active')); ?></strong><small>Foundation</small></a>
            <a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('modules')); ?>"><span>Aktiverte moduler</span><strong><?php echo esc_html(NLS1_Aurora_Account_Platform::count_enabled_modules()); ?></strong><small>På tvers av kontoer</small></a>
            <a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('system')); ?>"><span>Plattformstatus</span><strong class="is-green">OK</strong><small>Account schema <?php echo esc_html(NLS1_Aurora_Account_Platform::SCHEMA_VERSION); ?></small></a>
        </section>

        <section class="aurora-platform-card aurora-product-center">
            <div class="aurora-card-head">
                <div><span class="aurora-kicker">AURORA PRODUKTER</span><h2>Plattform og installerte plugins</h2><p>Alle Aurora-produkter på denne WordPress-installasjonen samlet på ett sted.</p></div>
            </div>
            <div class="aurora-product-grid">
                <?php foreach ($aurora_products as $product) :
                    $product_url = !empty($product['admin_url']) ? $product['admin_url'] : admin_url('plugins.php');
                    $status_label = !empty($product['active']) ? 'Aktiv' : 'Installert';
                ?>
                <article class="aurora-product-card">
                    <div class="aurora-product-icon"><?php echo esc_html(strtoupper(substr($product['name'],0,1))); ?></div>
                    <div class="aurora-product-body">
                        <div class="aurora-product-title"><h3><?php echo esc_html($product['name']); ?></h3><span class="aurora-product-state <?php echo !empty($product['active'])?'is-active':'is-installed'; ?>"><?php echo esc_html($status_label); ?></span></div>
                        <p><?php echo esc_html($product['description'] ?: 'Aurora-komponent'); ?></p>
                        <div class="aurora-product-meta">
                            <span>Versjon <strong><?php echo esc_html($product['version'] ?: '—'); ?></strong></span>
                            <?php if (!empty($product['license_status'])) : ?><span>Lisens <strong><?php echo esc_html($product['license_status']); ?></strong></span><?php endif; ?>
                        </div>
                        <div class="aurora-product-actions">
                            <a class="button aurora-product-open <?php echo !empty($product['active'])?'button-primary':''; ?>" href="<?php echo esc_url($product_url); ?>"><?php echo !empty($product['active'])?'Åpne':'Administrer'; ?> →</a>
                            <?php if (!empty($product['quick_links'])) : ?>
                            <div class="aurora-product-open-menu">
                                <button type="button" class="button aurora-product-menu-trigger" aria-label="Snarveier for <?php echo esc_attr($product['name']); ?>">⌄</button>
                                <div class="aurora-product-dropdown" role="menu">
                                    <?php foreach ($product['quick_links'] as $quick_link) : ?>
                                        <a role="menuitem" href="<?php echo esc_url($quick_link['url']); ?>"><?php echo esc_html($quick_link['label']); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="aurora-platform-note aurora-platform-control-note">
            <strong>Aurora Control Center:</strong> Velg et Aurora-produkt over for å åpne produktets administrasjon. Fotografens kunder, prosjekter og bilder ligger ikke på plattformdashboardet.
        </div>




    <?php elseif ($section === 'fotoportal') : ?>
        <section class="aurora-platform-card aurora-product-admin-hero">
            <div class="aurora-card-head">
                <div>
                    <span class="aurora-kicker">AURORA PRODUKTADMIN</span>
                    <h2>Aurora Fotoportal</h2>
                    <p>Administrer Fotoportal-produktet og fotografkontoene. Åpne en konkret fotografkonto først når du skal inn i fotografens Workspace.</p>
                </div>
                <a class="button button-primary" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>">Administrer fotografkontoer</a>
            </div>
        </section>

        <div class="aurora-platform-grid">
            <section class="aurora-platform-card aurora-platform-card-wide">
                <div class="aurora-card-head">
                    <div><span class="aurora-kicker">FOTOGRAFKONTOER</span><h2>Fotografer / studioer</h2></div>
                    <a class="button" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>">Alle kontoer</a>
                </div>
                <table class="widefat striped aurora-account-table">
                    <thead><tr><th>Konto</th><th>Kontakt</th><th>Plan</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($accounts as $row) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($row->account_name); ?></strong><small><?php echo esc_html($row->account_slug); ?></small></td>
                            <td><?php echo esc_html($row->contact_name ?: '—'); ?><small><?php echo esc_html($row->contact_email ?: ''); ?></small></td>
                            <td><?php echo esc_html($row->plan_name); ?></td>
                            <td><span class="aurora-license-status is-<?php echo esc_attr($row->status); ?>"><?php echo esc_html(ucfirst($row->status)); ?></span></td>
                            <td><a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$row->id])); ?>">Administrer →</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="aurora-platform-card">
                <span class="aurora-kicker">FOTOPORTAL STATUS</span>
                <h2>Produktgrunnlag</h2>
                <p>Fotoportal bruker Aurora-kontoer og tenant-skille. Kunde-, prosjekt- og bildedata administreres først inne i valgt fotografkonto.</p>
                <div class="aurora-foundation-state">
                    <span class="is-done">✓ Fotografkontoer</span>
                    <span class="is-done">✓ Tenant-skille</span>
                    <span class="is-done">✓ Kundeportal</span>
                    <span class="is-done">✓ Gallerier og bildevalg</span>
                </div>
            </section>
        </div>

        <section class="aurora-platform-card">
            <span class="aurora-kicker">ARBEIDSFLYT</span>
            <h2>Fra produktadmin til fotograf</h2>
            <p><strong>Aurora Control Center</strong> → <strong>Aurora Fotoportal Admin</strong> → <strong>Fotografkonto</strong> → <strong>Photographer Workspace</strong></p>
            <p>Dette skillet hindrer at plattformeierens kontrollsenter blandes med den enkelte fotografens operative kundedata.</p>
        </section>

    <?php elseif ($section === 'accounts') : ?>
        <section class="aurora-platform-card aurora-account-registry-head">
            <div class="aurora-card-head">
                <div>
                    <span class="aurora-kicker">FOTOGRAFKONTOER / KUNDEKARTOTEK</span>
                    <h2>Fotografer og studioer</h2>
                    <p>Plattformeiers register over Aurora Fotoportal-kunder. Her administreres firma, kontakt, fakturainformasjon, status og tilgang.</p>
                </div>
                <span class="aurora-account-count"><?php echo esc_html(NLS1_Aurora_Account_Platform::count_accounts()); ?> kontoer</span>
            </div>
            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="aurora-account-toolbar">
                <input type="hidden" name="page" value="<?php echo esc_attr(NLS1_Aurora_Account_Platform::MENU_SLUG . '-accounts'); ?>">
                <label class="aurora-account-search"><span>Søk</span><input type="search" name="account_search" value="<?php echo esc_attr($account_filters['search']); ?>" placeholder="Studio, kontakt, e-post, telefon eller org.nr."></label>
                <label><span>Status</span><select name="account_status">
                    <option value="">Alle statuser</option>
                    <?php foreach (['trial'=>'Trial','active'=>'Aktiv','expired'=>'Utløpt','suspended'=>'Suspendert','cancelled'=>'Avsluttet','invalid'=>'Ugyldig'] as $key=>$label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($account_filters['status'],$key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label><span>Sorter</span><select name="account_sort">
                    <?php foreach (['name'=>'Navn A–Å','name_desc'=>'Navn Å–A','newest'=>'Nyeste først','oldest'=>'Eldste først','updated'=>'Sist oppdatert','last_active'=>'Sist aktiv','status'=>'Status'] as $key=>$label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($account_filters['sort'],$key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select></label>
                <button class="button button-primary">Filtrer</button>
                <?php if ($account_filters['search'] || $account_filters['status'] || $account_filters['sort'] !== 'name') : ?><a class="button" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>">Nullstill</a><?php endif; ?>
            </form>
        </section>

        <div class="aurora-platform-grid aurora-account-management-grid">
            <section class="aurora-platform-card">
                <span class="aurora-kicker">NY KONTO</span><h2>Registrer fotograf / studio</h2>
                <p>Opprett grunnprofilen. Resten kan kompletteres på kundekortet etterpå.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-platform-form">
                    <input type="hidden" name="action" value="aurora_create_photographer_account">
                    <?php wp_nonce_field('aurora_create_photographer_account'); ?>
                    <label>Fotograf / studionavn *<input type="text" name="account_name" required placeholder="f.eks. Nordlys Foto"></label>
                    <label>Organisasjonsnummer<input type="text" name="organization_number" placeholder="123 456 789"></label>
                    <label>Kontaktperson<input type="text" name="contact_name" placeholder="Fornavn Etternavn"></label>
                    <label>Konto-/login-e-post *<input type="email" name="contact_email" required placeholder="foto@eksempel.no"></label>
                    <label>Telefon<input type="text" name="contact_phone" placeholder="+47 999 99 999"></label>
                    <label>Faktura-e-post<input type="email" name="billing_email" placeholder="faktura@eksempel.no"></label>
                    <div class="aurora-trial-create-note"><strong>Demo aktiveres automatisk</strong><span><?php echo esc_html(NLS1_Aurora_Account_Platform::trial_days()); ?> dager fra opprettelse. Kan forlenges av Aurora Admin.</span></div>
                    <p><button class="button button-primary">Opprett fotografkonto + demo</button></p>
                </form>
            </section>

            <section class="aurora-platform-card aurora-platform-card-wide">
                <div class="aurora-card-head"><div><span class="aurora-kicker">KUNDEKARTOTEK</span><h2>Resultater</h2><p><?php echo esc_html(count($accounts)); ?> konto(er) i gjeldende visning.</p></div></div>
                <?php if ($accounts) : ?>
                <div class="aurora-account-registry-table-wrap">
                    <table class="widefat striped aurora-account-registry-table">
                        <thead><tr><th>Studio</th><th>Kontakt</th><th>Org.nr.</th><th>Plan</th><th>Status</th><th>Sist aktiv</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($accounts as $row) : ?>
                            <tr class="<?php echo $account && (int)$account->id===(int)$row->id?'is-selected':''; ?>">
                                <td><strong><?php echo esc_html($row->account_name); ?></strong><small><?php echo esc_html($row->billing_city ?: $row->account_slug); ?></small></td>
                                <td><?php echo esc_html($row->contact_name ?: '—'); ?><small><?php echo esc_html($row->contact_email ?: ''); ?><?php echo $row->contact_phone ? ' · '.esc_html($row->contact_phone) : ''; ?></small></td>
                                <td><?php echo esc_html($row->organization_number ?: '—'); ?></td>
                                <td><?php echo esc_html($row->plan_name ?: '—'); ?></td>
                                <td><span class="aurora-license-status is-<?php echo esc_attr($row->status); ?>"><?php echo esc_html($row->status); ?></span></td>
                                <td><?php echo esc_html($row->last_active_at ? wp_date(get_option('date_format').' H:i', strtotime($row->last_active_at)) : 'Ikke registrert'); ?></td>
                                <td><a class="button button-small" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$row->id])); ?>">Åpne</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?><div class="aurora-empty-state"><strong>Ingen kontoer funnet</strong><p>Prøv et annet søk eller nullstill filtrene.</p></div><?php endif; ?>
            </section>
        </div>

        <?php if ($account) :
            $account_modules = NLS1_Aurora_Account_Platform::get_account_modules($account->id);
            $license = NLS1_Aurora_Account_Platform::get_license($account->id);
            $trial_state = NLS1_Aurora_Account_Platform::trial_state($account);
            $trial_days_left = NLS1_Aurora_Account_Platform::trial_days_left($account);
        ?>
        <section class="aurora-platform-card aurora-account-detail aurora-customer-card">
            <div class="aurora-card-head">
                <div><span class="aurora-kicker">FOTOGRAFKONTO / KUNDEKORT</span><h2><?php echo esc_html($account->account_name); ?></h2><p><?php echo esc_html($account->contact_name ?: 'Ingen kontaktperson'); ?> · <?php echo esc_html($account->contact_email); ?></p></div>
                <span class="aurora-license-status is-<?php echo esc_attr($trial_state); ?>"><?php echo esc_html($trial_state); ?></span>
            </div>
            <div class="aurora-account-detail-grid aurora-account-summary-grid">
                <div><span>Plan</span><strong><?php echo esc_html($account->plan_name); ?></strong></div>
                <div><span>Lisens</span><strong><?php echo esc_html($license ? $license->license_name : 'Ikke satt'); ?></strong></div>
                <div><span>Opprettet</span><strong><?php echo esc_html($account->created_at ? wp_date(get_option('date_format'), strtotime($account->created_at)) : '—'); ?></strong></div>
                <div><span>Sist aktiv</span><strong><?php echo esc_html($account->last_active_at ? wp_date(get_option('date_format').' H:i', strtotime($account->last_active_at)) : 'Ikke registrert'); ?></strong></div>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-account-profile-form">
                <input type="hidden" name="action" value="aurora_save_photographer_account">
                <input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>">
                <?php wp_nonce_field('aurora_save_photographer_account'); ?>

                <div class="aurora-account-form-section">
                    <div class="aurora-account-form-section-head"><span class="aurora-kicker">FIRMA</span><h3>Studio og firmaopplysninger</h3></div>
                    <div class="aurora-account-form-grid">
                        <label class="is-wide">Fotograf / studionavn *<input type="text" name="account_name" value="<?php echo esc_attr($account->account_name); ?>" required></label>
                        <label>Organisasjonsnummer<input type="text" name="organization_number" value="<?php echo esc_attr($account->organization_number); ?>"></label>
                        <label>Nettside<input type="url" name="website_url" value="<?php echo esc_attr($account->website_url); ?>" placeholder="https://"></label>
                        <label>Status<select name="status"><?php foreach (['trial'=>'Trial','active'=>'Aktiv','expired'=>'Utløpt','suspended'=>'Suspendert','cancelled'=>'Avsluttet','invalid'=>'Ugyldig'] as $key=>$label) : ?><option value="<?php echo esc_attr($key); ?>" <?php selected($account->status,$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label>
                    </div>
                </div>

                <div class="aurora-account-form-section">
                    <div class="aurora-account-form-section-head"><span class="aurora-kicker">KONTAKT</span><h3>Kontakt og innlogging</h3><p>Konto-/login-e-post styres av Aurora Admin. Fotografen kan ikke endre denne selv.</p></div>
                    <div class="aurora-account-form-grid">
                        <label>Kontaktperson<input type="text" name="contact_name" value="<?php echo esc_attr($account->contact_name); ?>"></label>
                        <label>Telefon<input type="text" name="contact_phone" value="<?php echo esc_attr($account->contact_phone); ?>"></label>
                        <label class="is-wide">Konto-/login-e-post *<input type="email" name="contact_email" value="<?php echo esc_attr($account->contact_email); ?>" required></label>
                    </div>
                </div>

                <div class="aurora-account-form-section">
                    <div class="aurora-account-form-section-head"><span class="aurora-kicker">FAKTURA</span><h3>Fakturainformasjon</h3></div>
                    <div class="aurora-account-form-grid">
                        <label class="is-wide">Fakturanavn / juridisk navn<input type="text" name="billing_name" value="<?php echo esc_attr($account->billing_name); ?>"></label>
                        <label class="is-wide">Adresse<input type="text" name="billing_address" value="<?php echo esc_attr($account->billing_address); ?>"></label>
                        <label>Postnummer<input type="text" name="billing_postcode" value="<?php echo esc_attr($account->billing_postcode); ?>"></label>
                        <label>Sted<input type="text" name="billing_city" value="<?php echo esc_attr($account->billing_city); ?>"></label>
                        <label>Land<input type="text" name="billing_country" value="<?php echo esc_attr($account->billing_country ?: 'Norge'); ?>"></label>
                        <label>Faktura-e-post<input type="email" name="billing_email" value="<?php echo esc_attr($account->billing_email); ?>"></label>
                    </div>
                </div>

                <div class="aurora-account-form-section">
                    <div class="aurora-account-form-section-head"><span class="aurora-kicker">INTERNT</span><h3>Adminnotater</h3><p>Kun synlig for Aurora Admin.</p></div>
                    <label class="aurora-admin-notes"><textarea name="internal_notes" rows="5" placeholder="Interne notater om kunden, avtaler, oppfølging osv."><?php echo esc_textarea($account->internal_notes); ?></textarea></label>
                </div>
                <div class="aurora-account-savebar"><span>Endringer her gjelder selve fotograf-/studiokontoen.</span><button class="button button-primary">Lagre kundekort</button></div>
            </form>

            <div class="aurora-trial-panel">
                <div><span class="aurora-kicker">DEMO / ONBOARDING</span><h3><?php echo $trial_state === 'trial' ? 'Aktiv demoperiode' : ($trial_state === 'expired' ? 'Demo utløpt' : 'Kontostatus'); ?></h3>
                    <?php if ($account->plan_name === 'Trial' || in_array($trial_state, ['trial','expired'], true)) : ?><p>Demo utløper <strong><?php echo esc_html(NLS1_Aurora_Account_Platform::trial_end_label($account)); ?></strong><?php if ($trial_days_left !== null && $trial_state === 'trial') : ?> · <?php echo esc_html($trial_days_left); ?> dager igjen<?php endif; ?>.</p><?php else : ?><p>Kontoen er ikke i demo.</p><?php endif; ?>
                </div>
                <div class="aurora-trial-actions"><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="aurora_extend_trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>"><?php wp_nonce_field('aurora_extend_trial'); ?><select name="days"><option value="7">+7 dager</option><option value="14">+14 dager</option><option value="30">+30 dager</option></select><button class="button button-primary">Forleng demo</button></form><?php if ($trial_state === 'trial') : ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Vil du markere demoen som utløpt nå?');"><input type="hidden" name="action" value="aurora_expire_trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>"><?php wp_nonce_field('aurora_expire_trial'); ?><button class="button">Avslutt demo nå</button></form><?php endif; ?></div>
            </div>

            <?php $owner_user = !empty($account->owner_user_id) ? get_user_by('id', (int)$account->owner_user_id) : false; $invite_sent_at = $owner_user ? get_user_meta($owner_user->ID, 'aurora_fotoportal_invitation_sent_at', true) : ''; ?>
            <div class="aurora-invitation-panel"><div><span class="aurora-kicker">INNLOGGING / INVITASJON</span><h3>Fotografens tilgang</h3><p><?php if ($owner_user) : ?>Bruker <strong><?php echo esc_html($owner_user->user_email); ?></strong> er koblet til kontoen. <?php if ($invite_sent_at) : ?>Sist sendt <?php echo esc_html(wp_date(get_option('date_format').' H:i', strtotime($invite_sent_at))); ?>.<?php endif; ?><?php else : ?>Ingen fotografbruker er koblet til kontoen ennå.<?php endif; ?></p></div><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="aurora_resend_photographer_invitation"><input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>"><?php wp_nonce_field('aurora_resend_photographer_invitation'); ?><button class="button button-primary"><?php echo $invite_sent_at ? 'Send invitasjon på nytt' : 'Send invitasjon'; ?></button></form></div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-account-modules-form">
                <input type="hidden" name="action" value="aurora_save_account_modules"><input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>"><?php wp_nonce_field('aurora_save_account_modules'); ?>
                <div class="aurora-module-section-head"><div><span class="aurora-kicker">AURORA FOTOPORTAL</span><h3>Inkludert i Fotoportal</h3><p>Kjernefunksjoner følger alltid Fotoportal.</p></div><span class="aurora-included-badge">Inkludert</span></div>
                <div class="aurora-module-toggle-grid is-core"><?php foreach (NLS1_Aurora_Account_Platform::core_modules() as $key => $meta) : ?><div class="aurora-core-module"><span class="aurora-core-check">✓</span><span><strong><?php echo esc_html($meta[0]); ?></strong><small><?php echo esc_html($meta[1]); ?></small></span></div><?php endforeach; ?></div>
                <div class="aurora-module-section-head is-addons"><div><span class="aurora-kicker">TILLEGGSFUNKSJONER</span><h3>Tilleggsmoduler</h3></div><?php if ($trial_state === 'trial') : ?><span class="aurora-trial-badge">Trial</span><?php endif; ?></div>
                <div class="aurora-module-toggle-grid"><?php foreach (NLS1_Aurora_Account_Platform::addon_modules() as $key => $meta) : ?><label class="<?php echo empty($meta[3]) ? 'is-future-addon' : ''; ?>"><input type="checkbox" name="modules[]" value="<?php echo esc_attr($key); ?>" <?php checked(!empty($account_modules[$key])); ?>><span><strong><?php echo esc_html($meta[0]); ?></strong><small><?php echo esc_html($meta[1]); ?></small><?php if (empty($meta[3])) : ?><em>Ikke i standard Trial</em><?php endif; ?></span></label><?php endforeach; ?></div>
                <p><button class="button button-primary">Lagre tilleggsmoduler</button></p>
            </form>
        </section>
        <?php endif; ?>

    <?php elseif ($section === 'licenses') : ?>
        <div class="aurora-product-admin-topbar">
            <a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url()); ?>">← Aurora Control Center</a>
            <span>Aurora License</span>
        </div>
        <?php if (function_exists('aurora_license_render_platform_page')) : ?>
            <?php aurora_license_render_platform_page(); ?>
        <?php else : ?>
            <section class="aurora-platform-card">
                <span class="aurora-kicker">AURORA LICENSE</span>
                <h2>Lisensmotor er ikke aktiv</h2>
                <p>Aurora License er den sentrale lisensmotoren for alle Aurora-produkter. Installer og aktiver Aurora License for å administrere modulspesifikke lisensnøkler her.</p>
                <p><a class="button" href="<?php echo esc_url(admin_url('plugins.php')); ?>">Åpne plugins</a></p>
            </section>
        <?php endif; ?>

    <?php elseif ($section === 'modules') : ?>
        <section class="aurora-platform-card">
            <span class="aurora-kicker">MODULKATALOG</span><h2>Aurora Fotoportal-moduler</h2>
            <p>Fotoportal består av faste kjernefunksjoner og lisens-/pakke-styrte tilleggsmoduler.</p>

            <div class="aurora-module-section-head">
                <div><span class="aurora-kicker">KJERNE</span><h3>Inkludert i Aurora Fotoportal</h3><p>Disse er alltid tilgjengelige når Fotoportal er aktiv.</p></div>
                <span class="aurora-included-badge">Alltid inkludert</span>
            </div>
            <div class="aurora-module-catalog">
                <?php foreach (NLS1_Aurora_Account_Platform::core_modules() as $key => $meta) : ?>
                    <div><span class="aurora-module-code">✓</span><div><strong><?php echo esc_html($meta[0]); ?></strong><p><?php echo esc_html($meta[1]); ?></p><code><?php echo esc_html($key); ?></code></div></div>
                <?php endforeach; ?>
            </div>

            <div class="aurora-module-section-head is-addons">
                <div><span class="aurora-kicker">TILLEGG</span><h3>Tilleggsmoduler</h3><p>Disse kan styres av Trial og senere av Aurora License/pakke.</p></div>
            </div>
            <div class="aurora-module-catalog">
                <?php foreach (NLS1_Aurora_Account_Platform::addon_modules() as $key => $meta) : ?>
                    <div><span class="aurora-module-code"><?php echo esc_html(strtoupper(substr($key,0,2))); ?></span><div><strong><?php echo esc_html($meta[0]); ?></strong><p><?php echo esc_html($meta[1]); ?></p><code><?php echo esc_html($key); ?></code><?php if (!empty($meta[3])) : ?><span class="aurora-catalog-trial">Standard Trial</span><?php else : ?><span class="aurora-catalog-future">Ikke i standard Trial</span><?php endif; ?></div></div>
                <?php endforeach; ?>
            </div>
        </section>

    <?php elseif ($section === 'branding') : ?>
        <section class="aurora-platform-card">
            <span class="aurora-kicker">PLATTFORMBRANDING</span><h2>Aurora Admin</h2>
            <p>Dette er din plattformbranding. Fotografens egen logo/farger blir senere lagret på fotografkontoen.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-platform-form aurora-branding-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="aurora_save_platform_branding">
                <?php wp_nonce_field('aurora_save_platform_branding'); ?>
                <label>Plattformnavn<input type="text" name="platform_name" value="<?php echo esc_attr($branding['platform_name']); ?>"></label>
                <label>Selskap / leverandør<input type="text" name="company_name" value="<?php echo esc_attr($branding['company_name']); ?>"></label>
                <label>Support e-post<input type="email" name="support_email" value="<?php echo esc_attr($branding['support_email']); ?>"></label>
                <label>Logo URL<input type="url" name="logo_url" value="<?php echo esc_attr($branding['logo_url']); ?>" placeholder="https://..."></label>
                <label>Accent-farge<input type="color" name="accent" value="<?php echo esc_attr($branding['accent']); ?>"></label>
                <label>Testbilde for vannmerke<input type="file" name="watermark_preview_image" accept="image/*"><small>Brukes i fotografens vannmerke-preview og på Dashboard.</small></label>
                <?php if(!empty($branding['watermark_preview_url'])):?><div class="aurora-branding-preview"><img src="<?php echo esc_url($branding['watermark_preview_url']); ?>" alt="Testbilde" style="max-width:420px;width:100%;height:auto;border-radius:12px"></div><?php endif;?>

                <div class="aurora-branding-login-backgrounds">
                    <div class="aurora-module-section-head is-addons">
                        <div><span class="aurora-kicker">FOTOGRAFINNLOGGING</span><h3>Bakgrunnsbilder</h3><p>Bruk egne høyoppløselige bilder for desktop og mobil. Aurora fyller alltid hele skjermen med bildet.</p></div>
                    </div>
                    <div class="aurora-platform-grid">
                        <div>
                            <label>Desktop / PC<input type="file" name="photographer_login_bg_desktop" accept="image/jpeg,image/png,image/webp"><small>Anbefalt minst 1920×1080. Bildet beskjæres med <code>cover</code> slik at hele skjermen fylles.</small></label>
                            <?php if(!empty($branding['photographer_login_bg_desktop'])):?><div class="aurora-login-bg-preview is-desktop"><img src="<?php echo esc_url($branding['photographer_login_bg_desktop']); ?>" alt="Desktop login-bakgrunn"></div><?php endif;?>
                        </div>
                        <div>
                            <label>Mobil<input type="file" name="photographer_login_bg_mobile" accept="image/jpeg,image/png,image/webp"><small>Anbefalt stående bilde, minst 1080×1920. Hvis tomt brukes desktop-bildet automatisk.</small></label>
                            <?php if(!empty($branding['photographer_login_bg_mobile'])):?><div class="aurora-login-bg-preview is-mobile"><img src="<?php echo esc_url($branding['photographer_login_bg_mobile']); ?>" alt="Mobil login-bakgrunn"></div><label class="aurora-inline-check"><input type="checkbox" name="remove_mobile_login_bg" value="1"> Fjern eget mobilbilde og bruk desktop-bildet</label><?php endif;?>
                        </div>
                    </div>
                </div>

                <div class="aurora-branding-login-backgrounds">
                    <div class="aurora-module-section-head is-addons">
                        <div><span class="aurora-kicker">FOTOKUNDEINNLOGGING</span><h3>Bakgrunnsbilder</h3><p>Disse bildene brukes på den sikre innloggingen for fotografens kunder. Velg gjerne et motiv som illustrerer album, print eller ferdige fotografier.</p></div>
                    </div>
                    <div class="aurora-platform-grid">
                        <div>
                            <label>Desktop / PC<input type="file" name="customer_login_bg_desktop" accept="image/jpeg,image/png,image/webp"><small>Anbefalt minst 1920×1080. Hvis feltet er tomt brukes fotograf-loginens desktop-bakgrunn som fallback.</small></label>
                            <?php if(!empty($branding['customer_login_bg_desktop'])):?><div class="aurora-login-bg-preview is-desktop"><img src="<?php echo esc_url($branding['customer_login_bg_desktop']); ?>" alt="Fotokunde desktop login-bakgrunn"></div><label class="aurora-inline-check"><input type="checkbox" name="remove_customer_login_bg_desktop" value="1"> Fjern eget kundebilde og bruk fallback</label><?php endif;?>
                        </div>
                        <div>
                            <label>Mobil<input type="file" name="customer_login_bg_mobile" accept="image/jpeg,image/png,image/webp"><small>Anbefalt stående bilde, minst 1080×1920. Hvis tomt brukes kundens desktop-bilde, deretter fotograf-loginens bakgrunn.</small></label>
                            <?php if(!empty($branding['customer_login_bg_mobile'])):?><div class="aurora-login-bg-preview is-mobile"><img src="<?php echo esc_url($branding['customer_login_bg_mobile']); ?>" alt="Fotokunde mobil login-bakgrunn"></div><label class="aurora-inline-check"><input type="checkbox" name="remove_customer_login_bg_mobile" value="1"> Fjern eget mobilbilde og bruk fallback</label><?php endif;?>
                        </div>
                    </div>
                </div>
                <p><button class="button button-primary">Lagre Aurora-branding</button></p>
            </form>
        </section>

    <?php elseif ($section === 'system') : ?>
        <div class="aurora-platform-grid">
            <section class="aurora-platform-card">
                <span class="aurora-kicker">SYSTEM</span><h2>Account Platform</h2>
                <dl class="aurora-system-list">
                    <div><dt>Plugin</dt><dd>Aurora Fotoportal <?php echo esc_html(NLS1_FOTOPORTAL_VERSION); ?></dd></div>
                    <div><dt>Account schema</dt><dd><?php echo esc_html(NLS1_Aurora_Account_Platform::SCHEMA_VERSION); ?></dd></div>
                    <div><dt>Fotografkontoer</dt><dd><?php echo esc_html(NLS1_Aurora_Account_Platform::count_accounts()); ?></dd></div>
                    <div><dt>Tenant-dataisolasjon</dt><dd><span class="aurora-license-status is-trial">Neste fase</span></dd></div>
                </dl>
            </section>
            <section class="aurora-platform-card">
                <span class="aurora-kicker">SUPPORTMODUS</span><h2>Fotografvisning</h2>
                <p>Normal Aurora Admin viser ikke sluttkundedata. Åpne fotografens egen Aurora Workspace for testing og administrasjon.</p>
                <p><a class="button" href="<?php echo esc_url(NLS1_Photographer_Workspace::url('dashboard')); ?>">Åpne fotografvisning</a></p>
            </section>
        </div>
    <?php endif; ?>
</div>
