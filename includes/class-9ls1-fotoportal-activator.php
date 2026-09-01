<?php
if (!defined('ABSPATH')) exit;

class NLS1_Fotoportal_Activator {
    public static function activate() {
        if (class_exists('NLS1_Aurora_Account_Platform')) {
            NLS1_Aurora_Account_Platform::maybe_install();
        }
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        $clients    = $wpdb->prefix . '9ls1_fotoportal_clients';
        $contacts   = $wpdb->prefix . '9ls1_fotoportal_contacts';
        $projects   = $wpdb->prefix . '9ls1_fotoportal_projects';
        $contracts  = $wpdb->prefix . '9ls1_fotoportal_contracts';
        $signatures = $wpdb->prefix . '9ls1_fotoportal_signatures';
        $tokens     = $wpdb->prefix . '9ls1_fotoportal_access_tokens';
        $settings   = $wpdb->prefix . '9ls1_fotoportal_settings';
        $logs       = $wpdb->prefix . '9ls1_fotoportal_logs';
        $documents  = $wpdb->prefix . '9ls1_fotoportal_documents';
        $templates  = $wpdb->prefix . '9ls1_fotoportal_document_templates';
        $galleries  = $wpdb->prefix . '9ls1_fotoportal_galleries';
        $images     = $wpdb->prefix . '9ls1_fotoportal_images';
        $favorites  = $wpdb->prefix . '9ls1_fotoportal_favorites';
        $comments   = $wpdb->prefix . '9ls1_fotoportal_image_comments';
        $downloads  = $wpdb->prefix . '9ls1_fotoportal_downloads';

        dbDelta("CREATE TABLE $clients (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_number VARCHAR(50) DEFAULT '',
            client_name VARCHAR(190) NOT NULL,
            client_group VARCHAR(80) DEFAULT '',
            client_type VARCHAR(80) DEFAULT 'private',
            email VARCHAR(190) DEFAULT '',
            phone VARCHAR(50) DEFAULT '',
            address TEXT NULL,
            postal_code VARCHAR(20) DEFAULT '',
            city VARCHAR(100) DEFAULT '',
            profile_image_id BIGINT UNSIGNED NULL,
            notes LONGTEXT NULL,
            status VARCHAR(50) DEFAULT 'active',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY customer_number (customer_number),
            KEY client_group (client_group),
            KEY client_type (client_type),
            KEY email (email),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $contacts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id BIGINT UNSIGNED NOT NULL,
            first_name VARCHAR(150) NOT NULL,
            last_name VARCHAR(150) DEFAULT '',
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(50) DEFAULT '',
            contact_role VARCHAR(100) DEFAULT 'Hovedkontakt',
            is_primary TINYINT(1) DEFAULT 0,
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY email (email),
            KEY is_primary (is_primary),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $projects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id BIGINT UNSIGNED NOT NULL,
            project_number VARCHAR(50) DEFAULT '',
            project_name VARCHAR(190) NOT NULL,
            project_type VARCHAR(80) NOT NULL,
            project_date DATE NULL,
            location VARCHAR(190) DEFAULT '',
            description LONGTEXT NULL,
            status VARCHAR(80) DEFAULT 'created',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY project_number (project_number),
            KEY project_type (project_type),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $contracts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            contract_name VARCHAR(190) NOT NULL,
            contract_version VARCHAR(50) DEFAULT '1.0',
            contract_text LONGTEXT NOT NULL,
            signer_name VARCHAR(190) DEFAULT '',
            signer_email VARCHAR(190) DEFAULT '',
            token_hash VARCHAR(255) DEFAULT '',
            sent_at DATETIME NULL,
            signed_at DATETIME NULL,
            signed_ip VARCHAR(100) DEFAULT '',
            status VARCHAR(80) DEFAULT 'draft',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY token_hash (token_hash),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $signatures (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            contract_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            client_id BIGINT UNSIGNED NOT NULL,
            email VARCHAR(190) NOT NULL,
            ip_address VARCHAR(100) DEFAULT '',
            signed_at DATETIME NULL,
            token_hash VARCHAR(255) NOT NULL,
            status VARCHAR(80) DEFAULT 'pending',
            is_test TINYINT(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY contract_id (contract_id),
            KEY project_id (project_id),
            KEY client_id (client_id),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $tokens (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id BIGINT UNSIGNED NULL,
            project_id BIGINT UNSIGNED NULL,
            token_hash VARCHAR(255) NOT NULL,
            token_type VARCHAR(80) NOT NULL,
            expires_at DATETIME NULL,
            used_at DATETIME NULL,
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY token_type (token_type),
            KEY token_hash (token_hash),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $settings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(190) NOT NULL,
            setting_value LONGTEXT NULL,
            autoload TINYINT(1) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id BIGINT UNSIGNED NULL,
            project_id BIGINT UNSIGNED NULL,
            log_type VARCHAR(80) DEFAULT 'note',
            message TEXT NOT NULL,
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY project_id (project_id),
            KEY log_type (log_type),
            KEY is_test (is_test)
        ) $charset_collate;");


        dbDelta("CREATE TABLE $documents (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id BIGINT UNSIGNED NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            attachment_id BIGINT UNSIGNED NULL,
            document_title VARCHAR(190) NOT NULL,
            document_type VARCHAR(80) DEFAULT 'Annet',
            file_url TEXT NULL,
            notes TEXT NULL,
            status VARCHAR(80) DEFAULT 'active',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY project_id (project_id),
            KEY document_type (document_type),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $templates (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            template_name VARCHAR(190) NOT NULL,
            project_type VARCHAR(80) DEFAULT '',
            document_type VARCHAR(80) DEFAULT 'Kontrakt',
            template_content LONGTEXT NOT NULL,
            status VARCHAR(80) DEFAULT 'active',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY project_type (project_type),
            KEY document_type (document_type),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");



        dbDelta("CREATE TABLE $galleries (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            client_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            gallery_number VARCHAR(50) DEFAULT '',
            gallery_title VARCHAR(190) NOT NULL,
            base_dir TEXT NOT NULL,
            base_url TEXT NOT NULL,
            zip_filename VARCHAR(255) DEFAULT '',
            original_count INT UNSIGNED DEFAULT 0,
            preview_count INT UNSIGNED DEFAULT 0,
            thumbnail_count INT UNSIGNED DEFAULT 0,
            downloadable_until DATE NULL,
            auto_delete_at DATE NULL,
            local_backup_confirmed TINYINT(1) DEFAULT 0,
            watermark_enabled TINYINT(1) DEFAULT 1,
            download_enabled TINYINT(1) DEFAULT 0,
            status VARCHAR(80) DEFAULT 'uploaded',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY client_id (client_id),
            KEY project_id (project_id),
            KEY gallery_number (gallery_number),
            KEY status (status),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $images (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            gallery_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            original_path TEXT NOT NULL,
            original_url TEXT NULL,
            preview_path TEXT NULL,
            preview_url TEXT NULL,
            thumbnail_path TEXT NULL,
            thumbnail_url TEXT NULL,
            file_ext VARCHAR(20) DEFAULT '',
            file_size BIGINT UNSIGNED DEFAULT 0,
            width INT UNSIGNED NULL,
            height INT UNSIGNED NULL,
            sort_order INT UNSIGNED DEFAULT 0,
            is_selected TINYINT(1) DEFAULT 0,
            is_download_enabled TINYINT(1) DEFAULT 0,
            status VARCHAR(80) DEFAULT 'original_uploaded',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY gallery_id (gallery_id),
            KEY project_id (project_id),
            KEY status (status),
            KEY is_selected (is_selected),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $favorites (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            image_id BIGINT UNSIGNED NOT NULL,
            gallery_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            client_id BIGINT UNSIGNED NULL,
            user_email VARCHAR(190) DEFAULT '',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY image_id (image_id),
            KEY gallery_id (gallery_id),
            KEY project_id (project_id),
            KEY client_id (client_id),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $comments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            image_id BIGINT UNSIGNED NOT NULL,
            gallery_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            client_id BIGINT UNSIGNED NULL,
            user_email VARCHAR(190) DEFAULT '',
            comment_text TEXT NOT NULL,
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY image_id (image_id),
            KEY gallery_id (gallery_id),
            KEY project_id (project_id),
            KEY client_id (client_id),
            KEY is_test (is_test)
        ) $charset_collate;");

        dbDelta("CREATE TABLE $downloads (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            gallery_id BIGINT UNSIGNED NULL,
            image_id BIGINT UNSIGNED NULL,
            project_id BIGINT UNSIGNED NOT NULL,
            client_id BIGINT UNSIGNED NULL,
            download_type VARCHAR(80) DEFAULT 'preview',
            user_email VARCHAR(190) DEFAULT '',
            ip_address VARCHAR(100) DEFAULT '',
            is_test TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY gallery_id (gallery_id),
            KEY image_id (image_id),
            KEY project_id (project_id),
            KEY client_id (client_id),
            KEY download_type (download_type),
            KEY is_test (is_test)
        ) $charset_collate;");


        flush_rewrite_rules();
        update_option('9ls1_fotoportal_version', NLS1_FOTOPORTAL_VERSION);
    }
}
