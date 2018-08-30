<?php

namespace app\admin\controller\officer;

use app\admin\model\OfficerAchievement;
use app\admin\model\PoliceOfficer;
use app\admin\model\SubjectCate;
use app\admin\model\SubjectList;
use app\common\controller\Backend;

/**
 * 成绩管理
 *
 * @icon fa fa-circle-o
 */
class Achievement extends Backend
{
    
    /**
     * OfficerAchievement模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('OfficerAchievement');

        //获取科目类
        $this->cateName = SubjectCate::column('cateName', 'id');
        //获取科目
        $this->listName = SubjectList::column('name','id');
        //获取警员
        $this->officerName = PoliceOfficer::column('name','id');

        $this->view->assign('cate_name', $this->cateName);
        $this->view->assign('list_name', $this->listName);
        $this->view->assign('officer_name', $this->officerName);

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax())
        {
            $id = substr($_SERVER['HTTP_REFERER'],'-4','2');
            if ($id == 'id'){
                $ids = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/');
//                var_dump($_SERVER['HTTP_REFERER']);die;
            }else{
                $type = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/');
            }

            if (!empty($type)){
                switch ($type){
                    case '1': $where['cateName'] = ['like','%体能%'];break;
                    case '2': $where['cateName'] = ['like','%技能%'];break;
                    case '3': $where['cateName'] = ['like','%比武%'];break;
                    case '4': $where['cateName'] = ['like','%会操%'];break;
                }
                $cate= db('subject_cate')->field('id')->where($where)->find();
                $cate_id = $cate['id'];
                $ids = NULL;
            }
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }

            if (!empty($ids)){
                $cate_id = '';
            }

            list($where, $sort, $order, $offset, $limit) = $this->mybuildparams($ids,$cate_id);

            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $cate = db('subject_cate')->field('id,cateName name')->select();
            $cate = collection($cate)->toArray();
            $s_list = db('subject_list')->field('id,name')->select();
            $s_list = collection($s_list)->toArray();

            foreach($list as $k)
            {
                $k['cate_name'] = $this->cateName[$k['cate_id']];
                $k['list_name'] = $this->listName[$k['list_id']];
                $k['officer_name']= $this->officerName[$k['police_officer']];
                if ($k['result'] == 1){
                    $k['result'] = '优秀';
                }elseif($k['result'] == 2){
                    $k['result'] = '良好';
                }elseif($k['result'] == 3){
                    $k['result'] = '合格';
                }elseif($k['result'] == 4){
                    $k['result'] = '不合格';
                }
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list,"cate"=>$cate,"s_list"=>$s_list);

            return json($result);
        }
        return $this->view->fetch();
    }



    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed $searchfields 快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function mybuildparams($ids,$cate_id,$searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');

        $filter = $this->request->get("newFilter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", "id");
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $filter = json_decode($filter, TRUE);

        $op = json_decode($op, TRUE);
        if(!empty($ids)){
            $filter['police_officer'] = $ids;
            $op['police_officer'] = '=';
        }

        if(!empty($cate_id)){
            $filter['cate_id'] = $cate_id;
            $op['cate_id'] = '=';
        }

        $filter = $filter ? $filter : [];
        $where = [];
        $tableName = '';
        if ($relationSearch)
        {
            if (!empty($this->model))
            {
                $tableName = $this->model->getQuery()->getTable() . ".";
            }
            $sort = stripos($sort, ".") === false ? $tableName . $sort : $sort;
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds))
        {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search)
        {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v)
            {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v)
        {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false)
            {
                $k = $tableName . $k;
            }
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym)
            {
                case '=':
                case '!=':
                    $where[] = [$k, $sym, (string) $v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', `{$k}`)";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '')
                    {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    }
                    else if ($arr[1] === '')
                    {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '')
                    {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    }
                    else if ($arr[1] === '')
                    {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        $where = function($query) use ($where) {
            foreach ($where as $k => $v)
            {
                if (is_array($v))
                {
                    call_user_func_array([$query, 'where'], $v);
                }
                else
                {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
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
            $params = $this->request->post("row/a");
            if ($params)
            {
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
        $catemodel = new SubjectCate();
        $catename = $catemodel->get($row->cate_id);

        $listmodel = new SubjectList();
        $listname = $listmodel->get($row->list_id);

        $officermodel = new PoliceOfficer();
        $officer_name = $officermodel->get($row->police_officer);

        $row->cate_name = $catename->cateName;
        $row->list_name = $listname->name;
        $row->name = $officer_name->name;
//        $row->police_officer = $officer_name->name;

        $this->view->assign("row", $row);
        $this->view->assign("cate", $row['cate_id']);
        $this->view->assign("list", $row['list_id']);
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
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

        $id = substr($_SERVER['HTTP_REFERER'],'-4','2');
        if ($id == 'id'){
            $ids = (int)trim(strrchr($_SERVER['HTTP_REFERER'], '/'),'/');
        }else{
            $this->error('请至警员列表添加成绩','police/officer?ref=addtabs');
        }


        $officer = new PoliceOfficer();
        $model = $officer->get($ids);

        $any_id = db('officer_achievement')->field('cate_id,list_id')
            ->where("police_officer = $ids")
            ->find();


        $this->assign('name',$model->name);
        $this->assign('cate',$any_id['cate_id']);
        $this->assign('list',$any_id['list_id']);
        $this->assign('id',$ids);
        return $this->view->fetch();
    }

}
