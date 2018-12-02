<?php

namespace ash;

use ash\token\IToken;



interface IExpressionNormalizer
{

	public function transform(IToken $token) : IToken;
}
