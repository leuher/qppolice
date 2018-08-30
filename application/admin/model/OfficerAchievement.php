<?php

namespace app\admin\model;

use think\Model;

class OfficerAchievement extends Model
{
    // 表名
    protected $table = 'officer_achievement';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'testtime_text'
    ];
    

    



    public function getTesttimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['testtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setTesttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
