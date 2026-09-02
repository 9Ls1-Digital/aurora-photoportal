<?php
if (!defined('ABSPATH')) exit;
$accounts = NLS1_Aurora_Account_Platform::get_accounts();
$branding = NLS1_Aurora_Account_Platform::platform_branding();
?>
<div class="wrap nls1-fotoportal-admin aurora-platform-admin" style="--aurora-accent:<?php echo esc_attr($branding['accent']); ?>">
    <header class="aurora-platform-header">
        <div>
            <span class="aurora-kicker">9LS1 DIGITAL / PLATFORM</span>
            <h1><?php echo esc_html($branding['platform_name']); ?> Admin</h1>
            <p>Administrer fotografer, lisenser, moduler og plattformbranding. Fotografenes kunder og bilder vises ikke i denne arbeidsflaten.</p>
        </div>
        <div class="aurora-platform-identity">
            <?php if ($branding['logo_url']) : ?><img src="<?php echo esc_url($branding['logo_url']); ?>" alt=""><?php else : ?><span class="aurora-platform-mark">A</span><?php endif; ?>
            <div><strong><?php echo esc_html($branding['company_name']); ?></strong><small>Platform owner</small></div>
        </div>
    </header>

    <nav class="aurora-platform-nav">
        <a class="<?php echo $section==='dashboard'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url()); ?>">Dashboard</a>
        <a class="<?php echo $section==='accounts'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>">Fotografkontoer</a>
        <a class="<?php echo $section==='licenses'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('licenses')); ?>">Lisenser</a>
        <a class="<?php echo $section==='modules'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('modules')); ?>">Moduler</a>
        <a class="<?php echo $section==='branding'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('branding')); ?>">Branding</a>
        <a class="<?php echo $section==='system'?'is-active':''; ?>" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('system')); ?>">System</a>
    </nav>

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

        <div class="aurora-platform-grid">
            <section class="aurora-platform-card aurora-platform-card-wide">
                <div class="aurora-card-head"><div><span class="aurora-kicker">FOTOGRAFKONTOER</span><h2>Fotografer / studioer</h2></div><a class="button button-primary" href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts')); ?>">Administrer</a></div>
                <table class="widefat striped aurora-account-table">
                    <thead><tr><th>Konto</th><th>Kontakt</th><th>Plan</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($accounts as $row) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($row->account_name); ?></strong><small><?php echo esc_html($row->account_slug); ?></small></td>
                            <td><?php echo esc_html($row->contact_name ?: '—'); ?><small><?php echo esc_html($row->contact_email ?: ''); ?></small></td>
                            <td><?php echo esc_html($row->plan_name); ?></td>
                            <td><span class="aurora-license-status is-<?php echo esc_attr($row->status); ?>"><?php echo esc_html(ucfirst($row->status)); ?></span></td>
                            <td><a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('accounts',['account_id'=>$row->id])); ?>">Åpne →</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="aurora-platform-card">
                <span class="aurora-kicker">DATASKILLE</span>
                <h2>Tenant foundation</h2>
                <p>Fotografkonto, modultilganger og lisens er nå egne Aurora-data. Eksisterende Fotoportal-kundedata er ennå ikke migrert til tenant-ID.</p>
                <div class="aurora-foundation-state"><span class="is-done">✓ Kontoer</span><span class="is-done">✓ Moduler</span><span class="is-done">✓ Lisenser</span><span>→ Tenant-ID på Fotoportal-data</span></div>
            </section>
        </div>

        <div class="aurora-platform-note">
            <strong>Administratorgrense:</strong> denne siden viser plattform-/fotografdata, ikke fotografens kunder, prosjekter eller bilder.
            <a href="<?php echo esc_url(NLS1_Photographer_Workspace::url('dashboard')); ?>">Åpne Photographer Workspace →</a>
        </div>

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
        <section class="aurora-platform-card">
            <span class="aurora-kicker">LISENSER</span><h2>Fotograflisenser</h2>
            <p>Velg en fotografkonto for å styre lisens, gyldighet, brukergrense og lagringskvote.</p>
            <div class="aurora-license-list">
            <?php foreach ($accounts as $row) :
                $lic = NLS1_Aurora_Account_Platform::get_license($row->id);
            ?>
                <a href="<?php echo esc_url(NLS1_Aurora_Account_Platform::url('licenses',['account_id'=>$row->id])); ?>" class="<?php echo $account && (int)$account->id===(int)$row->id?'is-selected':''; ?>">
                    <strong><?php echo esc_html($row->account_name); ?></strong>
                    <span><?php echo esc_html($lic ? $lic->license_name : 'Ingen lisens'); ?></span>
                    <span class="aurora-license-status is-<?php echo esc_attr($lic ? $lic->status : 'expired'); ?>"><?php echo esc_html($lic ? $lic->status : 'mangler'); ?></span>
                </a>
            <?php endforeach; ?>
            </div>
        </section>

        <?php if ($account) :
            $license = NLS1_Aurora_Account_Platform::get_license($account->id);
        ?>
        <section class="aurora-platform-card">
            <span class="aurora-kicker">REDIGER LISENS</span><h2><?php echo esc_html($account->account_name); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aurora-license-form">
                <input type="hidden" name="action" value="aurora_save_license">
                <input type="hidden" name="account_id" value="<?php echo esc_attr($account->id); ?>">
                <?php wp_nonce_field('aurora_save_license'); ?>
                <label>Lisensnavn<input type="text" name="license_name" value="<?php echo esc_attr($license ? $license->license_name : 'Aurora Fotoportal'); ?>"></label>
                <label>Lisensnøkkel<input type="text" name="license_key" value="<?php echo esc_attr($license ? $license->license_key : ''); ?>" placeholder="AUR-XXXX-XXXX"></label>
                <label>Status<select name="license_status">
                    <?php foreach (['active'=>'Aktiv','trial'=>'Prøve','expired'=>'Utløpt','suspended'=>'Suspendert'] as $k=>$label): ?>
                    <option value="<?php echo esc_attr($k); ?>" <?php selected($license ? $license->status : 'active',$k); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label>Gyldig fra<input type="date" name="valid_from" value="<?php echo esc_attr($license ? $license->valid_from : ''); ?>"></label>
                <label>Gyldig til<input type="date" name="valid_until" value="<?php echo esc_attr($license ? $license->valid_until : ''); ?>"></label>
                <label>Maks brukere<input type="number" min="1" name="max_users" value="<?php echo esc_attr($license ? $license->max_users : 1); ?>"></label>
                <label>Lagring (GB)<input type="number" min="1" name="storage_gb" value="<?php echo esc_attr($license ? $license->storage_gb : 10); ?>"></label>
                <p><button class="button button-primary">Lagre lisens</button></p>
            </form>
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
