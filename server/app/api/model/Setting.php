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
        'key'   => 'string',
        'value' => 'string',
    ];
    
    /**
     * 获取设置值
     */
    public static function getValue($key, $default = '')
    {
        $setting = self::where('key', $key)->find();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * 设置值
     */
    public static function setValue($key, $value)
    {
        $setting = self::where('key', $key)->find();
        
        if ($setting) {
            $setting->value = $value;
            $setting->save();
        } else {
            self::create([
                'key' => $key,
                'value' => $value
            ]);
        }
        
        return true;
    }
}

