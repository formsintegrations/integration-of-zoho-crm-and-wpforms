<?php
namespace BitCode\BITWPFZC\Admin\WPF;

use BitCode\BITWPFZC\Core\Util\Route;

final class Router{
    public function __construct()
    {
        //
    }
    
    
    public static function registerAjax()
    {
        Route::post('ff/get/form', [Handler::class, 'get_a_form']);
    }
} 