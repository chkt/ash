<?php

declare(strict_types=1);
namespace test\api;

use PHPUnit\Framework\TestCase;
use ash\api;



final class ArrayOpsTest
extends TestCase
{

	private function _produceApi() : api\ArrayOps {
		return new api\ArrayOps();
	}


	public function testAccArrayString() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('acc', 'string');

		$val = $api->$method([ 'foo' => 'bar'], 'foo');
		$this->assertEquals('bar', $val);
	}

	public function testAccArrayString_noProp() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('acc', 'string');

		$this->expectException(api\OperatorException::class);
		$this->expectExceptionMessage('EXPR bad op "{ foo }[\'bar\']"');

		$api->$method([ 'foo' => 'baz'], 'bar');
	}

	public function testAccArrayInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('acc', 'int');

		$val = $api->$method([ 'foo', 'bar' ], 1);
		$this->assertEquals('bar', $val);
	}

	public function testAccArrayInt_neg() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('acc', 'int');

		$this->expectException(api\OperatorException::class);
		$this->expectExceptionMessage('EXPR bad op "[ 2 ][-1]"');

		$api->$method([ 'foo', 'bar'], -1);
	}

	public function testAccArrayInt_range() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('acc', 'int');

		$this->expectException(api\OperatorException::class);
		$this->expectExceptionMessage('EXPR bad op "[ 2 ][2]"');

		$api->$method([ 'foo', 'bar'], 2);
	}
}
