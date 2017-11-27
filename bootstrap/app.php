<?php
session_start();

require_once __DIR__.'/../vendor/autoload.php';

use Respect\Validation\Validator as v;
use Symfony\Component\Yaml\Yaml;

final class Application
{
	public $container;
	private $app;

	public function __construct()
	{
		$config = $this->parseConfig();

		$this->app = new \Slim\App([
			'settings' => [
				'displayErrorDetails' => true,
				'db' => $config['db']
			]
		]);

		$this->container = $this->app->getContainer();

		$this->injectDependencies();

		$this->addMiddlewares();

		$this->initRoutes();
	}

	public function run()
	{
		$response = $this->app->run(false);
		$this->app->respond($response->render());
	}

	public function test($request, $response)
	{
		$app = $this->app;
		return $app($request, $response);
	}

	public function __get($property)
	{
		if(isset($this->container[$property]))
			return $this->container[$property];
	}

	private function parseConfig()
	{
		return Yaml::parse(file_get_contents(__DIR__ . '/../config.yml'));
	}

	private function injectDependencies()
	{
		// Custom valdiation library
		v::with('VUOX\\Components\\Validation\\Rules');

		// inject custom Response object to be used for deffered rendering of template
		$this->container['response'] = function($container) {
			$response = new \Braincase\Slim\Response($container, __DIR__ . '/../resources/views/');
			return $response->withProtocolVersion($container->get('settings')['httpVersion']);
		};

		$capsule = new \Illuminate\Database\Capsule\Manager;
		$capsule->addConnection($this->container['settings']['db']);
		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		// inject eloquent as our db library
		$this->container['db'] = function($container) use($capsule) {
			return $capsule;
		};

		// Session middleware
		$this->container['session'] = function($container) {
			return new \VUOX\Middlewares\SessionMiddleware($this->container, [
				'lifetime' => '1 hour'
			]);
		};

		// Cross Site Request Forgery
		$this->container['csrf'] = function($container) {
			return new \Slim\Csrf\Guard;
		};

		// validation helper that validates all the form inputs using Respect\Validation
		$this->container['validator'] = function($container) {

			return new \VUOX\Components\Validation\Validator();
		};

		$this->container['flash'] = function($container) {
			return new \Slim\Flash\Messages;
		};
	}

	private function createResponse($container)
	{
		$headers = new \Slim\Http\Headers(['Content-Type' => 'text/html; charset=UTF-8']);
		$template = new \Braincase\Slim\Template($container, __DIR__ . '/../resources/views/');
		$response = new \Braincase\Slim\Response($template, 200, $headers);

		return $response->withProtocolVersion($container->get('settings')['httpVersion']);
	}

	private function addMiddlewares()
	{
		// $app->add(new \VUOX\Middlewares\DummyMiddleware($container, 1));
		// $app->add(new \VUOX\Middlewares\DummyMiddleware($container, 2));
		// $app->add(new \VUOX\Middlewares\DummyMiddleware($container, 3));

		// the order of these is important
		$this->app->add($this->container->session);
		 // $app->add($container->csrf); // comment to disable csrf checks
	}

	private function initRoutes()
	{
		// test route
		$this->app->get('/template', 'VUOX\Controllers\TemplateController:getTemplate');

		// public area
		$this->app->get('/', 'VUOX\Controllers\PublicController:getHome')->setName('public.home');
		$this->app->get('/hosting', 'VUOX\Controllers\PublicController:getHosting')->setName('public.hosting');
		$this->app->get('/domain', 'VUOX\Controllers\PublicController:getDomain')->setName('public.domain');
		$this->app->get('/support', 'VUOX\Controllers\PublicController:getSupport')->setName('public.support');

		$this->app->group('', function() {
			$this->get('/signup', 'VUOX\Controllers\GuestController:getSignUp')->setName('guest.signup');
			$this->post('/signup', 'VUOX\Controllers\GuestController:postSignUp');
			$this->get('/signin', 'VUOX\Controllers\GuestController:getSignIn')->setName('guest.signin');
			$this->post('/signin', 'VUOX\Controllers\GuestController:postSignIn');
		})->add(new \VUOX\Controllers\GuestController($this->container));


		$this->app->group('/me', function() {
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
			
		})->add(new \VUOX\Controllers\MeController($this->container));

		$this->app->group('/admin', function() {
			$this->get('/orders/{status}', 'VUOX\Controllers\AdminController:getOrders')->setName('admin.orders');
			$this->get('/order/{id:[0-9]+}', 'VUOX\Controllers\AdminController:getOrder')->setName('admin.order');

			$this->post('/orderline/{id:[0-9]+}', 'VUOX\Controllers\AdminController:putOrderline')->setName('admin.orderline.edit');
			$this->post('/orderline/delete', 'VUOX\Controllers\AdminController:deleteOrderline')->setName('admin.orderline.delete');

		})->add(new \VUOX\Controllers\AdminController($this->container));

		$this->app->group('/users/{id:[0-9]+}', function() {
			$this->get('', 'VUOX\Controllers\Admin:getUser');
		});

		$this->app->post('/user', function($request, $response) {

			$this->validator->validate($request->getParams(), [
				'email' => v::notEmpty()->noWhiteSpace()->length(1, 8)
			]);

			return $response->withJSON($valid);
		});


		$this->app->get('/middleware/home', function($request, $response) {
			$response->getBody()->write("route:/middleware/home \n");

			return $response;
		})->setName('middleware.home');

		$this->app->get('/middleware', function($request, $response) {

			$nameKey = $this->csrf->getTokenNameKey();
		    $valueKey = $this->csrf->getTokenValueKey();
		    $name = $request->getAttribute($nameKey);
		    $value = $request->getAttribute($valueKey);

			$response->getBody()->write("route:/middleware $nameKey=$name ; $valueKey=$value\n");

			return $this->view->render($response, 'dummy.twig');

		})->setName('middleware');
	}
}
