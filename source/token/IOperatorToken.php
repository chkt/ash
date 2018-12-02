<?php

namespace ash\token;



interface IOperatorToken
extends IToken
{

	public function getPrecedence() : int;

	public function getAssociativity() : int;
}
