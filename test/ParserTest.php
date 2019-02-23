<?php

namespace test;

use PHPUnit\Framework\TestCase;
use eve\common\factory;
use eve\common\access;
use ash\api;
use ash\IParser;
use ash\Normalizer;
use ash\Parser;
use ash\SolverFactory;
use ash\token\TokenFactory;
use ash\Tokenizer;



final class ParserTest
extends TestCase
{

	private function _produceBaseFactory() : factory\BaseFactory {
		return new factory\BaseFactory();
	}

	private function _produceAccessorFactory(factory\IBaseFactory $base) : access\factory\TraversableAccessorFactory {
		return new access\factory\TraversableAccessorFactory($base);
	}


	private function _produceParser(
		factory\IBaseFactory $baseFactory = null,
		access\operator\IItemAccessorSurrogate $accessorFactory = null
	) : IParser {
		if (is_null($baseFactory)) $baseFactory = $this->_produceBaseFactory();
		if (is_null($accessorFactory)) $accessorFactory = $this->_produceAccessorFactory($baseFactory);

		$tokens = $baseFactory->produce(TokenFactory::class, [ $baseFactory ]);

		return $baseFactory->produce(Parser::class, [
			$baseFactory->produce(Tokenizer::class, [ $tokens ]),
			$baseFactory->produce(Normalizer::class, [ $tokens ]),
			$baseFactory->produce(SolverFactory::class, [
				$baseFactory,
				$baseFactory
					->produce(api\ApiFactory::class,[ $baseFactory, $accessorFactory ])
					->produce(),
			])
		]);
	}


	public function testParse() {
		$parser = $this->_produceParser();
		$solver = $parser->parse('a + b');

		$this->assertEquals(5, $solver->resolve([ 'a' => 2, 'b' => 3 ]));
	}
}
