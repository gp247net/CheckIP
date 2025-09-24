<?php
/**
 * Provides everything needed for the Extension
 */

 $config = file_get_contents(__DIR__.'/gp247.json');
 $config = json_decode($config, true);
 $extensionPath = $config['configGroup'].'/'.$config['configKey'];
 
 $this->loadTranslationsFrom(__DIR__.'/Lang', $extensionPath);
 
 if (gp247_extension_check_active($config['configGroup'], $config['configKey'])) {
     
     $this->loadViewsFrom(__DIR__.'/Views', $extensionPath);
     
     if (file_exists(__DIR__.'/config.php')) {
         $this->mergeConfigFrom(__DIR__.'/config.php', $extensionPath);
     }
 
     if (file_exists(__DIR__.'/function.php')) {
         require_once __DIR__.'/function.php';
     }
 
    app('router')->aliasMiddleware('check_ip', \App\GP247\Plugins\CheckIP\Middleware\CheckIP::class);

    // For admin
    $admin = (array) config('gp247-config.admin.middleware', []);
    if (!in_array('check_ip', $admin, true)) {
        $admin[] = 'check_ip';
        config(['gp247-config.admin.middleware' => $admin]);
    }

    // For front
    $front = (array) config('gp247-config.front.middleware', []);
    if (!in_array('check_ip', $front, true)) {
        $front[] = 'check_ip';
        config(['gp247-config.front.middleware' => $front]);
    }

    // For api
    $api_extend = (array) config('gp247-config.api.middleware', []);
    if (!in_array('check_ip', $api_extend, true)) {
        $api_extend[] = 'check_ip';
        config(['gp247-config.api.middleware' => $api_extend]);
    }

    // Ensure runtime Router groups receive middleware even if groups were already registered
    app('router')->prependMiddlewareToGroup('admin', 'check_ip');
    app('router')->prependMiddlewareToGroup('front', 'check_ip');
    app('router')->prependMiddlewareToGroup('api.extend', 'check_ip');
    // For front, group may be registered later by FrontServiceProvider; we keep config above
 }