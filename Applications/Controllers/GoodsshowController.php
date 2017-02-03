<?php
/**
 * 商品展示
 */
class GoodsShowController extends AppController
{
    function __construct()
    {
        $this->status = 2;
        parent::__construct();
    }
    //{"cur_page":"1","page_size":10,"type":"","query":"男人的"}
    /**
     * [goods 商品列表]
     */
    public function goods()
    {
        $cur_page = ($this->dparam['cur_page'] - 1) * $this->dparam['page_size'] ;
        $page_size = $cur_page + $this->dparam['page_size'] - 1;

        if(!empty($this->dparam['query']))
        {

            $cur_page = ($this->dparam['cur_page'] - 1) * $this->dparam['page_size'] ;
            $page_size = $this->dparam['page_size'];
            $where = " o.title like '%{$this->dparam['query']}%' ";
            if(!empty($this->dparam['type']) && $this->dparam['type'] == '9.9') $where .= ' and o.deal_price < 10 ';

            $list = A('Goods:getPageGoodsListSearch',[$cur_page,$page_size,$where]);

        }else{

            $cur_page = ($this->dparam['cur_page'] - 1) * $this->dparam['page_size'] ;
            $page_size = $cur_page + $this->dparam['page_size'] - 1;

            if(empty($this->dparam['type']))
            {

                $list = A('Goods:getPageGoodsList',[$cur_page,$page_size]);

            }else{
                if($this->dparam['type'] == 'today')
                {

                    $list = A('Goods:getPageGoodsListForToday',[$cur_page,$this->dparam['page_size']]);

                }else if($this->dparam['type'] == '9.9'){
                    $where = ' o.deal_price < 20 ';
                    $type = $this->dparam['type'];
                    $list = A('Goods:getPageGoodsListForType',[$cur_page,$page_size,$type,$where,false]);
                }else if(!empty($this->dparam['type'])){
                    $where = " o.gw_pid = {$this->dparam['type']} ";
                    $list = A('Goods:getPageGoodsListForType',[$cur_page,$page_size,$this->dparam['type'],$where,false]);
                }
            }

        }
        // echo count($list['list']);
        info(['msg'=>'请求成功','status'=>1,'total'=>$list['total'],'data'=>$list['list']]);

        // D($list);die;
        // info('请求成功',1,$list);
    }



    /**
     * [types_goods 商品分类]
     */
    public function getTypes()
    {
        $list = A('Goods:getTypes');
        foreach ($list as $k => &$v) {
            // if($v['id'] == 11) continue;
            $v['desc'] = L('goodsType')[$v['id']][1];
            $v['icon_url'] = RES_SITE."shoppingResource/goodstype/sort0{$v['id']}.png";
        }
        unset($list[10]);
        empty($list) && info('数据有误',-1);
        info('请求成功',1,$list);
    }

    //{"user_id":"Nuwd8XEsBs","num_iid":"525103323591","app":""}
    /**
     * 商品详情
     */
    public function goodsDetail()
    {
        if(empty($this->dparam['user_id']) || empty($this->dparam['num_iid'])) info('参数不全',-1);
        $detial = A('Goods:getGoodsDetail',[$this->dparam['num_iid']]);
        $detial['list']['share_url'] =  parent::SHARE_URL;
        $record = new RecordController;
        $record->clickRecord($this->dparam['user_id'],$this->dparam['num_iid']);
        info('请求成功',1,$detial['list']);
    }

    /**
     * 获取分享的商品的详情
     */
    public function getShareDetail()
    {
        $data=A('Goods:getShareDetail',[I('num_iid')]);
        info('请求成功',1,$data);
    }


}