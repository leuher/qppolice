<?php

namespace app\admin\controller\subject;

use app\admin\model\SubjectList;
use app\admin\model\SubjectCate;
use app\admin\model\PoliceType;
use app\common\controller\Backend;

/**
 * 成绩标准管理
 *
 * @icon fa fa-circle-o
 */
class Subjectscore extends Backend
{
    
    /**
     * SubjectScore模型对象
     */
    protected $model = null;
    protected $groupList = [];
    protected $cateStatus = [];
    protected $policeList = [];
    protected $groupStatus = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SubjectScore');

        //获取科目列表
        $this->groupList = SubjectList::column('name', 'id');
        //获取科目类别状态
        $this->cateStatus = SubjectCate::column('status', 'id');
        //获取警种列表
        $this->policeList = PoliceType::column('type_name', 'id');
        //获取科目状态
        $this->groupStatus = SubjectList::column('status', 'id');

        $this->view->assign('police_list', $this->policeList);
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

            //获取科目id
            $id = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
           
            $total = $this->model
                    ->where('list_id', $id)
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->where('list_id', $id)
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            //获取科目名称和警种名称
            foreach($list as $k)
            {   
                //判断科目发布状态，为0 更新
                if($this->groupStatus[$id] == 0 && $k['status'] == 1)
                {
                    $k['status'] = 0;
                    $this->model->update(['id' => $k['id'], 'status' => $k['status']]);
                }
                //科目成绩范围
                $k['qualification'] = $k['qualification_start'].'——'.$k['qualification_end'];
                $k['excellent'] = $k['excellent_start'].'——'.$k['excellent_end'];
                $k['list_name'] = $this->groupList[$k['list_id']];
                $k['type_name'] = $this->policeList[$k['type_id']];
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
        //获取科目id
        $id = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/'); 

        if ($this->request->isPost())
        {   
            $params = $this->request->post("row/a", '', 'trim');

            if ($params)
            {   
                //判断警钟成绩是否已存在
                $num = $this->model->where('list_id', $params['list_id'])->where('type_id', $params['type_id'])->count();
                if($num) $this->error('该警种已设置成绩，请先删除该成绩再添加');
                
                if ($this->dataLimit && $this->dataLimitFieldAutoFill)
                {
                    $params[$this->dataLimitField] = $this->auth->id;
                }

                //判断科目成绩是否为空
                if (empty($params['qualification_start']) || empty($params['qualification_end']) || empty($params['excellent_start']) || empty($params['excellent_end']))
                $this->error('科目成绩不得为空！');

                //判断科目列表科目发布状态 为0自动设置为0
                if ($this->groupStatus[$params['list_id']] == 0 && $params['status'] == 1)
                {
                    $params['status'] = 0;
                    $this->error('发布科目分数失败，请先更改科目发布状态');
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
        $this->view->assign('list_name', $this->groupList[$id]);
        $this->view->assign('list_id', $id);
        return $this->view->fetch();
    }
    
    /**
     * 编辑
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function edit($ids = NULL)
    {   
        //获取科目id
        $id = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/');

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
                //判断科目列表科目发布状态
                if ($this->groupStatus[$params['list_id']] == 0 && $params['status'] == 1)
                {
                    $params['status'] = 0;
                    $this->error('发布分数失败，请先更改科目发布状态');
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
        $this->view->assign('police_id', $row['type_id']);
        $this->view->assign('list_name', $this->groupList[$id]);
        $this->view->assign('list_id', $id);
        return $this->view->fetch();
    }
    

}
