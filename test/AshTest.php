<?php

namespace test;

use PHPUnit\Framework\TestCase;
use eve\common\factory\BaseFactory;
use ash\IParser;



final class AshTest
extends TestCase
{

	private function _produceParser() : IParser {
		$base = new BaseFactory();
		$factory = $base->produce('ash\\ParserFactory', [
			$base,
			$base->produce('ash\\api\\ApiFactory', [
				$base,
				$base->produce('eve\\common\\access\\factory\\TraversableAccessorFactory', [ $base ])
			])
		]);

		return $factory->produce();
	}


	public function testTernary() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a ? b : c');

		$this->assertEquals(2, $solver->resolve([ 'a' => 1, 'b' => 2, 'c' => 3]));
		$this->assertEquals(2, $solver->resolve([ 'a' => 1.1, 'b' => 2, 'c' => 3]));
		$this->assertEquals(2, $solver->resolve([ 'a' => '1', 'b' => 2, 'c' => 3]));
		$this->assertEquals(3, $solver->resolve([ 'a' => 0, 'b' => 2, 'c' => 3]));
		$this->assertEquals(3, $solver->resolve([ 'a' => 0.0, 'b' => 2, 'c' => 3]));
		$this->assertEquals(3, $solver->resolve([ 'a' => '', 'b' => 2, 'c' => 3]));
	}

	public function testLessThan() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a < b');

		$this->assertEquals(true, $solver->resolve([ 'a' => 1, 'b' => 2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 1.1, 'b' => 2.2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2, 'b' => 1]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2, 'b' => 2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2.2, 'b' => 1.1]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2.2, 'b' => 2.2]));
	}

	public function testLessOrEqual() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a <= b');

		$this->assertEquals(true, $solver->resolve([ 'a' => 1, 'b' => 2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 1.1, 'b' => 2.2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2, 'b' => 1]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2, 'b' => 2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2.2, 'b' => 1.1]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2.2, 'b' => 2.2]));
	}

	public function testGreaterThan() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a > b');

		$this->assertEquals(false, $solver->resolve([ 'a' => 1, 'b' => 2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 1.1, 'b' => 2.2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2, 'b' => 1]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2, 'b' => 2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2.2, 'b' => 1.1]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2.2, 'b' => 2.2]));
	}

	public function testGreaterOrEqual() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a >= b');

		$this->assertEquals(false, $solver->resolve([ 'a' => 1, 'b' => 2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 1.1, 'b' => 2.2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2, 'b' => 1]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2, 'b' => 2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2.2, 'b' => 1.1]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2.2, 'b' => 2.2]));
	}

	public function testPropInObject() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a in b');

		$this->assertEquals(true, $solver->resolve([ 'a' => 'foo', 'b' => [ 'foo' => 1 ] ]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 'foo', 'b' => [] ]));
	}

	public function testStrictEqual() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a == b');

		$this->assertEquals(false, $solver->resolve([ 'a' => false, 'b' => true]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 1, 'b' => 2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 1.1, 'b' => 2.2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => false, 'b' => false]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2, 'b' => 1]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2, 'b' => 2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2.2, 'b' => 1.1]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2.2, 'b' => 2.2]));
	}

	public function testStrictNotEqual() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a != b');

		$this->assertEquals(true, $solver->resolve([ 'a' => false, 'b' => true]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 1, 'b' => 2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 1.1, 'b' => 2.2]));
		$this->assertEquals(false, $solver->resolve([ 'a' => true, 'b' => true]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2, 'b' => 1]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2, 'b' => 2]));
		$this->assertEquals(true, $solver->resolve([ 'a' => 2.2, 'b' => 1.1]));
		$this->assertEquals(false, $solver->resolve([ 'a' => 2.2, 'b' => 2.2]));
	}

	public function testPrecedence() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a + 3 * b && c * 2 + d ? e && f : g || h');

		$this->assertEquals('foo', $solver->resolve([ 'a' => -2, 'b' => 1, 'c' => 2, 'd' => -3, 'e' => 'bar', 'f' => 'foo', 'g' => 'baz', 'h' => 'qux']));
		$this->assertEquals(''   , $solver->resolve([ 'a' => -2, 'b' => 1, 'c' => 2, 'd' => -3, 'e' => ''   , 'f' => 'foo', 'g' => 'baz', 'h' => 'qux']));
		$this->assertEquals('baz', $solver->resolve([ 'a' => -3, 'b' => 1, 'c' => 2, 'd' => -3, 'e' => 'bar', 'f' => 'foo', 'g' => 'baz', 'h' => 'qux']));
		$this->assertEquals('qux', $solver->resolve([ 'a' => -3, 'b' => 1, 'c' => 2, 'd' => -3, 'e' => 'bar', 'f' => 'foo', 'g' => ''   , 'h' => 'qux']));
		$this->assertEquals('baz', $solver->resolve([ 'a' => -2, 'b' => 1, 'c' => 2, 'd' => -4, 'e' => 'bar', 'f' => 'foo', 'g' => 'baz', 'h' => 'qux']));
		$this->assertEquals('qux', $solver->resolve([ 'a' => -2, 'b' => 1, 'c' => 2, 'd' => -4, 'e' => 'bar', 'f' => 'foo', 'g' => ''   , 'h' => 'qux']));
	}
}
