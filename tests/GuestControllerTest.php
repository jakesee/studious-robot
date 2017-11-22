<?php
namespace VUOX\Tests;

use \PHPUnit\Framework\TestCase;
use \Slim\Http\Environment;

class GuestControllerTest extends BaseTestCase
{
	public function testGuestCanSignUp()
	{
		$response = $this->request('POST', '/signup', [
			'name' => 'Jake See',
			'email' => 'jakesee@gmail.com',
			'password' => 'password'
		]);

		$this->assertNotNull($response);
	}
}