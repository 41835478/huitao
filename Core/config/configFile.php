<?php
/**
 * 项目目录
 */
defined('DIR_APP')        or define('DIR_APP',DIR.'/Applications/');
/**
 * 控制器目录
 */
defined('DIR_CONTROLLER') or define('DIR_CONTROLLER',DIR_APP.'Controllers/');
/**
 * 模型目录
 */
defined('DIR_MODEL')      or define('DIR_MODEL',DIR_APP.'Models/');
/**
 * 视图目录
 */
defined('DIR_VIEW')       or define('DIR_VIEW',DIR_APP.'Views/');
/**
 * 框架目录
 */
defined('DIR_CORE')       or define('DIR_CORE',DIR.'/Core/');
/**
 * 第三方插件 SDK 目录
 */
defined('DIR_LIB')        or define('DIR_LIB',DIR.'/lib/');
/**
 * 公共函数库目录
 */
defined('DIR_COMMON')     or define('DIR_COMMON',DIR_CORE.'common/');
/**
 * 框架配置文件目录
 */
defined('DIR_CONFIG')     or define('DIR_CONFIG',DIR_CORE.'config/');
/**
 * 框架核心目录
 */
defined('DIR_CORE_FILE')  or define('DIR_CORE_FILE',DIR_CORE.'core/');
defined('EXT')            or define('EXT','.php');
/**
 * ----------------------------华丽的分割线------------------------------------
 */
//加载项目运行所需的文件
/**
 * 加载公共函数库
 */
if(is_file(DIR_COMMON.'function'.EXT))
    require DIR_COMMON.'function'.EXT;
/**
 * 加载路由文件
 */
if(is_file(DIR_CORE_FILE.'Route'.EXT))
    require DIR_CORE_FILE.'Route'.EXT;
/**
 * 加载所有配置信息
 */
if(is_file(DIR_CONFIG.'config'.EXT))
    C(require DIR_CONFIG.'config'.EXT);
/**
 * 加载数据库基类
 */
// is_file(DIR_CORE_FILE.'Db'.EXT)         && require DIR_CORE_FILE.'Db'.EXT;
/**
 * 加载 Model Curd 核心类
 */
if(is_file(DIR_CORE_FILE.'Model'.EXT))
    require DIR_CORE_FILE.'Model'.EXT;

/**
 * 加载顶层控制器类
 */
if(is_file(DIR_CORE_FILE.'Controller'.EXT))
    require DIR_CORE_FILE.'Controller'.EXT;

if(is_file(DIR_CORE.'tools/func'.EXT))
    require DIR_CORE.'tools/func'.EXT;
