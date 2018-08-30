<?php

namespace addons\cms\model;

use think\Model;

/**
 * 评论模型
 */
class Comment Extends Model
{

    protected $name = "comment";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    //自定义初始化
    protected static function init()
    {
        
    }

    /**
     * 获取评论列表
     * @param array $params
     * @return array
     */
    public static function getCommentList($params)
    {
        $type = empty($params['type']) ? 'archives' : $params['type'];
        $aid = empty($params['aid']) ? 0 : $params['aid'];
        $pid = empty($params['pid']) ? 0 : $params['pid'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $fragment = empty($params['fragment']) ? 'comments' : $params['fragment'];
        $row = empty($params['row']) ? 10 : (int) $params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $pagesize = empty($params['pagesize']) ? $row : $params['pagesize'];
        $cache = !isset($params['cache']) ? false : (int) $params['cache'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        if ($type)
        {
            $where['type'] = $type;
        }
        if ($aid !== '')
        {
            $where['aid'] = $aid;
        }
        if ($pid !== '')
        {
            $where['pid'] = $pid;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['pid', 'id', 'createtime', 'updatetime']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::where($where)
                ->where($condition)
                ->order($order)
                ->cache($cache)
                ->paginate($pagesize, false, ['type' => '\\addons\\cms\\library\\Bootstrap', 'var_page' => 'cp', 'fragment' => $fragment]);
        $ids = [];
        foreach ($list as $k => $v)
        {
            $ids[] = $v['id'];
            $v->sublist = [];
        }
        if ($ids && $pid === 0)
        {
            $mainlist = [];
            $sublist = self::
                    where('pid', 'in', $ids)
                    ->order($order)
                    ->limit(10)
                    ->cache($cache)
                    ->select();
            foreach ($sublist as $k => $v)
            {
                $mainlist[$v['pid']][] = $v;
            }
            foreach ($list as $k => $v)
            {
                $v->sublist = isset($mainlist[$v['id']]) ? $mainlist[$v['id']] : [];
            }
        }
        self::render($list);
        return $list;
    }

    public static function render(&$list)
    {
        foreach ($list as $k => &$v)
        {
            
        }
        return $list;
    }

}
