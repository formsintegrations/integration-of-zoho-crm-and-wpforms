<?php
namespace BitCode\BITWPFZC\Admin;

use BitCode\BITWPFZC\Core\Util\DateTimeHelper;
use BitCode\BITWPFZC\Admin\Gclid\Handler as GclidHandler;

/**
 * The admin menu and page handler class
 */

class Admin_Bar
{
    public function register()
    {
        add_action('in_admin_header', [$this, 'RemoveAdminNotices']);
        add_action('admin_menu', [$this, 'AdminMenu'], 9, 0);
        add_action('admin_enqueue_scripts', [$this, 'AdminAssets']);
    }


    /**
     * Register the admin menu
     *
     * @return void
     */
    public function AdminMenu()
    {
        $capability = apply_filters('bitwpfzc_form_access_capability', 'manage_options');
        add_menu_page(__('Zoho CRM integration for WPForms', 'bitwpfzc'), 'WPForms Zoho CRM', $capability, 'bitwpfzc', array($this, 'RootPage'), 'data:image/svg+xml;base64,' . base64_encode('<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><defs><style>.cls-1{fill:#fff;}</style></defs><path class="cls-1" d="M105.71,104.4V23.6a1.33,1.33,0,0,0-1.31-1.31h-2.08L77.69,39,64,27.83,50.31,39,25.68,22.29H23.6a1.33,1.33,0,0,0-1.31,1.31v80.8a1.33,1.33,0,0,0,1.31,1.31h80.8a1.33,1.33,0,0,0,1.31-1.31ZM50,32.07l12-9.78H35.59Zm28,0,14.4-9.78H66Zm36-8.47v80.8a9.56,9.56,0,0,1-9.65,9.65H23.6A9.56,9.56,0,0,1,14,104.4V23.6A9.56,9.56,0,0,1,23.6,14h80.8a9.56,9.56,0,0,1,9.65,9.65Z"/><path class="cls-1" d="M48.75,97.53A4.76,4.76,0,0,1,44,92.88v0A6.35,6.35,0,0,1,44.62,90a12.47,12.47,0,0,1,1.58-2.5L69.93,58.26H47.48a2.77,2.77,0,0,1-2.6-1.82v0a7.21,7.21,0,0,1-.4-2.46,4.32,4.32,0,0,1,1.07-3.27,3.91,3.91,0,0,1,2.81-1H78.23a4.86,4.86,0,0,1,4.9,4.74v.06a6.15,6.15,0,0,1-.62,2.71,12.06,12.06,0,0,1-1.63,2.51L57.25,89h24a2.51,2.51,0,0,1,2.4,1.72v0a8,8,0,0,1,.36,2.45A4.33,4.33,0,0,1,83,96.51a3.9,3.9,0,0,1-2.81,1Z"/></svg>'), 30);
    }
    /**
     * Load the asset libraries
     *
     * @return void
     */
    public function AdminAssets($current_screen)
    {
        if (strpos($current_screen, 'bitwpfzc') === false) {
            return;
        }
        $parsed_url = parse_url(get_admin_url());
        $site_url = $parsed_url['scheme'] . "://" . $parsed_url['host'];
        $site_url .= empty($parsed_url['port']) ? null : ':' . $parsed_url['port'];
        $base_path_admin =  str_replace($site_url, '', get_admin_url());
        wp_enqueue_script(
            'bitwpfzc-vendors',
            BITWPFZC_ASSET_JS_URI . '/vendors-main.js',
            null,
            BITWPFZC_VERSION,
            true
        );
        wp_enqueue_script(
            'bitwpfzc-runtime',
            BITWPFZC_ASSET_JS_URI . '/runtime.js',
            null,
            BITWPFZC_VERSION,
            true
        );
        if (wp_script_is('wp-i18n')) {
            $deps = array('bitwpfzc-vendors', 'bitwpfzc-runtime', 'wp-i18n');
        } else {
            $deps = array('bitwpfzc-vendors', 'bitwpfzc-runtime', );
        }
        wp_enqueue_script(
            'bitwpfzc-admin-script',
            BITWPFZC_ASSET_JS_URI . '/index.js',
            $deps,
            BITWPFZC_VERSION,
            true
        );

        wp_enqueue_style(
            'bitwpfzc-styles',
            BITWPFZC_ASSET_URI . '/css/bitwpfzc.css',
            null,
            BITWPFZC_VERSION,
            'screen'
        );
       
        $gclidHandler = new GclidHandler();
        $gclid_enabled = $gclidHandler->get_enabled_form_lsit();
        $forms = \WPForms()->form->get();
        $all_forms = [];
        if ($forms) {
            foreach ($forms as $form) {
                $all_forms[] = (object)[
                    'id' => $form->ID,
                    'title' => $form->post_title,
                    'gclid' => in_array($form->ID, $gclid_enabled)
                ];
            }
        }
        $bitwpfzc = apply_filters(
            'bitwpfzc_localized_script',
            array(
                'nonce'     => wp_create_nonce('bitwpfzc_nonce'),
                'assetsURL' => BITWPFZC_ASSET_URI,
                'baseURL'   => $base_path_admin . 'admin.php?page=bitwpfzc#',
                'ajaxURL'   => admin_url('admin-ajax.php'),
                'allForms'  => is_wp_error($all_forms) ? null : $all_forms,
                'erase_all'  => get_option('bitwpfzc_erase_all'),
                'dateFormat'  => get_option('date_format'),
                'timeFormat'  => get_option('time_format'),
                'new_page'  => admin_url('admin.php?page=wpforms-builder'),
                'timeZone'  => DateTimeHelper::wp_timezone_string(),
                'redirect' => get_rest_url() . 'bitwpfzc/redirect',
            )
        );
        if (get_locale() !== 'en_US' && file_exists(BITWPFZC_PLUGIN_DIR_PATH . '/languages/generatedString.php')) {
            include_once BITWPFZC_PLUGIN_DIR_PATH . '/languages/generatedString.php';
            $bitwpfzc['translations'] = $bitwpfzc_i18n_strings;
        }
        wp_localize_script('bitwpfzc-admin-script', 'bitwpfzc', $bitwpfzc);
    }

    /**
     * apps-root id provider
     * @return void
     */
    public function RootPage()
    {
        require_once BITWPFZC_PLUGIN_DIR_PATH . '/views/view-root.php';
    }

    public function RemoveAdminNotices()
    {
        global $plugin_page;
        if (strpos($plugin_page, 'bitwpfzc') === false) {
            return;
        }
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
