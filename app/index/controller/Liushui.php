<?php
namespace app\index\controller;
use app\BaseController;
use think\facade\View;
use think\facade\Db;

class Liushui extends BaseController
{
    public function index()
    {
        $lname = get_ledger_info();
        
        if (!$lname) {
            return redirect('/index/index/login');
        }

        $lid = input('lid', 0);
        
        $year = input('year', date('Y'));
        $month = input('month', date('m'));
        
        $records = $this->getRecords($lid, $year, $month);
        
        $totalIncome = Db::name('qgbill')
            ->where('lid', $lid)
            ->where('szid', 0)
            ->sum('money');
        
        $totalExpense = Db::name('qgbill')
            ->where('lid', $lid)
            ->where('szid', 1)
            ->sum('money');
        
        $totalBalance = $totalIncome - $totalExpense;
        
        $currentMonthIncome = Db::name('qgbill')
            ->where('lid', $lid)
            ->where('szid', 0)
            ->whereBetween('btime', [$year . '-' . $month . '-01', date('Y-m-t', strtotime($year . '-' . $month . '-01'))])
            ->sum('money');
        
        $currentMonthExpense = Db::name('qgbill')
            ->where('lid', $lid)
            ->where('szid', 1)
            ->whereBetween('btime', [$year . '-' . $month . '-01', date('Y-m-t', strtotime($year . '-' . $month . '-01'))])
            ->sum('money');
        
        $groupedRecords = [];
        foreach ($records as $record) {
            $date = date('Y-m-d', strtotime($record['btime']));
            if (!isset($groupedRecords[$date])) {
                $groupedRecords[$date] = [];
            }
            $groupedRecords[$date][] = $record;
        }
        
        $availableYears = $this->getAvailableYears($lid);
        $availableMonths = $this->getAvailableMonths($lid, $year);
        
        $allYearMonths = $this->getAllYearMonths($lid);
        
        View::assign([
            'lname' => $lname,
            'totalIncome' => number_format($totalIncome, 2),
            'totalExpense' => number_format($totalExpense, 2),
            'totalBalance' => number_format($totalBalance, 2),
            'currentMonthIncome' => number_format($currentMonthIncome, 2),
            'currentMonthExpense' => number_format($currentMonthExpense, 2),
            'groupedRecords' => $groupedRecords,
            'currentYear' => $year,
            'currentMonth' => $month,
            'availableYears' => $availableYears,
            'availableMonths' => $availableMonths,
            'allYearMonths' => $allYearMonths
        ]);
        
        return View::fetch('/liushui');
    }
    
    private function getRecords($lid, $year = null, $month = null)
    {
        $query = Db::name('qgbill')
            ->alias('b')
            ->join('qgbill_sort s', 'b.sid = s.sid')
            ->where('b.lid', $lid);
        
        if ($year && $month) {
            $startDate = $year . '-' . $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $query->whereBetween('b.btime', [$startDate, $endDate]);
        }
        
        $records = $query->field('b.bid, b.lid, b.uid, b.szid, b.money, b.abstract, b.btime, s.sname, s.sicon')
            ->order('b.btime', 'desc')
            ->select()
            ->toArray();
        
        foreach ($records as &$record) {
            $timestamp = strtotime($record['btime']);
            $record['year'] = date('Y', $timestamp);
            $record['month'] = date('m', $timestamp);
            $record['day'] = date('d', $timestamp);
            $record['weekday'] = $this->getWeekdayChinese(date('w', $timestamp));
        }
        
        return $records;
    }
    
    private function getWeekdayChinese($weekday)
    {
        $weekdays = ['日', '一', '二', '三', '四', '五', '六'];
        return '周' . $weekdays[$weekday];
    }
    
    private function getAvailableYears($lid)
    {
        $years = Db::name('qgbill')
            ->where('lid', $lid)
            ->field("DISTINCT YEAR(btime) as year")
            ->order('year', 'desc')
            ->select()
            ->toArray();
        
        $availableYears = [];
        foreach ($years as $year) {
            $availableYears[] = $year['year'];
        }
        
        if (empty($availableYears)) {
            $availableYears[] = date('Y');
        }
        
        return $availableYears;
    }
    
    private function getAvailableMonths($lid, $year)
    {
        $months = Db::name('qgbill')
            ->where('lid', $lid)
            ->where("YEAR(btime) = {$year}")
            ->field("DISTINCT MONTH(btime) as month")
            ->order('month', 'asc')
            ->select()
            ->toArray();
        
        $availableMonths = [];
        foreach ($months as $month) {
            $availableMonths[] = str_pad($month['month'], 2, '0', STR_PAD_LEFT);
        }
        
        if (empty($availableMonths)) {
            $availableMonths[] = date('m');
        }
        
        return $availableMonths;
    }
    
    private function getAllYearMonths($lid)
    {
        $yearMonths = Db::name('qgbill')
            ->where('lid', $lid)
            ->field("YEAR(btime) as year, MONTH(btime) as month")
            ->order('year', 'desc')
            ->order('month', 'asc')
            ->select()
            ->toArray();
        
        $allYearMonths = [];
        foreach ($yearMonths as $item) {
            $year = $item['year'];
            $month = str_pad($item['month'], 2, '0', STR_PAD_LEFT);
            if (!isset($allYearMonths[$year])) {
                $allYearMonths[$year] = [];
            }
            $allYearMonths[$year][] = $month;
        }
        
        return $allYearMonths;
    }

    public function getBillDetail()
    {
        $bid = input('bid');
        
        if (empty($bid)) {
            return json(['code' => 400, 'msg' => '流水ID不能为空']);
        }
        
        $billDetail = Db::name('qgbill')
            ->alias('b')
            ->join('qgaccount a', 'b.aid = a.aid')
            ->join('qgbill_sort s', 'b.sid = s.sid')
            ->join('qgledger l', 'b.lid = l.lid')
            ->where('b.bid', $bid)
            ->field('b.*, a.aname, a.anumber, s.sname, s.sicon, l.lname')
            ->find();
            
        if (!$billDetail) {
            return json(['code' => 404, 'msg' => '流水记录不存在']);
        }
        
        $billDetail['btime_formatted'] = date('Y-m-d H:i:s', strtotime($billDetail['btime']));
        $billDetail['ctime_formatted'] = date('Y-m-d H:i:s', strtotime($billDetail['ctime']));
        
        return json(['code' => 200, 'data' => $billDetail]);
    }

    public function deleteBill()
    {
        if (!request()->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $bid = input('bid');
        $uid = session('user.uid');
        
        if (empty($bid)) {
            return json(['code' => 400, 'msg' => '流水ID不能为空']);
        }
        
        $bill = Db::name('qgbill')
            ->where('bid', $bid)
            ->find();
            
        if (!$bill) {
            return json(['code' => 404, 'msg' => '流水记录不存在']);
        }
        
        Db::startTrans();
        try {
            $account = Db::name('qgaccount')
                ->where('aid', $bill['aid'])
                ->find();
                
            if (!$account) {
                Db::rollback();
                return json(['code' => 404, 'msg' => '关联账户不存在']);
            }
            
            $newAmount = $account['amoney'];
            if ($bill['szid'] == 0) { 
                $newAmount -= $bill['money'];
            } else { 
                $newAmount += $bill['money'];
            }
            
            $updateResult = Db::name('qgaccount')
                ->where('aid', $bill['aid'])
                ->update([
                    'amoney' => $newAmount,
                    'etime' => date('Y-m-d H:i:s')
                ]);
                
            if (!$updateResult) {
                Db::rollback();
                return json(['code' => 500, 'msg' => '更新账户余额失败']);
            }
            
            $deleteResult = Db::name('qgbill')
                ->where('bid', $bid)
                ->delete();
                
            if (!$deleteResult) {
                Db::rollback();
                return json(['code' => 500, 'msg' => '删除流水记录失败']);
            }
            
            Db::commit();
            return json(['code' => 200, 'msg' => '流水记录删除成功']);
            
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => '删除失败: ' . $e->getMessage()]);
        }
    }
}
