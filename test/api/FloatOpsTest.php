<?php

declare(strict_types=1);
namespace test\api;

use PHPUnit\Framework\TestCase;
use ash\api;



final class FloatOpsTest
extends TestCase
{

	private function _produceApi() : api\FloatOps {
		return new api\FloatOps();
	}

	public function testAddFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('add', 'float');

		$val = $api->$method(1.1, 1.1);
		$this->assertEquals(2.2, $val);
		$this->assertInternalType('float', $val);
	}

	public function testAddFloatInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('add', 'int');

		$val = $api->$method(1.1, 1);
		$this->assertEquals(2.1, $val);
		$this->assertInternalType('float', $val);
	}

	public function testMulFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mul', 'float');

		$val = $api->$method(1.5, 0.5);
		$this->assertEquals(0.75, $val);
		$this->assertInternalType('float', $val);
	}

	public function testMulFloatInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mul', 'int');

		$val = $api->$method(1.1, 3);
		$this->assertEquals(3.3, $val);
		$this->assertInternalType('float', $val);
	}

	public function testSubFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('sub', 'float');

		$val = $api->$method(1.5, 1.4);
		$this->assertEquals(0.1, $val);
		$this->assertInternalType('float', $val);
	}

	public function testSubFloatInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('sub', 'int');

		$val = $api->$method(1.5, 1);
		$this->assertEquals(0.5, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(3.5, 1.4);
		$this->assertEquals(2.5, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatFloat_posZero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(1.0, 0.0);
		$this->assertEquals(INF, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatFloat_negZero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(-1.0, 0.0);
		$this->assertEquals(-INF, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatFloat_zerozero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'float');

		$val = $api->$method(0.0, 0.0);
		$this->assertNan($val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'int');

		$val = $api->$method(1.5, 2);
		$this->assertEquals(0.75, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatInt_posZero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'int');

		$val = $api->$method(1.0, 0);
		$this->assertEquals(INF, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatInt_negZero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'int');

		$val = $api->$method(-1.0, 0);
		$this->assertEquals(-INF, $val);
		$this->assertInternalType('float', $val);
	}

	public function testDivFloatInt_zerozero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('div', 'int');

		$val = $api->$method(0.0, 0);
		$this->assertNan($val);
		$this->assertInternalType('float', $val);
	}

	public function testModFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'float');

		$val = $api->$method(1.2, 1.1);
		$this->assertEquals(0.1, $val);
		$this->assertInternalType('float', $val);
	}

	public function testModFloatFloat_zero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'float');

		$val = $api->$method(1.0, 0.0);
		$this->assertNan($val);
		$this->assertInternalType('float', $val);
	}

	public function testModFloatInt() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'int');

		$val = $api->$method(1.1, 1);
		$this->assertEquals(0.1, $val);
		$this->assertInternalType('float', $val);
	}

	public function testModFloatInt_zero() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('mod', 'int');

		$val = $api->$method(1.0, 0);
		$this->assertNan($val);
		$this->assertInternalType('float', $val);
	}

	public function testLttFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('ltt', 'float');

		$val = $api->$method(1.1, 2.2);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(2.2, 1.1));
		$this->assertFalse($api->$method(2.2, 2.2));
		$this->assertFalse($api->$method(NAN, 1.1));
		$this->assertFalse($api->$method(2.2, NAN));
		$this->assertFalse($api->$method(NAN, NAN));
	}

	public function testLteFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('lte', 'float');

		$val = $api->$method(1.1, 2.2);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(2.2, 1.1));
		$this->assertTrue($api->$method(2.2, 2.2));
		$this->assertFalse($api->$method(NAN, 1.1));
		$this->assertFalse($api->$method(2.2, NAN));
		$this->assertFalse($api->$method(NAN, NAN));
	}

	public function testGttFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('gtt', 'float');

		$val = $api->$method(2.2, 1.1);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(1.1, 2.2));
		$this->assertFalse($api->$method(2.2, 2.2));
		$this->assertFalse($api->$method(NAN, 2.2));
		$this->assertFalse($api->$method(1.1, NAN));
		$this->assertFalse($api->$method(NAN, NAN));
	}

	public function testGteFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('gte', 'float');

		$val = $api->$method(2.2, 1.1);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(1.1, 2.2));
		$this->assertTrue($api->$method(2.2, 2.2));
		$this->assertFalse($api->$method(NAN, 2.2));
		$this->assertFalse($api->$method(1.1, NAN));
		$this->assertFalse($api->$method(NAN, NAN));
	}

	public function testTeqFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('teq', 'float');

		$val = $api->$method(2.2, 2.2);
		$this->assertTrue($val);
		$this->assertInternalType('boolean', $val);

		$this->assertFalse($api->$method(1.1, 2.2));
		$this->assertFalse($api->$method(2.2, 1.1));
		$this->assertFalse($api->$method(NAN, 2.2));
		$this->assertFalse($api->$method(2.2, NAN));
		$this->assertFalse($api->$method(NAN, NAN));
	}

	public function testTneFloatFloat() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('tne', 'float');

		$val = $api->$method(2.2, 2.2);
		$this->assertFalse($val);
		$this->assertInternalType('boolean', $val);

		$this->assertTrue($api->$method(1.1, 2.2));
		$this->assertTrue($api->$method(2.2, 1.1));
		$this->assertTrue($api->$method(NAN, 2.2));
		$this->assertTrue($api->$method(2.2, NAN));
		$this->assertTrue($api->$method(NAN, NAN));
	}
}
