<?php

namespace test;

use PHPUnit\Framework\TestCase;
use eve\common\factory\IBaseFactory;
use ash\ITokenizer;
use ash\INormalizer;
use ash\ISolverFactory;
use ash\IParser;
use ash\ParserFactory;
use ash\token\ITokenFactory;



final class ParserFactoryTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

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
				$map = [
					\ash\token\TokenFactory::class => ITokenFactory::class,
					\ash\Tokenizer::class => ITokenizer::class,
					\ash\Normalizer::class => INormalizer::class,
					\ash\SolverFactory::class => ISolverFactory::class,
					\ash\Parser::class => IParser::class
				];

				if (!array_key_exists($qname, $map)) $this->fail($qname);

				return $this->_mockInterface($map[$qname], $args);
			});

		return $base;
	}


	private function _produceFactory(IBaseFactory $baseFactory = null) {
		if (is_null($baseFactory)) $baseFactory = $this->_mockBaseFactory();


		return new ParserFactory($baseFactory);
	}


	public function testProduce() {
		$factory = $this->_produceFactory();
		$parser = $factory->produce();

		$this->assertInstanceOf(IParser::class, $parser);
	}
}
