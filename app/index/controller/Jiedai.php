<?php
namespace app\index\controller;
use app\BaseController;
use think\facade\View;
use think\facade\Db;

class Jiedai extends BaseController
{
    public function index()
    {
        $uid = session('user.uid');
        
        $accounts = Db::name('qgaccount')
            ->where('uid', $uid)
            ->select()
            ->toArray();
        
        foreach ($accounts as &$account) {
            $account['raw_amoney'] = $account['amoney'];
            $account['amoney'] = number_format($account['amoney'], 2);
            $time = $account['etime'] ?: $account['ctime'];
            $account['year'] = date('Y年', strtotime($time));
            $account['month_day'] = date('m/d', strtotime($time));
        }
        
        $transfers = Db::name('qgtransfer')
            ->alias('t')
            ->join('qgaccount ca', 't.caid = ca.aid') 
            ->join('qgaccount ra', 't.raid = ra.aid') 
            ->where('t.uid', $uid)
            ->field('t.*, ca.aname as from_account_name, ca.alogo as from_account_logo, ra.aname as to_account_name, ra.alogo as to_account_logo')
            ->order('t.ttime', 'desc')
            ->select()
            ->toArray();
        
        foreach ($transfers as &$transfer) {
            $transfer['year'] = date('Y年', strtotime($transfer['ttime']));
            $transfer['month'] = date('m', strtotime($transfer['ttime']));
            $transfer['day'] = date('d', strtotime($transfer['ttime']));
            $transfer['formatted_amount'] = number_format($transfer['tmoney'], 2);
        }
        
        return View::fetch('/jiedai', [
            'accounts' => $accounts,
            'transfers' => $transfers
        ]);
    }

    public function addzichan()
    {
        return View::fetch('/addzichan');
    }

    public function addzhangwu()
    {
        $category = request()->param('category', 0);
        
        return View::fetch('/addzhangwu',[
            'category' => $category,
        ]);
    }
    public function save()
    {
        $data = input('post.');
        
        if (empty($data['aname'])) {
            return json(['code' => 400, 'msg' => '请填写账户名称']);
        }
        
        $data['anumber'] = $data['anumber'] ?? '0';
        $data['amoney'] = $data['amoney'] ?? '0';
        
        $data['ctime'] = date('Y-m-d H:i:s');
        
        try {
            $result = Db::name('qgaccount')->insert($data);
            if (!$result) {
                return json(['code' => 500, 'msg' => '添加失败']);
            }
            return json(['code' => 200, 'msg' => '添加成功', 'url' => url('/index/jiedai/index')]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '添加失败: ' . $e->getMessage()]);
        }
    }

    public function transfer()
    {
        if (!request()->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }

        $fromAid = input('post.from_aid');
        $toAid = input('post.to_aid');
        $amount = input('post.amount');
        $abstract = input('post.abstract', ''); 
        $ttime = input('post.ttime'); 

        if (empty($fromAid)) {
            return json(['code' => 400, 'msg' => '请选择转出账户']);
        }

        if (empty($toAid)) {
            return json(['code' => 400, 'msg' => '请选择转入账户']);
        }

        if (empty($amount) || $amount <= 0) {
            return json(['code' => 400, 'msg' => '请输入有效的转账金额']);
        }

        if (empty($ttime)) {
            return json(['code' => 400, 'msg' => '请选择转账时间']);
        }

        if ($fromAid == $toAid) {
            return json(['code' => 400, 'msg' => '转出账户和转入账户不能相同']);
        }

        $uid = session('user.uid');

        Db::startTrans();
        try {
            $fromAccount = Db::name('qgaccount')
                ->where(['aid' => $fromAid, 'uid' => $uid])
                ->find();

            if (!$fromAccount) {
                Db::rollback();
                return json(['code' => 404, 'msg' => '转出账户不存在']);
            }

            $toAccount = Db::name('qgaccount')
                ->where(['aid' => $toAid, 'uid' => $uid])
                ->find();

            if (!$toAccount) {
                Db::rollback();
                return json(['code' => 404, 'msg' => '转入账户不存在']);
            }

            if ($fromAccount['amoney'] < $amount) {
                Db::rollback();
                return json(['code' => 400, 'msg' => '转出账户余额不足']);
            }

            $fromUpdateResult = Db::name('qgaccount')
                ->where(['aid' => $fromAid, 'uid' => $uid])
                ->update([
                    'amoney' => $fromAccount['amoney'] - $amount,
                    'etime' => date('Y-m-d H:i:s')
                ]);

            if (!$fromUpdateResult) {
                Db::rollback();
                return json(['code' => 500, 'msg' => '更新转出账户失败']);
            }

            $toUpdateResult = Db::name('qgaccount')
                ->where(['aid' => $toAid, 'uid' => $uid])
                ->update([
                    'amoney' => $toAccount['amoney'] + $amount,
                    'etime' => date('Y-m-d H:i:s')
                ]);

            if (!$toUpdateResult) {
                Db::rollback();
                return json(['code' => 500, 'msg' => '更新转入账户失败']);
            }

            $transferRecord = [
                'uid' => $uid,           
                'caid' => $fromAid,      
                'raid' => $toAid,        
                'tmoney' => $amount,     
                'abstract' => $abstract, 
                'ttime' => $ttime,       
                'ctime' => date('Y-m-d H:i:s') 
            ];

            $transferResult = Db::name('qgtransfer')->insert($transferRecord);
            if (!$transferResult) {
                Db::rollback();
                return json(['code' => 500, 'msg' => '保存转账记录失败']);
            }

            Db::commit();
            return json(['code' => 200, 'msg' => '转账成功']);

        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => '转账失败: ' . $e->getMessage()]);
        }
    }

    public function getAccounts()
    {
        $uid = session('user.uid');
        
        $accounts = Db::name('qgaccount')
            ->where('uid', $uid)
            ->field('aid,aname,amoney')
            ->select()
            ->toArray();
        
        return json(['code' => 200, 'data' => $accounts]);
    }

    public function getAccountInfo()
    {
        $aid = input('aid');
        $uid = session('user.uid');
        
        if (empty($aid)) {
            return json(['code' => 400, 'msg' => '账户ID不能为空']);
        }
        
        $account = Db::name('qgaccount')
            ->where(['aid' => $aid, 'uid' => $uid])
            ->find();
            
        if (!$account) {
            return json(['code' => 404, 'msg' => '账户不存在']);
        }
        
        return json(['code' => 200, 'data' => $account]);
    }

    public function updateAccount()
    {
        if (!request()->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $data = input('post.');
        $uid = session('user.uid');
        
        if (empty($data['aid'])) {
            return json(['code' => 400, 'msg' => '账户ID不能为空']);
        }
        
        if (empty($data['aname'])) {
            return json(['code' => 400, 'msg' => '请填写账户名称']);
        }
        
        $account = Db::name('qgaccount')
            ->where(['aid' => $data['aid'], 'uid' => $uid])
            ->find();
            
        if (!$account) {
            return json(['code' => 404, 'msg' => '账户不存在']);
        }
        
        $updateData = [
            'aname' => $data['aname'],
            'alogo' => $data['alogo'] ?? $account['alogo'],
            'amoney' => $data['amoney'] ?? $account['amoney'],
            'etime' => date('Y-m-d H:i:s')
        ];
        
        try {
            $result = Db::name('qgaccount')
                ->where(['aid' => $data['aid'], 'uid' => $uid])
                ->update($updateData);
                
            if ($result) {
                return json(['code' => 200, 'msg' => '账户信息更新成功']);
            } else {
                return json(['code' => 500, 'msg' => '账户信息更新失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '更新失败: ' . $e->getMessage()]);
        }
    }

    public function deleteAccount()
    {
        if (!request()->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $aid = input('aid');
        $uid = session('user.uid');
        
        if (empty($aid)) {
            return json(['code' => 400, 'msg' => '账户ID不能为空']);
        }
        
        $account = Db::name('qgaccount')
            ->where(['aid' => $aid, 'uid' => $uid])
            ->find();
            
        if (!$account) {
            return json(['code' => 404, 'msg' => '账户不存在']);
        }
        
        $billCount = Db::name('qgbill')
            ->where('aid', $aid)
            ->count();
            
        if ($billCount > 0) {
            return json(['code' => 400, 'msg' => '该账户存在流水记录，无法删除']);
        }
        
        try {
            $result = Db::name('qgaccount')
                ->where(['aid' => $aid, 'uid' => $uid])
                ->delete();
                
            if ($result) {
                return json(['code' => 200, 'msg' => '账户删除成功']);
            } else {
                return json(['code' => 500, 'msg' => '账户删除失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '删除失败: ' . $e->getMessage()]);
        }
    }

    public function deleteTransfer()
    {
        if (!request()->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $tid = input('tid');
        $uid = session('user.uid');
        
        if (empty($tid)) {
            return json(['code' => 400, 'msg' => '转账记录ID不能为空']);
        }
        
        $transfer = Db::name('qgtransfer')
            ->where(['tid' => $tid, 'uid' => $uid])
            ->find();
            
        if (!$transfer) {
            return json(['code' => 404, 'msg' => '转账记录不存在']);
        }
        
        try {
            $result = Db::name('qgtransfer')
                ->where(['tid' => $tid, 'uid' => $uid])
                ->delete();
                
            if ($result) {
                return json(['code' => 200, 'msg' => '转账记录删除成功']);
            } else {
                return json(['code' => 500, 'msg' => '转账记录删除失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '删除失败: ' . $e->getMessage()]);
        }
    }
}
