<?php

namespace ash;



final class Parser
implements IParser
{

	private $_tokenizer;
	private $_normalizer;
	private $_factory;


	public function __construct(
		ITokenizer $tokenizer,
		INormalizer $normalizer,
		ISolverFactory $factory
	) {
		$this->_tokenizer = $tokenizer;
		$this->_normalizer = $normalizer;
		$this->_factory = $factory;
	}


	public function parse(string $expression) : ISolver {
		$raw = $this->_tokenizer->parse($expression);
		$norm = $this->_normalizer->transform($raw);

		return $this->_factory->produce($norm);
	}
}
