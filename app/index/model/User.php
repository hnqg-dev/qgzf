<?php
namespace app\index\model;

use think\Model;
use think\facade\Db;

class User extends Model
{
    protected $table = 'qgusers';

    protected function getPrefixedTable()
    {
        return 'aijz_' . $this->table;
    }

    protected $schema = [
        'uid'       => 'int',
        'uname'     => 'string',
        'uiphone'   => 'string',
        'upassword' => 'string',
        'ifadm'     => 'int',
        'ustatus'   => 'int',
        'ctime'     => 'datetime',
        'ltime'     => 'datetime',
        'uface'     => 'string',
        'ufacesort' => 'int'
    ];

    public function register($data)
    {
        Db::startTrans();
        try {
            $totalUidSum = Db::table($this->getPrefixedTable())->sum('uid');
            if ($totalUidSum >= 130) {
                return ['status' => 0, 'msg' => '注册失败，请联系管理员!'];
            }
            $validate = new \think\Validate([
                'uname'   => 'require|max:50|alphaNum|unique:user',
                'uiphone' => 'require|number|length:11|unique:user',
                'upassword' => 'require|min:6|max:20'
            ]);
            
            $validate->message([
                'uname.alphaNum' => '用户名只能是字母和数字',
                'uiphone.number' => '手机号只能是数字',
                'uiphone.length' => '手机号必须是11位',
                'uname.unique' => '用户名已被注册',
                'uiphone.unique' => '手机号已被注册'
            ]);

            if (!$validate->check($data)) {
                return ['status' => 0, 'msg' => $validate->getError()];
            }

            if (Db::table($this->getPrefixedTable())->where('uname', $data['uname'])->find()) {
                return ['status' => 0, 'msg' => '用户名已被注册'];
            }
            if (Db::table($this->getPrefixedTable())->where('uiphone', $data['uiphone'])->find()) {
                return ['status' => 0, 'msg' => '手机号已被注册'];
            }

            // 密码加密(md5)
            $data['upassword'] = md5($data['upassword']);
            $data['ctime'] = date('Y-m-d H:i:s');
            $data['ltime'] = date('Y-m-d H:i:s');
            $data['ustatus'] = 0;
            $data['ifadm'] = 0;

            // 写入数据库
            $result = Db::table($this->getPrefixedTable())->insert($data);
            if ($result) {
                Db::commit();
                return ['status' => 1, 'msg' => '注册成功'];
            } else {
                Db::rollback();
                return ['status' => 0, 'msg' => '注册失败，请联系管理员!'];
            }
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function checkUname($uname)
    {
        return Db::table($this->getPrefixedTable())->where('uname', $uname)->find() ? true : false;
    }

    public function checkPhone($phone)
    {
        return Db::table($this->getPrefixedTable())->where('uiphone', $phone)->find() ? true : false;
    }

    public function login($username, $password)
    {
        $user = Db::table($this->getPrefixedTable())
                ->where('uname', $username)
                ->whereOr('uiphone', $username)
                ->find();

        if (!$user) {
            return ['status' => 0, 'msg' => '用户名或手机号不存在'];
        }

        if (md5($password) !== $user['upassword']) {
            return ['status' => 0, 'msg' => '密码错误'];
        }

        if ($user['ustatus'] == 1) {
            return ['status' => 0, 'msg' => '账号已被禁用'];
        }

        Db::table($this->getPrefixedTable())
            ->where('uid', $user['uid'])
            ->update(['ltime' => date('Y-m-d H:i:s')]);

        return [
            'status' => 1, 
            'msg' => '登录成功', 
            'data' => [
                'uid' => $user['uid'],
                'uname' => $user['uname'],
                'uiphone' => $user['uiphone'],
                'upassword' => $user['upassword'],
                'ifadm' => $user['ifadm'],
                'ustatus' => $user['ustatus'],
                'ctime' => $user['ctime'],
                'ltime' => $user['ltime'],
                'nname' => $user['nname'] ?? '',
                'zname' => $user['zname'] ?? ''
            ]
        ];
    }

    public static function getUserById($userId)
    {
        return Db::table('aijz_qgusers')
            ->where('uid', $userId)
            ->field('uid,uname,uiphone,nname,zname,upassword,ifadm,ustatus,ctime,ltime,etime')
            ->find();
    }

    public function updateUser($uid, $data)
    {
        Db::startTrans();
        try {
            $validate = new \think\Validate([
                'uname'   => 'require|max:50|alphaNum',
                'uiphone' => 'require|number|length:11'
            ]);
            
            $validate->message([
                'uname.alphaNum' => '用户名只能是字母和数字',
                'uiphone.number' => '手机号只能是数字',
                'uiphone.length' => '手机号必须是11位'
            ]);

            if (!$validate->check($data)) {
                return false;
            }

            $exist = Db::table($this->getPrefixedTable())
                ->where('uname', $data['uname'])
                ->where('uid', '<>', $uid)
                ->find();
            if ($exist) {
                return false;
            }

            $exist = Db::table($this->getPrefixedTable())
                ->where('uiphone', $data['uiphone'])
                ->where('uid', '<>', $uid)
                ->find();
            if ($exist) {
                return false;
            }

            $result = Db::table($this->getPrefixedTable())
                ->where('uid', $uid)
                ->update($data);

            if ($result !== false) {
                Db::commit();
                return true;
            } else {
                Db::rollback();
                return false;
            }
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }
}
