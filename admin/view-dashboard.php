<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap nls1-fotoportal-admin nls1-plugin-center">
    <h1>9Ls1 Plugins</h1>
    <p class="description">Samlet kontrollsenter for dine egne 9Ls1 WordPress-verktøy.</p>
    <div class="nls1-plugin-grid">
        <a class="nls1-plugin-card nls1-plugin-card-active" href="<?php echo esc_url(NLS1_Fotoportal_Admin::fotoportal_url()); ?>">
            <div class="nls1-plugin-icon">FP</div>
            <div>
                <h2>9Ls1 Fotoportal</h2>
                <p><?php echo esc_html(NLS1_Fotoportal_Admin::count_rows('clients', 'is_test = 0')); ?> kunder · <?php echo esc_html(NLS1_Fotoportal_Admin::count_rows('projects', 'is_test = 0')); ?> prosjekter</p>
                <span class="nls1-status-badge">Installert</span>
            </div>
        </a>
        <div class="nls1-plugin-card nls1-plugin-card-muted"><div class="nls1-plugin-icon">BI</div><div><h2>Brudepar Intervju</h2><p>Kan flyttes inn i Plugin Center senere.</p><span class="nls1-status-badge muted">Ikke koblet ennå</span></div></div>
        <div class="nls1-plugin-card nls1-plugin-card-muted"><div class="nls1-plugin-icon">PR</div><div><h2>Picture Resizer</h2><p>Kan kobles til Plugin Center senere.</p><span class="nls1-status-badge muted">Planlagt</span></div></div>
    </div>
</div>
