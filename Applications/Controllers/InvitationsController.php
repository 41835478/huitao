<?php
class InvitationsController extends AppController
{
    /**
     * [invitations 邀请好友列表以及好友带来的收入]
     */
    public function invitations()
    {
        // D($this->dparam['uid']);
        !empty($this->dparam['user_id']) or info('缺少用户id');
        /**
         * 查询到该用户邀请的好友给他带来的收入情况
         * score_source 好友uid  score_info 加积分的说明
         */
        $incomeDetail = M('uid_log')->field('score_source,score_info msg,createdAt date_time')->where(['uid' => ['=',$this->dparam['user_id']]])->select();
        if($incomeDetail) {
            $scoreSource = '';
            foreach($incomeDetail as $k => $v) {
                $scoreSource .= "'{$v['score_source']}',";
            }
            $scoreSource = rtrim($scoreSource,',');
            /**
             * 获取到好友头像
             */
            $img = M('uid')->field('head_img,objectId name,nickname')->where("objectId in({$scoreSource})")->select();
            /**
             * 组合数据转成前端需要的json格式 😢😢😢😢😢
             */
            foreach($img as $k => $v) {
                $img[$k]['data'] = $this->dataList($v['name'],$incomeDetail);
            }
            $invitations = [
                'msg'       => '请求成功',
                'status'    => 1,
                'data'      => $img
            ];
            info($invitations);
        } else {
            info('闲话不扯 赶快去邀请好友',1);
        }
    }
    public function dataList($objectId = 0,$score)
    {
        $infoList = [];
        foreach($score as $k => $v) {
            if($v['score_source'] == $objectId) {
                unset($v['score_source']);
                $infoList[] = $v;
            }
        }
        return $infoList;
    }
    /**
     * [rankingList 全部用户--好友邀请排行榜排名]
     */
    public function invitateInfo()
    {
        // $uid = I('user_id') or info('缺少用户id',-1);
        /**
         *  获取到该用户邀请的好友给他带来的收入情况 以及邀请的人数
         */

        if(!empty($this->dparam['user_id'])) {
            // $sql = "select c.price money,count(d.id) person_num from (select sum(a.price)  price,b.nickname name from gw_uid_log as a  join gw_uid b on a.uid=b.objectId group by a.uid  ORDER BY price desc limit 1) c join gw_uid_log d on d.score_type=2 where uid='{$this->dparam['user_id']}'";
            // $data = M()->query($sql,'single');
            $sql = "SELECT sum(price) as money from gw_uid_log where status in(1,2) AND uid='{$this->dparam['user_id']}'";
            $a = M()->query($sql,'single');
            $b = M('uid_log')->field('count(id) person_num')->where(['uid' => ['=', $this->dparam['user_id']], 'score_type' => ['=',2]])->select('single');
            $data = array_merge($a, $b);
        } else {
            $data['money']      = 0;
            $data['person_num'] = 0;
        }
        /**
         * 好友邀请排行榜排名
         */
        // $sql = "select c.price price,c.name name,count(d.id) friends_num from (select sum(a.price)  price,b.nickname name from gw_uid_log as a  join gw_uid b on a.uid=b.objectId group by a.uid  ORDER BY price desc limit 10) c join gw_uid_log d on d.score_type=2";
        $sql = 'select a.uid,price as price,friends_num,name,head_img from
    (select sum(price) price ,uid from gw_uid_log where status in(1,2) group by uid) a
JOIN
    (select count(id) friends_num ,uid from gw_uid_log where score_type = 2 group by uid) b on a.uid =b.uid
JOIN
    (select nickname as name,head_img as head_img,objectId from gw_uid) c on  c.objectId=a.uid order by a.price desc,b.friends_num desc limit 10';
        $desc = M()->query($sql,'all');
        $result = [
            'status'       => 1,
            'msg'          => '请求成功',
            'money'        => $data['money'],
            'person_num'   => $data['person_num'],
            'ranking_list' => $desc
        ];
        info($result);
    }
}