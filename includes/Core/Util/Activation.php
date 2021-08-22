<?php

namespace BitCode\BITWPFZC\Core\Util;

use BitCode\BITWPFZC\Core\Database\DB;

/**
 * Class handling plugin activation.
 *
 * @since 1.0.0
 */
final class Activation
{
    public function activate()
    {
        add_action('bitwpfzc_activation', array($this, 'install'));
    }

    public function install()
    {
        $installed = get_option('bitwpfzc_installed');
        if ($installed) {
            $oldversion = get_option('bitwpfzc_version');
        }
        if (!get_option('bitwpfzc_erase_all')) {
            update_option('bitwpfzc_erase_all', false);
        }
    
        if (!$installed || version_compare($oldversion, BITWPFZC_VERSION, '!=')) {
            DB::migrate();
            update_option('bitwpfzc_installed', time());
        }
        update_option('bitwpfzc_version', BITWPFZC_VERSION);
    }
}
