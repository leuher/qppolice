<?php

namespace app\admin\model;

use think\Model;

class Channel extends Model
{

    // 表名
    protected $name = 'channel';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'url'
    ];

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return isset($data['type']) && isset($data['outlink']) && $data['type'] == 'link' ? $data['outlink'] : addon_url('cms/channel/index', [':id' => $data['id'], ':diyname' => $diyname]);
    }

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
        self::afterDelete(function($row) {
            static $tree;
            if (!$tree)
            {
                $tree = \fast\Tree::instance();
                $tree->init(collection(Channel::order('weigh desc,id desc')->field('id,parent_id,name,type,diyname,status')->select())->toArray(), 'parent_id');
            }
            $childIds = $tree->getChildrenIds($row['id']);
            if ($childIds)
            {
                Channel::destroy(function($query) use($childIds) {
                    $query->where('id', 'in', $childIds);
                });
            }
            $childIds[] = $row['id'];
            db('archives')->where('channel_id', 'in', $childIds)->update(['deletetime' => time()]);
        });
    }

    public static function getTypeList()
    {
//        return ['channel' => __('Channel'), 'list' => __('List'), 'link' => __('Link')];
        return [ 'list' => __('List')];
    }

    public static function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function model()
    {
        return $this->belongsTo('Modelx', 'model_id')->setEagerlyType(0);
    }

}
