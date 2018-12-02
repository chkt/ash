<?php

namespace ash;

use ash\token\IToken;



interface INormalizer
{

	public function transform(IToken $token) : IToken;
}
