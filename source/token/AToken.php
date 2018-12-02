<?php

namespace ash\token;



abstract class AToken
implements IToken, IStreamToken
{

	private $_chars;


	public function __construct() {
		$this->_chars = '';
	}


	public function getChars() : string {
		return $this->_chars;
	}


	abstract protected function _isValid(string $chars, string $char) : bool;


	public function append(string $char) : bool {
		if ($this->_isValid($this->_chars, $char)) {
			$this->_chars .= $char;

			return true;
		}
		else return false;
	}


	public function getProjection() : array {
		return [
			'type' => $this->getType(),
			'data' => $this->getChars()
		];
	}
}
