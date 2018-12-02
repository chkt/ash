<?php

namespace test\model\expr;

use PHPUnit\Framework\TestCase;
use ash\Tokenizer;
use ash\token\IToken;
use ash\token\ITokenFactory;
use ash\token\BinaryOperatorLiteral;
use ash\token\AccessGroup;
use ash\token\Expression;
use ash\token\ExpressionGroup;
use ash\token\NameLiteral;
use ash\token\CallGroup;
use ash\token\ExpressionList;



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
					case 'expression' : return new Expression($factory, $args);
					case 'nameLiteral' : return new NameLiteral();
					case 'binaryOperatorLiteral' : return new BinaryOperatorLiteral();
					case 'accessGroup' : return new AccessGroup($factory);
					case 'expressionGroup' : return new ExpressionGroup($factory);
					case 'callGroup' : return new CallGroup($factory);
					case 'expressionList' : return new ExpressionList($factory, $args);
					default : throw new \ErrorException($name);
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

	public function testParseMemberAccess() {
		$ast = [
			'type' => IToken::TOKEN_EXPRESSION,
			'data' => [[
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'foo'
			], [
				'type' => IToken::TOKEN_OPERATOR,
				'data' => '.'
			], [
				'type' => IToken::TOKEN_NAME_LITERAL,
				'data' => 'bar'
			], [
				'type' => IToken::TOKEN_OPERATOR,
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

	public function testParseMemberExpression() {
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
				'type' => IToken::TOKEN_OPERATOR,
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

	public function testMemberExpressionChain() {
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


	public function testParseOperation() {
		$ast = [
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
		];

		$parser = $this->_produceParser();

		$this->assertEquals($ast, $parser->parse('a+b*c')->getProjection());
		$this->assertEquals($ast, $parser->parse(' a + b * c ')->getProjection());

		$this->assertEquals('a + b * c', $parser->parse('a+b*c')->getChars());
	}

	public function testParseOperation_missingOperator() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 2: "a "_"b"');

		$parser->parse('a b');
	}

	public function testParseOperation_leadingOperand() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 1: " "_"+ b"');

		$parser->parse(' + b');
	}

	public function testParseOperation_trailingOperand() {
		$parser = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('EXPR failure at 4: "a + "_""');

		$parser->parse('a + ');
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
						'type' => IToken::TOKEN_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_EXPRESSION_GROUP,
						'data' => [
							'type' => IToken::TOKEN_EXPRESSION,
							'data' => [[
								'type' => IToken::TOKEN_NAME_LITERAL,
								'data' => 'b'
							], [
								'type' => IToken::TOKEN_OPERATOR,
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
						'type' => IToken::TOKEN_OPERATOR,
						'data' => '+'
					], [
						'type' => IToken::TOKEN_NAME_LITERAL,
						'data' => 'b'
					]]
				]
			], [
				'type' => IToken::TOKEN_OPERATOR,
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
						'type' => IToken::TOKEN_OPERATOR,
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
						'type' => IToken::TOKEN_OPERATOR,
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
