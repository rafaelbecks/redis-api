<?php

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

$app->get("/listar-string/:key",function($key) use($app,$redis){

    try {
        $data = $redis->sMembers($key);

        if(count($data) > 0)
            echo json_encode(array("list" => $data));
        else
            echo json_encode(array("mensaje" => "No existen datos asociados al key"));
        
    } catch (Exception $e) {
        echo $e->getMessage();        
    }
});

$app->put("/remover-elemento/:key",function($key) use($app, $redis){

    try {
        $body = json_decode($app->request->getBody());

        $redis->sRem($key,$body->value);

        echo json_encode(array("mensaje" => "El elemento ".$body->value." ha sido removido del set"));
    } catch (Exception $e) {
        echo $e->getMessage();        
    }

});

$app->delete("/eliminar/:key", function($key) use($redis){

    try {
        
        $result = $redis->delete($key);

        if($result)
        {
            echo json_encode(array("mensaje" => "El set ".$key." ha sido eliminado exitosamente"));
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
});

$app->get("/verificar-existencia/:key/:valor",function($key,$valor) use($redis){

    try {   

        $result = $redis->sIsMember($key,$valor);

        $respuesta = ($result) ? "El valor existe en el set" : "El valor no existe";

        echo json_encode(array("mensaje" => $respuesta));

    } catch (Exception $e) {
        echo $e->getMessage();
    }

});

$app->get("/interseccion/:seta/:setb",function($seta,$setb) use($redis){

    try {
        
        $result = $redis->sInter($seta,$setb);

        $respuesta = (count($result)>0) ? $result : "No existen elementos comunes entre los sets";

        echo json_encode(array("mensaje" => $respuesta));

    } catch (Exception $e) {
        echo $e->getMessage();   
    }

});

$app->put("/interseccion-multiple",function() use($app,$redis)
{
    try {
        $body = json_decode($app->request->getBody());

        $result = call_user_func_array(array($redis, "sInter"), $body->sets);   

        $respuesta = (count($result)>0) ? $result : "No existen elementos comunes entre los sets";

        echo json_encode(array("mensaje" => $respuesta));

    } catch (Exception $e) {
        echo $e->getMessage();
    }
});

$app->run();

?>
