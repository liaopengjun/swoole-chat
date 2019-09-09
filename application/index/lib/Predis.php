<?php

namespace app\index\lib;

class Predis {

    public $redis = "";
    public static $pre = "sms_";
    public static $userpre = "user_";
    public static $incr = "incr_";
    public static $fd = 'fd_';
    /**
     * 定义单例模式的变量
     * @var null
     */
    private static $_instance = null;


    public static function getInstance() {

        if(empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    public static function smsKey($phone){
        return self::$pre.$phone;
    }
    public static function userkey($user) {
        return self::$userpre.$user;
    }

    public static function incrkey($incr) {
        return self::$incr.$incr;
    }

    public static function fdkey($fd) {
        return self::$fd.$fd;
    }

    private function __construct() {
        $this->redis = new \Redis();

        $result = $this->redis->connect("127.0.0.1", 6379,7200);
        if($result === false) {
            throw new \Exception('redis connect error');
        }
    }

    /**
     * set
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key, $value, $time = 0 ) {
        if(!$key) {
            return '';
        }
        if(is_array($value)) {
            $value = json_encode($value);
        }
        if(!$time) {
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $time, $value);
    }
    /*
     * 获取时间
     */
    public function ttl($key){

        if($key){
            return '';
        }

        return $this->redis->ttl($key);

    }

    /**
     *  哈希
     */
    public function hset($key,$fields,$value){

        if(!$key) {
            return '';
        }

        return $this->redis->hset($key,$fields, $value);
    }

    /**
     * 哈希获取
     */
    public function hget($key,$fields){

        if(!$key){
            return '';
        }

        return $this->redis->hget($key,$fields);
    }


    /**
     * get
     * @param $key
     * @return bool|string
     */
    public function get($key) {
        if(!$key) {
            return '';
        }

        return $this->redis->get($key);
    }
    //删除
    public function del($key) {
        if(!$key) {
            return '';
        }

        return $this->redis->del($key);
    }

    public function expire($key,$time){
        if(!$key) {
            return '';
        }

        return $this->redis->expire($key,$time);
    }

    //判断一个值是否存在
    public function exists($key){
        if(!$key) {
            return '';
        }

        return $this->redis->exists($key);
    }

    //自增
    public function incr($key){

        if(!$key) {
            return '';
        }

        return $this->redis->incr($key);
    }

    /** sadd
     * @param $value
     * @return int
     */
    public function sadd($key,$value){
        return $this->redis->sadd($key,$value);

    }

    /**
     * @param $key
     * @param $value
     * @return intW
     */
    public function srem($key,$value){
        return $this->redis->srem($key,$value);

    }
    /**
     *
     * @param $key
     * @return array
     */
    public function sMembers($key) {
        return $this->redis->sMembers($key);
    }

    /**
     * @param $name
     * @param $arguments
     * @return array
     */
    public function __call($name, $arguments) {
        //echo $name.PHP_EOL;
        //print_r($arguments);
        if(count($arguments) != 2) {
            return '';
        }
        $this->redis->$name($arguments[0], $arguments[1]);
    }
}