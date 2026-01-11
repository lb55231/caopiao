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
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 获取公开的配置项
            $settings = $db->table($prefix . 'setting')
                ->whereIn('name', ['webtitle', 'weblogo', 'keywords', 'description', 'copyright', 'icp', 'serviceqq', 'servicecode'])
                ->column('value', 'name');
            
            // 默认配置
            $config = [
                'webtitle' => $settings['webtitle'] ?? '彩票系统',
                'weblogo' => $settings['weblogo'] ?? '',
                'keywords' => $settings['keywords'] ?? '',
                'description' => $settings['description'] ?? '',
                'copyright' => $settings['copyright'] ?? '',
                'icp' => $settings['icp'] ?? '',
                'serviceqq' => $settings['serviceqq'] ?? '',
                'servicecode' => $settings['servicecode'] ?? ''
            ];
            
            return $this->success('获取成功', $config);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

