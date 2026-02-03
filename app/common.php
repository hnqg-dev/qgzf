<?php


function get_sys_config()
{
    try {
        $config = \think\facade\Db::name('qgsystem')->where('id', 1)->find();
        if ($config) {
            return [
                'webtitle' => $config['webtitle'] ?? '全哥账房',
                'mbname' => $config['mbname'] ?? 'default/',
                'regstate' => $config['regstate'] ?? '1',
                'copyright' => $config['copyright'] ?? '无聊全哥',
                'version' => $config['version'] ?? '1.0',
                'year' => $config['year'] ?? '2024-2025'
            ];
        }
    } catch (\Exception $e) {
        \think\facade\Log::error('获取系统配置失败: ' . $e->getMessage());
    }
    
    return [
        'webtitle' => '全哥账房',
        'mbname' => 'default/',
        'regstate' => '1',
        'copyright' => '无聊全哥',
        'version' => '1.0',
        'year' => '2024-2025'
    ];
}

function get_ledger_info()
{
    if (!\think\facade\Session::has('user')) {
        return null;
    }

    $lid = \think\facade\Request::param('lid');
    if ($lid) {
        \think\facade\Session::set('lid', $lid);
        \think\facade\Session::save();
    } else {
        $lid = \think\facade\Session::get('lid');
    }

    if (!$lid) {
        return null;
    }

    $lname = \think\facade\Session::get('current_lname');
    if (!$lname) {
        try {
            $ledgerInfo = \think\facade\Db::name('qgledger')
                ->where('lid', $lid)
                ->field('lname')
                ->find();
            
            if ($ledgerInfo && isset($ledgerInfo['lname'])) {
                $lname = $ledgerInfo['lname'];
                \think\facade\Session::set('current_lname', $lname);
                return $lname;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            \think\facade\Log::error('获取账本信息失败: ' . $e->getMessage());
            return null;
        }
    }
    
    return $lname;
}

function get_ledger_id()
{
    if (!\think\facade\Session::has('user')) {
        return null;
    }

    $lid = \think\facade\Request::param('lid');
    if ($lid) {
        \think\facade\Session::set('lid', $lid);
        \think\facade\Session::save();
        return $lid;
    } else {
        return \think\facade\Session::get('lid');
    }
}
