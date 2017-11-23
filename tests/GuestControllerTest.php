<?php
namespace VUOX\Tests;

use \VUOX\Controllers\GuestController;
use \PHPUnit\Framework\TestCase;
use \Slim\Http\Environment;
use \Respect\Validation\Validator as v;

class GuestControllerTest extends BaseTestCase
{
    public function testGuestCanFailSignUp()
    {
        $controller = new GuestController($this->app->container);

        $request = $this->createRequest('POST', '/signup', [
            'name' => '',
            'email' => '',
            'password' => ''
        ]);

        $response = $controller->postSignUp($request, $this->app->response);
        $data = $response->getTemplate()->data;
        
        $this->assertEquals('Name must not be empty', $data['errors']['name'][0]);
        $this->assertEquals('Email must not be empty', $data['errors']['email'][0]);
        $this->assertEquals('Password must not be empty', $data['errors']['password'][0]);
        
        $this->assertEquals('Name must have a length between 3 and 60', $data['errors']['name'][1]);
        $this->assertEquals('Email must be valid email', $data['errors']['email'][1]);
        $this->assertEquals('Password must have a length greater than 8', $data['errors']['password'][1]);
    }

 //    public function testGuestCanFailSignup()
 //    {
 //        $response = $this->request('POST', '/signup', [
 //            'name' => '',
 //            'email' => '',
 //            'password' => ''
 //        ]);

 
 //    }

	// public function testGuestCanSignUp()
	// {
	// 	$response = $this->request('POST', '/signup', [
	// 		'name' => 'Bill Gates',
	// 		'email' => 'bill.gates@gmail.com',
	// 		'password' => 'difficultpassword'
	// 	]);

	// 	$data = $response->getTemplate()->data;
 //        print_r($this->app->environment); exit();
 //        $this->assertNull($data['errors']);
	// }
}
