<?php
/**
 * 路由解析与自动加载
 */

class Route {
    protected static $arr = [];
    public function __construct()
    {




       /**
        * 设定错误和异常处理
        */
       set_error_handler('Route::errorHandler');
       set_exception_handler('Route::exceptionHandler');
        /**
         * 注册自动加载
         */
        spl_autoload_register(function($className) {

            if(is_file(DIR_CONTROLLER.$className.EXT)) {

                require DIR_CONTROLLER.$className.EXT;

            } else if(is_file(DIR_MODEL.$className.EXT)) {

                require DIR_MODEL.$className.EXT;

            } else if(is_file(DIR_CONFIG.$className.EXT)) {

                require DIR_CONFIG.$className.EXT;

            }
        });
        /**
         * 路由处理
         */
        self::pathInfo();
    }
    /**
     * [appDebug 错误记录形式]
     * @str  [String]       [错误信息]
     * @return [type]      [description]
     */
    public static function appDebug($str)
    {
        if(!APP_DEBUG) {
            // message, $file, $lineNumber
            // $st = '';
            $st = $str['msg'].'\r\n'.'错误提示:'.$str['message'].'\r\n'.'错误文件:'.$str['file'].'\r\n'.'错误行号:'.$str['lineNumber'].'\r\n';
            error_log(json_encode($st,JSON_UNESCAPED_UNICODE),3,'error.log');
        } else {
            echo json_encode($str,JSON_UNESCAPED_UNICODE);
        }
    }
    /**
     * [pathInfo 路由处理]
     * 第一种 index.php/test/test
     * 第二种 index.php?c=test&f=test
     * @return [type] [description]
     */
    public static function pathInfo()
    {


        if(substr(php_sapi_name(), 0, 3) == 'cli'){

                $arr = getopt('c:f:');

                self::checkClass($arr['c'],$arr['f']);
        }else{

            if(!empty($_SERVER['PATH_INFO'])) {

                $path = $_SERVER['PATH_INFO'];

            } else {

                $path = !empty($_SERVER['REQUEST_URI']) ? str_replace(DIR_FILE,'',$_SERVER['REQUEST_URI']) : '';
                $path = preg_replace("/\?.*/isu", "", $path);

            }
            if($path) {

                $arr = explode('/',ltrim($path,'/'));
                if(!empty($_GET['c']) && !empty($_GET['f'])) {

                    self::checkClass($_GET['c'],$_GET['f']);

                } else if(!empty($arr[0]) && !empty($arr[1])) {

                    self::checkClass($arr[0],$arr[1]);

                } else if(!empty($arr[0]) && empty($arr[1]) && isset(L('urlMap')[$arr[0]])) {

                    $arr = L('urlMap')[$arr[0]];

                    self::checkClass($arr[0],$arr[1]);

                }
            }
        //SendHttpStatusCode(500);
        }
    }
    /**
     * [checkClass 检查类和方法是否合法]
     * @param  [string] $class [类名]
     * @param  string $func  [方法名]
     * @return [type]        []
     */
    public static function checkClass($class = '', $func = '')
    {
        //类文件首字母转大写
        $cla = ucfirst($class);
        $cla = strpos($cla,'Controller') ? $cla : $cla.'Controller';

        /**
         * 检查类是否已被定义
         */

        if(class_exists($cla)) {
            /**
             *  获取到该类的实例对象
             */
            $cla = new $cla();
            /**
             * 检查类中方法是否存在
             */
            if(method_exists($cla,$func)) {
                call_user_func_array([$cla,$func],self::$arr);
            }
            else {
                die('方法不存在');
            }
        } else {
            die('类不存在');
        }
    }
    /**
    * [exceptionHandler 自定义异常处理]
    */
    public static function exceptionHandler($e)
    {
        $err_log = [
            'msg'           => date('Y-m-d H:i:s').'  捕捉到了一条异常消息',
            'message'       => $e->getMessage(),
            'exceptionCode' => $e->getCode(),
            'file'          => $e->getFile(),
            'lineNumber'    => $e->getLine(),
            'getTrace'      => $e->getTrace(),
        ];
        self::appDebug($err_log);
    }
    /**
     * [errorHandler 自定义错误处理]
    */
    public static function errorHandler($errno, $message, $file, $lineNumber)
    {
        $err_log = [
            'msg'           => date('Y-m-d H:i:s').'  捕捉到了一条错误消息',
            'message'       => $message,
            'file'          => $file,
            'lineNumber'    => $lineNumber,
        ];
        self::appDebug($err_log);
    }
}
