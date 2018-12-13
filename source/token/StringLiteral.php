<?php

namespace ash\token;



final class StringLiteral
extends AToken
implements ILiteralToken
{

	private $_delim;
	private $_escape;
	private $_complete;


	public function __construct() {
		parent::__construct();

		$this->_delim = null;
		$this->_escape = false;
		$this->_complete = false;
	}


	public function getType() : int {
		return self::TOKEN_STRING_LITERAL;
	}


	protected function _isValid(string $chars, string $char) : bool {
		if ($this->_complete) return false;

		if (strlen($chars) === 0) {
			if (strpos('\'"', $char) === false) return false;

			$this->_delim = $char;

			return true;
		}

		if ($char === $this->_delim && !$this->_escape) $this->_complete = true;

		if ($char === '\\') $this->_escape = !$this->_escape;
		else $this->_escape = false;

		return true;
	}


	public function getValueType() : string {
		return self::TYPE_STRING;
	}
}
