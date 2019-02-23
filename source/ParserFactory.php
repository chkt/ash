<?php

namespace ash;

use eve\common\IFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\IBaseFactory;
use ash\token\TokenFactory;



final class ParserFactory
implements IFactory
{

	private $_baseFactory;
	private $_apiFactory;


	public function __construct(
		IBaseFactory $baseFactory,
		ISimpleFactory $apiFactory
	) {
		$this->_baseFactory = $baseFactory;
		$this->_apiFactory = $apiFactory;
	}


	public function produce() : IParser {
		$base = $this->_baseFactory;

		$api = $this->_apiFactory->produce();
		$tokens = $base->produce(TokenFactory::class, [ $base ]);

		return $base->produce(Parser::class, [
			$base->produce(Tokenizer::class, [ $tokens ]),
			$base->produce(Normalizer::class, [ $tokens ]),
			$base->produce(SolverFactory::class, [ $base, $api ])
		]);
	}
}
