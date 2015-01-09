<?php
namespace Ajax\Test\App\Config;

use Cake\Routing\Router;

//Router::extensions(['json']);

Router::scope('/', function($routes) {
	$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
	$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
});
