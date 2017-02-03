<?php
/**
 * 新手任务接口
 */
class NewbietaskController extends AppController
{

	//{"user_id":""}
	/**
	 * [runCourse 看完教程]
	 */
	public function runCourse()
	{
		$this->checkuid();
		#判断是否已经奖励过
		$run = M()->query("SELECT id FROM gw_uid_log WHERE uid = '{$this->dparam['user_id']}' and score_type = 5");

		if(empty($run))
			$this->unionAdd($this->dparam['user_id'],1,5,'完成新手任务');
		else
			info('用户已经获得过该奖励',-1);
	}

	//{"user_id":""}
	/**
	 * [shareGoods 分享商品]
	 */
	public function shareGoods()
	{
		$this->checkuid();
		if(!empty($this->dparam['num_iid']))
		{
			M()->query("INSERT INTO gw_share_log (uid,num_iid) VALUES ('{$this->dparam['user_id']}','{$this->dparam['num_iid']}')");
		}
		#判断是否已经奖励过
		$run = M()->query("SELECT id FROM gw_uid_log WHERE uid = '{$this->dparam['user_id']}' and score_type = 6");
		if(empty($run))
			$this->unionAdd($this->dparam['user_id'],1,6,'首次分享商品奖励');
		else
			info('用户已经获得过该奖励',-1);
	}

	/**
	 * [add 增加操作]
	 * @param [type]  $uid        [用户id]
	 * @param [type]  $price      [增加的金额]
	 * @param [type]  $score_type [收入类型]
	 * @param [type]  $score_info [description]
	 * @param integer $status     [预估/余额]
	 */
	private function unionAdd($uid,$price,$score_type,$score_info,$status=2)
	{
		try {
			M()->startTrans();
			M('uid_log')->add(['uid'=>$uid,'score_type'=>$score_type,'score_info'=>$score_info,'price'=>$price,'status'=>$status],true);
			M('message')->add(['uid'=>$uid,'content'=>$score_info],true);
			M('income_log')->add(['uid'=>$uid,'score_type'=>$score_type,'score_info'=>$score_info,'price'=>$price,'status'=>$status],true);
			M()->query("UPDATE gw_uid set `price` = `price` + $price where `objectId` = '{$uid}'");
		} catch (Exception $e) {
			M()->rollback();
			info('数据处理失败',-1);
		}
		M()->commit();
		info('操作成功',1);
	}

	private function checkuid()
	{
		if(empty($this->dparam['user_id'])) info('用户uid不正确',-1);
		$uid_info = M()->query("SELECT * FROM gw_uid where objectId = '{$this->dparam['user_id']}'");
		if(empty($uid_info)) info('用户不存在',-1);
	}

}