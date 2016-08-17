<?php

/**
 * Description: MyRedis test
 * License:
 * User: carey
 * Date: 2016/8/8
 * Time: 16:45
 */

require_once(dirname(__DIR__) . '/redis/MyRedis.class.php');

$redis = MyRedis::instance()->redis();
$a = $redis->lpush('t',1);
$a1 = MyRedis::instance()->lpush('tt',11);
var_dump($a,$a1);
var_dump($redis );
