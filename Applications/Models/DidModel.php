<?php

class DidModel
{

    public function getDidInfo($where,$field='*',$status='single')
    {
        if(empty($where))
            info('where不能为空',-1);
        $d_info = M('did')->field($field)->where($where)->limit(1)->select($status);

        return $d_info;
    }

    public function addDid($data,$status=true)
    {
        if(empty($data))
            info('数据有误',-1);

        // $did_data = $this->filterData($data);
        // return M('did')->add($did_data,$status);
        return M('did')->add($data,$status);
    }

    /**
     * [filterData 数据过滤]
     */
    // public function filterData($data)
    // {
    //     if(is_array($data))
    //     {
    //         $field_list = M('did')->getTableFields();
    //         foreach ($data as $k => &$v)
    //         {
    //             if(!in_array($k,$field_list)) unset($data[$k]);
    //         }
    //     }
    //     // D($data);die;
    //     return $data;
    // }
}

