<?php

use VUOX\Models\User;

// public area
$app->get('/', 'VUOX\Controllers\PublicController:getHome')->setName('public.home');
$app->get('/hosting', 'VUOX\Controllers\PublicController:getHosting')->setName('public.hosting');
$app->get('/domain', 'VUOX\Controllers\PublicController:getDomain')->setName('public.domain');
$app->get('/support', 'VUOX\Controllers\PublicController:getSupport')->setName('public.support');

$app->group('', function() {
	$this->get('/signup', 'VUOX\Controllers\GuestController:getSignUp')->setName('guest.signup');
	$this->post('/signup', 'VUOX\Controllers\GuestController:postSignUp');
	$this->get('/signin', 'VUOX\Controllers\GuestController:getSignIn')->setName('guest.signin');
	$this->post('/signin', 'VUOX\Controllers\GuestController:postSignIn');
})->add(new \VUOX\Controllers\GuestController($container));


$app->group('/me', function() {
	$this->get('/signout', 'VUOX\Controllers\MeController:getSignOut')->setName('me.signout');
	$this->get('/dashboard', 'VUOX\Controllers\MeController:getDashboard')->setName('me.dashboard');
	$this->get('/services', 'VUOX\Controllers\MeController:getServices')->setName('me.services');

	$this->get('/profile', 'VUOX\Controllers\MeController:getProfile')->setName('me.profile');
	$this->post('/profile', 'VUOX\Controllers\MeController:postProfile');
	$this->post('/password', 'VUOX\Controllers\MeController:postPassword')->setName('me.password');

	$this->get('/contacts', 'VUOX\Controllers\MeController:getContacts')->setName('me.contacts');
	$this->post('/contact', 'VUOX\Controllers\MeController:postContact');
	$this->get('/contact/{id:[0-9]+}', 'VUOX\Controllers\MeController:editContact')->setName('me.contact.edit');
	$this->post('/contact/{id:[0-9]+}', 'VUOX\Controllers\MeController:putContact');

	$this->get('/cart', 'VUOX\Controllers\MeController:getCart')->setName('me.cart');
	$this->post('/cart', 'VUOX\Controllers\MeController:postCart'); // update cart
	$this->post('/cart/empty', 'VUOX\Controllers\MeController:postCartEmpty')->setName('me.cart.empty');
	$this->post('/cart/add', 'VUOX\Controllers\MeController:postCartAdd')->setName('me.cart.add'); // add items to cart
	$this->post('/cart/remove', 'VUOX\Controllers\MeController:postCartRemove')->setName('me.cart.remove'); // add items to cart
	$this->post('/cart/update', 'VUOX\Controllers\MeController:postCartUpdate')->setName('me.cart.update'); // update item options
	$this->post('/cart/checkout', 'VUOX\Controllers\MeController:postCartCheckout')->setName('me.cart.checkout'); // check cart and calculate totals
	$this->post('/cart/confirm', 'VUOX\Controllers\MeController:postCartConfirm')->setName('me.cart.confirm'); // confirms the order
	
})->add(new \VUOX\Controllers\MeController($container));

$app->group('/admin', function() {
	$this->get('/orders/{status}', 'VUOX\Controllers\AdminController:getOrders')->setName('admin.orders');
	$this->get('/order/{id:[0-9]+}', 'VUOX\Controllers\AdminController:getOrder')->setName('admin.order');

	$this->post('/orderline/{id:[0-9]+}', 'VUOX\Controllers\AdminController:putOrderline')->setName('admin.orderline.edit');
	$this->post('/orderline/delete', 'VUOX\Controllers\AdminController:deleteOrderline')->setName('admin.orderline.delete');

})->add(new \VUOX\Controllers\AdminController($container));

$app->group('/users/{id:[0-9]+}', function() {
	$this->get('', 'VUOX\Controllers\Admin:getUser');
});

use Respect\Validation\Validator as v;
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