<?php

if (!isset($routes)) {
	$routes = \Config\Services::routes(true);
}

$routes->group('admin', ['namespace' => 'App\Modules\Admin\Controllers', 'filter' => 'admin'], function ($subroutes) {

	/*** Route for Dashboard ***/
	$subroutes->add('', 'Dashboard::index');
	$subroutes->add('index', 'Dashboard::index');
	$subroutes->post('save', 'Dashboard::save');
	$subroutes->add('test', 'Dashboard::test');
	$subroutes->add('users', 'User::index');
	$subroutes->add('user_add', 'User::user_add');
	$subroutes->post('user_created', 'User::user_created');
	$subroutes->add('users_fetch', 'User::users_fetch');
	$subroutes->post('user_del', 'User::user_delete');
	$subroutes->add('user_edit/(:num)', 'User::user_edit/$1');
	$subroutes->post('user_edit', 'User::user_edit');
	$subroutes->add('userh', 'User::user_deleted');
	$subroutes->add('fetchuserh', 'User::fetch_user_deleted');
	$subroutes->add('bulk', 'Bulk::index');
	$subroutes->add('bulk/(:any)', 'Bulk::index/$1');
	$subroutes->post('bulkcreate', 'Bulk::create');
	$subroutes->post('bulkdelete', 'Bulk::delete');
	$subroutes->post('bulkreset', 'Bulk::reset');
	$subroutes->add('bulkfile/(:any)', 'Bulk::file/$1');
	$subroutes->add('dom', 'Domain::index');
	$subroutes->post('domfetch', 'Domain::domainfetch');
	$subroutes->post('domcreate', 'Domain::domaincreate');
	$subroutes->post('domdel', 'Domain::domaindelete');
	$subroutes->post('domeditfetch', 'Domain::editfetch');
	$subroutes->post('domeditaction', 'Domain::editaction');
	$subroutes->add('domdet/(:num)', 'Domain::detail/$1');
	$subroutes->post('domdetfetch/(:any)', 'Domain::detailfetch/$1');
	$subroutes->add('category', 'Category::index');
	$subroutes->post('ctgfetch', 'Category::categoryfetch');
	$subroutes->post('ctgcreate', 'Category::categorycreate');
	$subroutes->post('ctgdel', 'Category::categorydelete');
	$subroutes->post('ctgeditfetch', 'Category::editfetch');
	$subroutes->post('ctgeditaction', 'Category::editaction');
	$subroutes->add('ctgdet/(:num)', 'Category::detail/$1');
	$subroutes->post('ctgdetfetch/(:any)', 'Category::detailfetch/$1');
	$subroutes->get('group', 'Group::index');
	$subroutes->post('grpfetch', 'Group::fetch');
	$subroutes->post('grpcreate', 'Group::create');
	$subroutes->post('grpeditfetch', 'Group::editfetch');
	$subroutes->post('grpeditaction', 'Group::editaction');
	$subroutes->add('grpdel', 'Group::delete');
	$subroutes->add('grpuseridfetch', 'Group::fecthWithUser');
	$subroutes->add('grpoufetch', 'Group::fecthWithOu');
	$subroutes->add('grpdet/(:num)', 'Group::detail/$1');
	$subroutes->post('grpdetfetch/(:any)', 'Group::detailfetch/$1');
	$subroutes->get('unit', 'Unit::index');
	$subroutes->post('unitfetch', 'Unit::fetch');
	$subroutes->post('unitadd', 'Unit::add');
	$subroutes->post('unitdel', 'Unit::delete');
	$subroutes->post('uniteditfetch', 'Unit::editfetch');
	$subroutes->post('uniteditaction', 'Unit::editaction');
	$subroutes->get('logs', 'Log::logs');
	$subroutes->post('logfetch', 'Log::logfetch');
	$subroutes->add('impcsv', 'Dashboard::importcsv');
	$subroutes->add('impcsvact', 'Dashboard::importcsv_act');
	$subroutes->add('coba', 'Dashboard::coba');
});
