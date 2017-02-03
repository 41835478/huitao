<?php
class HeadacheController {
    public function tracking() {
        $data = $_GET;
        //安卓设备码 以及回调地址 不能为空
        if(!empty($data['imei']) && !empty($data['callback_url'])) {
            //检测是否已经激活过了
            if(M('did_log') ->where(['imei' => ['=', $data['imei']]])->select('single'))

                $this->return_json(false,'已经激活过了');
            //如果这个用户已经存在记录了 并且没有回调过 就不需要在添加了
            $query = M('tracking')->where(['imei' => ['=', $data['imei']], 'status' => ['=', 1]],['AND'])->select('single');
            if($query) {
                $this->return_json(true,'ok');
            } else {
                if(M('tracking')->add($data))
                    $this->return_json(true,'ok');
                else
                    $this->return_json(false,'未能成功处理');
            }
        }
        $this->return_json(false,'缺少参数');
    }
    //扣量处理
    public static function amount($imei = []) {
        //状态为3 为匹配到未发送的 可以理解为暂时 作中转扣量处理
        //状态为2 为已经回调的用户
        //状态为1  为未匹配到的用户
        $jishu = 10;
        //匹配imei  匹配到状态改为3
        $queryImei = M('tracking')->field('imei')->where(['imei' => ['=', $imei['imei']],'status' => ['=', 1]],['AND'])->select('single');
        if(!empty($queryImei)) {
            M('tracking')->where(['imei' => ['=', $imei['imei']]])->save(['status' => 3, 'type' => 1, 'uid' => $imei['uid']]);
            //先获取到之前匹配到的数据总量 基数为10 扣除80%
            $data = M('tracking')->field('imei,callback_url')->where(['status' => ['=',3]])->select();
            // 当状态等于三的总数==基数 随机取两条发送回调并且状态改为2 且其他的改为4 表示是扣的量
            if(count($data) >= $jishu) {
                foreach(array_rand($data,2) as $v) {
                    M('tracking')->where(['imei' => ['=', $data[$v]['imei']]])->save(['status' => 2]);
                    get_curl(urldecode($data[$v]['callback_url']));
                    unset($data[$v]);
                }
                $imei = "'".implode("','",array_column($data,'imei'))."'";
                M('tracking')->where("imei in({$imei})")->save(['status' => 4]);
            }
        }
    }
    public static function queryActiv($unique = []) {
        // 查询跟踪信息中是否有用户激活 如果有去就修改下状态值和请求下回调地址
        if(is_array($unique)) {
            foreach($unique as $v) {
                $data = M('tracking') ->where(['imei' => ['=', $v],'status' => ['=',1]],['AND']) ->select('single');
                if($data) {
                    M('tracking')->where(['imei' => ['=', $v]])->save(['status' => 2, 'type' => 2]);
                    get_curl(urldecode($data['callback_url']));
                }
            }
        }
    }
    public function return_json($status,$msg) {
        echo json_encode(['success' => $status, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }
}