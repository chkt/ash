<?php

namespace ash\token;

use eve\common\projection\IProjectable;



interface IToken
extends IProjectable
{

	const TOKEN_EXPRESSION = 1;
	const TOKEN_EXPRESSION_GROUP = 2;
	const TOKEN_EXPRESSION_LIST = 3;

	const TOKEN_NAME_LITERAL = 5;
	const TOKEN_ACCESS_GROUP = 7;

	const TOKEN_OPERATOR = 8;
	const TOKEN_CALL_GROUP = 9;

	const TOKEN_BINARY_OPERATION = 10;



	public function getType() : int;

	public function getChars() : string;
}
