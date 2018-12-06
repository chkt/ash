<?php

namespace test\model\expr;

use PHPUnit\Framework\TestCase;
use ash\token\IToken;
use ash\token\ILiteralToken;
use ash\token\IGroupToken;
use ash\token\IListToken;
use ash\token\ITokenFactory;
use ash\token\ExpressionList;
use ash\token\Operator;
use ash\token\BinaryOperation;
use ash\token\BinaryOperatorLiteral;
use ash\token\IntegerValue;
use ash\token\FloatValue;
use ash\Normalizer;



final class NormalizerTest
extends TestCase
{

	private function _mockToken(int $type, string $chars = '') {
		$token = $this
			->getMockBuilder(IToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getChars')
			->willReturn($chars);

		$token
			->method('getProjection')
			->willReturn([
				'type' => $type,
				'data' => $chars
			]);

		return $token;
	}

	private function _mockLiteralToken(int $type, int $valueType, string $chars) {
		$token = $this
			->getMockBuilder(ILiteralToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getChars')
			->willReturn($chars);

		$token
			->method('getValueType')
			->willReturn($valueType);

		return $token;
	}

	private function _mockListToken(int $type, array $tokens) {
		$token = $this
			->getMockBuilder(IListToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getProjection')
			->willReturnCallback(function() use ($tokens, $type) {
				$data = [];

				foreach ($tokens as $token) $data[] = $token->getProjection();

				return [
					'type' => $type,
					'data' => $data
				];
			});

		$token
			->method('numChildren')
			->willReturnCallback(function() use ($tokens) {
				return count($tokens);
			});

		$token
			->method('getChildren')
			->willReturn($tokens);

		return $token;
	}

	private function _mockGroupToken(int $type, IToken $child) {
		$token = $this
			->getMockBuilder(IGroupToken::class)
			->getMock();

		$token
			->method('getType')
			->willReturn($type);

		$token
			->method('getProjection')
			->willReturn([
				'type' => $type,
				'data' => $child->getProjection()
			]);

		$token
			->method('getChild')
			->willReturn($child);

		return $token;
	}

	private function _mockTokenFactory() {
		$factory = $this
			->getMockBuilder(ITokenFactory::class)
			->getMock();

		$factory
			->method('produce')
			->with(
				$this->isType('string'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $name, array $args) use ($factory) {
				switch ($name) {
					case 'expressionList' : return new ExpressionList($factory, $args);
					case 'binaryOperatorLiteral' : return new BinaryOperatorLiteral();
					case 'operator' : return new Operator($factory, $args);
					case 'binaryOperation' : return new BinaryOperation($factory, $args);
					case 'integerValue' : return new IntegerValue($factory, $args);
					case 'floatValue' : return new FloatValue($factory, $args);
					default : $this->fail($name);
				}
			});

		return $factory;
	}


	private function _produceExpression(array $ast) : IToken {
		$type = $ast['type'];
		$data = $ast['data'];

		if (is_string($data))  {
			if (!array_key_exists('value', $ast)) return $this->_mockToken($type, $data);
			else return $this->_mockLiteralToken($type, $ast['value'], $data);
		}
		else if (array_key_exists('type', $data)) {
			$child = $this->_produceExpression($data);

			return $this->_mockGroupToken($type, $child);
		}
		else {
			$children = [];

			foreach ($data as $item) $children[] = $this->_produceExpression($item);

			return $this->_mockListToken($type, $children);
		}
	}


	private function _produceNormalizer(ITokenFactory $factory = null) {
		if (is_null($factory)) $factory = $this->_mockTokenFactory();

		return new Normalizer($factory);
	}


	public function testNormalizeNumberInt() {
		$token = [ 'type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '1200', 'value' => ILiteralToken::TYPE_INT_DEC ];
		$ast = [ 'type' => IToken::TOKEN_VALUE, 'data' => 1200 ];

		$expr = $this->_produceExpression($token);
		$norm = $this->_produceNormalizer()->transform($expr);

		$this->assertSame($ast, $norm->getProjection());
		$this->assertInstanceOf(IntegerValue::class, $norm);
	}

	public function testNormalizeNumberFloat() {
		$token = [ 'type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '120.012e-120', 'value' => ILiteralToken::TYPE_FLOAT];
		$ast = [ 'type' => IToken::TOKEN_VALUE, 'data' => 120.012e-120 ];

		$expr = $this->_produceExpression($token);
		$norm = $this->_produceNormalizer()->transform($expr);

		$this->assertSame($ast, $norm->getProjection());
		$this->assertInstanceOf(FloatValue::class, $norm);
	}

	public function testNormalizeNumberBin() {
		$token = [ 'type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '0b100000000', 'value' => ILiteralToken::TYPE_INT_BIN ];
		$ast = [ 'type' => IToken::TOKEN_VALUE, 'data' => 256 ];

		$expr = $this->_produceExpression($token);
		$norm = $this->_produceNormalizer()->transform($expr);

		$this->assertSame($ast, $norm->getProjection());
		$this->assertInstanceOf(IntegerValue::class, $norm);
	}

	public function testNormalizeNumberHex() {
		$token = [ 'type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '0xff', 'value' => ILiteralToken::TYPE_INT_HEX ];
		$ast = [ 'type' => IToken::TOKEN_VALUE, 'data' => 255 ];

		$expr = $this->_produceExpression($token);
		$norm = $this->_produceNormalizer()->transform($expr);

		$this->assertSame($ast, $norm->getProjection());
		$this->assertInstanceOf(IntegerValue::class, $norm);
	}


	public function testNormalizeLiteral() {
		$token = [
			'type' => IToken::TOKEN_NAME_LITERAL,
			'data' => 'foo'
		];

		$expr = $this->_produceExpression($token);
		$ast = $token;

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeOperator() {
		$token = [
			'type' => IToken::TOKEN_OPERATOR,
			'data' => '+'
		];

		$expr = $this->_produceExpression($token);
		$ast = $token;

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeExpression() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		]);
		$ast = [
			'type' => IToken::TOKEN_NAME_LITERAL,
			'data' => 'foo'
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeExpressionGroup() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION_GROUP,
			'data' => [
				'type' => IToken::TOKEN_EXPRESSION,
				'data' => [[
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'foo'
				]]
			]
		]);
		$ast = [
			'type' => IToken::TOKEN_NAME_LITERAL,
			'data' => 'foo'
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeAccessGroup() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_ACCESS_GROUP,
			'data' => [
				'type' => IToken::TOKEN_EXPRESSION,
				'data' => [[
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'foo'
				]]
			]
		]);
		$ast = [
			'type' => IToken::TOKEN_NAME_LITERAL,
			'data' => 'foo'
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeExpressionList() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION_LIST,
			'data' => [[
				'type' => IToken::TOKEN_EXPRESSION,
				'data' => [[
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'foo'
				]]
			], [
				'type' => IToken::TOKEN_EXPRESSION,
				'data' => [[
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'bar'
				]]
			]]
		]);
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION_LIST,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());;
	}

	public function testNormalizeCallGroup() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_CALL_GROUP,
			'data' => [
				'type' => IToken::TOKEN_EXPRESSION_LIST,
				'data' => [[
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'foo'
					]]
				], [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'bar'
					]]
				]]
			]
		]);
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION_LIST,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeMalformed() {
		$expr = $this->_produceExpression([
			'type' => -1,
			'data' => 'foo'
		]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR invalid target "foo"');

		$this->_produceNormalizer()->transform($expr);
	}


	public function testNormalizeOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'a'
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'b'
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'c'
			]]
		]);
		$ast = [
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'a'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_OPERATOR,
					'data' => '*'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'b'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'c'
				]]
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeAccess() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_ACCESS_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'bar'
					]]
				]
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		]);
		$ast = [
			'type' => ITOKEN::TOKEN_BINARY_OPERATION,
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
					'data' => 'foo'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'bar'
				]]
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeGroup() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_EXPRESSION_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'a'
					], [
						'type' => IToken::TOKEN_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'b'
					]]
				]
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'c'
			]]
		]);

		$ast = [
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATION,
				'data' => [[
					'type' => IToken::TOKEN_OPERATOR,
					'data' => '+'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'a'
				], [
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'b'
				]]
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'c'
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}


	 public function testNormalizeIdentifierValueOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '1200',
				'value' => ILiteralToken::TYPE_INT_DEC
			]]
		]);
		$ast = [
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1200
			]]
		];

		$norm = $this->_produceNormalizer()->transform($expr);

		$this->assertSame($ast, $norm->getProjection());
	}

	public function testNormalizeValueIdentifierOperation() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '1200',
				'value' => ILiteralToken::TYPE_INT_DEC
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		]);
		$ast = [
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_VALUE,
				'data' => 1200
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		];

		$norm = $this->_produceNormalizer()->transform($expr);

		$this->assertSame($ast, $norm->getProjection());
	}


	public function testNormalizeCall() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_CALL_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => [[
						'type' => IToken::TOKEN_EXPRESSION,
						'data' => [[
							'type' => IToken::TOKEN_NAME_LITERAL,
							'data' => 'bar'
						]]
					], [
						'type' => IToken::TOKEN_EXPRESSION,
						'data' => [[
							'type' => IToken::TOKEN_NAME_LITERAL,
							'data' => 'baz'
						]]
					]]
				]
			]]
		]);

		$ast = [
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
					'type' => IToken::TOKEN_NAME_LITERAL,
					'data' => 'baz'
				]]
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}

	public function testNormalizeCallChain() {
		$expr = $this->_produceExpression([
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_CALL_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => [[
						'type' => IToken::TOKEN_EXPRESSION,
						'data' => [[
							'type' => IToken::TOKEN_NAME_LITERAL,
							'data' => 'bar'
						]]
					]]
				]
			], [
				'type' => IToken::TOKEN_CALL_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => []
				]
			]]
		]);
		$ast = [
			'type' => IToken::TOKEN_BINARY_OPERATION,
			'data' => [[
				'type' => Itoken::TOKEN_OPERATOR,
				'data' => 'call'
			], [
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
					]]
				]]
			], [
				'type' => IToken::TOKEN_EXPRESSION_LIST,
				'data' => []
			]]
		];

		$fast = $this->_produceNormalizer()->transform($expr);
		$this->assertEquals($ast, $fast->getProjection());
	}
}
