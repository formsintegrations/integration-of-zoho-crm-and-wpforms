<?php
if (!defined('ABSPATH')) {
    exit;
}
$scheme = parse_url(home_url())['scheme'];
define('BITWPFZC_PLUGIN_BASENAME', plugin_basename(BITWPFZC_PLUGIN_MAIN_FILE));
define('BITWPFZC_PLUGIN_DIR_PATH', plugin_dir_path(BITWPFZC_PLUGIN_MAIN_FILE));
define('BITWPFZC_ROOT_URI', set_url_scheme(plugins_url('', BITWPFZC_PLUGIN_MAIN_FILE), $scheme));
define('BITWPFZC_ASSET_URI', BITWPFZC_ROOT_URI . '/assets');
define('BITWPFZC_ASSET_JS_URI', BITWPFZC_ROOT_URI . '/assets/js');
// Autoload vendor files.
if (file_exists(BITWPFZC_PLUGIN_DIR_PATH . 'vendor/autoload.php')) {
    include_once BITWPFZC_PLUGIN_DIR_PATH . 'vendor/autoload.php';
    // Initialize the plugin.
    BitCode\BITWPFZC\Plugin::load(BITWPFZC_PLUGIN_MAIN_FILE);
} else {
    echo 'BITWPZC: vendors missing';
}


