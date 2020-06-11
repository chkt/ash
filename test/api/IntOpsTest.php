<?php

declare(strict_types=1);
namespace test\api;

use PHPUnit\Framework\TestCase;
use ash\api;



final class IntOpsTest
extends TestCase
{

	private function _produceApi() {
		return new api\IntOps();
	}


	public function testAddIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('add', 'int');

		$val = $api->$method(1, 1);
		$this->assertEquals(2, $val);
		$this->assertInternalType('int', $val);
	}

	public function testAddIntFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('add', 'float');

		$val = $api->$method(1, 1.1);
		$this->assertEquals(2.1, $val);
		$this->assertInternalType('float', $val);
	}

	public function testMulIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mul', 'int');

		$val = $api->$method(2, 3);
		$this->assertEquals(6, $val);
		$this->assertInternalType('int', $val);
	}

	public function testMulIntFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mul', 'float');

		$val = $api->$method(3, 0.5);
		$this->assertEquals(1.5, $val);
		$this->assertInternalType('float', $val);
	}

	public function testSubIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('sub', 'int');

		$val = $api->$method(1, 1);
		$this->assertEquals(0, $val);
		$this->assertInternalType('int', $val);
	}

	public function testSubIntFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('sub', 'float');

		$val = $api->$method(1, 0.5);
		$this->assertEquals(0.5, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'int');

		$val = $api->$method(5, 2);
		$this->assertEquals(2, $val);
		$this->assertInternalType('int', $val);
	}

	public function testDivIntInt_zero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'int');

		$this->expectException(api\OperatorException::class);
		$this->expectExceptionMessage('EXPR bad op "1 / 0');

		$api->$method(1, 0);
	}

	public function testDivIntFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(5, 2.0);
		$this->assertEquals(2.5, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivIntFloat_posZero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(1, 0.0);
		$this->assertEquals(INF, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivIntFloat_negZero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(-1, 0.0);
		$this->assertEquals(-INF, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivIntFloat_zerozero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(0, 0.0);
		$this->assertNan($val);
		$this->assertInternalType('float', $val);
	}

	public function testModIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'int');

		$val = $api->$method(5, 2);
		$this->assertEquals(1, $val);
		$this->assertInternalType('int', $val);
	}

	public function testModIntInt_zero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'int');

		$this->expectException(api\OperatorException::class);
		$this->expectExceptionMessage('EXPR bad op "1 % 0');

		$api->$method(1, 0);
	}

	public function testModIntFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'float');

		$val = $api->$method(5, 1.5);
		$this->assertEquals(0.5, $val);
		$this->assertInternalType('float', $val);
	}

	public function testModIntFloat_zero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'float');

		$val = $api->$method(1, 0.0);
		$this->assertNan($val);
		$this->assertInternalType('float', $val);
	}

	public function testLttIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('ltt', 'int');

		$val = $api->$method(1, 2);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(2, 1));
		$this->assertFalse($api->$method(2, 2));
	}

	public function testLteIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('lte', 'int');

		$val = $api->$method(1, 2);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(2, 1));
		$this->assertTrue($api->$method(2, 2));
	}

	public function testGttIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('gtt', 'int');

		$val = $api->$method(2, 1);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(1, 2));
		$this->assertFalse($api->$method(2, 2));
	}

	public function testGteIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('gte', 'int');

		$val = $api->$method(2, 1);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(1, 2));
		$this->assertTrue($api->$method(2, 2));
	}

	public function testTeqIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('teq', 'int');

		$val = $api->$method(2, 2);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(1, 2));
		$this->assertFalse($api->$method(2, 1));
	}

	public function testTneIntInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('tne', 'int');

		$val = $api->$method(2, 2);
		$this->assertFalse($val);
		$this->assertInternalType('boolean', $val);

		$this->assertTrue($api->$method(1, 2));
		$this->assertTrue($api->$method(2, 1));
	}
}
