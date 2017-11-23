<?php

use VUOX\Models\User;

// test route
$app->get('/template', 'VUOX\Controllers\TemplateController:getTemplate');

// public area
$app->get('/', 'VUOX\Controllers\PublicController:getHome')->setName('public.home');
$app->get('/hosting', 'VUOX\Controllers\PublicController:getHosting')->setName('public.hosting');
$app->get('/domain', 'VUOX\Controllers\PublicController:getDomain')->setName('public.domain');
$app->get('/support', 'VUOX\Controllers\PublicController:getSupport')->setName('public.support');

$app->group('', function() use ($app) {
	$app->get('/signup', 'VUOX\Controllers\GuestController:getSignUp')->setName('guest.signup');
	$app->post('/signup', 'VUOX\Controllers\GuestController:postSignUp');
	$app->get('/signin', 'VUOX\Controllers\GuestController:getSignIn')->setName('guest.signin');
	$app->post('/signin', 'VUOX\Controllers\GuestController:postSignIn');
})->add(new \VUOX\Controllers\GuestController($container));


$app->group('/me', function() use ($app) {
	$app->get('/signout', 'VUOX\Controllers\MeController:getSignOut')->setName('me.signout');
	$app->get('/dashboard', 'VUOX\Controllers\MeController:getDashboard')->setName('me.dashboard');
	$app->get('/services', 'VUOX\Controllers\MeController:getServices')->setName('me.services');

	$app->get('/profile', 'VUOX\Controllers\MeController:getProfile')->setName('me.profile');
	$app->post('/profile', 'VUOX\Controllers\MeController:postProfile');
	$app->post('/password', 'VUOX\Controllers\MeController:postPassword')->setName('me.password');

	$app->get('/contacts', 'VUOX\Controllers\MeController:getContacts')->setName('me.contacts');
	$app->post('/contact', 'VUOX\Controllers\MeController:postContact');
	$app->get('/contact/{id:[0-9]+}', 'VUOX\Controllers\MeController:editContact')->setName('me.contact.edit');
	$app->post('/contact/{id:[0-9]+}', 'VUOX\Controllers\MeController:putContact');

	$app->get('/cart', 'VUOX\Controllers\MeController:getCart')->setName('me.cart');
	$app->post('/cart', 'VUOX\Controllers\MeController:postCart'); // update cart
	$app->post('/cart/empty', 'VUOX\Controllers\MeController:postCartEmpty')->setName('me.cart.empty');
	$app->post('/cart/add', 'VUOX\Controllers\MeController:postCartAdd')->setName('me.cart.add'); // add items to cart
	$app->post('/cart/remove', 'VUOX\Controllers\MeController:postCartRemove')->setName('me.cart.remove'); // add items to cart
	$app->post('/cart/update', 'VUOX\Controllers\MeController:postCartUpdate')->setName('me.cart.update'); // update item options
	$app->post('/cart/checkout', 'VUOX\Controllers\MeController:postCartCheckout')->setName('me.cart.checkout'); // check cart and calculate totals
	$app->post('/cart/confirm', 'VUOX\Controllers\MeController:postCartConfirm')->setName('me.cart.confirm'); // confirms the order
	
})->add(new \VUOX\Controllers\MeController($container));

$app->group('/admin', function() use ($app) {
	$app->get('/orders/{status}', 'VUOX\Controllers\AdminController:getOrders')->setName('admin.orders');
	$app->get('/order/{id:[0-9]+}', 'VUOX\Controllers\AdminController:getOrder')->setName('admin.order');

	$app->post('/orderline/{id:[0-9]+}', 'VUOX\Controllers\AdminController:putOrderline')->setName('admin.orderline.edit');
	$app->post('/orderline/delete', 'VUOX\Controllers\AdminController:deleteOrderline')->setName('admin.orderline.delete');

})->add(new \VUOX\Controllers\AdminController($container));

$app->group('/users/{id:[0-9]+}', function() use ($app) {
	$app->get('', 'VUOX\Controllers\Admin:getUser');
});

$app->post('/user', function($request, $response) {

	$this->validator->validate($request->getParams(), [
		'email' => v::notEmpty()->noWhiteSpace()->length(1, 8)
	]);

	return $response->withJSON($valid);
});


$app->get('/middleware/home', function($request, $response) {
	$response->getBody()->write("route:/middleware/home \n");

	return $response;
})->setName('middleware.home');

$app->get('/middleware', function($request, $response) {

	$nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $name = $request->getAttribute($nameKey);
    $value = $request->getAttribute($valueKey);

	$response->getBody()->write("route:/middleware $nameKey=$name ; $valueKey=$value\n");

	return $this->view->render($response, 'dummy.twig');

})->setName('middleware');
