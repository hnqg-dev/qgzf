<?php
namespace app\index\controller;

use think\facade\Session;
use think\facade\Filesystem;

class User
{
    public function logout()
    {
        Session::clear();
        cookie(null);
        
        return json([
            'code' => 1,
            'msg' => '退出成功',
            'url' => '/index/index/login'
        ]);
    }

    public function clearCache()
    {
        try {
            $dirs = [
                runtime_path(),
                app()->getRootPath().'cache',
                app()->getRootPath().'temp'
            ];
            
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $this->delDir($dir);
                }
            }
            
            \think\facade\Cache::clear();
            
            return json([
                'code' => 1,
                'msg' => '缓存清除成功'
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 0,
                'msg' => '清除缓存失败: '.$e->getMessage()
            ]);
        }
    }

    private function delDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            if (is_dir($path)) {
                $this->delDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

}
