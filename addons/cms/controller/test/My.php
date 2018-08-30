<?php

namespace addons\cms\controller\test;

use addons\cms\controller\Base;

/**
 * 我的消息
 * @ApiRoute ("/addons/ffff/test.my")
 */
class My extends Base
{

    protected $noNeedLogin = ['vote'];

    /**
     * 首页
     * @ApiSummary  介绍
     * @ApiRoute    ("/aaa/dd")
     * @param string $name
     * @param integer $age
     * @return string
     */
    public function index()
    {
        return 'index';
    }

    /**
     * 赞与踩
     * @ApiSummary  介绍
     * @ApiRoute    /aaa/dd
     */
    public function vote()
    {
        return 'vote';
    }

    /**
     * 成与美
     */
    public function getHostData()
    {
        return 'vote';
    }

    /**
     * 查看首页
     * 
     * @ApiTitle    Api Title
     * @apisummary  Get information about user
     * @ApiSector   User
     * @ApiMethod   get
     * @ApiMethod   get2
     * @ApiRoute    /api/index/test/c/{id}/name/{name}
     * @ApiHeaders  (name=id, type=integer, required=true, description="User id")
     * @ApiParams   (name="id", type="integer", required=true, description="User id")
     * @ApiParams   (name="name", type="string", required=true, description="Username")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     * @ApiReturnParams   (name="id", type="integer", required=true, description="User id")
     * @ApiReturnParams   (name="name", type="string", required=true, description="Username")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     * @ApiReturn   (data="{
     *  'transaction_id':'int',
     *  'transaction_status':'string'
     * }")
     */
    public function test($name = '')
    {
        print_r($this->request->request());
    }

}
