<?php

namespace test;

use ash\IParser;
use ash\Normalizer;
use ash\Parser;
use ash\SolverFactory;
use ash\token\TokenFactory;
use ash\Tokenizer;
use eve\common\factory\IBaseFactory;
use PHPUnit\Framework\TestCase;



final class ParserTest
extends TestCase
{

	private function _mockBaseFactory() {
		$base = $this
			->getMockBuilder(IBaseFactory::class)
			->getMock();

		$base
			->method('produce')
			->with(
				$this->isType('string'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) {
				return new $qname(...$args);
			});

		return $base;
	}


	private function _produceParser(IBaseFactory $baseFactory = null) : IParser {
		if (is_null($baseFactory)) $baseFactory = $this->_mockBaseFactory();

		$tokens = $baseFactory->produce(TokenFactory::class, [ $baseFactory ]);

		return $baseFactory->produce(Parser::class, [
			$baseFactory->produce(Tokenizer::class, [ $tokens ]),
			$baseFactory->produce(Normalizer::class, [ $tokens ]),
			$baseFactory->produce(SolverFactory::class, [ $baseFactory ])
		]);
	}


	public function testParse() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a + b');

		$this->assertEquals(5, $solver->resolve([ 'a' => 2, 'b' => 3 ]));
	}
}
