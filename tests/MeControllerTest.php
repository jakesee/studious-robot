<?php
namespace VUOX\Tests;

use PHPUnit\Framework\TestCase;
use VUOX\Controllers\MeController;
use VUOX\Controllers\GuestController;

class MeControllerTest extends BaseTestCase
{
	public function setup()
	{
		$this->app = new \Application();
		$this->controller = $controller = new MeController($this->app->container);

		$guest = new GuestController($this->app->container);
		$guest->postSignIn($this->createRequest('POST', '/signin', [
			'email' => 'bill.gates@gmail.com',
			'password' => 'difficultpassword'
		]), $this->app->response);

		$this->assertTrue($this->app->session->isValid());
	}

    public function testCanSignOut()
    {
        $request = $this->createRequest('GET', '/signout');
        $response = $this->controller->getSignOut($request, $this->app->response);

        $this->assertzFalse($this->app->session->isValid());
        $this->assertLocation($response, '/');
    }

	public function testCanGetProfile()
	{
		$request = $this->createRequest('GET', 'me/profile');

		$response = $this->controller->getProfile($request, $this->app->response);
		$data = $response->getTemplate()->data;

		$this->assertEquals('Bill Gates', $data['user']['name']);
		$this->assertEquals('bill.gates@gmail.com', $data['user']['email']);
	}

    public function testUpdateProfileEmail()
    {
        $request = $this->createRequest('GET', 'me/profile');
        $response = $this->controller->getProfile($request, $this->app->response);
        $data = $response->getTemplate()->data;

        $request = $this->createRequest('POST', 'me/profile', [
            'name' => 'Lucas Fox',
            'email' => 'lucas.fox@microsoft.com',
            'confirm_email' => 'lucas.fox@microsoft.com',
        ]);

        $response = $this->controller->postProfile($request, $this->app->response);
        $this->assertNull($response->getTemplate());
        $this->assertLocation($response, '/me/profile');

        // get the details again to check whether updated correctly
        $request = $this->createRequest('GET', 'me/profile');
        $response = $this->controller->getProfile($request, $this->app->response);
        $data = $response->getTemplate()->data;
        $this->assertEquals('Lucas Fox', $data['user']['name']);
        $this->assertEquals('lucas.fox@microsoft.com', $data['user']['email']);
    }
}
