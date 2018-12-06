<?php

namespace ash\token;



interface ILiteralToken
extends IToken
{

	const TYPE_INT_DEC = 1;
	const TYPE_INT_BIN = 2;
	const TYPE_INT_HEX = 3;

	const TYPE_FLOAT = 4;

	const TYPE_NAME = 5;



	public function getValueType() : string;
}
