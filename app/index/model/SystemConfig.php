<?php
namespace app\index\model;

use think\Model;

class SystemConfig extends Model
{
    protected $name = 'qgsystem';
    
    // 设置字段映射
    protected $schema = [
        'id'        => 'int',
        'webtitle'  => 'string',
        'version'   => 'string',
        'regstate'  => 'int',
        'mbname'    => 'string',
        'weburl'    => 'string',
        'urlssl'    => 'int',
        'copyright' => 'string',
        'year'      => 'string'
    ];
}
