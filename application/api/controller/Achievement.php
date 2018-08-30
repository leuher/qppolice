<?php

namespace app\api\controller;
use app\common\controller\Api;

class Achievement extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];


    //今日成绩
    public function TodayResult()
    {
        $uid = input('post.uid');
        if (empty($uid)){
            $this -> error('缺少参数');
        }

        $star_time = strtotime(date("Y-m-d 00:00:00"));
        $end_time = strtotime(date("Y-m-d 23:59:59"));

        $date = db('officer_achievement')->alias('oa')
            ->field('sl.name,oa.achievement,oa.result,sl.desc')
//            ->join('police_officer','po.id = oa.police_officer','LEFT')
            ->join('subject_list sl','oa.list_id = sl.id','LEFT')
            ->where("oa.police_officer = $uid")
            ->where('oa.createtime','egt',$star_time)
            ->where('oa.createtime','elt',$end_time)
            ->select();
        if (empty($date)){
            $this -> error('暂无数据');
        }
        $this -> success('查询成功',$date);

    }

    //历史成绩
    public function AllResult(){
        $uid = input('post.uid');
        $year = input('post.year',date('Y'));
        $mounth = input('post.mounth',date('m'));
        if (empty($uid)){
            $this -> error('缺少参数');
        }

//        $day = cal_days_in_month(CAL_GREGORIAN, $mounth, $year);

        $day = date("t",strtotime("$year-$mounth"));

//        $firstday = "$year-$mounth-01 00:00:00";
//        $first = strtotime("$year-$mounth-01 00:00:00");
//
//        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
//        $lastd = strtotime("$lastday 23:59:59");
//
//        $date = db('officer_achievement')->alias('oa')
//            ->field('sl.name list_name,oa.list_id,oa.achievement,oa.result,oa.testtime,sl.desc')
//            ->join('police_officer po','po.id = oa.police_officer','LEFT')
//            ->join('subject_list sl','oa.list_id = sl.id','LEFT')
//            ->where("oa.police_officer = $uid")
//            ->where('oa.testtime','egt',$first)
//            ->where('oa.testtime','elt',$lastd)
//            ->order('oa.testtime asc')
//            ->select();
//
        $arr = array();
//        foreach ($date as $k => $v){
//            $arr[$v['list_id']] = $v;
//        }




        for ($i = 0 ;$i <= $day; $i++){
            $firstday = "$year-$mounth-$i 00:00:00";
            $first = strtotime("$year-$mounth-$i 00:00:00");
            $lastd = strtotime("$year-$mounth-$i 23:59:59");

            $date = db('officer_achievement')->alias('oa')
                ->field('sl.name list_name,oa.list_id,oa.achievement,oa.result,oa.testtime,sl.desc')
//                ->join('police_officer po','po.id = oa.police_officer','LEFT')
                ->join('subject_list sl','oa.list_id = sl.id','LEFT')
                ->where("oa.police_officer = $uid")
                ->where('oa.testtime','egt',$first)
                ->where('oa.testtime','elt',$lastd)
                ->order('oa.testtime asc')
                ->select();

            $name = db('officer_achievement')->alias('oa')
                ->field('sl.id,sl.name list_name')
                ->join('subject_list sl','oa.list_id = sl.id','LEFT')
                ->where("oa.police_officer = $uid")
                ->group('list_id')
                ->order('oa.testtime ASC')
                ->select();

            $user = db('police_officer')->field('name,head_image')->where("id = $uid")->find();

            $arr[] = $date;
        }
        $arr1=array_filter ($arr);


        foreach ($arr1 as $k => $v){
            $arr2 = array();
            foreach ($v as $key=>$value){
                $arr2[$value['list_id']] = [
                    'list_id'=>$value['list_id'],
                    'achievement'=>$value['achievement'],
                    'result'=>$value['result'],
                    'desc'=>$value['desc'],
                    ];
            }
            $new[] = [
                'time'=>date("m-d",$v[0]['testtime']),
                'data' => $arr2
            ];
        }
        $list = array();
        $list['list_name'] =$name;
        $list['user'] =$user;

        if (empty($new)){
            $list['data'] =[];
            $this -> error('暂无信息',$list);
        }

        $list['data'] =$new;

        $this->success('查询成功',$list);
    }

}
