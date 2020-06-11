<?php

namespace ash\token;



final class TernaryGroup
extends AGroupToken
{

	public function getType() : int {
		return IToken::TOKEN_TERNARY_GROUP;
	}


	protected function _getLeadingSymbol() : string {
		return '?';
	}

	protected function _getTrailingSymbol() : string {
		return '';
	}

	protected function _getTargetName() : string {
		return 'ternaryList';
	}
}
