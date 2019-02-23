<?php

namespace test;

use PHPUnit\Framework\TestCase;
use eve\common\factory\ISimpleFactory;
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

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => $value) {
			if (is_int($key)) $key = 'p' . $key;

			$ins->$key = $value;
		}

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

	private function _mockApiFactory() {
		$factory = $this->_mockInterface(ISimpleFactory::class);

		return $factory;
	}


	private function _produceFactory(IBaseFactory $baseFactory = null, ISimpleFactory $apiFactory = null) {
		if (is_null($baseFactory)) $baseFactory = $this->_mockBaseFactory();
		if (is_null($apiFactory)) $apiFactory = $this->_mockApiFactory();

		return new ParserFactory($baseFactory, $apiFactory);
	}


	public function testProduce() {
		$factory = $this->_produceFactory();
		$parser = $factory->produce();

		$this->assertInstanceOf(IParser::class, $parser);
		$this->assertObjectHasAttribute('p0', $parser);
		$this->assertInstanceOf(ITokenizer::class, $parser->p0);
		$this->assertObjectHasAttribute('p1', $parser);
		$this->assertInstanceOf(INormalizer::class, $parser->p1);
		$this->assertObjectHasAttribute('p2', $parser);
		$this->assertInstanceOf(ISolverFactory::class, $parser->p2);
	}
}
