<?php

namespace BitCode\BITWPFZC\Admin;

class AdminHooks
{
    public function __construct()
    {
        //
    }
    public function register()
    { 
        $dirs = new \FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $serviceName = basename($dirInfo);
                if (file_exists(__DIR__.'/'.$serviceName)
                    && file_exists(__DIR__.'/'.$serviceName.'/Hooks.php')
                ) {
                    $hooks = "BitCode\\BITWPFZC\\Admin\\{$serviceName}\\Hooks";
                    if (method_exists($hooks, 'registerHooks')) {
                        (new $hooks())->registerHooks();                    
                    }
                }
            }
        }
        return;
    }
}