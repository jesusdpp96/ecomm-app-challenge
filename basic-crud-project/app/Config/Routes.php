<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Product routes - Web views
// $routes->get('/', 'Home::index'); TODO: remove this route and component
$routes->get('/', 'ProductController::index');
$routes->get('products', 'ProductController::index');
$routes->get('products/create', 'ProductController::create');
$routes->post('products', 'ProductController::store');
$routes->get('products/(:num)', 'ProductController::show/$1');
$routes->get('products/(:num)/edit', 'ProductController::edit/$1');
$routes->post('products/(:num)', 'ProductController::update/$1');
$routes->delete('products/(:num)', 'ProductController::delete/$1');

// API routes
$routes->group('api', function($routes) {
    $routes->get('products', 'ProductController::apiIndex');
    $routes->post('products', 'ProductController::store');
    $routes->get('products/(:num)', 'ProductController::show/$1');
    $routes->put('products/(:num)', 'ProductController::update/$1');
    $routes->delete('products/(:num)', 'ProductController::delete/$1');
    $routes->get('products/search', 'ProductController::search');
});
