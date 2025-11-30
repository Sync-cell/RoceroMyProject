<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Auth::login');

// -----------------------
// AUTH ROUTES
// -----------------------
$routes->get('admin/login', 'Auth::login');
$routes->get('admin/register', 'Auth::register');
$routes->post('admin/store', 'Auth::store');
$routes->post('admin/authenticate', 'Auth::authenticate');
$routes->get('admin/logout', 'Auth::logout');

$routes->get('admin/activity', 'ActivityController::index');

// -----------------------
// DASHBOARD
// -----------------------
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/admin/dashboard', 'TaskController::index');

// -----------------------
// TASK ROUTES
// -----------------------
$routes->group('task', function($routes) {
    $routes->get('/', 'TaskController::index');
    $routes->get('create', 'TaskController::index');
    $routes->post('store', 'TaskController::store');
    $routes->get('edit/(:num)', 'TaskController::edit/$1');
    $routes->post('update/(:num)', 'TaskController::update/$1');
    $routes->get('delete/(:num)', 'TaskController::delete/$1');
    $routes->get('done/(:num)', 'TaskController::done/$1');
    $routes->get('accept/(:num)', 'TaskController::accept/$1');
    $routes->get('decline/(:num)', 'TaskController::decline/$1');
    $routes->get('tasks/completed', 'TaskController::completed');
    $routes->get('export-csv', 'TaskController::exportCsv');
});

// -----------------------
// IP MONITOR ROUTES  (✓ FIXED)
// -----------------------
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('ip_monitor', 'IpMonitorController::index');
    $routes->post('ip-block/(:num)', 'IpMonitorController::block/$1');
    $routes->post('ip-unblock/(:num)', 'IpMonitorController::unblock/$1');
});
$routes->get('blocked', 'BlockedController::index');




// -----------------------
// MAINTENANCE & UPLOAD
// -----------------------
$routes->post('upload', 'UploadController::upload');
$routes->post('maintenance/toggle', 'MaintenanceController::toggle');
$routes->post('maintenance/add-ip', 'MaintenanceController::addWhitelistIp');
$routes->post('maintenance/remove-ip', 'MaintenanceController::removeWhitelistIp');

// -----------------------
// TEST
// -----------------------
$routes->get('test/ip-status', 'TestIpController::status');

// -----------------------
// API ROUTES
// -----------------------
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('tasks', 'TaskController::index');
    $routes->get('tasks/(:num)', 'TaskController::show/$1');
    $routes->post('tasks', 'TaskController::create');
    $routes->put('tasks/(:num)', 'TaskController::update/$1');
    $routes->delete('tasks/(:num)', 'TaskController::delete/$1');
});
