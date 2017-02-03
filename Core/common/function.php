<?php
function D($str)
{
	echo '<pre>';
	if(is_array($str) || is_object($str)) {
		print_r($str);
	} else {
		echo $str;
	}
}
function C($name = null, $value = null)
{
	static $config = [];
	/**
	 * 无参数时 返回所有
	 */
	if(is_null($name))
		return $config;
	/**
	 * 设置和获取值
	 */
	if(is_string($name)) {
		$name = strtoupper($name);
		if(is_null($value))
			return isset($config[$name]) ? $config[$name] : $value;
		else
			$config[$name] = $value;
	}
	if(is_array($name))
		$config = array_merge($config,array_change_key_case($name,CASE_UPPER));
	return null;
}
//发送http状态码SendHttpStatusCode
function SendHttpStatusCode($code = null)
{
   static $status = [
		403 => 'Forbidden',
		404 => 'Not Found',
		500 => 'Internal Server Error'
   ];
   if(isset($status[$code])) {
		header('HTTP/1.1 '.$code.' '.$status[$code]);
		header('Status:'.$code.' '.$status[$code]);
   }
}
/**
 * [info 最终返回数据]
 * @param  [string] $msg    [提示]
 * @param  integer $status [状态码]
 * @param  array   $data   [数据]
 * @result array            附加数据
 * @return [json]          [json]
 */
function info($msg,$status = -1,$data = [],$result = [])
{
	if(is_array($msg))
		$arr = $msg;
	else
		$arr = ['status' => $status,'msg' => $msg,'data' => $data];

	if(!empty(AppController::$aes))
		$arr =  aes_encode(json_encode($arr));

	echo json_encode($arr,JSON_UNESCAPED_UNICODE);
	exit;
}

//curl 数据抓取
function get_curl($url, $data = [])
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	/**
	 *  将curl_exec()获取的信息以字符串返回，而不是直接输出
	 */
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	/**
	 * POST 方式
	 */
	if($data) {
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	}
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
/**
 * [A A('test:index')] 每次都会重新new一下这个对象再去调用该方法
 * 而 A('test') 不管执行多少次 只要test类名不发生变化 永远都会new一次
 * @param [type] $name  [description]
 * @param array  $value [description]
 */
function A($name,$value = [])
{
	static $obj = '';
	static $cla = '';
	static $val = [];
	if($cla != $name || !empty(array_diff($value, $val))) {
		$obj = '';
		$cla = $name;
		$val = $value;
	}
	if(strpos($name,':')) {
		$arr = explode(':',$name);
		if(!empty($arr[0]) && !empty($arr[1])) {
			$arr[0] = $arr[0].'Model';
			$arr[0] =  new $arr[0];
			$handle = call_user_func_array($arr,$value);
			return $handle;
		}
		return;
	} else {
		if(!$obj) {
			$name = $name.'Model';
			$obj =  new $name;
		}
	}
	return $obj;
}
/**
 * [M 实例化Model类]
 * @param [type] $name        [类名]
 * @param [type] $table_sufix [表前缀为null 会去读取配置文件中设置的值]
 * 不管调用多少次 只要表名未发生变化 永远都是new 一次Model类
 */
function M($name = null,$table_sufix = null) {
	static $obj = '';
	static $table = '';
	if($table != $name) {
		$table = $name;
		$obj = '';
	}
	if(!$obj) {
		$obj = new Model($table,$table_sufix);
	}
	return $obj;

}
/**
 * [R 实例化reidsModel]
 */
function R()
{
	static $redis_obj = '';
	if(!$redis_obj)
	{
		if(is_file(DIR_CORE_FILE.'redisModel'.EXT))
			require DIR_CORE_FILE.'redisModel'.EXT;
		$redis_obj = new redisModel();
	}
	return $redis_obj;
}
	/**
	 * 获取随机字符串
	 */
	function randstr($len=6,$format='NUMBER'){

		switch($format){
			case 'MIX':
				$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				break;
			case 'CHAR':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
			case 'UPPER':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
			case 'LOWER':
				$chars='abcdefghijklmnopqrstuvwxyz';
				break;
			case 'NUPPER':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				break;
			case 'NLOWER':
				$chars='abcdefghijklmnopqrstuvwxyz0123456789';
				break;
			case 'NUMBER':
				$chars='123456789';
				break;
			default :
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				break;
		}
		$output='';
		while(strlen($output)<$len) {
			$output.=substr($chars,(mt_rand()%strlen($chars)),1);
		}
		return $output;
	}
/**
 * [model_in 以逗号拼接关联数组]
 * @param  array  $data [description]
 * @return [type]       [description]
 */
function model_in($data = [])
{
	if(is_array($data)) {

		return implode(',',oneDimensionalArray($data));
	}
}
/**
 * [search 从关联数组或者二维数组搜索指定值]
 * @param  [type] $arr [被搜索的数组]
 * @param  [type] $str [要搜索的key]
 * @return [type]      [description]
 */
function search($arr = [], $str = '') {
	if(is_array($arr) && is_string($str)) {
		$arr = oneDimensionalArray($arr);
		foreach($arr as $k => $v) {
			if(is_array($v)) {
				foreach($v as $key => $value) {
					if(is_array($value) && array_key_exists($str,$value)){
						return $value[$str];
					} else {
						if($key == $str) {
							return $value;
						}
					}
				}
			} else {
				if($k === $str) {
					return $v;
				} else if($v === $str) {
					return $arr;
				}
			}
		}
	}
	return;
}
/**
 * [oneDimensionalArray 多维数组转一维数组]
 */
function oneDimensionalArray($arr = [])
{
	foreach($arr as $k => $v) {
		if(is_array($v)) {
			$data = $arr; //原样返回
		} else {
			$data[$k] = $v;
		}
	}
	return $data;
}
/**
 * [formattedData 格式化数据]
 */
function formattedData($data) {
	if(is_array($data)) {
		foreach($data as $k => $v) {
		   $im_data[$k] = addslashes(htmlspecialchars($v));
		}
	} else {
		$im_data = addslashes(htmlspecialchars($data));
	}
	return $im_data;
}

/**
 * 调用语言包
 * $lan语言包名
 */
function L($lan)
{
	return require DIR . '/Core/language/' . $lan . '.php';
}
/**
 * 抛出异常
 */
function E($str)
{
	throw new Exception($str, 1);
}
/**
 * [I 可指定或者自动获取get post raw参数 get:name,post:id raw:data:id 如果是自动 找到第一个则就会返回]
 * @param [type] $name    [变量的名称 支持指定类型]
 * @param string $default [变量不存在的时候默认值]
 */
function I($name, $default = null)
{
	/**
	 * [$count 支持获取二级]
	 */
	$count = substr_count($name,':');
	/**
	 * [$type 如果$name传get post 或raw 则返回对应的所有数据]
	 */
	$type = $name;
	/**
	 * 指定获取二级参数
	 */
	if($count == 2)
		list($type,$name,$field) = explode(':',$name);
	/**
	 * 指定获取1级参数
	 */
	else if($count == 1)
		list($type,$name) = explode(':',$name);
	/**
	 * [$type 转小写进行匹配指定方式]
	 */
	$type = strtolower($type);
	/**
	 * 分配数据
	 */
	switch ($type) {
		case 'get':
			$data = $_GET;
			break;
		case 'post':
			$data = $_POST;
			break;
		case 'raw':
			$data = analysisRaw();
			break;
		default : // 自动获取
			/**
			 * 自动获取的方式顺序 获取到就立马返回
			 */
			$data = [$_POST,$_GET,analysisRaw()];
			/**
			 * [$type 设为空 实现自动获取]
			 */
			$type = '';
	}
	/**
	 * 如果查询所有 直接返回
	 */
	if($name == 'get' || $name == 'post' || $name == 'raw')
		return $data;
	/**
	 * 实现自动获取
	 */
	if(!$type) {
		return search($data,$name);
	/**
	 * 实现指定二级查询
	 */
	} else if(isset($field) && isset($data[$name])) {
		$data = $data[$name];
		$name = $field;
	}
	return isset($data[$name]) ? $data[$name] : $default;
}
/**
 * 解析raw传递过来的数据
 */
function analysisRaw()
{
	return filterEmpty($_SERVER['REQUEST_METHOD'] == 'POST' ? json_decode(rawurldecode(file_get_contents('php://input')),true) : $_GET);
}
/**
 * [filterEmpty 过滤数组空或null]
 */
function filterEmpty($arr = [])
{
	$data = [];
	if(is_array($arr)){
		foreach($arr as $k => $v) {
			if($v !== '' && $v !== null)
				$data[$k] = $v;
		}
	}
	return $data;
}

/**
 * [aes_encode 加密]
 */
function aes_encode($source){

	$data['secret'] = randstr(10);
	$key = substr(MD5($data['secret']-2),8,16);
	$data['content'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $source, MCRYPT_MODE_CBC, $key));
	return $data;

}

/**
 * [aes_decode 解密]
 */
function aes_decode($crypttext,$secret){

	$key = substr(MD5($secret-2),8,16);
	$_str = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($crypttext), MCRYPT_MODE_CBC, $key), "\0");
	$str = json_decode($_str,true);

	return $str;

}
/**
 * [is_key_exists 查询多个key是否存在]
 * @param  [type]  $parame [要查找的key]
 * @param  [type]  $data   [查找的数据]
 * @return boolean         [找到返回1 找不到返回null]
 */
function is_key_exists($parame, $data)
{
	foreach($parame as $v) {
		if(!array_key_exists($v, $data))
			return;
	}
	return 1;
}
/**
 * [generateInvitationCode 给每位用户生成专属邀请码]
 */
function generateInvitationCode($sum) {
	$a = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
	$count = count($a);
	$str   = '';
	while($sum > 0) {
        $str = $a[$sum % $count].$str;
		$sum  = floor($sum/$count);
	}
	while(mb_strlen($str) < 4) {
		$str = '0'.$str;
	}
	return $str;
}
