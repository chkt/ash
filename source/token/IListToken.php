<?php

namespace ash\token;



interface IListToken
extends IToken
{

	public function numChildren() : int;

	public function getChildAt(int $index) : IToken;

	public function getChildren() : array;
}
