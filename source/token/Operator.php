<?php

namespace ash\token;



final class Operator
implements IOperatorToken
{

	private $_chars;
	private $_precedence;
	private $_associativity;


	public function __construct(ITokenFactory $factory, array $data) {
		$len = count($data);

		if ($len < 3) throw new \ErrorException();

		$this->_chars = $data[0];
		$this->_precedence = $data[1];
		$this->_associativity = $data[2];
	}


	public function getType() : int {
		return IToken::TOKEN_BINARY_OPERATOR;
	}

	public function getChars() : string {
		return $this->_chars;
	}


	public function getPrecedence() : int {
		return $this->_precedence;
	}

	public function getAssociativity() : int {
		return $this->_associativity;
	}


	public function getProjection() : array {
		return [
			'type' => $this->getType(),
			'data' => $this->getChars()
		];
	}
}
