<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim;

$app->get('/', function () {
    echo "Redis Client API";
});

$app->get('/redis', function () {
    $redis = new Redis();
    $connection = $redis->connect("127.0.0.1", "6379");
});


$app->run();

?>
