<?php

namespace test\api;

use PHPUnit\Framework\TestCase;
use ash\api;



final class StringOpsTest
extends TestCase
{

	private function _produceApi() {
		return new api\StringOps();
	}

	public function testTeqStringString() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('teq', 'string');

		$val = $api->$method('foo', 'bar');
		$this->assertFalse($val);
		$this->assertInternalType('boolean', $val);

		$this->assertTrue($api->$method('foo', 'foo'));
		$this->assertTrue($api->$method('', ''));
	}

	public function testTneStringString() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('tne', 'string');

		$val = $api->$method('foo', 'bar');
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method('foo', 'foo'));
		$this->assertFalse($api->$method('', ''));
	}

	public function testPinStringArray() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('pin', 'array');

		$val = $api->$method('foo', [ 'foo' => 1 ]);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method('foo', []));
	}
}
