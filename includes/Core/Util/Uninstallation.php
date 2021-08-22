<?php
namespace BitCode\BITWPFZC\Core\Util;

/**
 * Class handling plugin uninstallation.
 *
 * @since 1.0.0
 * @access private
 * @ignore
 */
final class Uninstallation
{
    /**
     * Registers functionality through WordPress hooks.
     *
     * @since 1.0.0-alpha
     */
    public function register()
    {
        add_action('bitwpfzc_uninstall', array($this, 'uninstall'));
    }

    public function uninstall()
    {
        if (get_option('bitwpfzc_erase_all')) {
            global $wpdb;
            $tableArray = [
             $wpdb->prefix . "bitwpfzc_zoho_crm_log_details",
             $wpdb->prefix . "bitwpfzc_integration",
             $wpdb->prefix . "bitwpfzc_gclid",
            ];
            foreach ($tableArray as $tablename) {
                $wpdb->query("DROP TABLE IF EXISTS $tablename");
            }
            $columns = ["bitwpfzc_db_version", "bitwpfzc_installed", "bitwpfzc_version", "bitwpfzc_erase_all"];
            foreach ($columns as $column) {
                $wpdb->query("DELETE FROM `{$wpdb->prefix}options` WHERE option_name='$column'");
            }
        }
    }
}
