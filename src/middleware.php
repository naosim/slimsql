<?php

use Slim\App;
use Naosim\Lib\ApiTokenAuth;
use Naosim\Lib\ApiTokenAccountRepositoryImpl;

return function (App $app) {
    // APIトークンによる制限
    $app->add(new ApiTokenAuth($app, 'slimsql-api-token', new ApiTokenAccountRepositoryImpl(require __DIR__ . '/apitokenmap.php')));
};
