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
			case '0' :
			case '1' :
			case '2' :
			case '3' :
			case '4' :
			case '5' :
			case '6' :
			case '7' :
			case '8' :
			case '9' : return 'numberLiteral';
			case '\'' :
			case '"' : return 'stringLiteral';
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
			case IToken::TOKEN_NUMBER_LITERAL :
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
