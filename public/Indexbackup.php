<?php
namespace app\index\controller;
use app\BaseController;
use app\index\Base;
use think\facade\View;
use think\facade\Session;
use app\index\model\User;
use app\index\model\Qgledger;
use app\index\model\Qgaccount;

class Index extends BaseController
{
    protected function initialize()
    {
        (new Base())->verifyAppAccess();
    }

    
    public function index()
    {
        if (!session('?user')) {
            return redirect('/index/index/login');
        }
        
        $userId = session('uid');
        
        try {
            $ledgerCount = \think\facade\Db::name('qgledger')
                ->where('uid', $userId)
                ->count();
        } catch (\Exception $e) {
            \think\facade\Log::error('查询账本数量失败: ' . $e->getMessage());
            $ledgerCount = 0;
        }
        
        try {
            $accountCount = \think\facade\Db::name('qgaccount')
                ->where('uid', $userId)
                ->count();
        } catch (\Exception $e) {
            \think\facade\Log::error('查询账户数量失败: ' . $e->getMessage());
            $accountCount = 0;
        }
        
        View::assign([
            'ledgerCount' => $ledgerCount,
            'accountCount' => $accountCount
        ]);
        
        return View::fetch('/index');
    }

    public function login()
    {
        if ($this->request->isPost()) {
            if (!token()) {
                return json(['status' => 0, 'msg' => '非法请求']);
            }

            $username = htmlspecialchars(trim($this->request->post('username')));
            $password = $this->request->post('password');
            $remember = (int)$this->request->post('remember', 0);

            $ip = $this->request->ip();
            $key = 'login_attempts_' . $ip;
            $attempts = cache($key) ?: 0;
            
            if ($attempts >= 5) {
                return json(['status' => 0, 'msg' => '尝试次数过多，请稍后再试']);
            }

            $userModel = new User();
            $result = $userModel->login($username, $password);

            if ($result['status'] === 1) {
                cache($key, null);
                $userSessionData = [
                    'uid' => $result['data']['uid'],
                    'uname' => $result['data']['uname'],
                    'uiphone' => $result['data']['uiphone'],
                    'ifadm' => $result['data']['ifadm'] ?? 0,
                    'nname' => $result['data']['nname'] ?? '',
                    'zname' => $result['data']['zname'] ?? ''
                ];
                session('user', $userSessionData);
                session('uid', $userSessionData['uid']);
                if ($remember) {
                    $token = md5(uniqid());
                    $userData = $result['data'];
                    Session::set('remember_token', $token);
                    $userModel->where('uid', $userData['uid'])->update(['remember_token' => $token]);
                }
                return json(['status' => 1, 'msg' => '登录成功', 'url' => '/']);
            } else {
                cache($key, $attempts + 1, 3600);
                return json(['status' => 0, 'msg' => $result['msg']]);
            }
        }
        return View::fetch('/login');
    }

    public function register()
    {
        if ($this->request->isPost()) {
            if (!token()) {
                return json(['status' => 0, 'msg' => '非法请求']);
            }

            $data = [
                'uname' => htmlspecialchars(trim($this->request->post('uname'))),
                'uiphone' => htmlspecialchars(trim($this->request->post('uiphone'))),
                'upassword' => $this->request->post('upassword')
            ];
            $ip = $this->request->ip();
            $key = 'register_attempts_' . $ip;
            $attempts = cache($key) ?: 0;
            
            if ($attempts >= 3) {
                return json(['status' => 0, 'msg' => '注册尝试次数过多，请稍后再试']);
            }
            $userModel = new User();
            $result = $userModel->register($data);
            
            if ($result['status'] === 1) {
                cache($key, null);
                try {
                    $faceDir = public_path() . 'upload/face/' . $data['uname'];
                    $pzimgDir = public_path() . 'upload/pzimg/' . $data['uname'];
                    if (!is_dir($faceDir)) {
                        mkdir($faceDir, 0777, true);
                    }
                    if (!is_dir($pzimgDir)) {
                        mkdir($pzimgDir, 0777, true);
                    }
                } catch (\Exception $e) {
                    \think\facade\Log::error('创建用户文件夹失败: ' . $e->getMessage());
                }
            } else {
                cache($key, $attempts + 1, 3600);
            }
            
            return json($result);
        }
        
        return View::fetch('/reg');
    }

}
