<?php

namespace ash\token;

use eve\common\factory\IInstancingFactory;



interface ITokenFactory
extends IInstancingFactory
{

	public function produce(string $name, array $args = []) : IToken;
}
