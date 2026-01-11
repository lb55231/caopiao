<?php
namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\service\BetService;

/**
 * 投注控制器
 */
class Game extends BaseController
{
    protected $betService;
    
    public function initialize()
    {
        parent::initialize();
        $this->betService = new BetService();
    }
    
    /**
     * 提交投注
     */
    public function bet()
    {
        $params = $this->getPostParams([
            'cpid', 'cpname', 'typeid', 'period_id', 
            'wanfa', 'betcode', 'beishu', 'amount', 'odds'
        ]);
        
        if (empty($params['cpid']) || empty($params['amount'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->betService->submitBet($this->userId, $params);
            return $this->success('投注成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 投注记录
     */
    public function betRecords()
    {
        $pagination = $this->getPagination();
        
        try {
            $result = $this->betService->getBetList(
                $this->userId,
                $pagination['page'],
                $pagination['page_size']
            );
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

