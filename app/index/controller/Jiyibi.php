<?php
namespace app\index\controller;
use app\BaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;

class Jiyibi extends BaseController
{
    public function index()
    {
        $lname = get_ledger_info();
        
        if (!$lname) {
            return redirect('/index/index/login');
        }

        $uid = Session::get('uid');
        if (!$uid) {
            return redirect('/index/index/login');
        }

        $lid = input('lid', 0);
        if (!$lid) {
            return redirect('/index/zhangben/index');
        }

        View::assign([
            'lname' => $lname,
            'uid' => $uid,
            'lid' => $lid
        ]);

        $accounts = Db::name('qgaccount')
            ->where('uid', $uid)
            ->order('etime', 'desc')
            ->select();
        View::assign('accounts', $accounts);

        $expense_categories = Db::name('qgbill_sort')
            ->where([
                'lid' => $lid,
                'uid' => $uid,
                'szid' => 1, 
                'parentid' => 0
            ])
            ->order('sid', 'asc')
            ->select();
        View::assign('expense_categories', $expense_categories);

        $income_categories = Db::name('qgbill_sort')
            ->where([
                'lid' => $lid,
                'uid' => $uid,
                'szid' => 0, 
                'parentid' => 0
            ])
            ->order('sid', 'asc')
            ->select();
        View::assign('income_categories', $income_categories);

        $expense_subcategories = [];
        foreach ($expense_categories as $category) {
            $subcategories = Db::name('qgbill_sort')
                ->where([
                    'lid' => $lid,
                    'uid' => $uid,
                    'szid' => 1, 
                    'parentid' => $category['sid']
                ])
                ->order('sid', 'asc')
                ->select();
            $expense_subcategories[$category['sid']] = $subcategories;
        }
        View::assign('expense_subcategories', $expense_subcategories);

        $income_subcategories = [];
        foreach ($income_categories as $category) {
            $subcategories = Db::name('qgbill_sort')
                ->where([
                    'lid' => $lid,
                    'uid' => $uid,
                    'szid' => 0, 
                    'parentid' => $category['sid']
                ])
                ->order('sid', 'asc')
                ->select();
            $income_subcategories[$category['sid']] = $subcategories;
        }
        View::assign('income_subcategories', $income_subcategories);

        $current_date = date('Y-m-d');
        View::assign('current_date', $current_date);

        $current_time = date('Y-m-d H:i:s');
        View::assign('current_time', $current_time);

        return View::fetch('/jiyibi');
    }

    public function submit()
    {
        try {
            $data = input();
            
            if (isset($data['zszid'])) {
                $form_type = 'expense';
                $required_fields = ['zlid', 'zuid', 'zsid', 'zbtime', 'zctime', 'zaccount', 'zabstract', 'zmoney'];
                foreach ($required_fields as $field) {
                    if (!isset($data[$field])) {
                        return json(['code' => 0, 'msg' => '表单数据不完整：缺少 ' . $field]);
                    }
                }
                $bill_data = [
                    'szid' => $data['zszid'],
                    'lid' => $data['zlid'],
                    'uid' => $data['zuid'],
                    'sid' => $data['zsid'],
                    'btime' => $data['zbtime'],
                    'ctime' => $data['zctime'],
                    'aid' => $data['zaccount'],
                    'abstract' => $data['zabstract'],
                    'money' => $data['zmoney']
                ];
                $money = floatval($data['zmoney']);
                $account_id = $data['zaccount'];
            } elseif (isset($data['sszid'])) {
                $form_type = 'income';
                $required_fields = ['slid', 'suid', 'ssid', 'sbtime', 'sctime', 'saccount', 'sabstract', 'smoney'];
                foreach ($required_fields as $field) {
                    if (!isset($data[$field])) {
                        return json(['code' => 0, 'msg' => '表单数据不完整：缺少 ' . $field]);
                    }
                }
                $bill_data = [
                    'szid' => $data['sszid'],
                    'lid' => $data['slid'],
                    'uid' => $data['suid'],
                    'sid' => $data['ssid'],
                    'btime' => $data['sbtime'],
                    'ctime' => $data['sctime'],
                    'aid' => $data['saccount'],
                    'abstract' => $data['sabstract'],
                    'money' => $data['smoney']
                ];
                $money = floatval($data['smoney']);
                $account_id = $data['saccount'];
            } else {
                return json(['code' => 0, 'msg' => '表单类型错误']);
            }

            if (empty($bill_data['sid'])) {
                return json(['code' => 0, 'msg' => '请选择分类或创建分类']);
            }

            if (empty($bill_data['aid']) || $bill_data['aid'] == 0) {
                return json(['code' => 0, 'msg' => '请选择资产账户或创建资产账户']);
            }

            if (!empty($bill_data['abstract']) && mb_strlen($bill_data['abstract'], 'utf-8') > 20) {
                return json(['code' => 0, 'msg' => '备注字数不能超过20个中文字']);
            }

            Db::startTrans();
            try {
                $bill_id = Db::name('qgbill')->insertGetId($bill_data);
                
                if (!$bill_id) {
                    throw new \Exception('账单记录插入失败');
                }

                $account = Db::name('qgaccount')->where('aid', $account_id)->find();
                if (!$account) {
                    throw new \Exception('资产账户不存在');
                }

                if ($form_type === 'expense') {
                    $new_balance = $account['amoney'] - $money;
                } else {
                    $new_balance = $account['amoney'] + $money;
                }

                $update_result = Db::name('qgaccount')
                    ->where('aid', $account_id)
                    ->update([
                        'amoney' => $new_balance,
                        'etime' => $bill_data['ctime']
                    ]);

                if (!$update_result) {
                    throw new \Exception('资产账户更新失败');
                }

                Db::commit();

                if (isset($data['action']) && $data['action'] === 'again') {
                    return json(['code' => 1, 'msg' => '保存成功', 'type' => 'refresh']);
                } else {
                    $lid = $bill_data['lid'];
                    $redirectUrl = '/index/liushui/index?lid=' . $lid;
                    return json(['code' => 1, 'msg' => '保存成功', 'type' => 'redirect', 'url' => $redirectUrl]);
                }

            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => 0, 'msg' => '保存失败：' . $e->getMessage()]);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '系统错误：' . $e->getMessage()]);
        }
    }
}
