<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Naosim/Lib/SQLWrapper.php';
require __DIR__ . '/../src/Naosim/Lib/PDOFactory.php';
require_once __DIR__ . '/../src/Naosim/Lib/ValidationErrorException.php';
require_once __DIR__ . '/../src/Naosim/Lib/RequestGetter.php';
require_once __DIR__ . '/../src/Naosim/Lib/ApiTokenAuth.php';

use Naosim\Lib\ValidationErrorException;

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// エラーハンドリング: JSONで返す
// 例外がValidationErrorExceptionの場合のみ400で返す
// それ以外は500
$app->getContainer()['errorHandler'] = function ($c) {
    return function ($request, $response, Exception $exception) {
        return $response->withJson([
            'error' => [
                'message' => $exception->getMessage(),
                'exception_name' => get_class($exception),
                'detail' => $exception->getTraceAsString(),
            ]
        ], $exception instanceof ValidationErrorException ? 400 : 500);
    };
};

// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Run app
$app->run();
