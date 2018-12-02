<?php

namespace ash\token;



final class Expression
extends AListToken
{

	public function getType() : int {
		return IToken::TOKEN_EXPRESSION;
	}


	protected function _hasInternalWS() : bool {
		return true;
	}


	private function _getOperatorSuccessor(string $char) : string {
		switch ($char) {
			case '(' : return 'expressionGroup';
			default : return 'nameLiteral';
		}
	}

	private function _getOperandSuccessor(string $char) : string {
		switch ($char) {
			case '[' : return 'accessGroup';
			case '(' : return 'callGroup';
			default : return 'binaryOperatorLiteral';
		}
	}


	protected function _getTokenName(array $tokens, string $char) : string {
		$len = count($tokens);

		if ($len === 0) return $this->_getOperatorSuccessor($char);

		$token = $tokens[$len - 1];
		$type = $token->getType();

		switch ($type) {
			case IToken::TOKEN_NAME_LITERAL :
			case IToken::TOKEN_ACCESS_GROUP :
			case IToken::TOKEN_EXPRESSION_GROUP :
			case IToken::TOKEN_CALL_GROUP :
				return $this->_getOperandSuccessor($char);

			case IToken::TOKEN_OPERATOR :
				return $this->_getOperatorSuccessor($char);

			default : throw new \ErrorException($type);
		}
	}
}
