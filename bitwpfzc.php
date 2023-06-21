<?php

/**
 * Plugin Name: Integration of Zoho CRM and WPForms
 * Plugin URI:  https://formsintegrations.com/wp-forms-integration-with-zoho-crm
 * Description: Sends WPForms entries to Zoho CRM
 * Version:     1.0.5
 * Author:      Forms Integrations
 * Author URI:  https://formsintegrations.com
 * Text Domain: bitwpfzc
 * Requires PHP: 5.6
 * Domain Path: /languages
 * License: GPLv2 or later
 */

/***
 * If try to direct access  plugin folder it will Exit
 **/
if (!defined('ABSPATH')) {
    exit;
}
global $bitwpfzc_db_version;
$bitwpfzc_db_version = '1.0';


// Define most essential constants.
define('BITWPFZC_VERSION', '1.0.5');
define('BITWPFZC_PLUGIN_MAIN_FILE', __FILE__);


require_once plugin_dir_path(__FILE__) . 'includes/loader.php';

function bitwpfzc_activate_plugin()
{
    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        wp_die(
            esc_html__('bitwpfzc requires PHP version 5.6.', 'bitwpfzc'),
            esc_html__('Error Activating', 'bitwpfzc')
        );
    }
    do_action('bitwpfzc_activation');
}

register_activation_hook(__FILE__, 'bitwpfzc_activate_plugin');

function bitwpfzc_uninstall_plugin()
{
    do_action('bitwpfzc_uninstall');
}
register_uninstall_hook(__FILE__, 'bitwpfzc_uninstall_plugin');
