<?php

if (!isset($routes)) {
	$routes = \Config\Services::routes(true);
}

$routes->group('user', ['namespace' => 'App\Modules\User\Controllers', 'filter' => 'user'], function ($subroutes) {

	/*** Route for Dashboard ***/
	$subroutes->add('', 'Dashboard::index');
	$subroutes->add('index', 'Dashboard::index');
	$subroutes->post('save', 'Dashboard::save');
});
