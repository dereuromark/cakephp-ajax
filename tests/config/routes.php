<?php

namespace Ajax\Test\App\Config;

use Cake\Routing\RouteBuilder;

/**
 * @phpcs:disable
 * @var \Cake\Routing\RouteBuilder $routes
 */
$routes->scope('/', function (RouteBuilder $routes) {
	$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
	$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
});
