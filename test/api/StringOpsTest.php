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


	public function testPinStringArray() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('pin', 'array');

		$val = $api->$method('foo', [ 'foo' => 1 ]);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method('foo', []));
	}
}
