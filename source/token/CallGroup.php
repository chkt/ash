<?php

namespace ash\token;



final class CallGroup
extends AGroupToken
{

	public function getType() : int {
		return IToken::TOKEN_CALL_GROUP;
	}


	protected function _getTargetName() : string {
		return 'expressionList';
	}

	protected function _getLeadingSymbol() : string {
		return '(';
	}

	protected function _getTrailingSymbol() : string {
		return ')';
	}
}
