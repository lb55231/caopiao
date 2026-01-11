<?php
namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\service\BetService;
use app\api\service\GameService;

/**
 * 游戏/投注控制器
 */
class Game extends BaseController
{
    protected $betService;
    protected $gameService;
    
    public function initialize()
    {
        parent::initialize();
        $this->betService = new BetService();
        $this->gameService = new GameService();
    }
    
    /**
     * 提交投注（支持批量投注）
     */
    public function bet()
    {
        // 获取所有参数
        $params = $this->getPostParams();
        
        // 检查必填字段
        if (empty($params['cpname']) || empty($params['period']) || empty($params['bets'])) {
            return $this->error('请填写完整信息');
        }
        
        // 调试：记录 token 和 userId
        $token = $this->request->header('token', '') ?: $this->request->header('Token', '');
        file_put_contents(app()->getRootPath() . 'runtime/bet_debug.log', 
            date('Y-m-d H:i:s') . "\n" .
            "Token: " . $token . "\n" .
            "userId from request: " . ($this->request->userId ?? 'null') . "\n" .
            "userId from controller: " . $this->userId . "\n\n",
            FILE_APPEND
        );
        
        // 检查用户是否登录
        if (!$this->userId) {
            return $this->error('请先登录', null, 401);
        }
        
        try {
            $result = $this->betService->submitBatchBet($this->userId, $params);
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
    
    /**
     * 获取期号信息
     */
    public function period()
    {
        $cpname = $this->request->param('cpname', '');
        
        if (empty($cpname)) {
            return $this->error('缺少彩票名称参数');
        }
        
        try {
            $result = $this->gameService->getPeriod($cpname);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 获取玩法配置
     */
    public function plays()
    {
        try {
            $result = $this->gameService->getPlays();
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

