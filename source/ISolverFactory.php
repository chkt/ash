<?php

namespace ash;

use eve\common\IFactory;
use ash\token\IToken;



interface ISolverFactory
extends IFactory

{

	public function produce(IToken $root) : ISolver;
}
