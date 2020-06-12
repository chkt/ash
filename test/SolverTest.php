<?php

namespace test;

use \ErrorException;
use PHPUnit\Framework\TestCase;
use eve\common\IHost;
use ash\api;
use ash\token;
use ash\token\IToken;
use ash\Solver;




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
			->getMockBuilder(token\IValueToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getChars')
			->willReturn((string) $value);

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
			else if ($itemType === IToken::TOKEN_BRANCH) $operands[] = $this->_mockBranch($itemType, $item['data'], $data[0]['data']);
 			else if ($itemType === IToken::TOKEN_EXPRESSION_LIST) $operands[] = $this->_mockList($itemType, $item['data']);
 			else $this->fail($itemType);
		}

		$token = $this
			->getMockBuilder(token\IOperationToken::class)
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

	private function _mockBranch(int $type, array $data, string $op) {
		$children = [];

		foreach ($data as $item) {
			$childType = $item['type'];

			if ($childType === IToken::TOKEN_NAME_LITERAL) $children[] = $this->_mockToken($childType, $item['data']);
			else if ($childType === IToken::TOKEN_BINARY_OPERATION) $children[] = $this->_mockOperation($childType, $item['data']);
			else $this->fail($childType);
		}

		$branch = $this
			->getMockBuilder(token\IBranchToken::class)
			->getMock();

		$branch
			->method('getType')
			->willReturn($type);

		$branch
			->method('getBranchIndex')
			->willReturnCallback(function ($value) use ($op) {
				if ($op === 'abc') return (int)!(bool)$value;
				else if ($op === 'anb') return (int)(bool)$value - 1;
				else if ($op === 'aab') return (int)!(bool)$value - 1;
				else throw new ErrorException();
			});

		$branch
			->method('getChildAt')
			->willReturnCallback(function ($index) use ($children) {
				return $children[$index];
			});

		return $branch;
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
			->getMockBuilder(token\IListToken::class)
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

	private function _mockArrayOps() {
		$ops = $this
			->getMockBuilder(api\IOps::class)
			->setMethods([ 'getMethodName', 'accString' ])
			->getMock();

		$ops
			->method('getMethodName')
			->willReturnMap([
				['acc', 'string', 'accString'],
				['ace', 'string', 'accString']
			]);

		$ops
			->method('accString')
			->willReturnCallback(function(array $source, string $prop) {
				if (!array_key_exists($prop, $source)) $this->fail();

				return $source[$prop];
			});

		return $ops;
	}

	private function _mockIntOps() {
		$ops = $this
			->getMockBuilder(api\IOps::class)
			->setMethods([ 'getMethodName', 'addInt', 'mulInt' ])
			->getMock();

		$ops
			->method('getMethodName')
			->willReturnCallback(function(string $op, string $type) {
				return $op . ucfirst($type);
			});

		$ops
			->method('addInt')
			->willReturnCallback(function(int $a, int $b) {
				return $a + $b;
			});

		$ops
			->method('mulInt')
			->willReturnCallback(function(int $a, int $b) {
				return $a * $b;
			});

		return $ops;
	}

	private function _mockFnOps() {
		$ops = $this
			->getMockBuilder(api\IOps::class)
			->setMethods([ 'getMethodName', 'runArray' ])
			->getMock();

		$ops
			->method('getMethodName')
			->willReturnMap([
				[ 'run', 'array', 'runArray']
			]);

		$ops
			->method('runArray')
			->willReturnCallback(function(callable $fn, array $args) {
				return $fn(...$args);
			});

		return $ops;
	}

	private function _mockBoolOps() {
		$ops = $this
			->getMockBuilder(api\IOps::class)
			->setMethods([ 'getMethodName', 'boolInt' ])
			->getMock();

		$ops
			->method('getMethodName')
			->willReturnMap([
				[ 'bool', 'int', 'boolInt' ]
			]);

		$ops
			->method('boolInt')
			->willReturnCallback(function(int $n) {
				return $n !== 0;
			});

		return $ops;
	}

	private function _mockApi(
		api\IOps $arrayOps = null,
		api\IOps $intOps = null,
		api\IOps $fnOps = null,
		api\IOps $boolOps = null
	) {
		if (is_null($boolOps)) $boolOps = $this->_mockBoolOps();
		if (is_null($arrayOps)) $arrayOps = $this->_mockArrayOps();
		if (is_null($intOps)) $intOps = $this->_mockIntOps();
		if (is_null($fnOps)) $fnOps = $this->_mockFnOps();

		$api = $this
			->getMockBuilder(IHost::class)
			->getMock();

		$api
			->method('hasKey')
			->willReturnMap([
				[ 'op-bool', true ],
				[ 'op-array', true ],
				[ 'op-int', true ],
				[ 'op-float', false],
				[ 'op-fn', true ]
			]);

		$api
			->method('getItem')
			->willReturnMap([
				[ 'op-bool', $boolOps ],
				[ 'op-array', $arrayOps ],
				[ 'op-int', $intOps ],
				[ 'op-fn', $fnOps ]
			]);

		return $api;
	}


	private function _produceSolver(IToken $token, IHost $api = null) {
		if (is_null($api)) $api = $this->_mockApi();

		return new Solver($api, $token);
	}

	private function _produceExpression(array $ast) {
		$type = $ast['type'];

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL :
			case IToken::TOKEN_BINARY_OPERATOR :
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


	public function testSolveValue() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_VALUE,
			'data' => 1200
		]);
		$context = [];

		$solver = $this->_produceSolver($expr);

		$this->assertEquals(1200, $solver->resolve($context));
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

	public function testSolveAccess() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'acc'
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

	public function testSolveAccessExpression_name() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'acc'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_BINARY_OPERATOR,
					'data' => 'ace'
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

	public function testSolveAccessExpression_value() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'ace'
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


	public function testSolveBinaryOperation_name() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'add'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		]);
		$context = [ 'bar' => 1, 'baz' => 2 ];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(3, $solver->resolve($context));
	}

	public function testSolveBinaryOperation_value() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'add'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1200
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		]);

		$solver = $this->_produceSolver($expr);

		$this->assertSame(1202, $solver->resolve([ 'foo' => 2 ]));
	}

	public function testSolveBinaryOperation_noApi() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'add'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1.1
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 0.9
			]]
		]);
		$solver = $this->_produceSolver($expr);

		$this->expectException(ErrorException::class);
		$this->expectExceptionMessage('EXPR no ops "float"');

		$solver->resolve([]);
	}

	public function testSolveBinaryOperation_noType() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'add'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 'bar'
			]]
		]);
		$solver = $this->_produceSolver($expr);

		$this->expectException(ErrorException::class);
		$this->expectExceptionMessage('EXPR no op "add int string"');

		$solver->resolve([]);
	}

	public function testSolveBinaryOperationTree() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'mul'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_BINARY_OPERATOR,
					'data' => 'add'
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
		$context = ['foo' => 1, 'bar' => 3, 'baz' => 5];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(18, $solver->resolve($context));
	}


	public function testSolveTernaryOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [
				['type' => IToken::TOKEN_BINARY_OPERATOR, 'data' => 'abc'],
				['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'foo'],
				['type' => IToken::TOKEN_BRANCH, 'data' => [
					['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'bar'],
					['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'baz']
				]]
			]
		]);
		$context = [ 'foo' => 1, 'bar' => 2, 'baz' => 3];

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(2, $solver->resolve($context));
	}

	public function testSolveLogicalOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [
				['type' => IToken::TOKEN_BINARY_OPERATOR, 'data' => 'anb'],
				['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'foo'],
				['type' => IToken::TOKEN_BRANCH, 'data' => [
					['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'bar']
				]]
			]
		]);

		$solver = $this->_produceSolver($expr);
		$this->assertEquals(2, $solver->resolve([ 'foo' => 1, 'bar' => 2 ]));
		$this->assertEquals(0, $solver->resolve([ 'foo' => 0, 'bar' => 2 ]));
	}


	public function testCall() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'run'
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
						'type' => IToken::TOKEN_BINARY_OPERATOR,
						'data' => 'add'
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
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => 'run'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_BINARY_OPERATOR,
					'data' => 'run'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
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


	public function testCastString() {
		$token = $this
			->getMockBuilder(IToken::class)
			->getMock();

		$token
			->method('getChars')
			->willReturn('foo');

		$solver = $this->_produceSolver($token);
		$this->assertEquals('foo', (string) $solver);
	}
}
