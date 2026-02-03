<?php
namespace app\index\controller;
use app\BaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;

class Tongji extends BaseController
{
    public function index()
    {
        $lname = get_ledger_info();
        
        if (!$lname) {
            return redirect('/index/index/login');
        }
        
        View::assign('lname', $lname);
        
        return View::fetch('/tongji');
    }
    
    private function buildTimeCondition($timeCondition)
    {
        if (empty($timeCondition)) {
            return '1=1';
        }
        
        $conditions = [];
        foreach ($timeCondition as $condition) {
            if (count($condition) === 3) {
                $conditions[] = "b.{$condition[0]} {$condition[1]} '{$condition[2]}'";
            }
        }
        
        return implode(' AND ', $conditions);
    }
    
    public function getStatsData()
    {
        try {
            $year = Request::param('year', date('Y'));
            $month = Request::param('month', 'all');
            $lid = get_ledger_id();
            $uid = session('user.uid');
            
            if (!$lid || !$uid) {
                return json(['code' => 0, 'msg' => '参数错误']);
            }
            
            $timeCondition = [];
            if ($month !== 'all') {
                $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                $endDate = date('Y-m-t', strtotime($startDate));
                $timeCondition = [
                    ['btime', '>=', $startDate],
                    ['btime', '<=', $endDate . ' 23:59:59']
                ];
            } else {
                $timeCondition = [
                    ['btime', '>=', $year . '-01-01'],
                    ['btime', '<=', $year . '-12-31 23:59:59']
                ];
            }
            
            $totalIncome = Db::name('qgbill')
                ->where('lid', $lid)
                ->where('uid', $uid)
                ->where('szid', 0) // 收入
                ->where($timeCondition)
                ->sum('money');
            
            $totalExpense = Db::name('qgbill')
                ->where('lid', $lid)
                ->where('uid', $uid)
                ->where('szid', 1) // 支出
                ->where($timeCondition)
                ->sum('money');
            
            $totalBalance = $totalIncome - $totalExpense;
            
            $expenseLevel1Categories = Db::name('qgbill_sort')
                ->where('lid', $lid)
                ->where('szid', 1) 
                ->where('parentid', 0) 
                ->field('sid, sname')
                ->select();
            
            $expenseLevel1 = [];
            foreach ($expenseLevel1Categories as $category) {
                $subCategoryIds = Db::name('qgbill_sort')
                    ->where('lid', $lid)
                    ->where('szid', 1) 
                    ->where('parentid', $category['sid']) 
                    ->column('sid');
                
                $total = 0;
                if (!empty($subCategoryIds)) {
                    $total = Db::name('qgbill')
                        ->where('lid', $lid)
                        ->where('uid', $uid)
                        ->where('szid', 1) 
                        ->where('sid', 'in', $subCategoryIds)
                        ->where($timeCondition)
                        ->sum('money') ?: 0;
                }
                
                $expenseLevel1[] = [
                    'sname' => $category['sname'],
                    'total' => floatval($total)
                ];
            }
            
            $expenseLevel2 = Db::name('qgbill_sort')
                ->alias('s')
                ->leftJoin('qgbill b', 's.sid = b.sid AND b.lid = ' . $lid . ' AND b.uid = ' . $uid . ' AND b.szid = 1 AND ' . $this->buildTimeCondition($timeCondition))
                ->where('s.lid', $lid)
                ->where('s.szid', 1) 
                ->where('s.parentid', '>', 0) 
                ->field('s.sname, COALESCE(SUM(b.money), 0) as total')
                ->group('s.sid, s.sname')
                ->select();
            
            $incomeLevel1Categories = Db::name('qgbill_sort')
                ->where('lid', $lid)
                ->where('szid', 0) 
                ->where('parentid', 0) 
                ->field('sid, sname')
                ->select();
            
            $incomeLevel1 = [];
            foreach ($incomeLevel1Categories as $category) {
                $subCategoryIds = Db::name('qgbill_sort')
                    ->where('lid', $lid)
                    ->where('szid', 0)
                    ->where('parentid', $category['sid']) 
                    ->column('sid');
                
                $total = 0;
                if (!empty($subCategoryIds)) {
                    $total = Db::name('qgbill')
                        ->where('lid', $lid)
                        ->where('uid', $uid)
                        ->where('szid', 0)
                        ->where('sid', 'in', $subCategoryIds)
                        ->where($timeCondition)
                        ->sum('money') ?: 0;
                }
                
                $incomeLevel1[] = [
                    'sname' => $category['sname'],
                    'total' => floatval($total)
                ];
            }
            
            $incomeLevel2 = Db::name('qgbill_sort')
                ->alias('s')
                ->leftJoin('qgbill b', 's.sid = b.sid AND b.lid = ' . $lid . ' AND b.uid = ' . $uid . ' AND b.szid = 0 AND ' . $this->buildTimeCondition($timeCondition))
                ->where('s.lid', $lid)
                ->where('s.szid', 0) 
                ->where('s.parentid', '>', 0) 
                ->field('s.sname, COALESCE(SUM(b.money), 0) as total')
                ->group('s.sid, s.sname')
                ->select();
            
            return json([
                'code' => 1,
                'data' => [
                    'totalIncome' => floatval($totalIncome ?: 0),
                    'totalExpense' => floatval($totalExpense ?: 0),
                    'totalBalance' => floatval($totalBalance ?: 0),
                    'expenseLevel1' => $expenseLevel1,
                    'expenseLevel2' => $expenseLevel2,
                    'incomeLevel1' => $incomeLevel1,
                    'incomeLevel2' => $incomeLevel2
                ]
            ]);
            
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '获取数据失败: ' . $e->getMessage()]);
        }
    }
    
    public function getLevel2Data()
    {
        try {
            $year = Request::param('year', date('Y'));
            $month = Request::param('month', 'all');
            $level1Name = Request::param('level1Name');
            $tabType = Request::param('tabType'); 
            $lid = get_ledger_id();
            $uid = session('user.uid');
            
            if (!$lid || !$uid || !$level1Name || !$tabType) {
                return json(['code' => 0, 'msg' => '参数错误']);
            }
            
            $timeCondition = [];
            if ($month !== 'all') {
                $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                $endDate = date('Y-m-t', strtotime($startDate));
                $timeCondition = [
                    ['btime', '>=', $startDate],
                    ['btime', '<=', $endDate . ' 23:59:59']
                ];
            } else {
                $timeCondition = [
                    ['btime', '>=', $year . '-01-01'],
                    ['btime', '<=', $year . '-12-31 23:59:59']
                ];
            }
            
            $szid = $tabType === 'expense' ? 1 : 0;
            
            $level1Category = Db::name('qgbill_sort')
                ->where('lid', $lid)
                ->where('szid', $szid)
                ->where('parentid', 0)
                ->where('sname', $level1Name)
                ->field('sid')
                ->find();
            
            if (!$level1Category) {
                return json(['code' => 0, 'msg' => '一级分类不存在']);
            }
            
            $level2Categories = Db::name('qgbill_sort')
                ->where('lid', $lid)
                ->where('szid', $szid)
                ->where('parentid', $level1Category['sid'])
                ->field('sid, sname')
                ->select();
            
            $level2Data = [];
            foreach ($level2Categories as $category) {
                $total = Db::name('qgbill')
                    ->where('lid', $lid)
                    ->where('uid', $uid)
                    ->where('szid', $szid)
                    ->where('sid', $category['sid'])
                    ->where($timeCondition)
                    ->sum('money') ?: 0;
                
                $level2Data[] = [
                    'name' => $category['sname'],
                    'value' => floatval($total)
                ];
            }
            
            return json([
                'code' => 1,
                'data' => $level2Data
            ]);
            
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '获取二级分类数据失败: ' . $e->getMessage()]);
        }
    }
}
