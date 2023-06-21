<?php

namespace BitCode\BITWPFZC;

/**
 * Main class for the plugin.
 *
 * @since 1.0.0-alpha
 */
use BitCode\BITWPFZC\Core\Database\DB;
use BitCode\BITWPFZC\Admin\Admin_Bar;
use BitCode\BITWPFZC\Admin\AdminHooks;
use BitCode\BITWPFZC\Core\Util\Request;
use BitCode\BITWPFZC\Core\Util\Activation;
use BitCode\BITWPFZC\Core\Util\Deactivation;
use BitCode\BITWPFZC\Core\Util\Uninstallation;
use BitCode\BITWPFZC\Core\Ajax\AjaxService;
use BitCode\BITWPFZC\Integration\Integrations;

final class Plugin
{
    /**
     * Main instance of the plugin.
     *
     * @since 1.0.0-alpha
     * @var   Plugin|null
     */
    private static $instance = null;

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function initialize()
    {
        add_action('plugins_loaded', [$this, 'init_plugin'], 11);
        (new Activation())->activate();
        (new Deactivation())->register();
        (new Uninstallation())->register();
    }

    public function init_plugin()
    {
        if (!function_exists('wpforms') || !is_callable('wpforms')) {
            add_action('admin_notices', [$this ,'wpfNotFound']);
            return;
        }
        add_action('init', array($this, 'init_classes'), 10);
        add_filter('plugin_action_links_' . plugin_basename(BITWPFZC_PLUGIN_MAIN_FILE), array( $this, 'plugin_action_links' ));
    }

    public function wpfNotFound()
    {
        echo '<div class="error"><p>WPForms plugin is required for Zoho CRM integration<p></div>';
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if (Request::Check('admin')) {
            (new Admin_Bar())->register();
        }
        if (Request::Check('ajax')) {
            new AjaxService();
        }
        (new AdminHooks())->register();
        (new Integrations())->registerHooks();
    }

    /**
     * Plugin action links
     *
     * @param  array $links
     *
     * @return array
     */
    public function plugin_action_links($links)
    {
        $links[] = '<a href="https://formsintegrations.com/wpforms-integration-with-zoho-crm/documentation" target="_blank">' . __('Docs', 'bitwpfzc') . '</a>';

        return $links;
    }

    /**
     * Retrieves the main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @return bitwpfzc Plugin main instance.
     */
    public static function instance()
    {
        return static::$instance;
    }

    public static function update_tables()
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        global $bitwpfzc_db_version;
        $installed_db_version = get_site_option("bitwpfzc_db_version");
        if ($installed_db_version!=$bitwpfzc_db_version) {
            DB::migrate();
        }
    }
    /**
     * Loads the plugin main instance and initializes it.
     *
     * @since 1.0.0-alpha
     *
     * @param string $main_file Absolute path to the plugin main file.
     * @return bool True if the plugin main instance could be loaded, false otherwise./
     */
    public static function load($main_file)
    {
        if (null !== static::$instance) {
            return false;
        }
        // static::update_tables();
        static::$instance = new static($main_file);
        static::$instance->initialize();
        return true;
    }
}
