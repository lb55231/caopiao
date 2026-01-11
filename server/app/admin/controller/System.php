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
                'webtitle' => Setting::getSettingValue('webtitle', '彩票系统管理后台'),
                'weblogo' => Setting::getSettingValue('weblogo', ''),
                'serviceqq' => Setting::getSettingValue('serviceqq', ''),
                'servicecode' => Setting::getSettingValue('servicecode', ''),
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
                'webtitle' => Setting::getSettingValue('webtitle', ''),
                'weblogo' => Setting::getSettingValue('weblogo', ''),
                'serviceqq' => Setting::getSettingValue('serviceqq', ''),
                'servicecode' => Setting::getSettingValue('servicecode', ''),
                'registerbonus' => floatval(Setting::getSettingValue('registerbonus', 0)),
                'needinvitecode' => intval(Setting::getSettingValue('needinvitecode', 0)),
                'damaliang' => floatval(Setting::getSettingValue('damaliang', 100)),
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
                    Setting::setValue($key, $value);
                }
            }
            
            return $this->success('保存成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

