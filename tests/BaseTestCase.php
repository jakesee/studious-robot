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
	protected static $app;

	protected function assertStatus($response, $expectedStatus)
	{
		$this->assertEquals($expectedStatus, $response->getStatusCode());
	}

	protected function assertContentType($response, $expectedContentType)
	{
		$this->assertEquals($expectedContentType, $response->getHeader('Content-Type'));
	}

	protected function assertLocation($response, $expectedLocation)
	{
		$this->assertEquals($expectedLocation, $response->getHeader('Location')[0]);
	}

	public function createRequest($method, $url, array $requestParameters = [])
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
