<?php
namespace VUOX\Tests;

use \VUOX\Controllers\GuestController;
use \PHPUnit\Framework\TestCase;
use \Slim\Http\Environment;
use \Respect\Validation\Validator as v;

class GuestControllerTest extends BaseTestCase
{
    private $controller;

    public function setUp()
    {
        self::$app = new \Application();
        $this->controller = new GuestController(self::$app->container);
    }

    public function testCanFailSignUp()
    {
        $request = $this->createRequest('POST', '/signup', [
            'name' => '',
            'email' => '',
            'password' => ''
        ]);

        $data = $this->controller->postSignUp($request, self::$app->response)->getTemplate()->data;
        
        $this->assertEquals('Name must not be empty', $data['errors']['name'][0]);
        $this->assertEquals('Email must not be empty', $data['errors']['email'][0]);
        $this->assertEquals('Password must not be empty', $data['errors']['password'][0]);
        
        $this->assertEquals('Name must have a length between 3 and 60', $data['errors']['name'][1]);
        $this->assertEquals('Email must be valid email', $data['errors']['email'][1]);
        $this->assertEquals('Password must have a length greater than 8', $data['errors']['password'][1]);
    }

	public function testCanSignUp()
	{
		$request = $this->createRequest('POST', '/signup', [
			'name' => 'Bill Gates',
			'email' => 'bill.gates@gmail.com',
			'password' => 'difficultpassword'
		]);

        $response = $this->controller->postSignUp($request, self::$app->response);
        $data = $response->getTemplate()->data;

        $this->assertNull($data['errors']);
        $this->assertLocation($response, '/me/dashboard');
        $this->assertTrue(self::$app->container->session->isValid());
	}

    public function testSignInFail()
    {
        $request = $this->createRequest('POST', '/signin', [
            'email' => 'jakesee@gmail.com',
            'password' => '',
        ]);

        $data = $this->controller->postSignIn($request, self::$app->response)->getTemplate()->data;

        $this->assertFalse($data['error']['result']);
    }

    public function testSignIn()
    {
        $request = $this->createRequest('POST', '/signin', [
            'email' => 'bill.gates@gmail.com',
            'password' => 'difficultpassword'
        ]);

        $response = $this->controller->postSignIn($request, self::$app->response);

        $this->assertLocation($response, '/me/dashboard');
        $this->assertTrue(self::$app->container->session->isValid());
    }

}
