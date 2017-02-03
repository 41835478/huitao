<?php
class HtGoodsController extends  HtController
{
    /**
     * [queryGoods 查询全部商品以及某个商品的详情]
     */
    public function querygoods()
    {
        $data = A('HtGoods:queryGoods',[$_POST]);
        $data ? info('ok',1,$data) : info('暂无数据',-1);
    }
    /**
     * [deleteGoods 删除商品]
     */
    public function deletegoods()
    {
        I('POST:id') or info('缺少唯一标示无法删除',-1);
        $data = A('HtGoods:deleteGoods',[I('id')]);
        $data ? info('删除成功',1,$data) : info('删除失败',-1);
    }
    /**
     * [addGoods 添加商品]
     */
    public function addgoods()
    {
        if(!empty($_POST)) {
            $res = A('HtGoods:addGoods',[$_POST]);
            $res ? info('添加成功',1) : info('添加失败',-1);
        }
    }
}