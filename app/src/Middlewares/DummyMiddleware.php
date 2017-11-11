<?php
namespace VUOX\Middlewares;

class DummyMiddleware extends Middleware
{
	protected $id;

	public function __construct($container, $id)
	{
		parent::__construct($container);

		$this->id = $id;
	}

	public function __invoke($request, $response, $next)
	{
		$this->response->getBody()->write("Before DummyMiddleware: $this->id \n");
		
		$response = $next($request, $response);

		$this->response->getBody()->write("After DummyMiddleware: $this->id \n");

		return $response;
	}
}