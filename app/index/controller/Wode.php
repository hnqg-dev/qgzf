<?php
namespace app\index\controller;
use app\BaseController;
use think\facade\View;

class Wode extends BaseController
{
    protected function success($msg = '', string $url = null, $data = '', int $wait = 3)
    {
        if (is_null($url)) {
            $url = $this->request->server('HTTP_REFERER') ?: '/';
        }
        throw new \think\exception\HttpResponseException(redirect($url)->with(['success' => $msg]));
    }

    protected function error($msg = '', string $url = null, $data = '', int $wait = 3)
    {
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        }
        throw new \think\exception\HttpResponseException(redirect($url)->with(['error' => $msg]));
    }

    public function index()
    {
        $userInfo = session('user');
        if (!$userInfo) {
            return View::fetch('/wode', ['user' => []]);
        }

        $userData = \think\facade\Db::name('qgusers')->where('uid', $userInfo['uid'])->find();
        $userInfo['ctime'] = isset($userData['ctime']) ? date('Y/m/d', strtotime($userData['ctime'])) : date('Y/m/d');
        $userInfo['days_used'] = (new \DateTime($userInfo['ctime']))->diff(new \DateTime())->days;
        session('user.uface', $userData['uface'] ?? 'default.jpg');
        session('user.ufacesort', $userData['ufacesort'] ?? 0);
        $userInfo = array_merge($userInfo, [
            'uface' => session('user.uface'),
            'ufacesort' => session('user.ufacesort')
        ]);
        
        $isAdmin = isset($userInfo['ifadm']) && $userInfo['ifadm'] == 1;
        $sysSettings = [];
        
            if ($isAdmin) {
                $sysSettings = \think\facade\Db::name('qgsystem')->where('id', 1)->find();
                if ($sysSettings) {
                    $sysSettings['sys_name'] = $sysSettings['webtitle'] ?? '';
                    $sysSettings['sys_version'] = $sysSettings['version'] ?? '';
                    $sysSettings['regstate'] = $sysSettings['regstate'] ?? '0';
                }
            }
        
        return View::fetch('/wode', [
            'user' => $userInfo,
            'sysSettings' => $sysSettings,
            'isAdmin' => $isAdmin
        ]);
    }
    
    public function logout()
    {
        if (!session('?user')) {
            return json(['code' => 0, 'msg' => '未登录']);
        }

        try {
            $uid = session('user.uid');
            session('user', null);
            \think\facade\Log::info("用户[{$uid}]已退出登录");
            
            return json([
                'code' => 1,
                'msg' => '退出成功',
                'url' => '/'
            ]);
        } catch (\Exception $e) {
            \think\facade\Log::error('退出登录失败: ' . $e->getMessage());
            return json(['code' => 0, 'msg' => '退出登录失败']);
        }
    }

    public function edituser()
    {
        if (!session('?user')) {
            return redirect('/');
        }

        $uid = $this->request->param('uid', session('user.uid'));
        $userModel = new \app\index\model\User();
        $userInfo = $userModel->getUserById($uid);
        
        if (!$userInfo) {
            return redirect('/index/wode/index');
        }

        View::assign('user', $userInfo);
        return View::fetch('/edituser');
    }

    public function doedituser()
    {
        if (!session('?user')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }

        if (!token()) {
            return json(['code' => 0, 'msg' => '非法请求']);
        }

        $uid = $this->request->post('uid', session('user.uid'));
        $data = [
            'uname' => htmlspecialchars(trim($this->request->post('uname'))),
            'uiphone' => htmlspecialchars(trim($this->request->post('uiphone'))),
            'nname' => htmlspecialchars(trim($this->request->post('nname'))),
            'zname' => htmlspecialchars(trim($this->request->post('zname'))),
            'ustatus' => intval($this->request->post('ustatus', 0)),
            'etime' => date('Y-m-d H:i:s')
        ];

        $userModel = new \app\index\model\User();
        $result = $userModel->updateUser($uid, $data);

        if ($result) {
            $user = session('user');
            $user['uname'] = $data['uname'];
            $user['uiphone'] = $data['uiphone'];
            $user['nname'] = $data['nname'];
            $user['zname'] = $data['zname'];
            session('user', $user);
            return json(['code' => 1, 'msg' => '修改成功']);
        } else {
            return json(['code' => 0, 'msg' => '修改失败']);
        }
    }

    public function changepassword()
    {
        if (!session('?user')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }

        if (!token()) {
            return json(['code' => 0, 'msg' => '非法请求']);
        }

        $uid = session('user.uid');
        $oldPassword = $this->request->post('old_password');
        $newPassword = $this->request->post('new_password');
        $confirmPassword = $this->request->post('confirm_password');

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            return json(['code' => 0, 'msg' => '密码不能为空']);
        }

        if ($newPassword !== $confirmPassword) {
            return json(['code' => 0, 'msg' => '两次输入的新密码不一致']);
        }

        if (strlen($newPassword) < 6) {
            return json(['code' => 0, 'msg' => '新密码长度不能少于6位']);
        }

        $user = \think\facade\Db::name('qgusers')->where('uid', $uid)->find();
        if (!$user || md5($oldPassword) !== $user['upassword']) {
            return json(['code' => 0, 'msg' => '原密码错误']);
        }

        $result = \think\facade\Db::name('qgusers')
            ->where('uid', $uid)
            ->update([
                'upassword' => md5($newPassword),
                'etime' => date('Y-m-d H:i:s')
            ]);

        if ($result) {
            return json(['code' => 1, 'msg' => '密码修改成功']);
        } else {
            return json(['code' => 0, 'msg' => '密码修改失败']);
        }
    }

    public function clearCache()
    {
        if (!session('?user')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }

        try {
            $uid = session('user.uid');
            \think\facade\Log::info("用户[{$uid}]开始清除缓存");
            
            \think\facade\Cache::clear();
            \think\facade\Log::info('模板缓存已清除');
            
            $runtimePath = app()->getRuntimePath();
            $preserveDirs = ['session']; 
            
            $dirsToClear = ['cache', 'temp', 'log'];
            foreach ($dirsToClear as $dir) {
                $path = $runtimePath . $dir;
                if (is_dir($path)) {
                    $this->deleteDir($path);
                    \think\facade\Log::info("已清除目录: {$path}");
                }
            }
            
            if (function_exists('opcache_reset')) {
                opcache_reset();
                \think\facade\Log::info('OPcache已重置');
            }
            
            return json([
                'code' => 1, 
                'msg' => '缓存清除成功',
                'data' => [
                    'cleared' => $dirsToClear,
                    'preserved' => $preserveDirs
                ]
            ]);
        } catch (\Exception $e) {
            \think\facade\Log::error('清除缓存失败：' . $e->getMessage());
            return json(['code' => 0, 'msg' => '缓存清除失败: ' . $e->getMessage()]);
        }
    }

    private function deleteDir($path)
    {
        if (!is_dir($path)) {
            return;
        }
        
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $path . DIRECTORY_SEPARATOR . $file;
                is_dir($filePath) ? $this->deleteDir($filePath) : unlink($filePath);
            }
        }
        rmdir($path);
    }

    public function updateSystemSetting()
    {
        if (!session('?user')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }

        if (!token()) {
            return json(['code' => 0, 'msg' => '非法请求']);
        }

        if (!isset(session('user')['ifadm']) || session('user')['ifadm'] != 1) {
            return json(['code' => 0, 'msg' => '无权限操作']);
        }

        $field = $this->request->post('field');
        $value = $this->request->post($field === 'webtitle' ? 'sys_name' : $field);

        if ($field === 'regstate') {
            $value = intval($value);
        }

        \think\facade\Log::info('更新系统设置请求数据:', [
            'field' => $field,
            'value' => $value,
            'post_data' => $this->request->post()
        ]);

        $allowedFields = ['webtitle', 'regstate', 'mbname'];
        if (!in_array($field, $allowedFields)) {
            return json(['code' => 0, 'msg' => '不允许修改该字段']);
        }

        try {
            $result = \think\facade\Db::name('qgsystem')
                ->where('id', 1)
                ->update([
                    $field => $value
                ]);

            if ($result) {
                return json(['code' => 1, 'msg' => '修改成功']);
            } else {
                return json(['code' => 0, 'msg' => '修改失败']);
            }
        } catch (\Exception $e) {
            \think\facade\Log::error('更新系统设置失败: ' . $e->getMessage());
            return json(['code' => 0, 'msg' => '系统错误: ' . $e->getMessage()]);
        }
    }
}
