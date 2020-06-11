<?php

namespace ash\token;



abstract class AListToken
implements IListToken, IStreamToken
{

	private $_factory;

	private $_tokens;
	private $_currentToken;


	public function __construct(ITokenFactory $factory, array $tokens) {
		$this->_factory = $factory;

		$this->_tokens = $tokens;
		$this->_currentToken = null;
	}


	public function getChars() : string {
		$chars = [];
		$delim = $this->_getDelimiter() . ($this->_hasInternalWS() ? ' ' : '');

		foreach ($this->_tokens as $token) $chars[] = $token->getChars();

		return implode($delim, $chars);
	}


	public function numChildren() : int {
		return count($this->_tokens);
	}

	public function getChildAt(int $index) : IToken {
		$index = min(max($index, 0), count($this->_tokens) - 1);

		return $this->_tokens[$index];
	}

	public function getChildren() : array {
		return $this->_tokens;
	}

	protected function _hasInternalWS() : bool {
		return true;
	}

	protected function _getDelimiter() : string {
		return '';
	}

	protected function _getMaxChildren() : int {
		return PHP_INT_MAX;
	}


	abstract protected function _getTokenName(array $tokens, string $char) : string;


	private function _appendToNext(string $char) : bool {
		$name = $this->_getTokenName($this->_tokens, $char);
		$token = $this->_factory->produce($name);

		if (!$token->append($char)) return false;

		$this->_tokens[] = $token;
		$this->_currentToken = $token;

		return true;
	}


	public function append(string $char) : bool {
		$delim = $this->_getDelimiter();
		$delimited = strlen($delim) !== 0;

		if (is_null($this->_currentToken)) {
			if (ctype_space($char)) return $this->_hasInternalWS();
			else return $this->_appendToNext($char);
		}

		if ($this->_currentToken->append($char)) return true;

		if (!$delimited) {
			$this->_currentToken = null;

			if (ctype_space($char)) return $this->_hasInternalWS();
			else return $this->_appendToNext($char);
		}

		if ($char === $delim) {
			$this->_currentToken = null;

			return count($this->_tokens) <= $this->_getMaxChildren();
		}

		if (ctype_space($char)) return $this->_hasInternalWS();

		return false;
	}


	public function getProjection() : array {
		$res = [];

		foreach ($this->_tokens as $token) $res[] = $token->getProjection();

		return [
			'type' => $this->getType(),
			'data' => $res
		];
	}
}
