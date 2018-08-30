<?php

namespace app\admin\controller\subject;

use app\admin\model\SubjectCate;
use app\common\controller\Backend;

/**
 * 科目列表
 *
 * @icon fa fa-circle-o
 */
class Subjectlist extends Backend
{
    
    /**
     * SubjectList模型对象
     */
    protected $model = null;
    protected $groupCate = [];
    protected $cateStatus = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SubjectList');

        //获取科目类别
        $this->groupCate = SubjectCate::column('cateName', 'id');
        //获取科目类别状态
        $this->cateStatus = SubjectCate::column('status', 'id');

    }
    
    /**
     * 显示
     * @return json
     */
    public function index()
    {
         //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            // 获取科目类别
            foreach($list as $k)
            {   
                if($this->cateStatus[$k['cate_id']] == 0 && $k['status'] == 1)
                {
                    $k['status'] = $this->cateStatus[$k['cate_id']];
                    $this->model->update(['id' => $k['id'], 'status' => $k['status']]);
                }
                $k['cate_name'] = $this->groupCate[$k['cate_id']]; 
            }

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", '', 'trim');
            
            if ($params)
            {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill)
                {
                    $params[$this->dataLimitField] = $this->auth->id;
                }

                // 判断科目类别的发布状态
                if ($this->cateStatus[$params['cate_id']] == 0 && $params['status'] == 1)
                {
                    $params['status'] = 0;
                    $this->error('发布科目失败，请先更改科目类别发布状态');
                }

                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate = true)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($this->model->getError());
                    }
                }
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('group_cate', $this->groupCate);
        return $this->view->fetch();
    }
    
    /**
     * 编辑
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds))
        {
            if (!in_array($row[$this->dataLimitField], $adminIds))
            {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", '', 'trim');
            if ($params)
            {   
                //判断科目类别的发布状态
                if ($this->cateStatus[$params['cate_id']] == 0 && $params['status'] == 1)
                {
                    $params['status'] = 0;
                    $this->error('发布科目失败，请先更改科目类别发布状态');
                }

                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate = true)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($row->getError());
                    }
                }
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        $this->view->assign('group_id', $row['cate_id']);
        $this->view->assign('group_cate', $this->groupCate);
        return $this->view->fetch();
    }
}
