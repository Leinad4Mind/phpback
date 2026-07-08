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
$routes->post('search', 'Home::search');

$routes->get('category/(:num)', 'Home::category/$1');
$routes->get('category/(:num)/(:segment)', 'Home::category/$1/$2');
$routes->get('category/(:num)/(:segment)/(:segment)', 'Home::category/$1/$2/$3');
$routes->get('category/(:num)/(:segment)/(:segment)/(:segment)/(:segment)/(:num)', 'Home::category/$1/$2/$3/$4/$5/$6');

$routes->get('idea/(:num)', 'Home::idea/$1');
$routes->get('idea/(:num)/(:segment)', 'Home::idea/$1');

$routes->get('profile/(:num)', 'Home::profile/$1');
$routes->get('profile/(:num)/(:segment)', 'Home::profile/$1');

$routes->get('login', 'Home::login');
$routes->get('login/(:segment)', 'Home::login/$1');
// (:segment), not (:num): infinite bans redirect here with -1 (cast in Home::login)
$routes->get('login/(:segment)/(:segment)', 'Home::login/$1/$2');

$routes->get('postidea', 'Home::postidea');
$routes->get('postidea/(:segment)', 'Home::postidea/$1');

$routes->get('register', 'Home::register');
$routes->get('register/(:segment)', 'Home::register/$1');

// Google OAuth2 sign-in. GET by design (outside the POST-only CSRF filter);
// the flow is protected by the session-bound `state` value plus PKCE.
$routes->get('auth/google', 'Auth::google');
$routes->get('auth/google/callback', 'Auth::googleCallback');

/*
 * -------------------------------------------------------------------------
 * Legacy `home/…` URLs (pre-2.0.2 links, bookmarks and sent emails)
 * -------------------------------------------------------------------------
 */
$routes->addRedirect('home', '/', 301);
$routes->addRedirect('home/category/(:num)', 'category/(:num)', 301);
$routes->addRedirect('home/category/(:num)/(:segment)', 'category/(:num)/(:segment)', 301);
$routes->addRedirect('home/category/(:num)/(:segment)/(:segment)', 'category/(:num)/(:segment)/(:segment)', 301);
$routes->addRedirect('home/category/(:num)/(:segment)/(:segment)/(:segment)/(:segment)/(:num)', 'category/(:num)/(:segment)/(:segment)/(:segment)/(:segment)/(:num)', 301);
$routes->addRedirect('home/idea/(:num)', 'idea/(:num)', 301);
$routes->addRedirect('home/idea/(:num)/(:segment)', 'idea/(:num)/(:segment)', 301);
$routes->addRedirect('home/profile/(:num)', 'profile/(:num)', 301);
$routes->addRedirect('home/profile/(:num)/(:segment)', 'profile/(:num)/(:segment)', 301);
$routes->addRedirect('home/login', 'login', 301);
$routes->addRedirect('home/login/(:segment)', 'login/(:segment)', 301);
$routes->addRedirect('home/login/(:segment)/(:segment)', 'login/(:segment)/(:segment)', 301);
$routes->addRedirect('home/postidea', 'postidea', 301);
$routes->addRedirect('home/postidea/(:segment)', 'postidea/(:segment)', 301);
$routes->addRedirect('home/register', 'register', 301);
$routes->addRedirect('home/register/(:segment)', 'register/(:segment)', 301);
// A 301 would turn the POST into a GET, so keep a working alias instead.
$routes->post('home/search', 'Home::search');

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
