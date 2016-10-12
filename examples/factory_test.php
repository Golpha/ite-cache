<?php

use Ite\Cache\CacheStrategyFactory;

chdir(realpath(__DIR__.'/..'));

require_once './vendor/autoload.php';

$factory = new CacheStrategyFactory();
$cache = $factory->create('file', [CacheStrategyFactory::EXPIRE_KEY => 5]);
//$cache = $factory->create('session', ['session_key' => 'session_factory' ,CacheStrategyFactory::EXPIRE_KEY => 5]);

var_dump($cache);