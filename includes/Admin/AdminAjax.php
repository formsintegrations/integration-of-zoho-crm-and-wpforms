<?php

namespace BitCode\BITWPFZC\Admin;

use BitCode\BITWPFZC\Core\Util\Route;

class AdminAjax
{
    public function __construct()
    {
        //
    }
    public function register()
    {
        if(strpos(\sanitize_text_field($_REQUEST['action']), 'bitwpfzc') === false) return;   
        $dirs = new \FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $serviceName = basename($dirInfo);
                if (file_exists(__DIR__.'/'.$serviceName)
                    && file_exists(__DIR__.'/'.$serviceName.'/Router.php')
                ) {
                    $routes = __NAMESPACE__. "\\{$serviceName}\\Router";
                    if (method_exists($routes, 'registerAjax')) {
                        (new $routes())->registerAjax();
                    }
                }
            }
        }
        Route::post('erase_all', [$this, 'toggle_erase_all']);
        return;
    }

    public function toggle_erase_all($data) {
        if (empty($data->toggle)) {
            wp_send_json_error(__('Toggle status can\'t be empty', 'bitgfzc'));
        }
        update_option('bitwpfzc_erase_all', (bool)  $data->toggle);
        wp_send_json_success(__('Erase in delete toggled', 'bitgfzc'));
    }
}