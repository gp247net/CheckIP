<?php
#App\GP247\Plugins\CheckIP\Models\ExtensionModel.php
namespace App\GP247\Plugins\CheckIP\Models;

use GP247\Core\Models\AdminMenu;

class ExtensionModel
{
    public function uninstallExtension()
    {
        (new CheckIPAccess)->uninstall();

        // Remove admin menu if created
        (new AdminMenu)->where('uri', 'route_admin::admin_checkip.index')->delete();
        $checkMenu = (new AdminMenu)->where('key', 'CheckIP')->first();
        if ($checkMenu) {
            if (!(new AdminMenu)->where('parent_id', $checkMenu->id)->count()) {
                (new AdminMenu)->where('key', 'CheckIP')->delete();
            }
        }
    }

    public function installExtension()
    {
        (new CheckIPAccess)->install();

        // Ensure admin menu root exists under SECURITY group if needed
        $checkMenu = AdminMenu::where('key','CheckIP')->first();
        if (!$checkMenu) {
            $menuSecurity = AdminMenu::where('key', 'ADMIN_SECURITY')->first();
            AdminMenu::insert([
                'parent_id' => $menuSecurity->id,
                'title' => 'Plugins/CheckIP::lang.admin.title',
                'icon' => 'fa fa-braille',
                'uri' => 'route_admin::admin_checkip.index',
            ]);
        }

    }
}

