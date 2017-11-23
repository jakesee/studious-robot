<?php
namespace VUOX\Tests;

use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function setup()
    {
        $this->app = new \Application();
    }

	public function testSampleTest()
	{
		$this->assertTrue(true);
	}
}
