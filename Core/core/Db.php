<?php
class Db
{
    public $conn = null;
    protected static $obj = null;
    private function __construct()
    {
        /**
        * [$conn 获取PDO实例]
        */
        $this->conn = new PDO(C('DB_DSN'),C('DB_USER'),C('DB_PWD'));
        /**
        * 指定编码集
        */
        !empty(C('DB_CHARSET')) && $this->conn->exec('SET NAMES '.C('DB_CHARSET'));
    }
    public static function getInstance()
    {
        if(is_null(self::$obj))
            self::$obj = new self;
        return self::$obj;
    }
}