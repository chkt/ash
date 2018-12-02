<?php

namespace ash\token;



final class BinaryOperatorLiteral
extends AToken
{

	public function getType() : int {
		return IToken::TOKEN_OPERATOR;
	}


	protected function _isValid(string $chars, string $char) : bool {
		$map = [
			'' => '.+-*/%'
		];

		return
			array_key_exists($chars, $map) &&
			strpos($map[$chars], $char) !== false;
	}
}
