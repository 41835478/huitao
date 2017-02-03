<?php
/**
 * 淘宝开放平台 SDK 调用
 */
class TaoBaoKeController extends AppController {
    /**
     * SDK  入口TopClient类实例
     */
    public static $taoBaoApi = '';
    /**
     * 初始化公共参数
     */
    public function __construct() {
        parent::__construct();
        self::$taoBaoApi = new TaoBaoApiController;
    }
    /**
     * [run api入口类]
     */
    public function run() {
        !empty($_GET['name']) or info('请填入要调用的api名称');
        switch($_GET['name']) {
            case 'message':
                return $this ->message();
        }
    }
    /**
     * [---message 淘宝消息订阅]
     */
    public function message() {
        // $resp['messages'] = array ( 'tmc_message' => array ( 0 => array ( 'content' => '{"buyer_id":"AAHnANc-ADye1K1g5A-7nMSJ","extre":"isv_code:appisvcode;","paid_fee":"8.60","shop_title":"景如工艺品工厂店","is_eticket":false,"create_order_time":"2016-12-28 13:14:46","order_id":"2942606005233222","order_status":"4","seller_nick":"支架底座炭雕超市","auction_infos":[{"detail_order_id":"2942606005233222","auction_id":"AAHLANc9ADye1K1g5HSefFqw","real_pay":"8.60","auction_pict_url":"i2/1998744183/TB26J48acwb61Bjy0FfXXXvlpXa_!!1998744183.jpg","auction_title":"盘子支架木质普洱茶饼陶瓷圆盘架仿红木底座托架工艺品摆件架摆盘","auction_amount":"1"}]}', 'id' => 4130201825909791653, 'pub_app_key' => '12497914', 'pub_time' => '2016-12-28 13:24:26', 'topic' => 'taobao_tae_BaichuanTradePaidDone', ),), );
            // $a   = json_encode($_POST['messages'], true);

        // D($resp['messages']);
        // exit;
        // if(!empty($resp['messages'])) {
            // $resp['messages'] = json_decode($resp['messages'], JSON_UNESCAPED_UNICODE);
        // } else {
        $resp = self::$taoBaoApi ->TmcMessagesConsumeRequest();
        // }
        !empty($resp['messages']) or exit;
        $a = $resp['messages'];
        $setSMessageIds = array_column($a['tmc_message'],'id');
        $data = [];
        foreach($a as $k => $v) {
            foreach($v as $_k => $_v) {
                $content  = json_decode($_v['content'], true);
                list($content['msg'], $content['status']) = $this ->option($_v['topic'],$content);
                $data[] = $content;
            }
        }
        //处理 入库
        $this ->addDatabaseContent($data, $setSMessageIds);
        echo '已经全部处理完成!!!';
        exit;
    }
    public function addDatabaseContent($data, $setSMessageIds) {
        $res = $order_id = [];
        foreach($data as $v) {
            $v = $this ->replaceField($v);
            /**
             * [判断是所属退款消息 还是订单交易消息]
             */
             $v = $this->replaceField($v);
            if(!empty($v['auction_infos'])) {
                if(!empty($v['auction_infos']['status']))
                    $v['auction_infos'][] = $v['auction_infos'];
                foreach($v['auction_infos'] as $_k => $_v) {
                    $_v = array_merge($v, $_v);
                    if($_v['status'] == 2) {
                        $order_id[] = $_v['detail_order_id'];
                        $n = self::$taoBaoApi ->TaeItemsListRequest($_v['auction_id']);
                        if(!empty($n[0])) {
                            $n = $n[0];
                            $_v['paid_fee'] = abs($_v['paid_fee'])-abs($n['post_fee']);
                            $_v['post_fee'] = $n['post_fee'];   //邮费
                            $_v['open_id']  = $n['open_id'];    //商品明文id
                        }
                    }
                    unset($_v['auction_infos']);
                    $title = $this->getGoodsTitle($_v['auction_id']);
                    $_v['auction_title'] = !empty($title) ? $title : $_v['auction_title'];
                    $_v['type'] = self::$taoBaoApi->type;
                    $add = M('order_status')->add($_v);
                    // M('order_status')->add($_v);
                }
            } else {
                if($v['status'] == 5) {
                    $res[] = $v['order_id'];
                }
                $v['type'] = self::$taoBaoApi->type;
                $add = M('order_status')->add($v);
            }
        }
        //确认消息
        $this ->confirmationMessage($setSMessageIds);
        $order_id = array_diff($order_id, $res); //付款成功 订单id
        $res      = array_diff($res, $order_id); //退款成功 订单id
        //先处理 付款成功 再处理退款成功的订单
        empty($order_id) or $this ->notice(2, $order_id);
        empty($res)      or $this ->notice(5, $res);
    }
    /**
     * [confirmationMessage 确认消息]
     */
    public function confirmationMessage($goodsId) {
        $id = array_chunk($goodsId, 200);
        foreach($id as $v) {
            self::$taoBaoApi ->confirmationMessage(implode($v, ','));
        }
    }
    /**
     * [notice 分发处理消息队列]
     */
    public function notice($status = '', $data = '') {
        $record  = new RecordController;
        $incomes = new IncomesController;
        switch($status) {
            /**
             * 付款成功时 需要处理以下逻辑:
             *     订单表订单id去和从api中获取到的订单id匹配  如果匹配到 则去查商品表 进行标题匹配然后获取到商品id详情时间倒序取最晚的第一个补全订单表
             *     拿订单id去获取到uid 然后给师傅分红
             */
            case 2:
                echo '付款成功:';
                $record  ->updateOrderInfo($data);
                $incomes ->buySuccess($data);
            break;
            //*/1 * * * * for i in `seq 600`; do date >> tes.log& sleep 0.001; done
            /**
             * 退款成功时:
             *     取消之前付款成功时给用户师傅分红的钱
             *
             */
            case 5:
                echo '退款成功:';
                $record  ->purchaseRecord($data, 5);
                $incomes ->buyFail($data);
            break;
        }

    }
   /**
    * 依据消息名称 映射 对应的名称 与状态号
    */
   public function option($name, $content)
   {
        static $data = [
            'taobao_tae_BaichuanTradeCreated'       => ['创建订单', 1],
            'taobao_tae_BaichuanTradePaidDone'      => ['付款成功', 2],
            'taobao_tae_BaichuanTradeSuccess'       => ['交易成功', 3],
            'taobao_tae_BaichuanTradeRefundCreated' => ['创建退款', 4],
            'taobao_tae_BaichuanTradeRefundSuccess' => ['退款成功', 5],
            'taobao_tae_BaichuanTradeClosed'        => ['交易关闭', 6]
        ];
        /**
         * 只记录上面定义的api数据
         */
        return isset($data[$name]) ? $data[$name] : info('未记录次api');
   }
    /**
     * [replaceField 重复字段替换]
     */
    private function replaceField($arr)
    {
        $data = [
            'tid'           => 'order_id',    //key  tid替换成order_id
            'refund_fee'    => 'paid_fee',
        ];
        foreach($arr as $k => $v) {
            if(array_key_exists($k, $data)) {
                $arr[$data[$k]] = $arr[$k];
                unset($arr[$k]);
            }
        }
        return $arr;
    }
    //获取到商品标题
    public function getGoodsTitle($actionId) {
        $n = self::$taoBaoApi ->goodsDetails($actionId);
        if(!empty($n['data']))
            return $n['data']['item_info']['title'];
    }






    /**
     *
     * [addOrderId 添加订单id]
     */
    public function addOrderId()
    {
        if(empty($this->dparam['uid']) || empty($this->dparam['order_id']) || empty($this->dparam['taobao_nick']))
            info('缺少参数', -1);
        if(is_array($this->dparam['order_id'])) {
            foreach($this->dparam['order_id'] as $k => $v) {
                $this->dparam['order_id']     = (string)$v;
                $this->dparam['created_date'] = date('Y-m-d');
                if(!M('order') ->where(['order_id' => ['=', $v]]) ->select('single'))
                    $add = M('order')->add($this->dparam);
            }
            !empty($add) ? info('添加成功',1) : info('添加失败',-1);
        } else {
            info('类型格式不对',-1);
        }
    }
    /**
     * 记录版本号
     */
    public function deviceVer() {
        $params = $this->dparam;
        $type = !empty($params['type']) ? 1 : 0;
        $data = '缺少参数';
        if(!empty($params['device']) && !empty($params['status'])) {
            switch ($params['status']) {
                //查库
                case 1:
                    $data = M('device')->where(['deviceVer' => ['=', $params['device']]])->field('type')->select('single');
                    $data = isset($data['type']) ? $data['type'] : info('库里可能还没存在',-1);
                    break;
                //修改
                case 2:
                    $data = M('device')->where(['deviceVer' => ['=',$params['device']]])->save(['type' => $type]);
                    break;
                //添加
                case 3:
                    $data = M('device')->add(['deviceVer' => $params['device'], 'type' => $type]);
                    break;
            }
        }
        info('',(int)$data);
    }
}