<?php
namespace app\index\controller;
use app\BaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;

class Zhangben extends BaseController
{
    public function index()
    {
        $uid = Session::get('user.uid');
        
        $ledgers = Db::name('qgledger')
            ->where('uid', $uid)
            ->field('lid, uid, lname, lsubtitle, ctime')
            ->select()
            ->toArray();
        
        foreach ($ledgers as &$ledger) {
            $billCount = Db::name('qgbill')
                ->where('lid', $ledger['lid'])
                ->count();
            $sortCount = Db::name('qgbill_sort')
                ->where('lid', $ledger['lid'])
                ->count();
            
            $ledger['hasRecords'] = ($billCount > 0 || $sortCount > 0);
        }
        
        View::assign([
            'ledgers' => $ledgers,
            'uid' => $uid
        ]);
        
        return View::fetch('/zhangben');
    }
        
    public function add()
    {
        try {
            if ($this->request->isPost()) {
                $uid = Session::get('user.uid');
                $lname = $this->request->post('lname');
                $lsubtitle = $this->request->post('lsubtitle');
                
                if (empty($uid) || empty($lname)) {
                    return json(['code' => 0, 'msg' => '参数不完整']);
                }
                
                $data = [
                    'uid' => $uid,
                    'lname' => $lname,
                    'lsubtitle' => $lsubtitle ?: '',
                    'ctime' => date('Y-m-d H:i:s')
                ];
                
                $result = Db::name('qgledger')->insert($data);
                
                if ($result) {
                    return json(['code' => 1, 'msg' => '添加成功']);
                } else {
                    return json(['code' => 0, 'msg' => '添加失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '服务器错误: ' . $e->getMessage()]);
        }
    }
    public function zbsort()
    {
        $lid = $this->request->param('lid', 0);
        
        $uid = Session::get('user.uid');
        
        $ledger = Db::name('qgledger')
            ->where('lid', $lid)
            ->field('lname')
            ->find();
        
        $ledgerName = $ledger ? $ledger['lname'] : '账本名';
        
        $zhiChuSorts = Db::name('qgbill_sort')
            ->where('lid', $lid)
            ->where('szid', 1)
            ->where('parentid', 0)
            ->field('sid, sname')
            ->order('sid', 'asc')
            ->select()
            ->toArray();
        
        $shouRuSorts = Db::name('qgbill_sort')
            ->where('lid', $lid)
            ->where('szid', 0)
            ->where('parentid', 0)
            ->field('sid, sname')
            ->order('sid', 'asc')
            ->select()
            ->toArray();
        
        $zhiChuCategories = Db::name('qgbill_sort')
            ->where('lid', $lid)
            ->where('uid', $uid)
            ->where('szid', 1)
            ->where('parentid', 0)
            ->field('sid, sname, sicon')
            ->order('sid', 'asc')
            ->select()
            ->toArray();
        
        $shouRuCategories = Db::name('qgbill_sort')
            ->where('lid', $lid)
            ->where('uid', $uid)
            ->where('szid', 0)
            ->where('parentid', 0)
            ->field('sid, sname, sicon')
            ->order('sid', 'asc')
            ->select()
            ->toArray();
        
        $allSubCategories = Db::name('qgbill_sort')
            ->where('lid', $lid)
            ->where('uid', $uid)
            ->where('parentid', '>', 0)
            ->field('sid, sname, sicon, parentid')
            ->order('sid', 'asc')
            ->select()
            ->toArray();
        
        foreach ($zhiChuCategories as &$category) {
            $hasSubcategories = false;
            foreach ($allSubCategories as $subcategory) {
                if ($subcategory['parentid'] == $category['sid']) {
                    $hasSubcategories = true;
                    break;
                }
            }
            $category['hasSubcategories'] = $hasSubcategories;
            
            $billCount = Db::name('qgbill')
                ->where('sid', $category['sid'])
                ->count();
            $category['hasBills'] = $billCount > 0;
        }
        
        foreach ($shouRuCategories as &$category) {
            $hasSubcategories = false;
            foreach ($allSubCategories as $subcategory) {
                if ($subcategory['parentid'] == $category['sid']) {
                    $hasSubcategories = true;
                    break;
                }
            }
            $category['hasSubcategories'] = $hasSubcategories;
            
            $billCount = Db::name('qgbill')
                ->where('sid', $category['sid'])
                ->count();
            $category['hasBills'] = $billCount > 0;
        }
        
        foreach ($allSubCategories as &$subcategory) {
            $billCount = Db::name('qgbill')
                ->where('sid', $subcategory['sid'])
                ->count();
            $subcategory['hasBills'] = $billCount > 0;
        }
        
        View::assign([
            'lid' => $lid,
            'uid' => $uid,
            'ledgerName' => $ledgerName,
            'zhiChuSorts' => $zhiChuSorts,
            'shouRuSorts' => $shouRuSorts,
            'zhiChuCategories' => $zhiChuCategories,
            'shouRuCategories' => $shouRuCategories,
            'allSubCategories' => $allSubCategories
        ]);
        
        return View::fetch('/zbsort');
    }
    
    public function addZhiChuSort()
    {
        try {
            if ($this->request->isPost()) {
                $zpid = $this->request->post('zpid', 0);
                $zlid = $this->request->post('zlid', 0);
                $zuid = $this->request->post('zuid', 0);
                $zszid = $this->request->post('zszid', 0);
                $zsname = $this->request->post('zsname', '');
                $zsicon = $this->request->post('zsicon', '');
                
                if (empty($zsname)) {
                    return json(['code' => 0, 'msg' => '名称不能为空']);
                }
                
                if (mb_strlen($zsname, 'utf-8') > 4) {
                    return json(['code' => 0, 'msg' => '名称最多4个汉字']);
                }
                
                if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', $zsname)) {
                    return json(['code' => 0, 'msg' => '名称不能包含特殊符号']);
                }
                
                $data = [
                    'parentid' => $zpid,
                    'lid' => $zlid,
                    'uid' => $zuid,
                    'szid' => $zszid,
                    'sname' => $zsname,
                    'sicon' => $zsicon
                ];
                
                $result = Db::name('qgbill_sort')->insert($data);
                
                if ($result) {
                    return json(['code' => 1, 'msg' => '']);
                } else {
                    return json(['code' => 0, 'msg' => '添加失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '服务器错误: ' . $e->getMessage()]);
        }
    }
    
    public function addShouRuSort()
    {
        try {
            if ($this->request->isPost()) {
                $spid = $this->request->post('spid', 0);
                $slid = $this->request->post('slid', 0);
                $suid = $this->request->post('suid', 0);
                $sszid = $this->request->post('sszid', 0);
                $ssname = $this->request->post('ssname', '');
                $ssicon = $this->request->post('ssicon', '');
                
                if (empty($ssname)) {
                    return json(['code' => 0, 'msg' => '名称不能为空']);
                }
                
                if (mb_strlen($ssname, 'utf-8') > 4) {
                    return json(['code' => 0, 'msg' => '名称最多4个汉字']);
                }
                
                if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', $ssname)) {
                    return json(['code' => 0, 'msg' => '名称不能包含特殊符号']);
                }
                
                $data = [
                    'parentid' => $spid,
                    'lid' => $slid,
                    'uid' => $suid,
                    'szid' => $sszid,
                    'sname' => $ssname,
                    'sicon' => $ssicon
                ];
                
                $result = Db::name('qgbill_sort')->insert($data);
                
                if ($result) {
                    return json(['code' => 1, 'msg' => '']);
                } else {
                    return json(['code' => 0, 'msg' => '添加失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '服务器错误: ' . $e->getMessage()]);
        }
    }
    
    public function deleteSort()
    {
        try {
            if ($this->request->isPost()) {
                $sid = $this->request->post('sid', 0);
                $type = $this->request->post('type', ''); 
                
                if (empty($sid)) {
                    return json(['code' => 0, 'msg' => '参数错误']);
                }
                
                $sort = Db::name('qgbill_sort')->where('sid', $sid)->find();
                if (!$sort) {
                    return json(['code' => 0, 'msg' => '分类不存在']);
                }
                
                $billCount = Db::name('qgbill')
                    ->where('sid', $sid)
                    ->count();
                
                if ($billCount > 0) {
                    return json(['code' => 0, 'msg' => '该分类下有账单记录，无法删除']);
                }
                
                if ($type === 'category') {
                    $subcategories = Db::name('qgbill_sort')
                        ->where('parentid', $sid)
                        ->count();
                    
                    if ($subcategories > 0) {
                        return json(['code' => 0, 'msg' => '该分类下有二级分类，无法删除']);
                    }
                }
                
                $result = Db::name('qgbill_sort')->where('sid', $sid)->delete();
                
                if ($result) {
                    return json(['code' => 1, 'msg' => '删除成功']);
                } else {
                    return json(['code' => 0, 'msg' => '删除失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '服务器错误: ' . $e->getMessage()]);
        }
    }
    
    public function edit()
    {
        try {
            if ($this->request->isPost()) {
                $lid = $this->request->post('lid', 0);
                $lname = $this->request->post('lname');
                $lsubtitle = $this->request->post('lsubtitle');
                
                if (empty($lid) || empty($lname)) {
                    return json(['code' => 0, 'msg' => '参数不完整']);
                }
                
                $ledger = Db::name('qgledger')->where('lid', $lid)->find();
                if (!$ledger) {
                    return json(['code' => 0, 'msg' => '账本不存在']);
                }
                
                $data = [
                    'lname' => $lname,
                    'lsubtitle' => $lsubtitle ?: '',
                    'etime' => date('Y-m-d H:i:s')
                ];
                
                $result = Db::name('qgledger')->where('lid', $lid)->update($data);
                
                if ($result !== false) {
                    return json(['code' => 1, 'msg' => '修改成功']);
                } else {
                    return json(['code' => 0, 'msg' => '修改失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '服务器错误: ' . $e->getMessage()]);
        }
    }
    
    public function delete()
    {
        try {
            if ($this->request->isPost()) {
                $lid = $this->request->post('lid', 0);
                
                if (empty($lid)) {
                    return json(['code' => 0, 'msg' => '参数错误']);
                }
                
                $ledger = Db::name('qgledger')->where('lid', $lid)->find();
                if (!$ledger) {
                    return json(['code' => 0, 'msg' => '账本不存在']);
                }
                
                $billCount = Db::name('qgbill')
                    ->where('lid', $lid)
                    ->count();
                
                if ($billCount > 0) {
                    return json(['code' => 0, 'msg' => '该账本下有账单记录，无法删除']);
                }
                
                $sortCount = Db::name('qgbill_sort')
                    ->where('lid', $lid)
                    ->count();
                
                if ($sortCount > 0) {
                    return json(['code' => 0, 'msg' => '该账本下有分类记录，无法删除']);
                }
                
                $result = Db::name('qgledger')->where('lid', $lid)->delete();
                
                if ($result) {
                    return json(['code' => 1, 'msg' => '删除成功']);
                } else {
                    return json(['code' => 0, 'msg' => '删除失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '服务器错误: ' . $e->getMessage()]);
        }
    }
    
}
