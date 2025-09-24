<?php
namespace App\GP247\Plugins\CheckIP\Models;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Eloquent\Model;

class CheckIPAccess extends Model
{
    protected $guarded    = [];
    public $table = 'check_ip_access';
    protected $connection = GP247_DB_CONNECTION;

    
    public function uninstall()
    {
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }
    }

    public function install()
    {
        $this->uninstall();
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip', 20)->index();
            $table->string('description', 255)->nullable();
            $table->string('type', 10)->index();
            $table->string('status', 10)->index()->default(1);
            $table->timestamps();
        });
    }
    /**
     * Get list IP Allow
     */
    public static function getIpsAllow() {
        return self::where('type', 'allow')->where('status', 1)->pluck('ip')->all();
    }

    /**
     * Get list IP Deny
     */
    public static function getIpsDeny() {
        return self::where('type', 'deny')->where('status', 1)->pluck('ip')->all();
    }

}
