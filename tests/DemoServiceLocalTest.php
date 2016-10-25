<?php

require 'vendor/autoload.php';

use PhpScotland2016\Demo\Service\Interfaces\DemoServiceRequest;
use PhpScotland2016\Demo\Service\Interfaces\DemoServiceResponse;

use PhpScotland2016\Demo\Service\Impls\DemoServiceLocal;

class DemoServiceLocalTest extends PHPUnit_Framework_TestCase
{
	public function testHandleRequest() {
		$request = new DemoServiceRequest; 
		$request->setParam("wait_for", "1");
		$ut = new DemoServiceLocal;

		$response = $ut->handleRequest($request);
		$this->assertSame($response->getHeader("Content-Type"), "application/json");

		$json = $response->getJson();
		$this->assertTrue(is_string($json));
		$this->assertTrue(strlen($json) > 0);
		$this->assertStringStartsWith("{", $json, "Not valid json?");
		$this->assertStringEndsWith("}", $json, "Not valid json?");

		$arr = $response->getArray();
		$this->assertArrayHasKey("result", $arr);
		$this->assertSame($arr["result"], 0); // expect int not string
		$this->assertArrayHasKey("wait_for", $arr);
		$this->assertSame($arr["wait_for"], 1); // expect int not string
		$this->assertArrayHasKey("msg", $arr);
	}
}

