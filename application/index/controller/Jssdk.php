<?php
/**
 * Created by PhpStorm.
 * User: fedde
 * Date: 2018-04-18
 * Time: 18:24
 */
namespace app\index\controller;
use app\common\controller\Frontend;
use think\Cache;


class Jssdk extends Frontend
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    private $appId = 'wx987a5039d72cca10';
    private $appSecret = 'f64e90f53f535c2e7557ad143b074c73';

    public function _initialize()
    {
        parent::_initialize();
    }


    public function getSignPackage() {
        if($this->request->isAjax()) {
            $url  = $this->request->post('url');
            $jsapiTicket = $this->getJsApiTicket();
            $url = urldecode($url);
            $timestamp = time();
            $nonceStr = $this->createNonceStr();
            $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

            $signature = sha1($string);

            $signPackage = array(
                "appId" => $this->appId,
                "nonceStr" => $nonceStr,
                "timestamp" => $timestamp,
                "url" => $url,
                "signature" => $signature,
                "rawString" => $string
            );
            return json(['data' => $signPackage, 'code' => 1, 'message' => 'ok']);
        }
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = \cache('jsapi_ticket');
        if ($data['expire_time'] < time()) {
            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url),true);
            $ticket = $res['ticket'];
            if ($ticket) {
                $data['expire_time']  = time() + 7200;
                $data['jsapi_ticket'] = $ticket;
                \cache('jsapi_ticket',$data,7200);
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }
        return $ticket;
    }

    private function getAccessToken() {
        $data = \cache('access_token');
        if ($data['expire_time'] < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url),true);
            $access_token = $res['access_token'];
            if ($access_token) {
                $data['expire_time']    = time() + 7200;
                $data['access_token']   = $access_token;
                \cache('access_token',$data,7200);
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }

    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}