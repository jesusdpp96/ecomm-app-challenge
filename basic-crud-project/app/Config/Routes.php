<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================================================
// PUBLIC ROUTES - No authentication required
// =============================================================================

// Login routes
$routes->get('login', 'LoginController::index');
$routes->post('login', 'LoginController::login');

// Public product views (read-only)
$routes->get('/', 'ProductController::index');
$routes->get('products', 'ProductController::index');
$routes->get('products/(:num)', 'ProductController::show/$1');

// Public API for filtering (read-only)
$routes->get('api/products', 'ProductController::apiIndex');

// =============================================================================
// PROTECTED ROUTES - Authentication required
// =============================================================================

// Protected product management views
$routes->get('products/create', 'ProductController::create', ['filter' => 'auth']);
$routes->get('products/(:num)/edit', 'ProductController::edit/$1', ['filter' => 'auth']);

// Logout (requires authentication)
$routes->get('logout', 'LoginController::logout', ['filter' => 'auth']);

// =============================================================================
// API ROUTES - JSON only, authentication required
// =============================================================================

$routes->group('api', ['filter' => 'auth'], function($routes) {
    // Product CRUD operations (JSON only)
    $routes->post('products', 'ProductController::store');
    $routes->put('products/(:num)', 'ProductController::update/$1');
    $routes->delete('products/(:num)', 'ProductController::delete/$1');
    
    // Additional API endpoints
    $routes->get('products', 'ProductController::apiIndex');
    $routes->get('products/(:num)', 'ProductController::apiShow/$1');
    $routes->get('products/search', 'ProductController::search');
});
