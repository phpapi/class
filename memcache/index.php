<?php
require_once(__DIR__ . '/case.php');
require_once(__DIR__.'/MyMemcached.class.php');
$mem = MyMemcached::instance()->getMemcache();
$key = "test";
$t = $mem->set($key, 1);
var_dump($t);