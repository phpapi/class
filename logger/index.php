<?php

require_once(__DIR__.'/log.class.php');

use logger\fileLogger\logger;

Logger::info(json_encode(array(1,2,3)),'news');
Logger::debug('sdf');