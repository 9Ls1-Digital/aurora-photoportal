<?php
if (!defined('ABSPATH')) exit;
$accounts = NLS1_Aurora_Account_Platform::get_accounts();
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

    <?php if (isset($_GET['message']) && $_GET['message']==='account_created') : ?><div class="notice notice-success"><p>Fotografkonto er opprettet.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='account_missing') : ?><div class="notice notice-error"><p>Fotograf-/studionavn må fylles ut.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='modules_saved') : ?><div class="notice notice-success"><p>Modultilgang er lagret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='branding_saved') : ?><div class="notice notice-success"><p>Aurora-branding er lagret.</p></div><?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message']==='license_saved') : ?><div class="notice notice-success"><p>Lisensinnstillinger er lagret.</p></div><?php endif; ?>

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
        <div class="aurora-platform-grid">
            <section class="aurora-platform-card">
                <span class="aurora-kicker">NY KONTO</span><h2>Registrer fotograf / studio</h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-platform-form">
                    <input type="hidden" name="action" value="aurora_create_photographer_account">
                    <?php wp_nonce_field('aurora_create_photographer_account'); ?>
                    <label>Fotograf / studionavn *<input type="text" name="account_name" required placeholder="f.eks. Nordlys Foto"></label>
                    <label>Kontaktperson<input type="text" name="contact_name" placeholder="Fornavn Etternavn"></label>
                    <label>E-post<input type="email" name="contact_email" placeholder="foto@eksempel.no"></label>
                    <p><button class="button button-primary">Opprett fotografkonto</button></p>
                </form>
            </section>

            <section class="aurora-platform-card aurora-platform-card-wide">
                <span class="aurora-kicker">KONTOER</span><h2>Fotografkontoer</h2>
                <div class="aurora-account-list">
                <?php foreach ($accounts as $row) : ?>
                    <a class="<?php echo $account && (int)$account->id===(int)$row->id?'is-selected':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$row->id])); ?>">
                        <div><strong><?php echo esc_html($row->account_name); ?></strong><span><?php echo esc_html($row->contact_email ?: $row->account_slug); ?></span></div>
                        <span class="aurora-license-status is-<?php echo esc_attr($row->status); ?>"><?php echo esc_html($row->status); ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
            </section>
        </div>

        <?php if ($account) :
            $account_modules = NLS1_Aurora_Account_Platform::get_account_modules($account->id);
            $license = NLS1_Aurora_Account_Platform::get_license($account->id);
        ?>
        <section class="aurora-platform-card aurora-account-detail">
            <div class="aurora-card-head"><div><span class="aurora-kicker">FOTOGRAFKONTO</span><h2><?php echo esc_html($account->account_name); ?></h2><p><?php echo esc_html($account->contact_name); ?> · <?php echo esc_html($account->contact_email); ?></p></div><span class="aurora-license-status is-<?php echo esc_attr($account->status); ?>"><?php echo esc_html($account->status); ?></span></div>
            <div class="aurora-account-detail-grid">
                <div><span>Plan</span><strong><?php echo esc_html($account->plan_name); ?></strong></div>
                <div><span>Lisens</span><strong><?php echo esc_html($license ? $license->license_name : 'Ikke satt'); ?></strong></div>
                <div><span>Brukere</span><strong><?php echo esc_html($license ? $license->max_users : 1); ?> maks</strong></div>
                <div><span>Lagring</span><strong><?php echo esc_html($license ? $license->storage_gb : 0); ?> GB</strong></div>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="aurora_save_account_modules">
                <input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>">
                <?php wp_nonce_field('aurora_save_account_modules'); ?>
                <h3>Aktive moduler</h3>
                <div class="aurora-module-toggle-grid">
                    <?php foreach (NLS1_Aurora_Account_Platform::module_catalog() as $key => $meta) : ?>
                    <label>
                        <input type="checkbox" name="modules[]" value="<?php echo esc_attr($key); ?>" <?php checked(!empty($account_modules[$key])); ?>>
                        <span><strong><?php echo esc_html($meta[0]); ?></strong><small><?php echo esc_html($meta[1]); ?></small></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p><button class="button button-primary">Lagre modultilgang</button></p>
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
            <p>Dette er funksjonene som kan tildeles per fotografkonto. Kontotilgang endres under Fotografkontoer.</p>
            <div class="aurora-module-catalog">
                <?php foreach (NLS1_Aurora_Account_Platform::module_catalog() as $key => $meta) : ?>
                    <div><span class="aurora-module-code"><?php echo esc_html(strtoupper(substr($key,0,2))); ?></span><div><strong><?php echo esc_html($meta[0]); ?></strong><p><?php echo esc_html($meta[1]); ?></p><code><?php echo esc_html($key); ?></code></div></div>
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
