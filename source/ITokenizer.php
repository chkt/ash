<?php

namespace ash;

use ash\token\IToken;



interface ITokenizer
{

	public function parse(string $expression) : IToken;
}
