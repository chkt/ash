<?php

namespace ash\api;



final class OperatorException
extends \ErrorException
implements IOperatorException
{

	public function __construct(string $op) {
		parent::__construct('EXPR bad op "' . $op .'"');
	}
}
