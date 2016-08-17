<?php

/**
 * Description:
 * License:
 * User: carey
 * Date: 2016/8/17
 * Time: 10:22
 * Version:
 * Created by PhpStorm.
 */
class MyMemcached
{
    private $memcache;
    private static $_instance = array();

    /**
     * 构造函数
     * @param string $instance
     */
    private function __construct($instance='MEMCACHE_DEFAULT'){
        $config = include('case.php');
        $conf = $config[$instance];
        if(empty($conf)||(!isset($conf['host'])||!isset($conf['port']))){
            echo "MEMCACHE_CONFIG_NOT_FOUND";
        }
        if ( !class_exists ( 'memcached' )) {
            return "Unable to load class: memcached ";
        }
        $this->memcache = new memcached();
        $this->memcache->addServer($conf['host'],$conf['port']);
        return $this->memcache;
    }

    public static function instance($instance="MEMCACHE_DEFAULT")
    {
        if (!isset(self::$_instance[$instance])||!(self::$_instance[$instance] instanceof self)) {
            self::$_instance[$instance] = new self ($instance);
        }
        return self::$_instance[$instance];
    }

    public function getMemcache(){
        return $this->memcache;
    }
}