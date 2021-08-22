<?php

/**
 * Class For Database Migration
 *
 * @category Database
 * @author   BitCode Developer <developer@bitcode.pro>
 */

namespace BitCode\BITWPFZC\Core\Database;

/**
 * Database Migration
 */
final class DB
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;
        global $bitwpfzc_db_version;
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if (!empty($wpdb->collate)) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }
        $table_schema = array(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bitwpfzc_zoho_crm_log_details` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `form_id` bigint(20) NOT NULL,
                `integration_id` bigint(20) DEFAULT NULL,
                `api_type` varchar(255) DEFAULT NULL,
                `response_type` varchar(50) DEFAULT NULL,
                `response_obj` LONGTEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `integration_id` (`integration_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bitwpfzc_integration` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `category` varchar(50)  NOT NULL,
                `integration_name` varchar(255) DEFAULT NULL,
                `integration_type` varchar(50)  NOT NULL,
                `integration_details` longtext DEFAULT NULL,
                `form_id` bigint(20) unsigned DEFAULT NULL, /* form_id = 0 means all/app */
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `user_ip` int(11) unsigned DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,/* 0 disabled, 1 published,  2 trashed */
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bitwpfzc_gclid` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `form_id` bigint(20) unsigned DEFAULT NULL, /* form_id = 0 means all/app */
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `user_ip` int(11) unsigned DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,/* 0 disabled, 1 published,  2 trashed */
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;"
        );
       
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ($table_schema as $table) {
            dbDelta($table);
        }
        
        update_site_option(
            'bitwpfzc_db_version',
            $bitwpfzc_db_version
        );
    }
}
