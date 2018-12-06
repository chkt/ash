<?php

namespace ash\token;



final class NumberLiteral
extends AToken
implements ILiteralToken
{

	private $_state;
	private $_index;
	private $_length;


	public function __construct() {
		parent::__construct();

		$this->_state = 0;
		$this->_index = -1;
		$this->_length = 0;
	}


	public function getType() : int {
		return IToken::TOKEN_NUMBER_LITERAL;
	}

	public function getValueType() : string {
		switch ($this->_state) {
			case 0 : return self::TYPE_INT_DEC;
			case 4 : return self::TYPE_INT_BIN;
			case 5 : return self::TYPE_INT_HEX;
			default : return self::TYPE_FLOAT;
		}
	}


	private function _setState(int $next, int $index = 1) : bool {
		$this->_state = $next;
		$this->_index = $index;
		$this->_length += 1;

		return true;
	}

	private function _testCharacter(string $charset, string $char) : bool {
		$lo = strtolower($char);
		$index = strpos($charset, $lo);

		if ($index === false) return false;

		$state = $this->_state;

		if ($state === 0) {
			if ($lo === '.') return $this->_setState(1, $this->_length);
			else if ($lo === 'b') return $this->_setState(4);
			else if ($lo === 'x') return $this->_setState(5);
		}
		else if ($state === 1 && $lo === 'e') return $this->_setState(2, $this->_length);
		else if ($state === 2 && ($lo === '+' || $lo === '-')) return $this->_setState(3, $this->_length);

		$this->_length += 1;

		return true;
	}

	private function _selectComponentCharset(string $chars, array $charsets) : string {
		$last = $this->_length - 1;
		$offset = $last - $this->_index;

		if ($offset === 0) return $charsets[0];
		else if ($offset === 1 && $chars[$last] === '0') return $charsets[1];
		else return $charsets[2];
	}


	protected function _isValid(string $chars, string $char) : bool {
		$state = $this->_state;
		$digits = '0123456789';

		if ($state === 0) $charset = $this->_selectComponentCharset($chars, [ $digits, $digits . '.ebx', $digits . '.e' ]);
		else if ($state === 1) $charset = $this->_selectComponentCharset($chars, [ $digits, $digits . 'e', $digits . 'e' ]);
		else if ($state === 2) $charset = $this->_selectComponentCharset($chars, [ $digits . '+-', '', $digits ]);
		else if ($state === 3) $charset = $this->_selectComponentCharset($chars, [ $digits, '', $digits ]);
		else if ($state === 4) $charset = '01';
		else if ($state === 5) $charset = $digits . 'abcdef';
		else throw new \ErrorException($state);

		return $this->_testCharacter($charset, $char);
	}
}
