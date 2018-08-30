<?php

namespace addons\cms\model;

use think\Model;

/**
 * 文章模型
 */
class Archives Extends Model
{

    protected $name = "archives";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
        'fullurl',
        'likeratio',
        'tagslist',
    ];
    protected static $config = [];

    //自定义初始化
    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function getImageAttr($value, $data)
    {
        return $value ? $value : self::$config['default_archives_img'];
    }

    public function getTagslistAttr($value, $data)
    {
        $list = [];
        foreach (array_filter(explode(",", $data['tags'])) as $k => $v)
        {
            $list[] = ['name' => $v, 'url' => addon_url('cms/tags/index', [':name' => $v])];
        }
        return $list;
    }

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/archives/index', [':id' => $data['id'], ':diyname' => $diyname, ':channel' => $data['channel_id']]);
    }

    public function getFullUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/archives/index', [':id' => $data['id'], ':diyname' => $diyname, ':channel' => $data['channel_id']], true, true);
    }

    public function getLikeratioAttr($value, $data)
    {
        return ($data['dislikes'] > 0 ? min(1, $data['likes'] / ($data['dislikes'] + $data['likes'])) : ($data['likes'] ? 1 : 0.5)) * 100;
    }

    /**
     * 获取文档列表
     * @param array $tag
     * @return array
     */
    public static function getArchivesList($tag)
    {
        $type = empty($tag['type']) ? '' : $tag['type'];
        $model = !isset($tag['model']) ? '' : $tag['model'];
        $channel = !isset($tag['channel']) ? '' : $tag['channel'];
        $tags = empty($tag['tags']) ? '' : $tag['tags'];
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $flag = empty($tag['flag']) ? '' : $tag['flag'];
        $row = empty($tag['row']) ? 10 : (int) $tag['row'];
        $orderby = empty($tag['orderby']) ? 'createtime' : $tag['orderby'];
        $orderway = empty($tag['orderway']) ? 'desc' : strtolower($tag['orderway']);
        $limit = empty($tag['limit']) ? $row : $tag['limit'];
        $cache = !isset($tag['cache']) ? true : (int) $tag['cache'];
        $imgwidth = empty($tag['imgwidth']) ? '' : $tag['imgwidth'];
        $imgheight = empty($tag['imgheight']) ? '' : $tag['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        if ($model !== '')
        {
            $where['model_id'] = ['in', $model];
        }
        if ($channel !== '')
        {
            $where['channel_id'] = ['in', $channel];
        }
        if ($flag !== '')
        {
            if (stripos($flag, '&') !== false)
            {
                $arr = [];
                foreach (explode('&', $flag) as $k => $v)
                {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr)
                    $condition .= "(" . implode(' AND ', $arr) . ")";
            }else
            {
                $condition .= ($condition ? ' AND ' : '');
                $arr = [];
                foreach (array_merge(explode(',', $flag), explode('|', $flag)) as $k => $v)
                {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr)
                    $condition .= "(" . implode(' OR ', $arr) . ")";
            }
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['createtime', 'updatetime', 'views', 'weigh', 'id']) ? "{$orderby} {$orderway}" : "createtime {$orderway}");

        $model = self::with('channel');
        if ($tags)
        {
            $tagsList = Tags::where('name', 'in', explode(',', $tags))->limit($limit)->select();
            $archives = [];
            foreach ($tagsList as $k => $v)
            {
                $archives = array_merge($archives, explode(',', $v['archives']));
            }
            if ($archives)
            {
                $model->where('id', 'in', $archives);
            }
        }
        $list = $model
                ->where($where)
                ->where($condition)
                ->order($order)
                ->limit($limit)
                ->select();
        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    /**
     * 渲染数据
     * 
     * @param array $list
     * @param int $imgwidth
     * @param int $imgheight
     * @return array
     */
    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v)
        {
            $v['hasimage'] = $v->getData('image') ? true : false;
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['channellink'] = '<a href="' . $v['channel']['url'] . '">' . $v['channel']['name'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取分页列表
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageList($list, $tag)
    {
        $imgwidth = empty($tag['imgwidth']) ? '' : $tag['imgwidth'];
        $imgheight = empty($tag['imgheight']) ? '' : $tag['imgheight'];
        return self::render($list, $imgwidth, $imgheight);
    }

    /**
     * 获取分页信息
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageInfo($list, $tag)
    {
        return 'a';
    }

    /**
     * 获取分页过滤
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageFilter($list, $tag)
    {
        $exclude = empty($tag['exclude']) ? '' : $tag['exclude'];
        return $list;
    }

    /**
     * 获取分页排序
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageOrder($list, $tag)
    {
        $exclude = empty($tag['exclude']) ? '' : $tag['exclude'];
        return $list;
    }

    /**
     * 获取上一页下一页
     * @param string $type
     * @param string $archives
     * @param string $channel
     * @return array
     */
    public static function getPrevNext($type, $archives, $channel)
    {
        $model = self::where('id', $type === 'prev' ? '<' : '>', $archives)->where('status', 'normal');
        if ($channel !== '')
        {
            $model->where('channel_id', 'in', $channel);
        }
        $model->order($type === 'prev' ? 'id desc' : 'id asc');
        $row = $model->find();
        return $row;
    }

    public function channel()
    {
        return $this->belongsTo("Channel")->field('id,name,image,diyname,items')->setEagerlyType(1);
    }

}
