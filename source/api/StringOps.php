<?php

namespace ash\api;



final class StringOps
extends AOps
{

	public function pinArray(string $key, array $a) : bool {
		return array_key_exists($key, $a);
	}
}
