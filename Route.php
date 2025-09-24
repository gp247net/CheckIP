<?php
use Illuminate\Support\Facades\Route;

$config = file_get_contents(__DIR__.'/gp247.json');
$config = json_decode($config, true);

if(gp247_extension_check_active($config['configGroup'], $config['configKey'])) {
    Route::group(
        [
            'prefix' => GP247_ADMIN_PREFIX.'/checkip',
            'middleware' => GP247_ADMIN_MIDDLEWARE,
            'namespace' => '\\App\\GP247\\Plugins\\CheckIP\\Admin',
        ], 
        function () {
            Route::get('/', 'AdminController@index')
            ->name('admin_checkip.index');
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
