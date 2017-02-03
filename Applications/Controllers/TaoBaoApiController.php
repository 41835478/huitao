<?php
include DIR_LIB.'taobaosdk/TopSdk.php';
class TaoBaoApiController {
    public static $c = null;
    public $type = null;
    public function __construct()
    {
        self::$c  = new TopClient;
        $this->type = !empty($_GET['type']) ? $_GET['type'] : 1;

        //改变安卓 和ios的公共请求参数
        // */1 * * * * curl http://es1.laizhuan.com/shopping/TaoBaoKe/run?name=message&type=1 & sleep 30;  curl http://es1.laizhuan.com/shopping/TaoBaoKe/run?name=message&type=2
        switch ($this->type) {
            case 1: //安卓
                self::$c->appkey    = '23558798';
                self::$c->secretKey = '3e4ab6b4449c862ceec00c67f2140c2d';
                break;
            case 2: //ios
                self::$c->appkey    = '23597987';
                self::$c->secretKey = '035bff81056833b5a95ee1145eae7620';
                break;
        }
        self::$c ->format      = 'json';
    }
    /**
     * [goodsDetails 商品详情数据api http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.4deKKs&apiId=23559]
     * @param  [type] $actionId [商品open_iid 即商品模糊id]
     * @return [type]           [description]
     */
    public function goodsDetails($actionId = '') {
        $req = new TaeItemDetailGetRequest;
        $req ->setFields('itemInfo,priceInfo,skuInfo,stockInfo,rateInfo,descInfo,sellerInfo,mobileDescInfo,deliveryInfo,storeInfo,itemBuyInfo,couponInfo');
        $req ->setOpenIid($actionId);
        return self::returnArray(self::execute($req));
    }
    /**
     * [confirmationMessage 确认消费信息的状态 http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.2Oztjp&apiId=21985]
     * @param  string $id [收到的消息成功id 每次请求最多200个]
     * @return [type]     [description]
     */
    public function confirmationMessage($id = '') {
        $req = new TmcMessagesConfirmRequest;
        $req ->setSMessageIds($id);
        return self::execute($req);
    }
    /**
     * [TmcMessagesConsumeRequest 获取消费的多条消息 http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.pJy3zR&apiId=21986]
     */
    public function TmcMessagesConsumeRequest() {
        $req = new TmcMessagesConsumeRequest;
        $req->setQuantity(200);
        return self::returnArray(self::execute($req));
    }
    /**
     * [TaeItemsListRequest description 商品列表服务 https://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7629140.0.0.iKkJiU&apiId=23731]
     * 供外部调用~~~~~~
     */
    public function TaeItemsListRequest($auction_id = '') {
        $req = new TaeItemsListRequest;
        $req->setFields("title,nick,price,post_fee,shop_name");
        $req->setOpenIids($auction_id);
        // $req->setNumIids($auction_id);
        $resp = self::returnArray(self::execute($req));
        if(!empty($resp['items']['x_item']))
            return $resp['items']['x_item'];
        else
            return [];
        // else
            // return $resp;
    }











    private static function execute($obj) {
        return self::$c ->execute($obj);
    }
    public static function returnArray($obj) {
        return json_decode(json_encode((array)$obj, JSON_UNESCAPED_UNICODE),true);
    }
}