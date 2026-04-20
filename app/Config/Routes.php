<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin', 'Home::admin');
$routes->get('admin/', 'Home::admin');

$routes->group('', ['filter' => ['guest:customer']], static function ($routes) {
    $routes->get('login', 'AuthController::showCustomerLogin');
    $routes->post('login', 'AuthController::loginCustomer');
    $routes->get('register', 'AuthController::showRegister');
    $routes->post('register', 'AuthController::register');
});

$routes->group('admin', ['filter' => ['guest:admin']], static function ($routes) {
    $routes->get('login', 'AuthController::showAdminLogin');
    $routes->post('login', 'AuthController::loginAdmin');
});

$routes->post('logout', 'AuthController::logout', ['filter' => ['auth']]);

$routes->group('', ['filter' => ['auth:customer', 'role:customer']], static function ($routes) {
    $routes->get('dashboard', 'CustomerDashboardController::index');
    $routes->get('my-bookings', 'CustomerBookingsController::index');
    $routes->get('account', 'CustomerAccountController::index');
});

$routes->group('admin', ['filter' => ['auth:admin', 'role:admin']], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    $routes->get('rooms', 'RoomsController::index');
    $routes->get('rooms/create', 'RoomsController::create');
    $routes->post('rooms', 'RoomsController::store');
    $routes->get('rooms/(:num)/edit', 'RoomsController::edit/$1');
    $routes->post('rooms/(:num)', 'RoomsController::update/$1');
    $routes->post('rooms/(:num)/delete', 'RoomsController::delete/$1');

    $routes->get('tenants', 'TenantsController::index');
    $routes->get('tenants/create', 'TenantsController::create');
    $routes->post('tenants', 'TenantsController::store');
    $routes->get('tenants/(:num)/edit', 'TenantsController::edit/$1');
    $routes->post('tenants/(:num)', 'TenantsController::update/$1');
    $routes->post('tenants/(:num)/delete', 'TenantsController::delete/$1');

    $routes->get('bookings', 'BookingsController::index');
    $routes->get('bookings/create', 'BookingsController::create');
    $routes->post('bookings', 'BookingsController::store');
    $routes->get('bookings/(:num)/edit', 'BookingsController::edit/$1');
    $routes->post('bookings/(:num)', 'BookingsController::update/$1');
    $routes->post('bookings/(:num)/delete', 'BookingsController::delete/$1');
});
