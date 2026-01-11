<?php
namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\model\Setting;

/**
 * 系统配置控制器
 */
class System extends BaseController
{
    /**
     * 获取系统配置（公开接口）
     */
    public function config()
    {
        try {
            $config = [
                'webtitle' => Setting::getValue('webtitle', '彩票系统'),
                'weblogo' => Setting::getValue('weblogo', ''),
                'serviceqq' => Setting::getValue('serviceqq', ''),
                'servicecode' => Setting::getValue('servicecode', ''),
            ];
            
            return $this->success('获取成功', $config);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

