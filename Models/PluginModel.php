<?php
#App\GP247\Plugins\CheckIP\Models\PluginModel.php
namespace App\GP247\Plugins\CheckIP\Models;

use Illuminate\Database\Eloquent\Model;

class PluginModel extends Model
{
    public $timestamps    = false;
    public $table = '';
    protected $connection = GP247_DB_CONNECTION;
    protected $guarded    = [];
}
