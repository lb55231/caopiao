<?php
namespace app\admin\controller;

use app\common\controller\BaseController;
use app\api\model\Setting;

/**
 * 后台系统设置控制器
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
                'webtitle' => Setting::getConfigValue('webtitle', '彩票系统管理后台'),
                'weblogo' => Setting::getConfigValue('weblogo', ''),
                'serviceqq' => Setting::getConfigValue('serviceqq', ''),
                'servicecode' => Setting::getConfigValue('servicecode', ''),
            ];
            
            return $this->success('获取成功', $config);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 获取系统设置
     */
    public function getSettings()
    {
        try {
            $settings = [
                'webtitle' => Setting::getConfigValue('webtitle', ''),
                'weblogo' => Setting::getConfigValue('weblogo', ''),
                'serviceqq' => Setting::getConfigValue('serviceqq', ''),
                'servicecode' => Setting::getConfigValue('servicecode', ''),
                'registerbonus' => floatval(Setting::getConfigValue('registerbonus', 0)),
                'needinvitecode' => intval(Setting::getConfigValue('needinvitecode', 0)),
                'damaliang' => floatval(Setting::getConfigValue('damaliang', 100)),
            ];
            
            return $this->success('获取成功', $settings);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 保存系统设置
     */
    public function saveSettings()
    {
        $params = $this->getPostParams([
            'webtitle', 'weblogo', 'serviceqq', 'servicecode',
            'registerbonus', 'needinvitecode', 'damaliang'
        ]);
        
        try {
            foreach ($params as $key => $value) {
                if ($value !== null) {
                    Setting::setConfigValue($key, $value);
                }
            }
            
            return $this->success('保存成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

