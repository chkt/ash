<?php

declare(strict_types=1);
namespace ash\token;



final class IntegerValue
extends AValueToken
{

	public function getValue() : int {
		return parent::getValue();
	}
}
