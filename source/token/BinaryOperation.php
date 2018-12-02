<?php

namespace ash\token;



final class BinaryOperation
extends AOperationToken
{

	private $_left;
	private $_right;


	public function __construct(ITokenFactory $factory, array $tokens) {
		parent::__construct($factory, $tokens[0]);

		$this->_left = $tokens[1];
		$this->_right = $tokens[2];
	}


	public function getType() : int {
		return IToken::TOKEN_BINARY_OPERATION;
	}


	protected function _getOperands() : array {
		return [ $this->_left, $this->_right ];
	}
}
