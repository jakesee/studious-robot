<?php
namespace VUOX\Tests;

use PHPUnit\Framework\TestCase;
use \Slim\Http\Environment;
use \Slim\Http\Uri;
use \Slim\Http\Headers;
use \Slim\Http\RequestBody;
use \Slim\Http\Request;
use \Slim\Http\Response;

// Reference: http://lzakrzewski.com/2016/02/integration-testing-with-slim/
// TODO: Solve the problem of getting data prior to rendering by twigview
class BaseTestCase extends TestCase
{
	protected $app;
	protected $response;

	public function setup()
	{
		global $app; // bootstrapped by phpunit.xml

		$this->app = $app;
	}

	public function testAppIsInitialized()
	{
		$this->assertNotNull($this->app);
	}

	public function tearDown()
	{
		$this->app = null;
	}

	protected function assertStatus($expectedStatus)
	{
		$this->assertEquals($expectedStatus, $this->response->getStatusCode());
	}

	protected function assertContentType($expectedContentType)
	{
		$this->assertEquals($expectedContentType, $this->response->getHeader('Content-Type'));
	}

	protected function responseData()
	{
		return json_decode((string) $this->response->getBody(), true);
	}

	protected function request($method, $url, array $requestParameters = [])
	{
		$request = $this->prepareRequest($method, $url, $requestParameters);
		$response = new Response;

		$app = $this->app;
		$this->response = $app($request, $response);

		return $this->response;
	}

	private function prepareRequest($method, $url, array $requestParameters)
	{
		$env = Environment::mock([
			'SCRIPT_NAME' => '/index.php',
			'REQUEST_URI' => $url,
			'REQUEST_METHOD' => $method
		]);

		$parts = explode('?', $url);
		if(isset($parts[1]))
		{
			$env['QUERY_STRING'] = $parts[1];
		}

		$uri = Uri::createFromEnvironment($env);
		$headers = Headers::createFromEnvironment($env);
		$cookies = [];

		$serverParams = $env->all();

		$body = new RequestBody();
		$body->write(json_encode($requestParameters));

		$request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

		return $request->withHeader('Content-Type', 'application/json');
	}
}