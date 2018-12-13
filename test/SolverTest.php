<?php

namespace test;

use PHPUnit\Framework\TestCase;
use ash\Solver;
use ash\token\IToken;
use ash\token\IListToken;
use ash\token\IOperationToken;
use ash\token\IValueToken;




final class SolverTest
extends TestCase
{

	private function _mockToken(int $type, string $chars) {
		$token = $this
			->getMockBuilder(IToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getChars')
			->willReturn($chars);

		return $token;
	}

	private function _mockValueToken(int $type, $value) {
		$token = $this
			->getMockBuilder(IValueToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getValue')
			->willReturn($value);

		return $token;
	}

	private function _mockOperation(int $type, array $data) {
		$operator = $this->_mockToken($data[0]['type'], $data[0]['data']);
		$operands = [];

		for ($i = 1, $l = count($data); $i < $l; $i += 1) {
			$item = $data[$i];
			$itemType = $item['type'];

			if ($itemType === IToken::TOKEN_NAME_LITERAL) $operands[] = $this->_mockToken($itemType, $item['data']);
			else if ($itemType === IToken::TOKEN_VALUE) $operands[] = $this->_mockValueToken($itemType, $item['data']);
			else if ($itemType === IToken::TOKEN_BINARY_OPERATION) $operands[] = $this->_mockOperation($itemType, $item['data']);
 			else if ($itemType === IToken::TOKEN_EXPRESSION_LIST) $operands[] = $this->_mockList($itemType, $item['data']);
 			else $this->fail($itemType);
		}

		$token = $this
			->getMockBuilder(IOperationToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getOperator')
			->willReturn($operator);

		$token
			->method('getNumOperands')
			->willReturnCallback(function() use ($operands) {
				return count($operands);
			});

		$token
			->method('getOperandAt')
			->with($this->isType('int'))
			->willReturnCallback(function(int $index) use ($operands) {
				return $operands[$index];
			});

		return $token;
	}

	private function _mockList(int $type, array $data) {
		$children = [];

		foreach ($data as $item) {
			$type = $item['type'];

			if ($type === IToken::TOKEN_NAME_LITERAL) $children[] = $this->_mockToken($type, $item['data']);
			else if ($type === IToken::TOKEN_BINARY_OPERATION) $children[] = $this->_mockOperation($type, $item['data']);
			else $this->fail($type);
		}

		$list = $this
			->getMockBuilder(IListToken::class)
			->getMock();

		$list
			->method('numChildren')
			->willReturnCallback(function() use ($children) {
				return count($children);
			});

		$list
			->method('getChildren')
			->willReturn($children);

		return $list;
	}


	private function _produceSolver(IToken $token) {
		return new Solver($token);
	}

	private function _produceExpression(array $ast) {
		$type = $ast['type'];

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL :
			case IToken::TOKEN_OPERATOR :
				return $this->_mockToken($type, $ast['data']);

			case IToken::TOKEN_VALUE :
				return $this->_mockValueToken($type, $ast['data']);

			case IToken::TOKEN_BINARY_OPERATION :
				return $this->_mockOperation($type, $ast['data']);

			case IToken::TOKEN_EXPRESSION_LIST :
				return $this->_mockList($type, $ast['data']);

			default : $this->fail($type);
		}
	}


	public function testSolveInt() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_VALUE,
			'data' => 1200
		]);
		$context = [];

		$solver = $this->_produceSolver($expr);

		$this->assertEquals(1200, $solver->resolve($context));
	}

	public function testSolveFloat() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_VALUE,
			'data' => 120.012e-120
		]);
		$context = [];

		$solver = $this->_produceSolver($expr);

		$this->assertEquals(120.012e-120, $solver->resolve($context));
	}

	public function testSolveString() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_VALUE,
			'data' => 'foo'
		]);
		$context = [];

		$solver = $this->_produceSolver($expr);

		$this->assertEquals('foo', $solver->resolve($context));
	}


	public function testSolveName() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_NAME_LITERAL,
			'data' => 'bar'
		]);
		$context = [ 'bar' => 'foo' ];
		$solver = $this->_produceSolver($expr);

		$this->assertEquals('foo', $solver->resolve($context));
	}

	public function testSolveName_error() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_NAME_LITERAL,
			'data' => 'foo'
		]);

		$context = [];
		$solver = $this->_produceSolver($expr);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR inaccessible "foo"');

		$solver->resolve($context);
	}

	public function testSolveAccess() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		]);

		$context = [ 'bar' => [ 'baz' => 'foo' ]];
		$solver = $this->_produceSolver($expr);

		$this->assertEquals('foo', $solver->resolve($context));
	}

	public function testSolveAccess_error() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			]]
		]);

		$context = [ 'foo' => []];
		$solver = $this->_produceSolver($expr);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR inaccessible "bar"');

		$solver->resolve($context);
	}

	public function testSolveAccess_noName() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			]]
		]);

		$context = [ 'foo' => [ 'bar' => 'baz' ]];
		$solver = $this->_produceSolver($expr);

		$this->expectException(\TypeError::class);

		$solver->resolve($context);
	}

	public function testSolveAccessExpression() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_OPERATOR,
					'data' => '[...]'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'bar'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'foo'
				]]
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		]);
		$context = [
			'foo' => 'quux',
			'bar' => [ 'quux' => [ 'baz' => 'foo']]
		];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals('foo', $solver->resolve($context));
	}

	public function testSolveAccessIntExpression() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '[...]'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 0
			]]
		]);
		$context = [ 'foo' => [ 'bar' ]];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals('bar', $solver->resolve($context));
	}

	public function testSolveAccessStringExpression() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '[...]'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 'bar'
			]]
		]);
		$context = [ 'foo' => [ 'bar' => 'baz' ]];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals('baz', $solver->resolve($context));
	}


	public function testSolveOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		]);
		$context = [ 'bar' => 3, 'baz' => 5 ];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(15, $solver->resolve($context));
	}

	public function testSolveOperation_badType() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1.0
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 'bar'
			]]
		]);
		$context = [];
		$solver = $this->_produceSolver($expr);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR undefined for "double", "string"');

		$solver->resolve($context);
	}


	public function testSolveOperationPrecedence() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_OPERATOR,
					'data' => '-'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'baz'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'foo'
				]]
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			]]
		]);
		$context = [ 'foo' => 1, 'bar' => 3, 'baz' => 5 ];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(12, $solver->resolve($context));
	}

	public function testSolveIntIdentifierOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => Itoken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1200
 			], [
 				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		]);

		$solver = $this->_produceSolver($expr);

		$this->assertSame(1201, $solver->resolve([ 'foo' => true ]));
		$this->assertSame(1202, $solver->resolve([ 'foo' => 2 ]));
		$this->assertSame(1200.12, $solver->resolve([ 'foo' => 0.12]));
	}

	public function testSolveFloatIdentifierOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => Itoken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 10.01
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		]);

		$solver = $this->_produceSolver($expr);

		$this->assertSame(11.01, $solver->resolve([ 'foo' => true ]));
		$this->assertSame(12.01, $solver->resolve([ 'foo' => 2 ]));
		$this->assertSame(10.13, $solver->resolve([ 'foo' => 0.12]));
	}


	public function testCall() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => 'call'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_EXPRESSION_LIST,
				'data' => [[
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'bar'
				], [
					'type' => IToken::TOKEN_BINARY_OPERATION,
					'data' => [[
						'type' => IToken::TOKEN_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'baz'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'quux'
					]]
				]]
			]]
		]);
		$context = [
			'foo' => function($a, $b) { return $a * $b; },
			'bar' => 3,
			'baz' => 4,
			'quux' => 3
		];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(21, $solver->resolve($context));
	}

	public function testCallChain() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => 'call'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_OPERATOR,
					'data' => 'call'
				], [
					'type' => ITOKEN::TOKEN_NAME_LITERAL,
					'data' => 'foo'
				], [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'bar'
					]]
				]]
			], [
				'type' => IToken::TOKEN_EXPRESSION_LIST,
				'data' => []
			]]
		]);
		$context = [
			'bar' => 'baz',
			'foo' => function($value) {
				return function() use ($value) {
					return $value;
				};
			}
		];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals('baz', $solver->resolve($context));
	}
}
