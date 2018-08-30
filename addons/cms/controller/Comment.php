<?php

namespace addons\cms\controller;

use addons\cms\model\Archives;
use addons\cms\model\Comment as CommentModel;
use addons\cms\model\Page;
use app\common\library\Email;
use think\addons\Controller;
use think\Exception;
use think\Validate;

/**
 * 评论
 */
class Comment extends Controller
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 发表评论
     */
    public function post()
    {
        $this->request->filter('strip_tags');
        $type = $this->request->post("type");
        $aid = (int) $this->request->post("aid");
        $pid = intval($this->request->post("pid"));
        $username = $this->request->post("username");
        $email = $this->request->post("email");
        $website = $this->request->post("website");
        $content = $this->request->post("content");
        $subscribe = intval($this->request->post("subscribe"));
        $useragent = $this->request->server('HTTP_USER_AGENT');
        $ip = $this->request->ip();
        $website = $website != '' && substr($website, 0, 7) != 'http://' && substr($website, 0, 8) != 'https://' ? "http://" . $website : $website;
        $username = preg_replace("/<(.*?)>/", "", $username);
        $content = preg_replace("/<(.*?)>/", "", $content);
        $content = preg_replace("/\r?\n/", '<br />', $content);
        $token = $this->request->post('__token__');
        try
        {
            $archives = $type == 'archives' ? Archives::get($aid) : Page::get($aid);
            if (!$archives || $archives['status'] == 'hidden')
            {
                throw new Exception("文档未找到");
            }

            $rule = [
                'type'      => 'require|in:archives,page',
                'pid'       => 'require|number',
                'username'  => 'require|chsDash|length:3,30',
                'email'     => 'require|email|length:3,30',
                'website'   => 'url|length:3,50',
                'content'   => 'require|length:3,250',
                '__token__' => 'token',
            ];
            $data = [
                'type'      => $type,
                'pid'       => $pid,
                'username'  => $username,
                'email'     => $email,
                'website'   => $website,
                'content'   => $content,
                '__token__' => $token,
            ];
            $validate = new Validate($rule);
            $result = $validate->check($data);
            if (!$result)
            {
                throw new Exception($validate->getError());
            }

            $lastcomment = CommentModel::where(['type' => $type, 'aid' => $aid, 'email' => $email, 'ip' => $ip])->order('id', 'desc')->find();
            if ($lastcomment && time() - $lastcomment['createtime'] < 30)
            {
                throw new Exception("对不起！您发表评论的速度过快！请稍微休息一下，喝杯咖啡");
            }
            if ($lastcomment && $lastcomment['content'] == $content)
            {
                throw new Exception("您可能连续了相同的评论，请不要重复提交");
            }
            $data = [
                'type'      => $type,
                'pid'       => $pid,
                'aid'       => $aid,
                'username'  => $username,
                'email'     => $email,
                'content'   => $content,
                'ip'        => $ip,
                'useragent' => $useragent,
                'subscribe' => (int) $subscribe,
                'website'   => $website,
                'status'    => 'normal'
            ];
            CommentModel::create($data);

            $archives->setInc('comments');
            if ($pid)
            {
                //查找父评论，是否并发邮件通知
                $parent = CommentModel::get($pid);
                if ($parent && $parent['subscribe'] && Validate::is($parent['email'], 'email'))
                {
                    $domain = $this->request->domain();
                    $config = get_addon_config('cms');
                    $title = "{$parent['username']}，您发表在《{$archives['title']}》上的评论有了新回复 - {$config['sitename']}";
                    $archivesurl = $domain . $archives['url'];
                    $unsubscribe_url = addon_url("cms/comment/unsubscribe", ['id' => $parent['id'], 'key' => md5($parent['id'] . $parent['email'])], true, true);
                    $content = "亲爱的{$parent['username']}：<br />您于" . date("Y-m-d H:i:s") .
                            "在《<a href='{$archivesurl}' target='_blank'>{$archives['title']}</a>》上发表的评论<br /><blockquote>{$parent['content']}</blockquote>" .
                            "<br />{$username}发表了回复，内容是<br /><blockquote>{$content}</blockquote><br />您可以<a href='{$archivesurl}'>点击查看评论详情</a>。" .
                            "<br /><br />如果你不愿意再接受最新评论的通知，<a href='{$unsubscribe_url}'>请点击这里取消</a>";
                    $email = new Email;
                    $result = $email
                            ->to($parent['email'])
                            ->subject($title)
                            ->message('<div style="min-height:550px; padding: 100px 55px 200px;">' . $content . '</div>')
                            ->send();
                }
            }
            $this->success(__('评论成功'));
        }
        catch (Exception $e)
        {
            $this->error($e->getMessage(), null, ['token' => $this->request->token()]);
        }
    }

    /**
     * 取消评论订阅
     */
    public function unsubscribe()
    {
        $id = (int) $this->request->param('id');
        $key = $this->request->param('key');
        $comment = CommentModel::get($id);
        if (!$comment)
        {
            $this->error("评论未找到");
        }
        if ($key !== md5($comment['id'] . $comment['email']))
        {
            $this->error("无法进行该操作");
        }
        if (!$comment['subscribe'])
        {
            $this->error("评论已经取消订阅，请勿重复操作");
        }
        $comment->subscribe = 0;
        $comment->save();
        $this->success('取消评论订阅成功');
    }

}
