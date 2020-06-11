<?php

declare(strict_types=1);
namespace test\api;

use PHPUnit\Framework\TestCase;
use ash\api;



final class BoolOpsTest
extends TestCase
{

	private function _produceApi() : api\BoolOps {
		return new api\BoolOps();
	}


	public function testBoolInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('bool', 'int');

		$this->assertEquals(false, $api->$method(0));
		$this->assertEquals(true, $api->$method(1));
		$this->assertEquals(true, $api->$method(-1));
	}

	public function testBoolFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('bool', 'float');

		$this->assertEquals(false, $api->$method(0.0));
		$this->assertEquals(true, $api->$method(1.0));
		$this->assertEquals(false, $api->$method(NAN));
	}

	public function testBoolString() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('bool', 'string');

		$this->assertEquals(false, $api->$method(''));
		$this->assertEquals(true, $api->$method(' '));
		$this->assertEquals(true, $api->$method('true'));
		$this->assertEquals(true, $api->$method('false'));
		$this->assertEquals(true, $api->$method('0'));
	}
}
