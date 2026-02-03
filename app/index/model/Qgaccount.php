<?php
namespace app\index\model;

use think\Model;
use think\facade\Db;

class Qgaccount extends Model
{
    protected $table = 'qgaccount';

    protected function getPrefixedTable()
    {
        return 'aijz_' . $this->table;
    }

    public function getCountByUserId($userId)
    {
        return $this->where('uid', $userId)->count();
    }
}
