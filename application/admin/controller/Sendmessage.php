<?php

namespace app\admin\controller;

use app\admin\model\Message;
use app\admin\model\PoliceGroup;
use app\admin\model\PoliceType;
use app\admin\model\PoliceOfficer;
use app\common\controller\Backend;

/**
 * 发送消息管理
 *
 * @icon fa fa-circle-o
 */
class Sendmessage extends Backend
{
    
    /**
     * Sendmessage模型对象
     */
    protected $model = null;
    protected $policeGroup = [];
    protected $policeType = [];
    protected $policeOfficer = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Sendmessage');

        $this->policeGroup = PoliceGroup::field('id,group_name')->select();
        $this->policeType = PoliceType::field('id,type_name')->select();
        $this->policeOfficer = PoliceOfficer::field('id,name,police_type,police_group')->select();
    }

    /**
     * 查看
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
            //获取消息id
            $id = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/');

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->where('message_id', $id)
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->where('message_id', $id)
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            if($list)
            {
                //获取警员id
                foreach($list as $k)
                {   
                    $officer_id[] = explode(',', $k['officer_id']);
                }
                //获取警员名称
                foreach($officer_id as $k => $v)
                {   
                   foreach ($v as $key => $value) 
                   {
                        $name[] = PoliceOfficer::where('id',$value)->value('name');
                   }
                   $officer_name[] = implode(',', $name);
                   unset($name);
                }
                for($i = 0; $i < count($list); $i++)
                {
                    $list[$i]['officer_name'] = $officer_name[$i];
                }
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    
    /**
     * 添加
     */
    public function add($id = NULL)
    {   
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill)
                {
                    $params[$this->dataLimitField] = $this->auth->id;
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
        //获取消息
        $message_arr = Message::get($id);
        $message = strip_tags($message_arr['content']);

        $this->view->assign('message', $message);
        return $this->view->fetch();
    }
    
}
