<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Naosim\Lib\SQLWrapper;
use Naosim\Lib\PDOFactoryImpl;
use Naosim\Lib\RequestGetter;


return function (App $app) {
    $container = $app->getContainer();
    $logger = $container->get('logger');

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container, $logger) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    
    
    function execute(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $sql = $parsedBody["sql"];
        $result = (new SQLWrapper(new PDOFactoryImpl()))->exec($sql);
        return $response->withJson($result);
    }
    
    // controller
    $app->post('/select', function (Request $request, Response $response, array $args) {
        // return select($request, $response);
        $req = new RequestGetter($request);
        $sql = $req->getRequired('sql');
        $result = (new SQLWrapper(new PDOFactoryImpl()))->select($sql);
        return $response->withJson($result);
    });
    
    $app->post('/insert', function (Request $request, Response $response, array $args) {
        $parsedBody = $request->getParsedBody();
        $sql = $parsedBody["sql"];
        $result = (new SQLWrapper(new PDOFactoryImpl()))->insert($sql);
        return $response->withJson(array('id'=>$result));
    });
    
    $app->post('/exec', function (Request $request, Response $response, array $args) {
        return execute($request, $response);
    });
    
    $app->get('/show/tables', function (Request $request, Response $response, array $args) {
        $result = (new SQLWrapper(new PDOFactoryImpl()))->showTables();
        return $response->withJson($result);
    });
};
