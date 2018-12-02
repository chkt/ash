<?php

namespace ash\token;



interface IOperationToken
extends IToken
{

	const OPERAND_BINARY_BEFORE = 0;
	const OPERAND_BINARY_AFTER = 1;



	public function getOperator() : IToken;


	public function getNumOperands() : int;

	public function getOperandAt(int $index) : IToken;
}
