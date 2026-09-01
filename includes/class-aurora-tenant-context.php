<?php
if (!defined('ABSPATH')) exit;

class NLS1_Aurora_Tenant_Context {
    const SCHEMA_VERSION = '0.2.0';

    public static function domain_tables() {
        return [
            'clients','contacts','projects','contracts','signatures','access_tokens',
            'logs','documents','document_templates','galleries','images',
            'favorites','image_comments','downloads',
        ];
    }

    public static function table($key) {
        global $wpdb;
        return $wpdb->prefix . '9ls1_fotoportal_' . sanitize_key($key);
    }

    public static function current_account_id() {
        $filtered=(int)apply_filters('9ls1_aurora_current_account_id',0);
        if($filtered>0 && self::account_exists($filtered)) return $filtered;
        $account=NLS1_Aurora_Account_Platform::default_account();
        return $account ? (int)$account->id : 0;
    }

    public static function current_account() {
        $id=self::current_account_id();
        return $id ? NLS1_Aurora_Account_Platform::get_account($id) : null;
    }

    public static function account_exists($id) {
        return (bool)NLS1_Aurora_Account_Platform::get_account((int)$id);
    }

    public static function table_exists($table) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table)) === $table;
    }

    public static function table_has_account_id($table) {
        global $wpdb;
        if(!self::table_exists($table)) return false;
        return (bool)$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `$table` LIKE %s",'account_id'));
    }

    public static function maybe_migrate() {
        if((string)get_option('9ls1_aurora_tenant_schema_version','')===self::SCHEMA_VERSION) return;
        global $wpdb;
        $account_id=self::current_account_id();
        if(!$account_id) return;

        foreach(self::domain_tables() as $key){
            $table=self::table($key);
            if(!self::table_exists($table)) continue;
            if(!self::table_has_account_id($table)){
                $wpdb->query("ALTER TABLE `$table` ADD COLUMN `account_id` BIGINT UNSIGNED NOT NULL DEFAULT 0");
                $wpdb->query("ALTER TABLE `$table` ADD INDEX `account_id` (`account_id`)");
            }
            $wpdb->query($wpdb->prepare(
                "UPDATE `$table` SET account_id=%d WHERE account_id=0 OR account_id IS NULL",
                $account_id
            ));
        }
        update_option('9ls1_aurora_tenant_schema_version',self::SCHEMA_VERSION,false);
    }

    public static function stamp_insert(array $data) {
        $data['account_id']=self::current_account_id();
        return $data;
    }

    public static function migration_status() {
        global $wpdb;
        $status=[];
        foreach(self::domain_tables() as $key){
            $table=self::table($key);
            if(!self::table_exists($table)) continue;
            $has=self::table_has_account_id($table);
            $status[$key]=[
                'account_id'=>$has,
                'unscoped_rows'=>$has ? (int)$wpdb->get_var("SELECT COUNT(*) FROM `$table` WHERE account_id=0") : null,
            ];
        }
        return $status;
    }
}
