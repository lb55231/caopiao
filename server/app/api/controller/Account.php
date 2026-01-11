<?php
namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\service\AccountService;

/**
 * 账户控制器
 */
class Account extends BaseController
{
    protected $accountService;
    
    public function initialize()
    {
        parent::initialize();
        $this->accountService = new AccountService();
    }
    
    /**
     * 充值申请
     */
    public function recharge()
    {
        $params = $this->getPostParams([
            'paytype', 'paytypetitle', 'amount', 
            'userpayname', 'bankname', 'bankcode'
        ]);
        
        if (empty($params['paytype']) || empty($params['amount'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->accountService->submitRecharge($this->userId, $params);
            return $this->success('提交成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 提现申请
     */
    public function withdraw()
    {
        $params = $this->getPostParams(['bank_id', 'amount', 'password']);
        
        if (empty($params['bank_id']) || empty($params['amount'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->accountService->submitWithdraw($this->userId, $params);
            return $this->success('提交成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 银行卡列表
     */
    public function bankList()
    {
        try {
            $result = $this->accountService->getBankList($this->userId);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 添加银行卡
     */
    public function addBank()
    {
        $params = $this->getPostParams([
            'bankname', 'bankcode', 'userbankname', 'password'
        ]);
        
        if (empty($params['bankname']) || empty($params['bankcode']) || empty($params['userbankname'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->accountService->addBank($this->userId, $params);
            return $this->success('添加成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 删除银行卡
     */
    public function deleteBank()
    {
        $bankId = $this->request->param('id', 0);
        
        if (empty($bankId)) {
            return $this->error('参数错误');
        }
        
        try {
            $this->accountService->deleteBank($this->userId, $bankId);
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 设置默认银行卡
     */
    public function setDefaultBank()
    {
        $bankId = $this->request->param('id', 0);
        
        if (empty($bankId)) {
            return $this->error('参数错误');
        }
        
        try {
            $this->accountService->setDefaultBank($this->userId, $bankId);
            return $this->success('设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 账变记录
     */
    public function records()
    {
        $pagination = $this->getPagination();
        
        try {
            $result = $this->accountService->getFundRecords(
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
