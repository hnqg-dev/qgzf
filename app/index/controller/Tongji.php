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
            
            $buildQuery = function($szid) use ($lid, $uid, $year, $month) {
                $query = Db::name('qgbill')
                    ->where('lid', $lid)
                    ->where('uid', $uid)
                    ->where('szid', $szid);
                
                if ($year !== '所有年份' && !empty($year)) {
                    if ($month !== 'all') {
                        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                        $endDate = date('Y-m-t', strtotime($startDate));
                        $query->where('btime', '>=', $startDate)
                              ->where('btime', '<=', $endDate . ' 23:59:59');
                    } else {
                        $query->where('btime', '>=', $year . '-01-01')
                              ->where('btime', '<=', $year . '-12-31 23:59:59');
                    }
                }
                
                return $query;
            };
            
            $totalIncome = $buildQuery(0)->sum('money');
            $totalExpense = $buildQuery(1)->sum('money');
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
                    $query = $buildQuery(1);
                    $total = $query->where('sid', 'in', $subCategoryIds)->sum('money') ?: 0;
                }
                
                $expenseLevel1[] = [
                    'sname' => $category['sname'],
                    'total' => round(floatval($total), 2)
                ];
            }
            
            $expenseLevel2Categories = Db::name('qgbill_sort')
                ->where('lid', $lid)
                ->where('szid', 1) 
                ->where('parentid', '>', 0) 
                ->field('sid, sname')
                ->select();
            
            $expenseLevel2 = [];
            foreach ($expenseLevel2Categories as $category) {
                $total = $buildQuery(1)->where('sid', $category['sid'])->sum('money') ?: 0;
                
                $expenseLevel2[] = [
                    'sname' => $category['sname'],
                    'total' => round(floatval($total), 2)
                ];
            }
            
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
                    $query = $buildQuery(0);
                    $total = $query->where('sid', 'in', $subCategoryIds)->sum('money') ?: 0;
                }
                
                $incomeLevel1[] = [
                    'sname' => $category['sname'],
                    'total' => round(floatval($total), 2)
                ];
            }
            
            $incomeLevel2Categories = Db::name('qgbill_sort')
                ->where('lid', $lid)
                ->where('szid', 0) 
                ->where('parentid', '>', 0) 
                ->field('sid, sname')
                ->select();
            
            $incomeLevel2 = [];
            foreach ($incomeLevel2Categories as $category) {
                $total = $buildQuery(0)->where('sid', $category['sid'])->sum('money') ?: 0;
                
                $incomeLevel2[] = [
                    'sname' => $category['sname'],
                    'total' => round(floatval($total), 2)
                ];
            }
            
            return json([
                'code' => 1,
                'data' => [
                    'totalIncome' => round(floatval($totalIncome ?: 0), 2),
                    'totalExpense' => round(floatval($totalExpense ?: 0), 2),
                    'totalBalance' => round(floatval($totalBalance ?: 0), 2),
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
            
            $szid = $tabType === 'expense' ? 1 : 0;
            
            $buildQuery = function() use ($lid, $uid, $szid, $year, $month) {
                $query = Db::name('qgbill')
                    ->where('lid', $lid)
                    ->where('uid', $uid)
                    ->where('szid', $szid);
                
                if ($year !== '所有年份' && !empty($year)) {
                    if ($month !== 'all') {
                        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                        $endDate = date('Y-m-t', strtotime($startDate));
                        $query->where('btime', '>=', $startDate)
                              ->where('btime', '<=', $endDate . ' 23:59:59');
                    } else {
                        $query->where('btime', '>=', $year . '-01-01')
                              ->where('btime', '<=', $year . '-12-31 23:59:59');
                    }
                }
                
                return $query;
            };
            
            if ($level1Name === '全部') {
                $level2Categories = Db::name('qgbill_sort')
                    ->where('lid', $lid)
                    ->where('szid', $szid)
                    ->where('parentid', '>', 0)
                    ->field('sid, sname')
                    ->select();
                
                $level2Data = [];
                foreach ($level2Categories as $category) {
                    $total = $buildQuery()->where('sid', $category['sid'])->sum('money') ?: 0;
                    
                    $level2Data[] = [
                        'name' => $category['sname'],
                        'value' => round(floatval($total), 2)
                    ];
                }
                
                return json([
                    'code' => 1,
                    'data' => $level2Data
                ]);
            }
            
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
                $total = $buildQuery()->where('sid', $category['sid'])->sum('money') ?: 0;
                
                $level2Data[] = [
                    'name' => $category['sname'],
                    'value' => round(floatval($total), 2)
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
