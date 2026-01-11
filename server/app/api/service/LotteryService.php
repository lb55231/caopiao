<?php
namespace app\api\service;

use app\api\model\Lottery;
use app\api\model\LotteryPeriod;
use app\api\model\LotteryResult;

/**
 * 彩票服务类
 */
class LotteryService
{
    /**
     * 获取彩票列表
     */
    public function getLotteryList()
    {
        $list = Lottery::where('state', 1)
            ->order('listorder', 'asc')
            ->select();
        
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->cpname,
                'typeid' => $item->typeid,
                'logo' => $this->generateLogoPath($item->typeid, $item->logo),
                'timelong' => $item->timelong,
                'state' => $item->state
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取彩票详情
     */
    public function getLotteryDetail($name)
    {
        $lottery = Lottery::where('cpname', $name)->find();
        
        if (!$lottery) {
            throw new \Exception('彩票不存在');
        }
        
        return [
            'id' => $lottery->id,
            'name' => $lottery->cpname,
            'typeid' => $lottery->typeid,
            'logo' => $this->generateLogoPath($lottery->typeid, $lottery->logo),
            'timelong' => $lottery->timelong,
            'closetime1' => $lottery->closetime1,
            'closetime2' => $lottery->closetime2,
            'state' => $lottery->state
        ];
    }
    
    /**
     * 获取当前期数
     */
    public function getCurrentPeriod($cpid)
    {
        $period = LotteryPeriod::where('cpid', $cpid)
            ->where('kjtime', '>', time())
            ->order('kjtime', 'asc')
            ->find();
        
        if (!$period) {
            throw new \Exception('暂无可投注期数');
        }
        
        return [
            'id' => $period->id,
            'qishu' => $period->qishu,
            'kjtime' => $period->kjtime,
            'cpid' => $period->cpid,
            'cpname' => $period->cpname,
            'typeid' => $period->typeid
        ];
    }
    
    /**
     * 获取历史开奖
     */
    public function getHistory($cpid, $limit = 10)
    {
        $list = LotteryResult::where('cpid', $cpid)
            ->order('opentime', 'desc')
            ->limit($limit)
            ->select();
        
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id' => $item->id,
                'expect' => $item->expect,
                'opencode' => $item->opencode,
                'opentime' => $item->opentime,
                'cpname' => $item->cpname
            ];
        }
        
        return $result;
    }
    
    /**
     * 生成logo路径
     */
    private function generateLogoPath($typeid, $logo)
    {
        if (!empty($logo)) {
            return $logo;
        }
        
        // 默认图标路径
        $iconMap = [
            1 => '/uploads/lottery/k3.png',
            2 => '/uploads/lottery/ssc.png',
            3 => '/uploads/lottery/11x5.png',
            4 => '/uploads/lottery/pk10.png',
            5 => '/uploads/lottery/lhc.png',
        ];
        
        return $iconMap[$typeid] ?? '/uploads/lottery/default.png';
    }
}

