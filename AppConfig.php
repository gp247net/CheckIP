<?php
/**
 * Plugin format 2.0
 */
#App\Plugins\Other\CheckIP\AppConfig.php
namespace App\GP247\Plugins\CheckIP;

use App\GP247\Plugins\CheckIP\Models\ExtensionModel;
use GP247\Core\Models\AdminConfig;
use GP247\Core\Models\AdminHome;
use GP247\Core\ExtensionConfigDefault;

class AppConfig extends ExtensionConfigDefault
{
    public function __construct()
    {
        //Read config from gp247.json
        $config = file_get_contents(__DIR__.'/gp247.json');
        $config = json_decode($config, true);
        $this->configGroup = $config['configGroup'];
        $this->configKey = $config['configKey'];
        $this->configCode = $config['configCode'] ?? $this->configKey;
        $this->requireCore = $config['requireCore'] ?? [];
        $this->requirePackages = $config['requirePackages'] ?? [];
        $this->requireExtensions = $config['requireExtensions'] ?? [];
        //Path
        $this->appPath = $this->configGroup . '/' . $this->configKey;
        //Language
        $this->title = trans($this->appPath.'::lang.title');
        //Image logo or thumb
        $this->image = $this->appPath.'/'.$config['image'];
        //
        $this->version = $config['version'];
        $this->auth = $config['auth'];
        $this->link = $config['link'] ?? '';
    }

    public function install()
    {
        $check = AdminConfig::where('key', $this->configKey)
            ->where('group', $this->configGroup)->first();
        if ($check) {
            //Check Plugin key exist
            $return = ['error' => 1, 'msg' =>  gp247_language_render('admin.extension.plugin_exist')];
        } else {
            //Insert plugin to config
            $dataInsert = [
                [
                    'group'  => $this->configGroup,
                    'code'    => $this->configCode,
                    'key'    => $this->configKey,
                    'sort'   => 0,
                    'store_id' => GP247_STORE_ID_GLOBAL,
                    'value'  => self::ON, //Enable extension
                    'detail' => $this->appPath.'::lang.title',
                ]
            ];
            try {
                AdminConfig::insert(
                    $dataInsert
                );
                (new ExtensionModel)->installExtension();
                $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.install_success')];
            } catch (\Throwable $th) {
                $return = ['error' => 1, 'msg' => $th->getMessage()];
            }
        }
        return $return;
    }

    public function uninstall()
    {
        //Please delete all values inserted in the installation step
        try {
            (new AdminConfig)
                ->where('key', $this->configKey)
                ->orWhere('code', $this->configKey.'_config')
                ->delete();
            //Admin config home
            AdminHome::where('extension', $this->appPath)->delete();
            (new ExtensionModel)->uninstallExtension();
            $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.uninstall_success')];
        } catch (\Throwable $e) {
            $return = ['error' => 1, 'msg' => $e->getMessage()];
        }

        return $return;
    }
    
    public function enable()
    {
        $process = (new AdminConfig)
            ->where('group', $this->configGroup)
            ->where('key', $this->configKey)
            ->update(['value' => self::ON]);

        //Admin config home
        AdminHome::where('extension', $this->appPath)->update(['status' => 1]);

        if (!$process) {
            $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Enable'])];
        }
        $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.enable_success')];
        return $return;
    }

    public function disable()
    {
        $process = (new AdminConfig)
            ->where('group', $this->configGroup)
            ->where('key', $this->configKey)
            ->update(['value' => self::OFF]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Disable'])];
        }
        $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.disable_success')];
        //Admin config home
        AdminHome::where('extension', $this->appPath)->update(['status' => 0]);

        return $return;
    }

    public function removeStore($storeId = null)
    {
        // code here
    }

    public function setupStore($storeId = null)
    {
       // code here
    }

    public function clickApp()
    {
        return redirect(gp247_route_admin('admin_checkip.index'));
    }

    public function getInfo()
    {
        $arrData = [
            'title' => $this->title,
            'key' => $this->configKey,
            'code' => $this->configCode,
            'image' => $this->image,
            'permission' => self::ALLOW,
            'version' => $this->version,
            'auth' => $this->auth,
            'link' => $this->link,
            'value' => 0,
            'appPath' => $this->appPath
        ];

        return $arrData;
    }
}
