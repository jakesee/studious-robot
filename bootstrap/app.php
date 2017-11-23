<?php
session_start();

require_once __DIR__.'/../vendor/autoload.php';
use Respect\Validation\Validator as v;

final class Application
{
	public $container;
	private $app;

	public function __construct()
	{
		$this->app = new \Slim\App([
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

	private function injectDependencies()
	{
		// Custom valdiation library
		v::with('VUOX\\Components\\Validation\\Rules');

		// inject custom Response object to be used for deffered rendering of template
		$this->container['response'] = function($container) {
			$headers = new \Slim\Http\Headers(['Content-Type' => 'text/html; charset=UTF-8']);
			$template = new \Braincase\Slim\Template($container, __DIR__ . '/../resources/views/');
			$response = new \Braincase\Slim\Response($template, 200, $headers);

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
		$app = $this->app;
		$container = $this->container;

		require_once __DIR__ . '/../routes/web.php';
	}
}
