<?php

namespace ash;

use ash\token\IToken;
use ash\token\ITokenFactory;



final class Tokenizer
implements ITokenizer
{

	private $_factory;


	public function __construct(ITokenFactory $factory) {
		$this->_factory = $factory;
	}


	private function _getErrorMessage(string $source, int $index) {
		$len = strlen($source);
		$start = max($index - 10, 0);
		$end = min($index + 10, $len);

		return sprintf(
			'EXPR failure at %s: %s"%s"_"%s"%s',
			$index,
			$start > 0 ? '…' : '',
			substr($source, $start, $index - $start),
			substr($source, $index, $end - $index),
			$end < $len ? '…' : ''
		);
	}


	public function parse(string $source) : IToken {
		$token = $this->_factory->produce('expression');

		for ($i = 0, $l = strlen($source); $i < $l; $i += 1) {
			$char = $source[$i];

			if (!$token->append($char)) throw new \ErrorException($this->_getErrorMessage($source, $i));
		}

		return $token;
	}
}
