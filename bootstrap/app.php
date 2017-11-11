<?php
session_start();

require_once __DIR__.'/../vendor/autoload.php';

use Respect\Validation\Validator as v;
v::with('VUOX\\Components\\Validation\\Rules');

$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => true,
		'db' => [
			'driver' => 'mysql',
			'host' => 'localhost',
			'database' => 'slim',
			'username' => 'root',
			'password' => 'Redbull25',
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => '',
		]
	]
]);


$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->settings['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// inject eloquent as our db library
$container['db'] = function($container) use($capsule) {

	return $capsule;
};

// Session middleware
$container['session'] = function($container) {
	return new \VUOX\Middlewares\SessionMiddleware($container, [
		'lifetime' => '1 hour'
	]);
};

// Cross Site Request Forgery
$container['csrf'] = function($container) {
	return new \Slim\Csrf\Guard;
};

// validation helper that validates all the form inputs using Respect\Validation
$container['validator'] = function($container) {

	return new \VUOX\Components\Validation\Validator($container->session);
};

$container['flash'] = function($container) {
	return new \Slim\Flash\Messages;
};

// twig view
$container['view'] = function($container) {
	$view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
		'cache' => false, // path/to/cache
		'debug' => true,
	]);

	$view->addExtension(new \Twig_Extension_Debug);

	$view->addExtension(new Slim\Views\TwigExtension($container->router, $container->request->getUri()));

	$view->addExtension($container->session);

	$view->getEnvironment()->addGlobal('flash', $container->flash);

	return $view;
};






// $app->add(new \VUOX\Middlewares\DummyMiddleware($container, 1));
// $app->add(new \VUOX\Middlewares\DummyMiddleware($container, 2));
// $app->add(new \VUOX\Middlewares\DummyMiddleware($container, 3));

// the order of these is important
$app->add($container->session);
 // $app->add($container->csrf); // comment to disable csrf checks

require_once __DIR__ . '/../routes/web.php';