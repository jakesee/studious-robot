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
		$this->app = new \Application();
	}

	public function testAppIsInitialized()
	{
		$this->assertNotNull($this->app);
	}

	public function tearDown()
	{
		// reset database
        $DB = $this->app->db->getConnection();
        $tables = $DB->select('SHOW TABLES');
        foreach($tables as $table)
        {
            $DB->table($table->Tables_in_slim)->truncate();
        }

        // remove the app
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

	protected function getResponseJson()
	{
		return json_decode((string) $this->response->getBody(), true);
	}

	protected function getResponseData()
	{
		return $this->response->getTemplate()->data;
	}

	protected function route($method, $url, array $requestParameters = [], $handler)
	{
		$request = $this->createRequest($method, $url, $requestParameters);
		
		$this->response = call_user_func_array($handler, array($request, $this->app->response));

		return $this->response;
	}

	public function createRequest($method, $url, array $requestParameters)
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
