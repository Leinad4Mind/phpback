<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * PHPBack routes. Auto-routing is disabled: every endpoint is declared
 * explicitly with its HTTP verb. All state-changing endpoints are POST and
 * are therefore covered by the global CSRF filter (see Config\Filters).
 */

$routes->get('/', 'Home::index');

/*
 * -------------------------------------------------------------------------
 * Installer / upgrader (self-locks once the app is installed)
 * -------------------------------------------------------------------------
 */
$routes->get('install', 'Install::index');
$routes->post('install/run', 'Install::run');

/*
 * -------------------------------------------------------------------------
 * Public pages (Home)
 * -------------------------------------------------------------------------
 */
$routes->get('home', 'Home::index');
$routes->post('home/search', 'Home::search');

$routes->get('home/category/(:num)', 'Home::category/$1');
$routes->get('home/category/(:num)/(:segment)', 'Home::category/$1/$2');
$routes->get('home/category/(:num)/(:segment)/(:segment)', 'Home::category/$1/$2/$3');
$routes->get('home/category/(:num)/(:segment)/(:segment)/(:segment)/(:segment)/(:num)', 'Home::category/$1/$2/$3/$4/$5/$6');

$routes->get('home/idea/(:num)', 'Home::idea/$1');
$routes->get('home/idea/(:num)/(:segment)', 'Home::idea/$1');

$routes->get('home/profile/(:num)', 'Home::profile/$1');
$routes->get('home/profile/(:num)/(:segment)', 'Home::profile/$1');

$routes->get('home/login', 'Home::login');
$routes->get('home/login/(:segment)', 'Home::login/$1');
$routes->get('home/login/(:segment)/(:num)', 'Home::login/$1/$2');

$routes->get('home/postidea', 'Home::postidea');
$routes->get('home/postidea/(:segment)', 'Home::postidea/$1');

$routes->get('home/register', 'Home::register');
$routes->get('home/register/(:segment)', 'Home::register/$1');

/*
 * -------------------------------------------------------------------------
 * Public form processors (Action)
 * -------------------------------------------------------------------------
 */
$routes->post('action/register', 'Action::register');
$routes->post('action/login', 'Action::login');

// Authenticated user actions (require a logged-in session)
$routes->group('action', ['filter' => 'login'], static function (RouteCollection $routes): void {
    $routes->post('logout', 'Action::logout');
    $routes->post('vote', 'Action::vote');
    $routes->post('unvote', 'Action::unvote');
    $routes->post('changepassword', 'Action::changepassword');
    $routes->post('newidea', 'Action::newidea');
    $routes->post('comment', 'Action::comment');
    $routes->post('flag', 'Action::flag');
});

// Serve an idea attachment through a controller (files live outside web root)
$routes->get('download/attachment/(:num)', 'Download::attachment/$1');

/*
 * -------------------------------------------------------------------------
 * Admin panel (Admin) — login page is public, the rest requires a level
 * -------------------------------------------------------------------------
 */
$routes->get('admin', 'Admin::index');
$routes->get('admin/index', 'Admin::index');
$routes->get('admin/index/(:segment)', 'Admin::index/$1');
$routes->post('adminaction/login', 'Adminaction::login');

$routes->group('admin', ['filter' => 'admin:1'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('ideas', 'Admin::ideas');
    $routes->post('ideas', 'Admin::ideas');
});
$routes->group('admin', ['filter' => 'admin:2'], static function (RouteCollection $routes): void {
    $routes->get('users', 'Admin::users');
    $routes->get('users/(:num)', 'Admin::users/$1');
});
$routes->group('admin', ['filter' => 'admin:3'], static function (RouteCollection $routes): void {
    $routes->get('system', 'Admin::system');
});

/*
 * -------------------------------------------------------------------------
 * Admin mutations (Adminaction) — all POST + CSRF, gated by admin level
 * (levels mirror the legacy 1.3.x isadmin privilege levels)
 * -------------------------------------------------------------------------
 */
$routes->group('adminaction', ['filter' => 'admin:1'], static function (RouteCollection $routes): void {
    $routes->post('deletecomment', 'Adminaction::deletecomment');
    $routes->post('deleteidea', 'Adminaction::deleteidea');
    $routes->post('approveidea', 'Adminaction::approveidea');
    $routes->post('ideastatus', 'Adminaction::ideastatus');
});
$routes->group('adminaction', ['filter' => 'admin:2'], static function (RouteCollection $routes): void {
    $routes->post('banuser', 'Adminaction::banuser');
    $routes->post('unban', 'Adminaction::unban');
});
$routes->group('adminaction', ['filter' => 'admin:3'], static function (RouteCollection $routes): void {
    $routes->post('editsettings', 'Adminaction::editsettings');
    $routes->post('editadmin', 'Adminaction::editadmin');
    $routes->post('addcategory', 'Adminaction::addcategory');
    $routes->post('updatecategories', 'Adminaction::updatecategories');
    $routes->post('deletecategory', 'Adminaction::deletecategory');
});
