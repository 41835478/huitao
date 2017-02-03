<?php
/**
 * 抽象顶层控制器类
 */
abstract class Controller
{
    /**
     * [$variable 存储视图变量]
     */
    protected static $variable = [];
    public function __construct()
    {
    }
    /**
     * [assing 分配变量]
     */
    public function assing($name = '',$value = '')
    {
        is_array($name) ? self::$variable = array_merge($name,self::$variable) : self::$variable[$name] = $value;
        return $this;
    }
    /**
     * [view 加载视图]
     */
    public function view($tpl = 'index')
    {
        /**
         * [$suffix 读取文件后缀 如果没设置就默认后缀.php]
         */
        $suffix = !empty(C('VIEW_SUFFIX')) ? explode(',',C('VIEW_SUFFIX')) : '.php';
        /**
         * 变量导入
         */
        extract(self::$variable);
        /**
         * 加载视图模板
         */
        foreach($suffix as $k => $v){
            /**
             * 加载视图头部文件
             */
            is_file(DIR_VIEW.'top'.$v) && include DIR_VIEW.'top'.$v;
            /**
             *  加载视图模板
             */
            is_file(DIR_VIEW.$tpl.$v) && include DIR_VIEW.$tpl.$v;
            /**
             * 加载视图 尾部文件
             */
            is_file(DIR_VIEW.'button'.$v) && include DIR_VIEW.'button'.$v;
        }

        /**
         * 清空变量值
         */
        self::$variable = [];
    }
}