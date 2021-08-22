<?php
namespace BitCode\BITWPFZC\Admin\WPF;

use BitCode\BITWPFZC\Core\Util\Request;

final class Hooks{
    public function __construct()
    {
        //
    }
    
    
    public function registerHooks()
    {
        if (Request::Check('frontend')) {
            add_action('wpforms_process_complete', [Handler::class, 'wpforms_process_complete'], 9, 4);
        }
    }
} 