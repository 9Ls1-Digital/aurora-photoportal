<?php
if (!defined('ABSPATH')) exit;

/**
 * Aurora tenant context and data migration foundation.
 *
 * dev.12 establishes account ownership on existing Fotoportal domain tables.
 * All current legacy rows are assigned to the seeded/default Photographer Account.
 */
class NLS1_Aurora_Tenant_Context {
    const SCHEMA_VERSION = '0.1.0';

    /**
     * Domain tables that contain photographer-owned business data.
     * Values are the existing suffixes after the WP prefix.
     */
    public static function domain_tables() {
        return [
            '9ls1_clients',
            '9ls1_client_contacts',
            '9ls1_projects',
            '9ls1_project_files',
            '9ls1_galleries',
            '9ls1_gallery_images',
            '9ls1_documents',
            '9ls1_contracts',
            '9ls1_logs',
        ];
    }

    public static function current_account_id() {
        // Future photographer login/session resolution will replace the default
        // account fallback. Keeping it centralized prevents tenant logic from
        // being duplicated throughout the plugin.
        $filtered = (int) apply_filters('9ls1_aurora_current_account_id', 0);
        if ($filtered > 0 && self::account_exists($filtered)) return $filtered;

        $account = NLS1_Aurora_Account_Platform::default_account();
        return $account ? (int)$account->id : 0;
    }

    public static function current_account() {
        $id = self::current_account_id();
        return $id ? NLS1_Aurora_Account_Platform::get_account($id) : null;
    }

    public static function account_exists($account_id) {
        return (bool) NLS1_Aurora_Account_Platform::get_account((int)$account_id);
    }

    public static function table_has_account_id($table) {
        global $wpdb;
        $column = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `$table` LIKE %s", 'account_id'));
        return (bool)$column;
    }

    public static function maybe_migrate() {
        $installed = (string)get_option('9ls1_aurora_tenant_schema_version', '');
        if ($installed === self::SCHEMA_VERSION) return;

        global $wpdb;
        $account_id = self::current_account_id();
        if (!$account_id) return;

        foreach (self::domain_tables() as $suffix) {
            $table = $wpdb->prefix . $suffix;
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if ($exists !== $table) continue;

            if (!self::table_has_account_id($table)) {
                // Additive migration only: existing domain schema is preserved.
                $wpdb->query("ALTER TABLE `$table` ADD COLUMN `account_id` BIGINT UNSIGNED NOT NULL DEFAULT 0");
                $wpdb->query("ALTER TABLE `$table` ADD INDEX `account_id` (`account_id`)");
            }

            // Existing installation belongs to the seeded/default photographer.
            $wpdb->query($wpdb->prepare(
                "UPDATE `$table` SET account_id=%d WHERE account_id=0 OR account_id IS NULL",
                $account_id
            ));
        }

        update_option('9ls1_aurora_tenant_schema_version', self::SCHEMA_VERSION, false);
    }

    public static function scope_sql($alias = '') {
        global $wpdb;
        $account_id = self::current_account_id();
        $prefix = $alias ? preg_replace('/[^a-zA-Z0-9_]/', '', $alias) . '.' : '';
        return $wpdb->prepare($prefix . 'account_id = %d', $account_id);
    }

    public static function stamp_insert(array $data) {
        $data['account_id'] = self::current_account_id();
        return $data;
    }

    public static function owns_row($table, $row_id, $id_column = 'id') {
        global $wpdb;
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $id_column = preg_replace('/[^a-zA-Z0-9_]/', '', $id_column);
        if (!$table || !$id_column || !self::table_has_account_id($table)) return false;

        return (bool)$wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM `$table` WHERE `$id_column`=%d AND account_id=%d LIMIT 1",
            (int)$row_id,
            self::current_account_id()
        ));
    }

    public static function migration_status() {
        global $wpdb;
        $status = [];
        foreach (self::domain_tables() as $suffix) {
            $table = $wpdb->prefix . $suffix;
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if ($exists !== $table) continue;
            $status[$suffix] = [
                'account_id' => self::table_has_account_id($table),
                'unscoped_rows' => self::table_has_account_id($table)
                    ? (int)$wpdb->get_var("SELECT COUNT(*) FROM `$table` WHERE account_id=0")
                    : null,
            ];
        }
        return $status;
    }
}
