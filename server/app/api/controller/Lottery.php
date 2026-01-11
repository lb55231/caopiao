<?php
namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\service\LotteryService;

/**
 * 彩票控制器
 */
class Lottery extends BaseController
{
    protected $lotteryService;
    
    public function initialize()
    {
        parent::initialize();
        $this->lotteryService = new LotteryService();
    }
    
    /**
     * 彩票列表
     */
    public function list()
    {
        try {
            $result = $this->lotteryService->getLotteryList();
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 彩票详情
     */
    public function detail()
    {
        $name = $this->request->param('name', '');
        
        if (empty($name)) {
            return $this->error('参数错误');
        }
        
        try {
            $result = $this->lotteryService->getLotteryDetail($name);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 获取当前期数
     */
    public function currentPeriod()
    {
        $cpid = $this->request->param('cpid', 0);
        
        if (empty($cpid)) {
            return $this->error('参数错误');
        }
        
        try {
            $result = $this->lotteryService->getCurrentPeriod($cpid);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 历史开奖
     */
    public function history()
    {
        $cpid = $this->request->param('cpid', 0);
        $limit = $this->request->param('limit', 10);
        
        if (empty($cpid)) {
            return $this->error('参数错误');
        }
        
        try {
            $result = $this->lotteryService->getHistory($cpid, $limit);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 获取最新公告
     */
    public function notice()
    {
        try {
            $result = $this->lotteryService->getNotice();
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 收益排行榜（近7天）
     */
    public function ranking()
    {
        try {
            $result = $this->lotteryService->getRanking();
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 自动开奖（补充缺失的开奖数据）
     */
    public function autoKaijiang()
    {
        try {
            $result = $this->lotteryService->autoKaijiang();
            return $this->success('自动开奖完成', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 自动开奖（别名，兼容下划线URL）
     */
    public function auto_kaijiang()
    {
        return $this->autoKaijiang();
    }
    
    /**
     * 自动结算（补充缺失的结算）
     */
    public function settlement()
    {
        try {
            $result = $this->lotteryService->settlement();
            return $this->success('结算完成', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
