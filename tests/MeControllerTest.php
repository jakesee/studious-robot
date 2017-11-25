<?php
namespace VUOX\Tests;

use PHPUnit\Framework\TestCase;
use VUOX\Controllers\MeController;
use VUOX\Controllers\GuestController;

class MeControllerTest extends BaseTestCase
{
	public function setup()
	{
		self::$app = new \Application();
		$this->controller = $controller = new MeController(self::$app->container);

		$guest = new GuestController(self::$app->container);
		$guest->postSignIn($this->createRequest('POST', '/signin', [
			'email' => 'bill.gates@gmail.com',
			'password' => 'difficultpassword'
		]), self::$app->response);

		$this->assertTrue(self::$app->session->isValid());
	}

	public function testCanGetProfile()
	{
		$request = $this->createRequest('GET', 'me/profile');

		$response = $this->controller->getProfile($request, self::$app->response);
		$data = $response->getTemplate()->data;

		$this->assertEquals('Bill Gates', $data['user']['name']);
		$this->assertEquals('bill.gates@gmail.com', $data['user']['email']);
	}

	public function testCanSignOut()
	{
		$request = $this->createRequest('GET', '/signout');
		$response = $this->controller->getSignOut($request, self::$app->response);

		$this->assertFalse(self::$app->session->isValid());
		$this->assertLocation($response, '/');
	}
}