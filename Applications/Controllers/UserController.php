<?php
class UserController extends AppController
{


	//{"ip":"192.168.1.109","deviceId":"203E0D03-DF9C-4107-84E0-8D966EA9BE09","deviceVer":"10.0.2","simType":"中国通","deviceName":"iOS","imei":"","deviceH":"667.000000","uuid":"Z203e0d03df9c410784e08d966ea9be09","onlineType":"-1","deviceW":"375.000000","appBid":"com.thor.laizhuanJP","appVer":"1.0","wifiBid":"e8:fc:af:a3:ee:fd","wifiSid":"houbu3","appName":"","jailbreak":"0","deviceModel":"iPhone","bdid":"","address":"上海市","idfa":"ZE1D62B8-DE2D-4266-9EC2-8ED4F55E67AB","phone":"13482509858","id_code":"1648","type":"0","token":"4c6d819ae6d50125158d253622f43868"}
	//
	//{"uuid":"Z203e0d03df9c410784e08d966ea9be09","idfa":"ZE1D62B8-DE2D-4266-9EC2-8ED4F55E67AB","deviceVer":"10.0.2","phone":"13482509859","id_code":"1648","type":"0","token":"4923d83346cfdb00e02a3b890f0582"}
	/**
	 * [login app登入]
	 */
	public function login()
	{

	    if(!preg_match("/^1[34578]\d{9}$/",$this->dparam['phone']) || empty($this->dparam['phone'])){
	    	info('非法手机号',-1);
	    }

		/**判断是否新用户**/
		$uid_info =  A('Uid:getInfo',["phone = {$this->dparam['phone']}",'*','single']);
		/**判断在did表中是否存在**/
		$did_info = $this->ckDidExisit();
		if(empty($this->dparam['token']) && !empty($uid_info))
		{
			if(empty($this->dparam['id_code']) && empty($this->dparam['password']))
			{
				info('好久没来了,请先登入',-2);
			}
			/**如果验证码登入,则验证验证码**/
			if(!empty($this->dparam['id_code']))
			{
				$this->checkCode();
			}

			/**如果密码登入,则验证密码**/
			if(!empty($this->dparam['password']) && !empty($uid_info) && empty($this->dparam['id_code']))
			{
				$this->checkPassWord($uid_info);
			}

		}

		if($uid_info)
		{   //老用户

			if(!empty($this->dparam['token']) && (empty($this->dparam['id_code']) && empty($this->dparam['password'])))
			{
				/**检查token**/
				$this->checkToken($uid_info);
			}

			$this->unionHandle($uid_info,$did_info);
			$msg = '登入成功';

		}else{  //新用户
			$this->checkCode(2);
			$objectId = $this->unionHandle($uid_info,$did_info);
			if($this->dparam['type'] === '1'){
				HeadacheController::amount(['imei' => $this ->dparam['imei'],'uid' => $objectId]);
			}else{
				// $idfa = !empty($this ->dparam['idfa'])?$this ->dparam['idfa']:'';
				// $uuid = !empty($this ->dparam['uuid'])?$this ->dparam['uuid']:'';
				// $bdid = !empty($this ->dparam['bdid'])?$this ->dparam['bdid']:'';
				// HeadacheController::amount(['idfa' =>$idfa,'uuid' =>$uuid,'bdid' =>$bdid,'uid' => $objectId]);
			}
			$msg = '注册成功';
		}

		$data =  A('Uid:getInfo',["phone = {$this->dparam['phone']}",'objectId as user_id,token,nickname,if(Invitation_code is not null,Invitation_code,objectId) as invite','single']);
		$data['msg'] = $msg;
		$data['status'] = 1;
		/**返回用户信息**/
		info($data);
	}


	/**
	 * [checkCode 检查验证码是否一致]
	 */
	public function checkCode($type=1)
	{
		if(empty($this->dparam['id_code'])){
			info($type == 2 ? '请先注册!' : '验证码不正确',-1);
		}
		if(empty($this->dparam['phone'])) info('没有手机号!',-1);
		$v_info = A('Vaild:getVcodeByPhone',[$this->dparam['phone']]);
		if(!empty($v_info))
			($v_info['vaild_code'] != $this->dparam['id_code']) && info('验证码不正确!',-1);
		else
			info('验证码不存在或过期!',-1);
		return true;
	}

	/**
	 * [checkPassWord 检查密码是否一致]
	 */
	public function checkPassWord($uid_info)
	{
		($uid_info['password'] != md5($this->dparam['password'])) && info('密码不正确!',-1);
		return true;
	}

	public function checkToken($uid_info)
	{
		($uid_info['token'] != $this->dparam['token']) && info('token有误,请先登入!',-2);
		return true;
	}

	/**
	 * [getCDidInfo 检查设备日志信息是否存在]
	 */
	public function getCkDidLogInfo($uidObjectId)
	{
		$where = $this->setDidWhere(1,$uidObjectId);
		$d_info = A('DidLog:getDidLogInfo',[$where]);
		return $d_info;
	}

	/**
	 * [ckDidExisit 检查在did中是否存在]
	 */
	public function ckDidExisit()
	{
		$where = $this->setDidWhere(2);
		$did_info = A('Did:getDidInfo',[$where]);
		return $did_info;
	}

	/**
	 * [setDidWhere 设置ios9 10 android 的 where条件]
	 */
	public function setDidWhere($type=1,$uid=null)
	{
		!empty($uid) ? $where['uid'] = ['=',$uid] : '';
		switch ($this->getPhoneType()) {
			case '1':	//IOS 10以下
				$where['bdid'] = ['=',$this->dparam['bdid']];
				break;
			case '2':	//IOS 10以上
				$Symbol = $type == 2 ? 'OR' : 'AND';
				$_uid = !empty($uid) ? " AND uid = '{$uid}'" : '';
				$where = "uuid = '{$this->dparam['uuid']}' {$Symbol} idfa = '{$this->dparam['idfa']}' {$_uid}";
				break;
			case '3':	//android
				$where['imei'] = ['=',$this->dparam['imei']];
				break;
		}
		return $where;
	}

	/**
	 * [getPhoneType 判断设备类型]
	 */
	public function getPhoneType()
	{
		return $this->dparam['type'] == '0' ? ((int)$this->dparam['deviceVer'] < 10 ? 1 : 2) : 3;
	}

	/**
	 * [unionHandle 连贯操作 ]
	 */
	public function unionHandle($uid_info,$did_info)
	{
			/**开启事务**/
			M()->startTrans();
			$uid_objectId = !empty($uid_info) ? $uid_info['objectId'] : A('Uid:addUid',[$this->dparam,true]);
			$did_id = !empty($did_info) ? $did_info['id'] : A('Did:addDid',[$this->dparam,true]);

			$uid_where = "objectId = '{$uid_objectId}'";

			if(!empty($uid_info))
			{	//老用户
				if(!empty($did_info))
				{	//设备号过去已经存在
					if (!strstr($uid_info['did_list'],$did_info['id'])) {
						$uid_data = $this->dparam;
						$uid_data['did'] = $did_info['id'];
						$uid_data['did_list'] = $uid_info['did_list'].','.$did_info['id'];
						$uid_data['did_count'] = $uid_info['did_count'] + 1;
						$uid_data['token'] = md5($this->dparam['phone'].time());
					}else{
						$uid_data = $this->dparam;
					}
				}else{	//设备号是新add的did
					$uid_data = $this->dparam;
					$uid_data['did'] = $did_id;
					$uid_data['did_list'] = $uid_info['did_list'].','.$did_id;
					$uid_data['did_count'] = $uid_info['did_count'] + 1;
				}
				$uid_data['password'] =  $uid_info['password'];
			}else{	//新用户
				$uid_data = $this->dparam;
				$uid_data['did'] = $did_id;
				$uid_data['did_list'] = $did_id;
				$uid_data['did_count'] = 1;
				$uid_data['token'] = md5($this->dparam['phone'].time());
			}
			$uid_data['logintime'] = time();

			if(!empty($this->dparam['password']) && !empty($this->dparam['id_code']))
			{
				$uid_data['password'] = md5($this->dparam['password']);
				$uid_data['token'] = md5($this->dparam['phone'].time());
			}
			try {
				if(!$uid_objectId) E("新增uid失败");
				if(!$did_id) E("新增did失败");
				if(!$this->getCkDidLogInfo($uid_objectId))
				{
					$didLog_data = $this->dparam;
					$didLog_data['uid'] = $uid_objectId;
					$didLog_data['did'] = $did_id;
					if(!A('DidLog:addDidLog',[$didLog_data,true])) E("新增didlog失败");
				}
				// //检查是否存在邀请页的好友关系
				// $friend_sfuid = $this->returnBindSfuid($this->dparam['phone']);
				// if(!empty($friend_sfuid))
				// {
				// 	$uid_data['sfuid'] = $friend_sfuid['sfuid'];
				// 	$friend_where = "phone = '{$this->dparam['phone']}'";
				// 	if(!M('friend_log')->where($friend_where)->save(['status'=>2],true)) E("更新friendlog失败");
				// 	##绑定好友记录uid_log
				// 	$uidlog_arr = ['uid'=> $friend_sfuid['sfuid'],'score_source'=> $uid_objectId,'score_info'=> '绑定好友','score_type'	=> 2,'status'=> 2];
				// 	//如果是首次邀请徒弟则奖励5元 其余不奖励
				// 	if(!M('uid_log')->where(['uid' => ['=', $friend_sfuid['sfuid']], 'score_type' => ['=', 2]],['and'])->field('id')->count())
				// 	{
				// 		$uidlog_arr['status'] 		= 2;
				// 		$uidlog_arr['price']  		= 2;
				// 		$uidlog_arr['score_info']	= '首次绑定好友奖励2元';
				// 		$sql = "UPDATE gw_uid SET price = price+2 WHERE objectId='{$friend_sfuid['sfuid']}'";
				// 		M()->query($sql);
				// 	}
				// 	M('uid_log')->add($uidlog_arr) or E('绑定失败');
				// }



				if(!A('Uid:updateUid',[$uid_where,$uid_data,true])) E("更新uid失败");

				$login_data = $this->dparam;
				$login_data['uid'] = $uid_objectId;
				$login_data['did'] = $did_id;
				if(!A('UidLoginLog:addUidLoginLog',[$login_data])) E("用户登入记录失败");

			} catch (Exception $e) {
				M()->rollback();
				info($e->getMessage(),-1);
			}
			M()->commit();
			return $uid_objectId;
	}

	/**
	 * [person_info 个人中心数据]
	 * {"user_id":"kQb9frCvDR"}
	 */
	//SELECT * FROM gw_uid_log WHERE createdAt < date_sub(curdate(),interval 1 day)
	public function personInfo()
	{
		if(empty($this->dparam['user_id'])) info('user_id不存在',-1);
		//取出用户最近7天的预结收入总和
		$predict_total = M()->query("SELECT sum(price) as predict FROM gw_uid_log WHERE createdAt > date_sub(curdate(),interval 7 day) AND status = 1 AND uid = '{$this->dparam['user_id']}'");
		//取出用户信息
		$uid_info =  A('Uid:getInfo',["objectId = '{$this->dparam['user_id']}'",'*','single']);
		if(empty($uid_info)) info('用户不存在',-1);
		$data = [
			'msg' => '请求成功',
			'status' => 1,
			'sfuid' => $uid_info['sfuid'],
			'nickname' => $uid_info['nickname'],
			'head_img' => $uid_info['head_img'],
			'balance' => (string)$uid_info['price'],	//可用余额
			'predict' => empty($predict_total['predict']) ? '0': (string)$predict_total['predict'],	//预估收入
			'total' => (string)($uid_info['price']+$uid_info['pnow']+$uid_info['pend']),	//总收入
			'withdrawn' => (string)$uid_info['pend'],	//已提现
			'processing' => (string)$uid_info['pnow'],	//提现处理中
		];
		info($data);
	}

	//{"user_id":"NPfk0woYpJ"}
	/**
	 * [withdrawals 提现明细]
	 */
	public function withdrawals()
	{
		$pnow_list = A('Pnow:getPnowInfo',["uid = '{$this->dparam['user_id']}'"]);
		// D($pnow_list);
		if(!empty($pnow_list))
		{
			foreach ($pnow_list as $k => $v) {
				$temp = [];
				if($v['status'] < 4){
					$temp['msg'] = $v['errmsg'];
				}elseif($v['status'] == 5){
					$temp['msg'] = $v['duiba_success'];
				}elseif($v['status'] == 6){
					$temp['msg'] = $v['duiba_end_errmsg'];
				}
				if(empty($temp['msg'])) $temp['msg'] = '请耐心等待';
				$temp['date_time'] = $v['updatedAt'];
				$temp['price'] = $v['price'];
				$temp['status'] = $v['status'];
				$arr[] = $temp;
			}
		}else{
			$arr = [];
		}

		// D($arr);die;
		info('请求成功',1,$arr);
	}
	//验证改用户是否可以绑定好友
	public function checkbindMasters($uid, $sfuid, $type = false) {
		if($uid == $sfuid)
			return $type ? : '不允许绑定自己';

		$where = [
			'uid' => ['=', $uid]
		];
		//判断该用户是否淘宝授权过 且淘宝授权记录只能有一次
		$a = M('taobao_log')->where($where)->field('taobao_id')->select('single');
		$count = M('taobao_log')->where(['taobao_id' => ['=', $a['taobao_id']]])->count();
		if($count < 1)
			return $type ? : '请您先淘宝授权';
		else if($count > 1)
			return $type ? : '您已经不是新用户啦';
		//徒弟 师傅如果是一个淘宝授权账号  禁止绑定关系
		$c = M('taobao_log')->where("uid in('{$uid}','{$sfuid}') AND taobao_id='{$a['taobao_id']}'")->field('taobao_id')->count();
		if($c == 2)
			return $type ? : '不允许淘宝授权账号一样的进行绑定好友';
		else if($c < 1)
			return $type ? : '您的好友可能还未允许淘宝授权';
		//用户设备号只有一次记录 才允许绑定好友关系
		$data = M('uid')->where(['objectId' => ['=',$uid]])->field('imei,idfa,bdid,idfa,uuid')->select('single');
		$sql  = "select * from gw_did_log where (bdid='{$data['bdid']}' OR idfa='{$data['idfa']}' AND uuid='{$data['uuid']}') OR (imei='{$data['imei']}')";
		$data  = M()->query($sql,'all');
		if(count($data) > 1)
			return $type ? : '您已经不是新用户啦';
		//杜绝出现互绑情况 比如 1的徒弟是2  1的师傅是2
		$where = [
			'uid' 		 => ['=', $uid],
			'score_type' => ['=', 2],
			['and']
		];
		$n = M('uid_log')->field('score_source')->where($where)->select();
		if(!empty($n)) {
			$n = array_column($n,'score_source');
			if(in_array($sfuid, $n))
				return $type ? : '他已经是您的好友了呀';
		}
		return true;
	}
	/**
	 * [bindMasters 绑定好友关系]
	 */
	public function bindMasters($type = false,$objectId = '', $sfuid = '')
	{
		//分配变量
		if(!$objectId || !$sfuid) {
			if(!empty($this->dparam['user_id']) && !empty($this->dparam['sfuid'])) {
				$objectId = $this ->dparam['user_id'];
				$sfuid    = $this ->dparam['sfuid'];
			} else {
				$type ? : info('缺少参数');
			}
		}
		/**
		 * 如果是特邀用户则需要先查出来特邀用户的uid
		 */
		$ShiFu = M('uid') ->where("objectId = '{$sfuid}' OR Invitation_code = '{$sfuid}'") ->field('objectId,nickname') ->select('single');
		if(isset($ShiFu['objectId']) && isset($ShiFu['nickname'])) {
			$sfuid = $ShiFu['objectId'];
			$nickname = $ShiFu['nickname'];
		}
		else {
			return $type ? : info('该好友账号可能还未注册');
		}
		/**
		 * 判断该用户是否存在表中
		 */
		$user = M('uid')->field('sfuid,nickname')->where(['objectId'=> ['=',$objectId]])->select('single');
		if(!$user)
			return $type ? : info('您赶紧去登录吧');
		/**
		 * 判断该用户之前是否绑定过师傅
		 */
		if(!empty($user['sfuid']))
			return $type ? : info('您已经填写过邀请人了');
		$nick = $user['nickname'];
		$value = $this->checkbindMasters($objectId, $sfuid, $type);
		if($value !== true)
			info($value,-1);

		// /**
		//  * 设备号  淘宝账号 只有一次记录的才认可为新用户 允许绑定好友关系
		//  */
		// $a = M('taobao_log')->where(['uid' => $arr])->field('taobao_id')->count();
		// if($a < 1)
		// 	info('请您先淘宝授权');
		// /**
		//  * [徒弟 师傅如果是一个淘宝授权账号  禁止绑定关系]
		//  */
		// $c = M('taobao_log')->where("uid = '{$objectId}' OR uid='{$sfuid}'")->field('taobao_id')->select();
		// if(count($c) == 2) {
		// 	$c = array_column($c,'taobao_id');
		// 	$c[0] != $c[1] or info('不允许淘宝授权账号一样的进行绑定好友');
		// } else if(count($c) >= 1) {
		// 	info('您的好友还没淘宝授权');
		// }
		// $b = count(M('did_log')->where(['uid' => $arr])->field('uid')->select());
		// if($a > 1 && $b > 1)
		// 	info('只有新注册用户淘宝授权成功才能绑定师傅');
		// /**
		//  * 杜绝出现互绑情况 比如 1的徒弟是2  1的师傅是2
		//  */
		// $n = M('uid_log')->field('score_source')->where(['uid' => ['=', $objectId]])->select();
		// if(!empty($n)) {
		// 	foreach($n as $v) {
		// 		if($v['score_source'] == $sfuid)
		// 			info('他已经是您的好友了呀');
		// 	}
		// }
		/**
		 * 绑定师徒关系  失败直接抛出异常进行回滚
		 */
		try {
			M()->startTrans();
			$arr = [
				'uid' 			=> $sfuid,
				'score_source'	=> $objectId,
				'score_info'	=> '绑定好友',
				'score_type'	=> 2,
				'status'		=> 2,
			];
			/**
			 * [$a 进行消息通知用户]
			 */
			$a = [
				[
					'uid'		 => $sfuid,
					'content'	 => $nick.'绑定了您为好友'
				],
				[
					'uid'		=> $objectId,
					'content'	=> '您成功绑定了'.$nickname.'为好友'
				],
			];
			//如果是首次邀请徒弟则奖励5元 其余不奖励
			if(!M('uid_log')->where(['uid' => ['=', $sfuid], 'score_type' => ['=', 2]],['and'])->field('id')->count()) {
				$sql = "UPDATE gw_uid SET price = price+2 WHERE objectId='{$sfuid}'";
				M()->query($sql);
				$arr['status'] 		= 2;
				$arr['price']  		= 2;
				$arr['score_info']	= '首次绑定好友奖励2元';
			}
			M('uid')->where(['objectId' => ['=',$objectId]])->save(['sfuid' => $sfuid]) or E('绑定失败');

			M('uid_log')->add($arr) or E('绑定失败');

			M('message')->batchAdd($a) or E('绑定失败');

		} catch(Exception $e) {
			M()->rollback();
			info($e->getMessage(),-1);
		}
		M()->commit();
		return $type ? true : info('绑定成功');
	}

	//{"user_id":"ZRzjAdppve"}
	/**
	 * [message 消息]
	 */
	public function message()
	{
		$msg_where = " uid = '{$this->dparam['user_id']}' ";
		$msg_field = " createdAt as date_time , content as msg";
		$msg_info = A('Message:getMsg',[$msg_where,$msg_field]);
		info('请求成功',1,$msg_info);
	}

	/**
	 * [uid_info 用户头像和昵称]
	 */
	public function uid_info()
	{
		$uid_info =  A('Uid:getInfo',["objectId = '{$_POST['user_id']}'",'nickname,head_img','single']);
		info('请求成功',1,$uid_info);
	}

	/**
	 * [returnBindSfuid 返回邀请页建立好友关系的师傅]
	 */
	public function returnBindSfuid($phone)
	{
		$where = [
			'status' => ['=', 1],
			'phone'	 => ['=', $phone],
			['and'],
		];
		$friend_exisit = M('friend_log')->where($where)->select('single');
		return $friend_exisit;
	}

	/**
	 * [addFrieldLog 邀请页建立好友关系]
	 */
	public function addFrieldLog()
	{
		if(!$_REQUEST['phone'] || !$_REQUEST['user_id']) info('数据不完整',-1);
	    if(!preg_match("/^1[34578]\d{9}$/",$_REQUEST['phone'])) info('非法手机号',-1);

		$is_new_user = M()->query("select id from gw_uid where phone ='{$_REQUEST['phone']}' ",'single',true);
		if(!empty($is_new_user)) info('亲,你已经是惠淘会员了!',-1);
		$friend_exisit = M()->query("select sfuid from gw_friend_log where phone = '{$_REQUEST['phone']}' ",'single',true);
		if(!empty($friend_exisit)) info('亲,您已经被邀请过!',-1);
		$add_data = ['phone'=>$_REQUEST['phone'],'sfuid'=>$_REQUEST['user_id']];
		if(M('friend_log')->add($add_data,true)) info('您已成功被邀请!',1);
	}
}
