<?php

namespace test\model\expr;

use PHPUnit\Framework\TestCase;
use ash\token\IListToken;
use ash\token\IOperationToken;
use ash\Solver;
use ash\token\IToken;



final class ExpressionSolverTest
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

	public function _mockOperation(int $type, array $data) {
		$operator = $this->_mockToken($data[0]['type'], $data[0]['data']);
		$operands = [];

		for ($i = 1, $l = count($data); $i < $l; $i += 1) {
			$item = $data[$i];
			$itemType = $item['type'];

			if ($itemType === IToken::TOKEN_NAME_LITERAL) $operands[] = $this->_mockToken($itemType, $item['data']);
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

			case IToken::TOKEN_BINARY_OPERATION :
				return $this->_mockOperation($type, $ast['data']);

			case IToken::TOKEN_EXPRESSION_LIST :
				return $this->_mockList($type, $ast['data']);

			default : $this->fail($type);
		}
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

	public function testSolvePrecedence() {
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
