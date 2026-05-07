<?php
//1. Load the auloloader so classes are available

require_once __DIR__ . "/../vendor/autoload.php";
//2. Boot the application (load config, bind services, etc.)
$router = new \Thunder\Routing\Router();
$app = new \Thunder\Core\Application($router, __DIR__ . '/../routes/api.php');
//3. Create a Request object from the current HTTP request
$request = \Thunder\Http\Request::fromGlobals();
//4. Hand the request to the app and get a response back
$response = $app->handle($request);
//5. Send the response to the browser
$response->send();


