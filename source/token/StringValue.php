<?php

declare(strict_types=1);
namespace ash\token;



final class StringValue
extends AValueToken
{

	public function getValue() : string {
		return parent::getValue();
	}
}
