<?php
namespace VUOX\Tests;

use \PHPUnit\Framework\TestCase;
use \Respect\Validation\Validator as v;
use \VUOX\Components\Validation\Validator;

class ValidatorTest extends BaseTestCase
{
    public function setUp()
    {
        $this->app = new \Application();
    }
    public function testValidatorCanFail()
    {
        $validator = new Validator();

        $input = [
            'name' => '',
            'email' => '',
            'password' => ''
        ];

        $validator->validate($input,[
            'name' => v::notEmpty()->length(3, 60),
            'email' => v::notEmpty()->email()->emailAvailable(),
            'password' => v::notEmpty()->length(8),
        ]);

        $this->assertTrue($validator->failed());

        $errors = $validator->getErrors();
        $this->assertEquals('Name must not be empty', $errors['name'][0]);
        $this->assertEquals('Email must not be empty', $errors['email'][0]);
        $this->assertEquals('Password must not be empty', $errors['password'][0]);

        $this->assertEquals('Name must have a length between 3 and 60', $errors['name'][1]);
        $this->assertEquals('Email must be valid email', $errors['email'][1]);
        $this->assertEquals('Password must have a length greater than 8', $errors['password'][1]);
    }

    public function testValidatorCanPass()
    {
        $validator = new Validator();

        $input = [
            'name' => 'Bill Gates',
            'email' => 'bill.gates@gmail.com',
            'password' => 'complicatedpassword'
        ];

        $validator->validate($input,[
            'name' => v::notEmpty()->length(3, 60),
            'email' => v::notEmpty()->email()->emailAvailable(),
            'password' => v::notEmpty()->length(8),
        ]);

        $this->assertFalse($validator->failed());
    }
}
