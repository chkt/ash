<?php

namespace ash\token;



final class ExpressionList
extends AListToken
{

	public function getType() : int {
		return IToken::TOKEN_EXPRESSION_LIST;
	}


	protected function _hasInternalWS() : bool {
		return true;
	}


	protected function _getDelimiter() : string {
		return ',';
	}


	protected function _getTokenName(array $tokens, string $char) : string {
		return 'expression';
	}
}
