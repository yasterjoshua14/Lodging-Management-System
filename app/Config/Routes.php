<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('tenant', 'Home::index');
$routes->get('tenant/', 'Home::index');
$routes->get('admin', 'Home::admin');
$routes->get('admin/', 'Home::admin');

$routes->group('', ['filter' => ['guest:tenant']], static function ($routes) {
    $routes->get('login', 'AuthController::showTenantLogin');
    $routes->post('login', 'AuthController::loginTenant');
    $routes->get('register', 'AuthController::showTenantRegister');
    $routes->post('register', 'AuthController::register');
    $routes->get('forgot-password', 'PasswordResetController::showTenantRequest');
    $routes->post('forgot-password', 'PasswordResetController::requestTenant');
    $routes->get('forgot-password/verify', 'PasswordResetController::showTenantVerify');
    $routes->post('forgot-password/verify', 'PasswordResetController::verifyTenant');
    $routes->post('forgot-password/resend', 'PasswordResetController::resendTenant');
    $routes->get('forgot-password/reset', 'PasswordResetController::showTenantReset');
    $routes->post('forgot-password/reset', 'PasswordResetController::resetTenant');
});

$routes->group('', ['filter' => ['guest:admin']], static function ($routes) {
    $routes->get('admin-login', 'AuthController::showAdminLogin');
    $routes->post('admin-login', 'AuthController::loginAdmin');
    $routes->get('admin-forgot-password', 'PasswordResetController::showAdminRequest');
    $routes->post('admin-forgot-password', 'PasswordResetController::requestAdmin');
    $routes->get('admin-forgot-password/verify', 'PasswordResetController::showAdminVerify');
    $routes->post('admin-forgot-password/verify', 'PasswordResetController::verifyAdmin');
    $routes->post('admin-forgot-password/resend', 'PasswordResetController::resendAdmin');
    $routes->get('admin-forgot-password/reset', 'PasswordResetController::showAdminReset');
    $routes->post('admin-forgot-password/reset', 'PasswordResetController::resetAdmin');
});

$routes->post('logout', 'AuthController::logout', ['filter' => ['auth']]);

$routes->group('', ['filter' => ['auth:tenant', 'role:tenant']], static function ($routes) {
    $routes->get('dashboard', 'TenantDashboardController::index');
    $routes->get('myBookings', 'TenantBookingsController::index');
    $routes->get('myAccount', 'TenantAccountController::index');
    $routes->post('myAccount', 'TenantAccountController::update');
});

$routes->group('', ['filter' => ['auth:admin', 'role:admin']], static function ($routes) {
    $routes->get('admin-dashboard', 'AdminDashboardController::index');

    $routes->get('rooms', 'AdminRoomsController::index');
    $routes->get('rooms/create', 'AdminRoomsController::create');
    $routes->post('rooms', 'AdminRoomsController::store');
    $routes->get('rooms/(:num)/edit', 'AdminRoomsController::edit/$1');
    $routes->post('rooms/(:num)', 'AdminRoomsController::update/$1');
    $routes->post('rooms/(:num)/delete', 'AdminRoomsController::delete/$1');

    $routes->get('tenants', 'AdminTenantsController::index');
    $routes->get('tenants/create', 'AdminTenantsController::create');
    $routes->post('tenants', 'AdminTenantsController::store');
    $routes->get('tenants/(:num)/id-document', 'AdminTenantsController::viewIdDocument/$1');
    $routes->post('tenants/(:num)/id-document/delete', 'AdminTenantsController::deleteIdDocument/$1');
    $routes->get('tenants/(:num)/edit', 'AdminTenantsController::edit/$1');
    $routes->post('tenants/(:num)', 'AdminTenantsController::update/$1');
    $routes->post('tenants/(:num)/delete', 'AdminTenantsController::delete/$1');

    $routes->get('bookings', 'AdminBookingsController::index');
    $routes->get('bookings/create', 'AdminBookingsController::create');
    $routes->post('bookings', 'AdminBookingsController::store');
    $routes->get('bookings/(:num)/edit', 'AdminBookingsController::edit/$1');
    $routes->post('bookings/(:num)', 'AdminBookingsController::update/$1');
    $routes->post('bookings/(:num)/delete', 'AdminBookingsController::delete/$1');
});

$routes->addRedirect('tenant/login', 'login');
$routes->addRedirect('tenant/register', 'register');
$routes->addRedirect('tenant/forgot-password', 'forgot-password');
$routes->addRedirect('tenant/dashboard', 'dashboard');
$routes->addRedirect('tenant/bookings', 'myBookings');
$routes->addRedirect('tenant/account', 'myAccount');

$routes->addRedirect('admin/login', 'admin-login');
$routes->addRedirect('admin/forgot-password', 'admin-forgot-password');
$routes->addRedirect('admin/dashboard', 'admin-dashboard');
$routes->addRedirect('admin/rooms', 'rooms');
$routes->addRedirect('admin/rooms/create', 'rooms/create');
$routes->addRedirect('admin/tenants', 'tenants');
$routes->addRedirect('admin/tenants/create', 'tenants/create');
$routes->addRedirect('admin/bookings', 'bookings');
$routes->addRedirect('admin/bookings/create', 'bookings/create');
