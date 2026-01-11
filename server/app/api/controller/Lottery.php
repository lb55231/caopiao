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
}
