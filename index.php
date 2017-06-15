<?php

//Dependencias
//sudo apt-get install lamp-server^ 
//sudo apt-get install php5-redis

require 'vendor/autoload.php';

$app = new \Slim\Slim;

$app->response->headers->set('Content-Type', 'application/json');

$redis = new Redis();
$connection = $redis->connect("127.0.0.1", "6379");

$app->get('/', function () {
    echo "Redis Client API";
});

$app->get('/verificacion-conexion', function () use($connection) {
    try {
        if($connection)
            $response = array("mensaje" => "La conexión a redis ha sido configurada exitosamente");
        else
            $response = array("mensaje" => "La conexión a redis no ha podido establecerse");

        echo json_encode($response);

    } catch (Exception $e) {
        $response = array("mensaje" => "Ha ocurrido un error al intentar conectarse a Redis: ".$e->getMessage());

        echo json_encode($response);
    }
});

$app->post('/agregar-string/:key',function($key) use($app,$redis){

    $body = json_decode($app->request->getBody());

    try {
        foreach ($body->value as $member) {
            $redis->sAdd($key,$member);        
        }

        $response = array("mensaje" => "El set ".$key." ha sido actualizado exitosamente");

        echo json_encode($response);
        
    } catch (Exception $e) {
        $response = array("mensaje" => "Ha ocurrido un error al intentar conectarse a Redis: ".$e->getMessage());

        echo json_encode($response);
    }

});

    // $result = $redis->sAdd("test" , "test otro"); 

$app->run();

?>
