<?php

namespace test;

use PHPUnit\Framework\TestCase;
use ash\Tokenizer;
use ash\token;
use ash\token\IToken;
use ash\token\ITokenFactory;



final class TokenizerTest
extends TestCase
{

	private function _mockFactory() {
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
					case 'expression' : return new token\Expression($factory, $args);
					case 'numberLiteral' : return new token\NumberLiteral();
					case 'stringLiteral' : return new token\StringLiteral();
					case 'nameLiteral' : return new token\NameLiteral();
					case 'binaryOperatorLiteral' : return new token\BinaryOperatorLiteral();
					case 'accessGroup' : return new token\AccessGroup($factory);
					case 'expressionGroup' : return new token\ExpressionGroup($factory);
					case 'ternaryGroup' : return new token\TernaryGroup($factory);
					case 'callGroup' : return new token\CallGroup($factory);
					case 'expressionList' : return new token\ExpressionList($factory, $args);
					case 'ternaryList' : return new token\TernaryList($factory, $args);
					default :
						$this->fail($name);

						return;
				}
			});

		return $factory;
	}

	private function _produceParser(ITokenFactory $factory = null) {
		if (is_null($factory)) $factory = $this->_mockFactory();

		return new Tokenizer($factory);
	}


	public function testParseEmpty() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => []
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('')->getProjection());

		$this->assertEquals('', $parser->parse('')->getChars());
	}


	public function testParseNumberLiteralInt() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '1234567890'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('1234567890')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 1234567890 ')->getProjection());

		$this->assertEquals('1234567890', $parser->parse(' 1234567890 ')->getChars());
	}

	public function testParseNumberLiteralIntZero() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0 ')->getProjection());

		$this->assertEquals('0', $parser->parse(' 0 ')->getChars());
	}

	public function testParseNumberLiteralBin() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0b010'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0b010')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0b010 ')->getProjection());

		$this->assertEquals('0b010', $parser->parse(' 0b010 ')->getChars());
	}

	public function testParseNumberLiteralBinZero() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0b0'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0b0')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0b0 ')->getProjection());

		$this->assertEquals('0b0', $parser->parse(' 0b0 ')->getChars());
	}

	public function testParseNumberLiteralHex() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0x1234567890abcdef'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0x1234567890abcdef')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0x1234567890abcdef ')->getProjection());

		$this->assertEquals('0x1234567890abcdef', $parser->parse('0x1234567890abcdef')->getChars());
	}

	public function testParseNumberLiteralHexZero() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0x0'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0x0')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0x0 ')->getProjection());

		$this->assertEquals('0x0', $parser->parse(' 0x0 ')->getChars());
	}

	public function testParseNumberLiteralFloat() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '12300.00789'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('12300.00789')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 12300.00789 ')->getProjection());

		$this->assertEquals('12300.00789', $parser->parse(' 12300.00789 ')->getChars());
	}

	public function testParseNumberLiteralFloatZero() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0.0'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0.0')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0.0 ')->getProjection());

		$this->assertEquals('0.0', $parser->parse(' 0.0 ')->getChars());
	}

	public function testParseNumberLiteralFloatZeroDot() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '0.012'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('0.012')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 0.012 ')->getProjection());

		$this->assertEquals('0.012', $parser->parse(' 0.012 ')->getChars());
	}

	public function testParseNumberLiteralFloatDotZero() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '120.0'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('120.0')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 120.0 ')->getProjection());

		$this->assertEquals('120.0', $parser->parse(' 120.0 ')->getChars());
	}

	public function testParseNumberLiteralFloatExponent() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '120.012e120'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('120.012e120')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 120.012e120 ')->getProjection());

		$this->assertEquals('120.012e120', $parser->parse(' 120.012e120 ')->getChars());
	}

	public function testParseNumberLiteralFloatExponentZero() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '120.012e0'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('120.012e0')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 120.012e0 ')->getProjection());

		$this->assertEquals('120.012e0', $parser->parse(' 120.012e0 ')->getChars());
	}

	public function testParseNumberLiteralFloatExponentPositive() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '120.012e+120'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('120.012e+120')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 120.012e+120 ')->getProjection());

		$this->assertEquals('120.012e+120', $parser->parse(' 120.012e+120 ')->getChars());
	}

	public function testParseNumberLiteralFloatExponentNegative() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '120.012e-120'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('120.012e-120')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 120.012e-120 ')->getProjection());

		$this->assertEquals('120.012e-120', $parser->parse(' 120.012e-120 ')->getChars());
	}


	public function testParseStringLiteralApos() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_STRING_LITERAL,
				'data' => '\'foo\''
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('\'foo\'')->getProjection());
		$this->assertEquals($ast, $parser->parse(' \'foo\' ')->getProjection());

		$this->assertEquals('\'foo\'', $parser->parse('\'foo\'')->getChars());
	}

	public function testParseStringLiteralQuot() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_STRING_LITERAL,
				'data' => '"foo"'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('"foo"')->getProjection());
		$this->assertEquals($ast, $parser->parse(' "foo" ')->getProjection());

		$this->assertEquals('"foo"', $parser->parse('"foo"')->getChars());
	}

	public function testParseStringLiteralEscaping() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_STRING_LITERAL,
				'data' => '"foo bar \\" baz \\qux \\\\"'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('"foo bar \\" baz \\qux \\\\"')->getProjection());
		$this->assertEquals($ast, $parser->parse(' "foo bar \\" baz \\qux \\\\" ')->getProjection());

		$this->assertEquals('"foo bar \\" baz \\qux \\\\"', $parser->parse(' "foo bar \\" baz \\qux \\\\" ')->getChars());
	}


	public function testParseNameLiteral() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo ')->getProjection());

		$this->assertEquals('foo', $parser->parse('foo')->getChars());
	}

	public function testParseAccess() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo.bar.baz')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo . bar . baz ')->getProjection());
		$this->assertEquals($ast, $parser->parse('foo. bar. baz')->getProjection());
		$this->assertEquals($ast, $parser->parse('foo .bar .baz')->getProjection());

		$this->assertEquals('foo . bar . baz', $parser->parse('foo.bar.baz')->getChars());
	}

	public function testParseAccessExpression() {
		$ast = [
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
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'baz'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo[bar].baz')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo [ bar ] . baz ')->getProjection());
		$this->assertEquals($ast, $parser->parse('foo[ bar ].baz')->getProjection());

		$this->assertEquals('foo [bar] . baz', $parser->parse('foo[bar].baz')->getChars());
	}

	public function testParseAccessExpressionChain() {
		$ast = [
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
				'type' => IToken::TOKEN_ACCESS_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'baz'
					]]
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo[bar][baz]')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo [ bar ] [ baz ] ')->getProjection());
		$this->assertEquals($ast, $parser->parse('foo[ bar ][ baz ]')->getProjection());

		$this->assertEquals('foo [bar] [baz]', $parser->parse('foo[bar][baz]')->getChars());
	}

	public function testParseAccessExpressionValue() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_ACCESS_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_STRING_LITERAL,
						'data' => '"bar"'
					]]
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo["bar"]')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo [ "bar" ] ')->getProjection());
		$this->assertEquals($ast, $parser->parse('foo[ "bar" ]')->getProjection());

		$this->assertEquals('foo ["bar"]', $parser->parse('foo["bar"]')->getChars());
	}


	public function testParseBinaryNameName() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'a'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '+'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'b'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '*'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'c'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('a+b*c')->getProjection());
		$this->assertEquals($ast, $parser->parse(' a + b * c ')->getProjection());

		$this->assertEquals('a + b * c', $parser->parse('a+b*c')->getChars());
	}

	public function testParseBinaryNameNumber() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'a'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '+'
			], [
				'type' => Itoken::TOKEN_NUMBER_LITERAL,
				'data' => '1.2'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('a+1.2')->getProjection());
		$this->assertEquals($ast, $parser->parse(' a + 1.2 ')->getProjection());

		$this->assertEquals('a + 1.2', $parser->parse(' a + 1.2 ')->getChars());
	}

	public function testParseBinaryNumberName() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NUMBER_LITERAL,
				'data' => '1.2'
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '+'
			], [
				'type' => Itoken::TOKEN_NAME_LITERAL,
				'data' => 'a'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('1.2+a')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 1.2 + a ')->getProjection());

		$this->assertEquals('1.2 + a', $parser->parse(' 1.2 + a ')->getChars());
	}

	public function testParseBinary_missingOperator() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 2: "a "_"b"');

		$parser->parse('a b');
	}

	public function testParseBinary_leadingOperand() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 1: " "_"+ b"');

		$parser->parse(' + b');
	}

	public function testParseBinary_trailingOperand() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 4: "a + "_""');

		$parser->parse('a + ');
	}

	public function testParseTernaryNameNameName() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [
				['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'a'],
				['type' => IToken::TOKEN_TERNARY_GROUP, 'data' =>
					[ 'type' => IToken::TOKEN_TERNARY_LIST, 'data' => [
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'b']]],
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'c']]]
					]]
				]
			]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('a?b:c')->getProjection());
		$this->assertEquals($ast, $parser->parse(' a ? b : c ')->getProjection());

		$this->assertEquals('a ?b: c', $parser->parse(' a ? b : c ')->getChars());
	}

	public function testParseTernaryNameNameNumber() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [
				['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'a'],
				['type' => IToken::TOKEN_TERNARY_GROUP, 'data' =>
					[ 'type' => IToken::TOKEN_TERNARY_LIST, 'data' => [
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'b']]],
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '1.2']]]
					]]
				]
			]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('a?b:1.2')->getProjection());
		$this->assertEquals($ast, $parser->parse(' a ? b : 1.2 ')->getProjection());

		$this->assertEquals('a ?b: 1.2', $parser->parse(' a ? b : 1.2 ')->getChars());
	}

	public function testParseTernaryNameNumberName() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [
				['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'a'],
				['type' => IToken::TOKEN_TERNARY_GROUP, 'data' =>
					[ 'type' => IToken::TOKEN_TERNARY_LIST, 'data' => [
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '1.2']]],
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'c']]]
					]]
				]
			]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('a?1.2:c')->getProjection());
		$this->assertEquals($ast, $parser->parse(' a ? 1.2 : c ')->getProjection());

		$this->assertEquals('a ?1.2: c', $parser->parse(' a ? 1.2 : c ')->getChars());
	}

	public function testParseTernaryNumberNameName() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [
				['type' => IToken::TOKEN_NUMBER_LITERAL, 'data' => '1.2'],
				['type' => IToken::TOKEN_TERNARY_GROUP, 'data' =>
					[ 'type' => IToken::TOKEN_TERNARY_LIST, 'data' => [
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'b']]],
						['type' => IToken::TOKEN_EXPRESSION, 'data' => [['type' => IToken::TOKEN_NAME_LITERAL, 'data' => 'c']]]
					]]
				]
			]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('1.2?b:c')->getProjection());
		$this->assertEquals($ast, $parser->parse(' 1.2 ? b : c ')->getProjection());

		$this->assertEquals('1.2 ?b: c', $parser->parse(' 1.2 ? b : c ')->getChars());
	}

	public function testParseTernary_missingTest() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 3: " b "_": c"');

		$parser->parse(' b : c');
	}

	public function testParseTernary_missingDefault() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 4: "a ? "_": c"');

		$parser->parse('a ? : c');
	}

	public function testParseTernary_missingAlternative() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 6: "a ? b "_""');

		$parser->parse('a ? b ');
	}

	public function testParseTernary_leadingTest() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 1: " "_"? b"');

		$parser->parse(' ? b');
	}

	public function testParseTernary_trailingTest() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 4: "a ? "_""');

		$parser->parse('a ? ');
	}

	public function testParseTernary_leadingAlternative() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 1: " "_": b"');

		$parser->parse(' : b');
	}

	public function testParseTernary_trailingAlternative() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 4: "a : "_""');

		$parser->parse('a : ');
	}

	public function testParseExpressionGroup() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_EXPRESSION_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'a'
					], [
						'type' => IToken::TOKEN_BINARY_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_EXPRESSION_GROUP,
						'data' => [
							'type' => IToken::TOKEN_EXPRESSION,
							'data' => [[
								'type' => IToken::TOKEN_NAME_LITERAL,
								'data' => 'b'
							], [
								'type' => IToken::TOKEN_BINARY_OPERATOR,
								'data' => '-'
							], [
								'type' => IToken::TOKEN_NAME_LITERAL,
								'data' => 'c'
							]]
						]
					]]
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('(a+(b-c))')->getProjection());
		$this->assertEquals($ast, $parser->parse(' ( a + ( b - c ) ) ')->getProjection());
		$this->assertEquals($ast, $parser->parse('(a + (b - c))')->getProjection());

		$this->assertEquals('(a + (b - c))', $parser->parse('(a+(b-c))')->getChars());
	}

	public function testParseGroupMemberAccess() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_EXPRESSION_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'a'
					], [
						'type' => IToken::TOKEN_BINARY_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'b'
					]]
				]
			], [
				'type' => IToken::TOKEN_BINARY_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'c'
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('(a+b).c')->getProjection());
		$this->assertEquals($ast, $parser->parse(' ( a + b ) . c ')->getProjection());
		$this->assertEquals($ast, $parser->parse('(a + b).c')->getProjection());

		$this->assertEquals('(a + b) . c', $parser->parse('(a+b).c')->getChars());
	}

	public function testParseGroupMemberExpression() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_EXPRESSION_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'a'
					], [
						'type' => IToken::TOKEN_BINARY_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'b'
					]]
				]
			], [
				'type' => IToken::TOKEN_ACCESS_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'c'
					]]
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('(a+b)[c]')->getProjection());
		$this->assertEquals($ast, $parser->parse(' ( a + b ) [ c ] ')->getProjection());
		$this->assertEquals($ast, $parser->parse('(a + b)[ c ]')->getProjection());

		$this->assertEquals('(a + b) [c]', $parser->parse('(a+b)[c]')->getChars());
	}


	public function testParseCall() {
		$ast = [
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
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo(bar,baz)')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo ( bar , baz ) ')->getProjection());
		$this->assertEquals($ast, $parser->parse('foo(bar, baz)')->getProjection());

		$this->assertEquals('foo (bar, baz)', $parser->parse('foo(bar,baz)')->getChars());
	}

	public function testParseNestedCall() {
		$ast = [
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
							'data' => 'qux'
						]]
					], [
						'type' => IToken::TOKEN_EXPRESSION,
						'data' => [[
							'type' => IToken::TOKEN_NAME_LITERAL,
							'data' => 'bar'
						], [
							'type' => IToken::TOKEN_CALL_GROUP,
							'data' => [
								'type' => IToken::TOKEN_EXPRESSION_LIST,
								'data' => [[
									'type' => IToken::TOKEN_EXPRESSION,
									'data' => [[
										'type' => IToken::TOKEN_NAME_LITERAL,
										'data' => 'baz'
									], [
										'type' => IToken::TOKEN_CALL_GROUP,
										'data' => [
											'type' => IToken::TOKEN_EXPRESSION_LIST,
											'data' => []
										]
									]]
								], [
									'type' => IToken::TOKEN_EXPRESSION,
									'data' => [[
										'type' => IToken::TOKEN_NAME_LITERAL,
										'data' => 'bang'
									]]
								]]
							]
						]]
					]]
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo(qux,bar(baz(),bang))')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo ( qux , bar ( baz ( ) , bang ) ) ')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo(qux, bar(baz(), bang))')->getProjection());

		$this->assertEquals('foo (qux, bar (baz (), bang))', $parser->parse('foo(qux,bar(baz(),bang))')->getChars());
	}

	public function testParseCallChain() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_CALL_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => []
				]
			], [
				'type' => IToken::TOKEN_CALL_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => []
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('foo()()')->getProjection());
		$this->assertEquals($ast, $parser->parse(' foo ( ) ( ) ')->getProjection());

		$this->assertEquals('foo () ()', $parser->parse('foo()()')->getChars());
	}

	public function testParseGroupCall() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_EXPRESSION_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION,
					'data' => [[
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'a'
					], [
						'type' => IToken::TOKEN_BINARY_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'b'
					]]
				]
			], [
				'type' => IToken::TOKEN_CALL_GROUP,
				'data' => [
					'type' => IToken::TOKEN_EXPRESSION_LIST,
					'data' => []
				]
			]]
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('(a+b)()')->getProjection());
		$this->assertEquals($ast, $parser->parse('( a + b ) ( ) ')->getProjection());
		$this->assertEquals($ast, $parser->parse('(a + b)()')->getProjection());

		$this->assertEquals('(a + b) ()', $parser->parse('(a+b)()')->getChars());
	}
}
