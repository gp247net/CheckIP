<?php
use Illuminate\Support\Facades\Route;

$config = file_get_contents(__DIR__.'/gp247.json');
$config = json_decode($config, true);

if(gp247_extension_check_active($config['configGroup'], $config['configKey'])) {
    Route::group(
        [
            'prefix' => GP247_ADMIN_PREFIX.'/checkip',
            'middleware' => GP247_ADMIN_MIDDLEWARE,
        ],
        function () {
            // Core 2.0: the admin screen is a full-page Livewire component on the
            // TailAdmin shell. It must be registered OUTSIDE a "namespace" route group —
            // Laravel prepends the group namespace to a class-string action, which would
            // corrupt the fully-qualified component class (e.g. CheckIP\Admin\App\...\AdminLivewire).
            if (class_exists(\App\GP247\Plugins\CheckIP\Livewire\AdminLivewire::class)) {
                Route::get('/', \App\GP247\Plugins\CheckIP\Livewire\AdminLivewire::class)
                ->name('admin_checkip.index');
            }

            // Legacy controller screen + actions keep their own namespace so the short
            // "AdminController@..." action strings resolve. Guarded with class_exists so
            // the plugin still boots if the Livewire class is absent (index falls back here).
            Route::group(
                ['namespace' => '\\App\\GP247\\Plugins\\CheckIP\\Admin'],
                function () {
                    if (!class_exists(\App\GP247\Plugins\CheckIP\Livewire\AdminLivewire::class)) {
                        Route::get('/', 'AdminController@index')
                        ->name('admin_checkip.index');
                    }
                    Route::get('create', function () {
                        return redirect()->route('admin_checkip.index');
                    });
                    Route::post('/create', 'AdminController@postCreate')->name('admin_checkip.create');
                    Route::get('/edit/{id}', 'AdminController@edit')->name('admin_checkip.edit');
                    Route::post('/edit/{id}', 'AdminController@postEdit')->name('admin_checkip.edit');
                    Route::post('/delete', 'AdminController@deleteList')->name('admin_checkip.delete');
                }
            );
        }
    );
}
