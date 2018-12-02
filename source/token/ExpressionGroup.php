<?php

namespace ash\token;



final class ExpressionGroup
extends AGroupToken
{

	public function getType() : int {
		return IToken::TOKEN_EXPRESSION_GROUP;
	}


	protected function _getTargetName() : string {
		return 'expression';
	}

	protected function _getLeadingSymbol() : string {
		return '(';
	}

	protected function _getTrailingSymbol() : string {
		return ')';
	}
}
