<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    private   $appId;
    private   $appSecret;
    private   $redirect_uri;
    private   $location_uri;

    public function _initialize()
    {
        parent::_initialize();
        $this->appId = '';
        $this->appSecret = '';
        $this->redirect_uri = urlencode('http://test.gmouse.net/index.php/home/index/getCode');
        $this->location_uri = '';
    }

    public function index()
    {
        echo $this->redirect_uri;
    }

    public function index1()
    {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appId."&redirect_uri=".$this->redirect_uri."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        header('location:'.$url);
    }

    public function getCode()
    {
        $code  = $this->request->get('code');
        if(empty($code)) $this->index();
        $Token  = $this->getToken($code);
        if(!$Token['openid']) $this->index();
        header('location:'.$this->location_uri);
    }

    public function getToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appId."&secret=".$this->appSecret."&code=".$code."&grant_type=authorization_code";
        $result = $this->httpGet($url);
        return json_decode($result,true);
    }

    public function getUserInfo($access_token,$openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid;
        $result =  $this->httpGet($url);
        return json_decode($result,true);
    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
}
