<?php

namespace ash;

use eve\common\IFactory;
use eve\common\factory\IBaseFactory;
use ash\token\TokenFactory;



final class ParserFactory
implements IFactory
{

	private $_baseFactory;


	public function __construct(IBaseFactory $baseFactory) {
		$this->_baseFactory = $baseFactory;
	}


	public function produce() : IParser {
		$base = $this->_baseFactory;

		$tokens = $base->produce(TokenFactory::class, [ $base ]);

		return $base->produce(Parser::class, [
			$base->produce(Tokenizer::class, [ $tokens ]),
			$base->produce(Normalizer::class, [ $tokens ]),
			$base->produce(SolverFactory::class, [ $base ])
		]);
	}
}
