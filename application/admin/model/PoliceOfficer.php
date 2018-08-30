<?php

namespace app\admin\model;

use think\Model;

class PoliceOfficer extends Model
{
    // 表名
    protected $table = 'police_officer';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        // 'jointime_text'
    ];

    public $type_id;

    



    // public function getJointimeTextAttr($value, $data)
    // {
    //     $value = $value ? $value : $data['jointime'];
    //     return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    // }

    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
