<?php

namespace ash\token;



final class AccessGroup
extends AGroupToken
{

	public function getType() : int {
		return IToken::TOKEN_ACCESS_GROUP;
	}


	public function _getTargetName() : string {
		return 'expression';
	}

	public function _getLeadingSymbol() : string {
		return '[';
	}

	public function _getTrailingSymbol() : string {
		return ']';
	}
}
