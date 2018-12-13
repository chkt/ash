<?php

namespace ash\token;

use eve\common\projection\IProjectable;



interface IToken
extends IProjectable
{

	const TOKEN_EXPRESSION = 1;

	const TOKEN_EXPRESSION_GROUP = 2;
	const TOKEN_ACCESS_GROUP = 3;
	const TOKEN_CALL_GROUP = 4;

	const TOKEN_EXPRESSION_LIST = 5;

	const TOKEN_NUMBER_LITERAL = 6;
	const TOKEN_STRING_LITERAL = 11;
	const TOKEN_NAME_LITERAL = 7;

	const TOKEN_VALUE = 8;

	const TOKEN_OPERATOR = 9;
	const TOKEN_BINARY_OPERATION = 10;



	public function getType() : int;

	public function getChars() : string;
}
