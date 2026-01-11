<?php
namespace app\api\model;

use think\Model;

/**
 * 系统设置模型
 */
class Setting extends Model
{
    // 设置表名
    protected $name = 'setting';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'    => 'int',
        'name'  => 'string',
        'value' => 'string',
    ];
    
    /**
     * 获取设置值
     */
    public static function getConfigValue($key, $default = '')
    {
        // 数据库字段是 name，不是 key
        $setting = self::where('name', $key)->find();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * 设置配置值
     */
    public static function setConfigValue($key, $value)
    {
        // 数据库字段是 name，不是 key
        $setting = self::where('name', $key)->find();
        
        if ($setting) {
            $setting->value = $value;
            $setting->save();
        } else {
            self::create([
                'name' => $key,
                'value' => $value
            ]);
        }
        
        return true;
    }
}

