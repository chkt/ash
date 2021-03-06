<?php

namespace ash\token;



final class NameLiteral
extends AToken
implements ILiteralToken
{

	public function getType() : int {
		return IToken::TOKEN_NAME_LITERAL;
	}


	protected function _isValid(string $chars, string $char) : bool {
		$list = 'abcdefghijklmnopqrstuvwxyz_';

		if (!empty($chars)) $list .= '01234567890';

		return stripos($list, $char) !== false;
	}


	public function getValueType() : string {
		return self::TYPE_NAME;
	}
}
