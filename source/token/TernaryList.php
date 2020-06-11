<?php

namespace ash\token;



final class TernaryList
extends AListToken
{

	public function getType() : int {
		return IToken::TOKEN_TERNARY_LIST;
	}


	protected function _getDelimiter() : string {
		return ':';
	}

	protected function _getMaxChildren() : int {
		return 2;
	}


	protected function _getTokenName(array $tokens, string $char) : string {
		return 'expression';
	}
}
